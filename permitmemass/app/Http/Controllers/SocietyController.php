<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Society;

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
        $locations = Society::orderBy('state')
        ->orderBy('city')
        ->orderBy('pincode')
        ->orderBy('name')
        ->paginate(10);
        return view('location.index', compact("locations"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('location.create');
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
            'name' => 'required',
            'noofresidents' => 'required',
            'address1'=>'required',
            'pincode' => 'required',
            'city' => 'required',
            'state' => 'required'
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
        //dd('in controller');
        $location = Society::find($id);
        return view('location.show')->with('location',$location);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $location = Society::find($id);
        return view('location.edit')->with('location',$location);
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
            'name' => 'required',
            'noofresidents' => 'required',
            'address1'=>'required',
            'pincode' => 'required',
            'city' => 'required',
            'state' => 'required'
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
