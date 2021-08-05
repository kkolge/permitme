<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RegUser;
use Illuminate\Support\Facades\Auth;
use App\LinkLocUser;
use App\Society;
use App\Charts\ReportChartLine;
use App\IotData;
use Carbon\Carbon;
use App\User;
use App\vLocDev;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\ExportHelpers;

class RegUsersController extends Controller
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
                
                $regUsersDownload = RegUser::join('location', 'reguser.locationid','=','location.id')
                    ->whereIn('location.id', vLocDev::whereIn('serial_no',session('GDevId'))->pluck('locationid'))
                    ->where ('reguser.isactive','=',true)
                    ->where('location.isactive','=',true)
                    ->select('reguser.name', 'reguser.phoneno', 'reguser.AadharNo', 'reguser.tagid', 'reguser.resiarea', 'reguser.resilandmark', DB::raw('(select case vaccinated when 1 then "Yes" else "No" end) as vaccinated'), 'reguser.firstvaccin', 'reguser.secondvaccin', 'location.name as lname', DB::raw('concat(location.address1, " ", location.address2) as address'), 'location.pincode', 'location.city', 'location.taluka', 'location.district', 'location.state')
                    ->get();
                //$regUsersDownload = DB::select("select r.name, r.phoneno, r.AadharNo, r.tagid, r.resiarea, r.resilandmark, (select case vaccinated when 1 then 'Yes' else 'No' end) as vaccinated, COALESCE(r.firstvaccin,' ') as firstvaccin, COALESCE(r.secondvaccin,' ') as secondvaccin, l.name as lname, concat(l.address1, ' ', l.address2) as address, l.pincode, l.city, l.taluka, l.district, l.state from reguser r join location l on r.locationid = l.id where r.isactive = true and l.isactive = true order by l.state asc, l.district asc, l.taluka asc, l.city asc, l.pincode asc, l.name asc, r.name asc");
                //dd($regUsersDownload); 
                //dd($locationsDownload);
                $colHeaders = array('Name','Phone No.', 'Aadhar No.', 'RFID Tag ID', 'Residential Area', 'Residential Landmark', 'Vaccinated', 'First Vaccin Date', 'Second Vaccin Date', 'Location Name', 'Location Address', 'Location Pincode', 'Location City', 'Location Taluka', 'Location District', 'Location State');
                $listOfFields = array('name','phoneno','AadharNo', 'tagid', 'resiarea', 'resilandmark', 'vaccinated', 'firstvaccin' ,'secondvaccin',  'lname', 'address', 'pincode', 'city', 'taluka', 'district', 'state');
                $fileName = "RegisteredUsers.csv";
                //dd('sending data to export controller');
                $this->generateCSV($fileName, $colHeaders, $regUsersDownload, 16, $listOfFields);
            }
        }

        $regUser = RegUser::join('location', 'reguser.locationid','=','location.id')
        ->whereIn('location.id', vLocDev::whereIn('serial_no',session('GDevId'))->pluck('locationid'))
        ->select('reguser.id','reguser.name', 'reguser.phoneno', 'reguser.tagid', 'reguser.resiarea', 'reguser.resilandmark', 'reguser.vaccinated', 'reguser.isactive', 'location.name as lname')
        ->paginate(20);
        
        if(count($regUser) != 0){
            return view('regusers.index', compact('regUser'));
        }
        else{
            return view('regusers.index',compact('regUser'))->with('error','No users registered for your location');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        

        return view('regusers.create');
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
            'name' => 'required|max:100',
            'phoneno' => 'required|digits:10|gt:0',
            'tagid' => 'required|max:20',
            'coverimage' => 'image|nullable|max:1999',
            'aadharno' => 'max:16',
            'resiarea' => 'required|min:5|max:100',
            'resilandmark' => 'required|min:5|max:150',
            'firstvaccin' => 'date|sometimes',
            'secondvaccin' => 'date|sometimes',
        ]);

        //handling the file upload 
        if($request->hasFile('coverimage')){
            $fileNameWithExt = $request->file('coverimage')->getClientOriginalName();
            //getting the filename
            $filename = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            //getting the extension
            $extension = $request->file('coverimage')->getClientOriginalExtension();
            //final filename
            $fileNameToStore = $filename."_".time().".".$extension;
            //now finally uploading 
            $path = $request->file('coverimage')->storeAs('public/coverimages',$fileNameToStore);
        }
        else{
            $fileNameToStore = 'noImage.jpg';
        }

        $user = new RegUser();
        $user->name = $request->input('name');
        $user->phoneno = $request->input('phoneno');
        $user->coverimage = $fileNameToStore;
        $user->tagid = $request->input('tagid');
        $user->AadharNo = $request->input('aadharno');
        $user->isactive = $request->input('isactive');
        $user->resiarea = $request->input('resiarea');
        $user->resilandmark = $request->input('resilandmark');
        if($request->input('vaccinated') == 1){
            $user->vaccinated = true;
        }
        else{
            $user->vaccinated = false;
        }
        
        $user->firstvaccin = $request->input('firstvaccin');
        $user->secondvaccin = $request->input('secondvaccin');

        //getting location id of the logged in user
        $linkLocId = LinkLocUser::where('userid','=',Auth::user()->id)->first();
        //dd ($linkLocId);
        $loc = Society::where('id','=',$linkLocId->locationid)->first();
        //dd($loc);
        $user->locationid = $loc->id;


        $user ->save();

        return redirect('/reguser')->with('success', 'User added successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
        if(isset($_GET['type']) && !empty($_GET['type'])){
            if($_GET['type']== 'download'){
                $type = 'download';
            }
            else{
                $type = 'normal';
            }
        }
        else{
            $type='normal';
        }
        
        //dd('I am here',$id, $type);
        
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }
        //dd("I am in show", $id);
        $stf = RegUser::where('phoneno','=',$id)->first();
        //dd($id,$stf);
        
        if($type == 'download'){
            $idataReport = iotdata::where('identifier',$id)
            ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
            ->join('vlocdev','iotdata.deviceid','=','vlocdev.serial_no')
            //->where('created_at','<=',Carbon::today()->addDays(1))
            ->select('iotdata.identifier','iotdata.temp','iotdata.spo2','iotdata.hbcount','iotdata.created_at', 'vlocdev.name')
            ->orderBy('iotdata.created_at','desc')->get();

            $colHeaders = array('Identifier', 'Temperature', 'SPO2', 'Pulse Rate', 'Capture Time', 'Caputre Location');
            $listOfFields = array('identifier','temp', 'spo2', 'hbcount', 'created_at', 'name');
            $fileName = "UserData.csv";
            //dd('sending data to export controller');
            $this->generateCSV($fileName, $colHeaders, $idataReport, 6, $listOfFields);

        }
        
        $idata = iotdata::where('identifier',$id)
            ->where('created_at','>=',Carbon::today()->subDays(15))
            //->where('created_at','<=',Carbon::today()->addDays(1))
            ->orderBy('created_at')->paginate(25);
        //dd($idata);

        if($idata->count() > 0){
            $lbl = collect([]);
            $tempVal = collect([]);
            $spo2Val = collect([]);
            foreach ($idata as $i){
                $lbl->push($i->created_at->format('Y-m-d H:i:s'));
                $tempVal->push($i->temp);
                $spo2Val->push($i->spo2);
            }

            $chart1 = new ReportChartLine();
            $chart1->labels($lbl);

            $chart1->dataset('Temperature','line',$tempVal)
                ->backgroundColor('orange')
                ->fill(true);
                
                //->borderColor('orange');
            $chart1->title("Temperature data for last 15 days");
            $chart1->options([
                'responsive' => true,
                'legend' => ['display' => true, 
                    'position' => 'bottom',
                    'align' => 'left',
                    'labels' => ['fontColor' => 'white', ],
                ],
                'title' => ['fontColor' => 'white'],
                'scales' =>[
                    'yAxes' => [
                        'display' => true,
                        'ticks' => ['beginAtZero' => true],
                        'gridLines' => ['display' => true],
                        'grid' => ['fontColor' => 'white'],                    
                    ],
                    'xAxes' => [
                        'display' => true,
                        'ticks' => ['beginAtZero' => true],
                        'gridLines' => ['display' => true],
                        'grid' => ['olor' => 'white']
                    ],
                ],
                'elements' => ['line' => ['borderColor' => 'rgba(255, 165, 0, 0.1)']],
                //'plugins' => '{datalabels: {color: \'white\'}',
            ]);
            
            $chart2 = new ReportChartLine();
            $chart2->labels($lbl);
            $chart2->title("SPO2 Data for last 15 days");
            $chart2->dataset('SPO2','line',$spo2Val)
                ->backgroundColor('blue');
            $chart2->options([
                'responsive' => true,
                'title' => ['fontColor' => 'white'],
                'legend' => ['display' => true, 
                    'position' => 'bottom',
                    'align' => 'left',
                    'labels' => ['fontColor' => 'white', ],
                ],
                'scales' =>[
                    'yAxes' => [
                        'display' => false,
                        'ticks' => ['beginAtZero' => true],
                        'gridLines' => ['display' => true]
                    ],
                    'xAxes' => [
                        'display' => false,
                        'ticks' => ['beginAtZero' => true],
                        'gridLines' => ['display' => true]
                    ],
                ],
                //'plugins' => '{datalabels: {color: \'red\'}, title: {display: true}}',
            ]);
            //$chart->title("SPO2 data for all users");

            return view('regusers.show',compact('stf','chart1', 'chart2'));//->with('stf',$stf);
        }
        else{
            $stf = '';
            return view('regusers.show')->with('error','No data found')->with('stf');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }
        //return ('I am here');

        $user = RegUser::where('phoneno','=',$id)->first();
        //dd($user);
        
        return view('regusers.edit')->with('user', $user);
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
        //dd($request);
        $this -> validate($request, [
            'name' => 'required|max:100',
            'phoneno' => 'required|digits:10',
            'tagid' => 'required|max:20',
            'coverimage' => 'image|nullable|max:1999',
            'aadharno' => 'max:16',
            'resiarea' => 'required|min:5|max:100',
            'resilandmark' => 'required|min:5|max:150',
            'firstvaccin' => 'date|sometimes',
            'secondvaccin' => 'date|sometimes',
        ]);

        //handling the file upload 
        if($request->hasFile('coverimage')){
            $fileNameWithExt = $request->file('coverimage')->getClientOriginalName();
            //getting the filename
            $filename = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            //getting the extension
            $extension = $request->file('coverimage')->getClientOriginalExtension();
            //final filename
            $fileNameToStore = $filename."_".time().".".$extension;
            //now finally uploading 
            $path = $request->file('coverimage')->storeAs('public/coverimages',$fileNameToStore);
        }


        $user = RegUser::find($id);
        $user->name = $request->input('name');
        $user->phoneno = $request->input('phoneno');
        if($request->hasFile('coverimage')){
            $user->coverimage = $fileNameToStore;
        }
        $user->tagid = $request->input('tagid');
        $user->AadharNo = $request->input('aadharno');
        $user->isactive = $request->input('isactive');
        $user->resiarea = $request->input('resiarea');
        $user->resilandmark = $request->input('resilandmark');
        if($request->input('vaccinated') == 1){
            $user->vaccinated = true;
        }
        else{
            $user->vaccinated = false;
        }
        
        $user->firstvaccin = $request->input('firstvaccin');
        $user->secondvaccin = $request->input('secondvaccin');
        //getting location id of the logged in user
        $linkLocId = LinkLocUser::where('userid','=',Auth::user()->id)->first();
        //dd ($linkLocId);
        $loc = Society::where('id','=',$linkLocId->locationid)->first();
        //dd($loc);
        $user->locationid = $loc->id;

            $user ->save();
            return redirect('/reguser')->with('success', 'User added successfully');
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
