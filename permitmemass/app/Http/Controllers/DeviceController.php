<?php

namespace App\Http\Controllers;

use App\Device;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\ExportHelpers;

class DeviceController extends Controller
{
    use ExportHelpers;

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
        if(isset($_GET['type']) && !empty($_GET['type'])){
            if($_GET['type']== 'download'){
                if(Auth::user()->hasRole(['Super Admin'])){
                    $device = DB::select('select d.serial_no, d.devtype, lld.name as installedat, concat(v.name, ":", v.pincode, ":", v.city, ":", v.taluka, ":", v.district, ":", v.state) as location from device d
                    inner join vlocdev v
                        on d.serial_no = v.serial_no
                    inner JOIN LinkLocDev lld
                        on lld.deviceid = d.id
                    where d.isactive = true
                        AND  v.locactive = true
                        and v.linkactive = true
                        and v.devactive = true
                    order by location asc, d.serial_no asc');

                
                }
                /*else if(Auth::user()->hasRole('Location Admin')){
                    $locationsDownload = DB::select("select l.name, l.noofresidents, concat(l.address1, ' ', l.address2) as address, l.pincode, l.city, l.taluka, l.district, l.state, ifnull(m.name,'Parent Location') as parent 
                    from location l 
                    left outer join location m 
                        on m.id = l.parent
                    where id = ? or parent = ? 
                    order BY 
                        parent asc, l.state ASC, l.district asc, l.taluka asc, l.city asc, l.pincode asc, l.name asc", session('GlocationId'), session('GlocationId'));
                }*/
                //dd($locationsDownload);
                $colHeaders = array('Device ID','Device Type', 'Installed At', 'Location Name');
                $listOfFields = array('serial_no','devtype','installedat', 'location');
                $fileName = "Device.csv";
                //dd('sending data to export controller');
                $this->generateCSV($fileName, $colHeaders, $device, 4, $listOfFields);
            }
        }

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
