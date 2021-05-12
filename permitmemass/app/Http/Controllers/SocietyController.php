<?php

namespace App\Http\Controllers;

use App\LinkLocUser;
use Illuminate\Http\Request;
use App\Society;
use \DB;
use Auth;

class SocietyController extends Controller
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
            $locations = Society::orderBy('state')
            ->orderBy('landmark')
            ->orderBy('city')
            ->orderBy('pincode')
            ->orderBy('name')
            ->paginate(20);
        }
        else if(Auth::user()->hasRole('Location Admin')){
           
            $locations = Society::where('id',session('GlocationId'))->orWhere('parent',session('GlocationId'))
            ->orderBy('state')
            ->orderBy('landmark')
            ->orderBy('city')
            ->orderBy('pincode')
            ->orderBy('name')
            ->paginate(20);

            //dd($locations);
        }else if(Auth::user()->hasRole('Site Admin')){
            $locations = Society::where('id','=', session('GlocationId'))          
            ->orderBy('state')
            ->orderBy('landmark')
            ->orderBy('city')
            ->orderBy('pincode')
            ->orderBy('name')
            ->paginate(20);
            
        }

        return view('location.index', compact("locations"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //Only Super Admin can do this
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }

        $locations = Society::where('isactive','=',true)
            ->select (DB::raw("id,CONCAT(name,':',pincode,':',city,':',taluka,':',district,':',state) AS name"))
            ->orderBy('name','asc')
            ->pluck('name','id')
            ->toArray();
        $locations[0] = 'Parent Location';
        //dd($locations);
        /*$locations = Society::where('parent','=',0)
        ->orderBy('state')
        ->orderBy('city')
        ->orderBy('pincode')
        ->orderBy('name')
        ->pluck('name');
        */
        return view('location.create', compact("locations"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Only Super Admin can do this
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }

        //dd($request);
        $this -> validate($request, [
            'name' => 'required',
            'noofresidents' => 'required',
            'address1'=>'required',
            'pincode' => 'required',
            'city' => 'required',
            'state' => 'required',
            'location' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'altitude' => 'required',
        ]);

        $loc = new Society();
        $loc->name = $request->input('name');
        $loc->noofresidents = $request->input('noofresidents');
        $loc->address1 = $request->input('address1');
        if($request->input('address2') != ""){
            $loc->address2 = $request->input('address2');
        }
        $loc->pincode = $request->input('pincode');
        $loc->city = $request->input('city');
        if($request->input('taluka') != ""){
            $loc->taluka = $request->input('taluka');
        }
        if($request->input('district') != ""){
            $loc->district = $request->input('district');
        }
        $loc->state = $request->input('state');
        $loc->isactive = $request->input('isactive');
        $loc->smsnotification = $request->input('sms');
        $loc->parent = $request->input('location');
        $loc->latitude = $request->input('latitude');
        $loc->longitude = $request->input('longitude');
        $loc->altitude = $request->input('altitude');
        $loc->save();

        return redirect('/location')->with('success', 'Location added successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //Only Super Admin can do this
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }
        //dd('in controller');
        $location = Society::find($id);
        $allLocations = Society::where('isactive','=',true)
            ->select (DB::raw("id,CONCAT(name,':',pincode,':',city,':',taluka,':',district,':',state) AS name"))
            ->orderBy('name','asc')
            ->pluck('name','id')
            ->toArray();
        $allLocations[0] = 'Parent Location';
        return view('location.show', compact('location','allLocations'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //Only Super Admin can do this
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }
        $location = Society::find($id);
        $allLocations = Society::where('isactive','=',true)
            ->select (DB::raw("id,CONCAT(name,':',pincode,':',city,':',taluka,':',district,':',state) AS name"))
            ->orderBy('name','asc')
            ->pluck('name','id')
            ->toArray();
        $allLocations[0] = 'Parent Location';
        return view('location.edit', compact('location','allLocations'));
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
        //Only Super Admin can do this
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }
        $this -> validate($request, [
            'name' => 'required',
            'noofresidents' => 'required',
            'address1'=>'required',
            'pincode' => 'required',
            'city' => 'required',
            'state' => 'required',
            'location' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'altitude' => 'required',
        ]);

        $loc = Society::find($id);
        $loc->name = $request->input('name');
        $loc->noofresidents = $request->input('noofresidents');
        $loc->address1 = $request->input('address1');
        if($request->input('address2') != ""){
            $loc->address2 = $request->input('address2');
        }
        $loc->pincode = $request->input('pincode');
        $loc->city = $request->input('city');
        if($request->input('taluka') != ""){
            $loc->taluka = $request->input('taluka');
        }
        if($request->input('district') != ""){
            $loc->district = $request->input('district');
        }
        $loc->state = $request->input('state');
        $loc->isactive = $request->input('isactive');
        $loc->smsnotification = $request->input('sms');
        $loc->parent = $request->input('location');
        $loc->latitude = $request->input('latitude');
        $loc->longitude = $request->input('longitude');
        $loc->altitude = $request->input('altitude');

        if($loc->isDirty()){
            $loc->update();
            return redirect('/location')->with('success', 'Location updated successfully');
        }
        else{
            return redirect('/location')->with('error', 'Data not updated. Same as before');
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
        //
    }
}
