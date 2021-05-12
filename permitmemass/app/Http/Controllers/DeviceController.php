<?php

namespace App\Http\Controllers;

use App\Device;
use Exception;
use Illuminate\Http\Request;
use Auth;

class DeviceController extends Controller
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
        if(Auth::user()->hasRole(['Super Admin'])){
            $dev = Device::orderBy('serial_no')->paginate(10);
        }
        else {
            $dev = Device::
            whereIn('serial_no',session('GDevId'))
            ->orderBy('serial_no')->paginate(10);
        }
        return view('device.index',compact("dev"));
    }

    public function getDevType(){
        return collect(['RFID'=>'RFID', 'KEYBOARD'=>'KEYBOARD', 'OTHER'=>'OTHER']);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }

        $devType = $this->getDevType();
        return view('device.create', compact('devType'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }

        $this -> validate($request, [
            'serialno' => 'required|min:5|max:12',
            'deviceType' => 'required',
        ]);
        
        
            $dev = new Device();
            $dev->serial_no = $request->input('serialno');
            $dev->devtype = $request->input('deviceType');
            $dev->isactive = $request->input('isactive');
            $dev->save();

        return redirect('/device/')->with ('success', 'Device added successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Device  $device
     * @return \Illuminate\Http\Response
     */
    //public function show(Device $device)
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Device  $device
     * @return \Illuminate\Http\Response
     */
    //public function edit(Device $device)
    public function edit($id)
    {
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }

        //return $device;
        $device = Device::find($id);
        $devType = $this->getDevType();
        return view('device.edit', compact('device','devType'));
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Device  $device
     * @return \Illuminate\Http\Response
     */
    //public function update(Request $request, Device $device)
    public function update(Request $request, $id)
    {

        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }

        $this -> validate($request, [
            'serialno' => 'required|min:5|max:12',
            'deviceType' => 'required',
        ]);
        
        $device = Device::find($id);
        $device->serial_no = $request->input('serialno');
        $device->devtype = $request->input('deviceType');
        $device->isactive = $request->input('isactive');

        $device->save();

        return redirect('/device') ->with ('success', 'Device updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function destroy(Device $device)
    {
        //
    }
}
