<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Device;
use App\LinkLocDev;
use App\Society;
use DB;

class LinkLocDevController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $link = DB::table('LinkLocDev')
            ->Join('location','LinkLocDev.locationid','=','location.id')
            ->Join('device','LinkLocDev.deviceid','=','device.id')
            ->select ('location.name','device.serial_no','LinkLocDev.isactive','LinkLocDev.id')
            ->orderBy('location.name')
            ->orderBy('device.serial_no')
            ->paginate(10);
        //return $link;

        return view('linkLocationDevice.index', compact("link"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
        //get list of locations 
        $loc = Society::orderBy('name')->pluck('name','id')->toArray();
        $dev = Device::orderBy('serial_no')->pluck('serial_no','id')->toArray();
        return view('linkLocationDevice.create',compact("loc","dev"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this -> validate($request, [
            'Location' => 'required|numeric|gt:0',
            'Device' => 'required|numeric|gt:0',
        ]);
        //check if the device is attached to some other location
        $devLoc = LinkLocDev::where('deviceid','=',$request->input('Device'))->get()->toArray();
        //dd($devLoc);
        if(count($devLoc) != 0){
            return redirect('/linkLoc')->with('error','Device already linked to other location');
        }
        $lnk = new LinkLocDev();
        $lnk->locationid = $request->input('Location');
        $lnk->deviceid = $request->input('Device');
        $lnk->isactive = $request->input('isactive');

        $lnk->save();

        return redirect('/linkLoc')->with('success','Device linked to Location');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $links = DB::table('LinkLocDev')
            ->Join('location','LinkLocDev.locationid','=','location.id')
            ->Join('device','LinkLocDev.deviceid','=','device.id')
            ->select ('location.name','device.serial_no','LinkLocDev.isactive','LinkLocDev.id')
            ->orderBy('location.name')
            ->get();

        $loc = Society::all()->pluck('name','id')->toArray();
        $dev = Device::all()->pluck('serial_no','id')->toArray();
        $lnk = LinkLocDev::find($id);
//return $lnk;

        return view('linkLocationDevice.edit',compact('links','lnk','loc','dev'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this -> validate($request, [
            'Location' => 'required|numeric|gt:0',
            'Device' => 'required|numeric|gt:0',
        ]);
        
        $lnk = LinkLocDev::find($id);
//TODO: need to add validation here 

        $lnk = LinkLocDev::find($id);
        $lnk->locationid = $request->input('Location');
        $lnk->deviceid = $request->input('Device');
        $lnk->isactive = $request->input('isactive');
        if($lnk->isDirty()){
            $lnk->update();
            return redirect('/linkLoc')->with('success','Device Location link updated successfully');
        }
        else{
            return redirect('/linkLoc')->with('error','Nothing to update');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $lnk = LinkLocDev::find($id);
        $lnk->delete();

        return redirect('/linkLoc')->with('success','Device Location link deleted successfully');

    }
}
