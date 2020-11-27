<?php

namespace App\Http\Controllers;

use App\Device;
use Illuminate\Http\Request;

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
        $dev = Device::orderBy('serial_no')->paginate(10);
        return view('device.index',compact("dev"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('device.create');
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
            'serialno' => 'required|min:5|max:12',
        ]);

        $dev = new Device();
        $dev->serial_no = $request->input('serialno');
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
        //return $device;
        $device = Device::find($id);
        return view('device.edit', compact('device'));
        
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
        $this -> validate($request, [
            'serialno' => 'required|min:5|max:12',
        ]);
        
        $device = Device::find($id);
        $device->serial_no = $request->input('serialno');
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
