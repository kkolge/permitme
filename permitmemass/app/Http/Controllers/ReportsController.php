<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AllDataReport;
use DB;
use App\Device;
use Illuminate\Support\Facades\Auth;
use App\LinkLocUser;
use App\LinkLocDev;
use App\IotData;
use Carbon\Carbon;
use App\Charts\ReportChartLine;
use App\RegUser;
use App\Http\Controllers\RegUsersController;
use App\vLocDev;
use Illuminate\Routing\RedirectController;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * This controller is used to generate all reports. 
     * All report related data is generated in various methods and passed to 
     * the reports.
     */

     /**
     *All Data Report 
     * @return \Illuminate\Http\Response
     */
	/*public function allDataReport(){
        // throw all sensor data 
       
            /*$allData = DB::select('select i.deviceid, d.id, l.locationid,  i.identifier, ln.name, i.temp, i.spo2, i.hbcount, i.created_at from iotdata as i
            join device as d on i.deviceid = d.serial_no
            join LinkLocDev as l on d.id = l.deviceid
            join location as ln on l.locationid = ln.id
            order by i.created_at desc');//->paginate(15);
            
            $allData = DB::table('iotdata')
                ->join('device','iotdata.deviceid','=','device.serial_no')
                ->join('LinkLocDev','LinkLocDev.deviceid','=','device.id')
                ->join('location','LinkLocDev.locationid','=','location.id')
                ->select('location.name as lname', 
                    'iotdata.identifier as identifier',
                    'iotdata.temp as temp', 'iotdata.spo2 as spo2',
                    'iotdata.hbcount as hbcount', 'iotdata.created_at as created_at'
                     )
                ->orderBy('created_at','desc')
                ->paginate(50);

        
      //dd($allData);
        return view('reports.allData', compact('allData'));

    }
    */
    
    public function allDataLocationReport(){
        // throw all sensor data 
       
        /*$allData = DB::select('select i.deviceid, d.id, l.locationid,  i.identifier, ln.name, i.temp, i.spo2, i.hbcount, i.created_at from iotdata as i
        join device as d on i.deviceid = d.serial_no
        join LinkLocDev as l on d.id = l.deviceid
        join location as ln on l.locationid = ln.id
        order by i.created_at desc');//->paginate(15);

        'GDesignation' => 'Site Admin',
                'GlocationName' => $loc1->name,
                'GlocationId'=> $loc1->id,
                'GnoOfResidents' => $loc1->noofresidents,
                'Gcity' => $loc1->city,
                'GisActive' => true,
                session([
            'GDevId' => $devNameList,

        ]);
        */

        /* 
            Adding code for filters
        */
        
        $fLocation = request()->input('location') ?? '*'; // Input::get('location');
        //dd($fLocation, request());        
        

        //dd(session('GDevId'));
        if(Auth::user()->hasRole(['Super Admin','Location Admin'])){
            if($fLocation == '*'){
                $allData = DB::table('iotdata')->whereIn('iotdata.deviceid',session('GDevId'))
                            ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
                            ->join('vlocdev','vlocdev.serial_no','iotdata.deviceid')
                            ->select('iotdata.identifier as identifier',
                            'iotdata.temp as temp', 'iotdata.spo2 as spo2',
                            'iotdata.hbcount as hbcount', 'iotdata.created_at as created_at',
                            'vlocdev.name as lname', 'iotdata.flagstatus')
                            ->orderBy('iotdata.created_at','desc')
                            ->orderBy('iotdata.hbcount', 'desc')
                            ->orderBy('iotdata.spo2','asc')
                            ->orderBy('iotdata.temp','desc')
                            ->paginate(50);
                            //dd(Carbon::today()->subDays(15));
            }
            else{
                $allData = DB::table('iotdata')->whereIn('iotdata.deviceid',session('GDevId'))
                            ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
                            ->join('vlocdev','vlocdev.serial_no','iotdata.deviceid')
                            ->where('vlocdev.name','=',$fLocation)
                            ->select('iotdata.identifier as identifier',
                            'iotdata.temp as temp', 'iotdata.spo2 as spo2',
                            'iotdata.hbcount as hbcount', 'iotdata.created_at as created_at',
                            'vlocdev.name as lname', 'iotdata.flagstatus')
                            ->orderBy('iotdata.created_at','desc')
                            ->orderBy('iotdata.hbcount', 'desc')
                            ->orderBy('iotdata.spo2','asc')
                            ->orderBy('iotdata.temp','desc')
                            ->paginate(50);
            }
            $ddLocation = (vLocDev::whereIn('serial_no',session('GDevId'))->pluck('name','name'))->unique();
        }
        elseif (Auth::user()->hasRole(['Site Admin'])){
            if($fLocation == '*'){
                $allData = DB::table('iotdata')->whereIn('iotdata.deviceid',session('GDevId'))
                            ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
                            ->join('vlocdev','vlocdev.serial_no','iotdata.deviceid')
                            ->join('device','device.serial_no','iotdata.deviceid')
                            ->join('LinkLocDev','LinkLocDev.deviceid','device.id')
                            ->where('LinkLocDev.isactive','=',true)
                            ->select('iotdata.identifier as identifier',
                            'iotdata.temp as temp', 'iotdata.spo2 as spo2',
                            'iotdata.hbcount as hbcount', 'iotdata.created_at as created_at',
                            'vlocdev.name as lname', 'iotdata.flagstatus', 'LinkLocDev.name as dname')
                            ->orderBy('iotdata.created_at','desc')
                            ->orderBy('iotdata.hbcount', 'desc')
                            ->orderBy('iotdata.spo2','asc')
                            ->orderBy('iotdata.temp','desc')
                            ->paginate(50);
            }
            else{
                $allData = DB::table('iotdata')->whereIn('iotdata.deviceid',session('GDevId'))
                            ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
                            ->join('vlocdev','vlocdev.serial_no','iotdata.deviceid')
                            ->join('device','device.serial_no','iotdata.deviceid')
                            ->join('LinkLocDev','LinkLocDev.deviceid','device.id')
                            ->where('LinkLocDev.isactive','=',true)
                            ->where('vlocdev.serial_no','=',$fLocation)
                            ->select('iotdata.identifier as identifier',
                            'iotdata.temp as temp', 'iotdata.spo2 as spo2',
                            'iotdata.hbcount as hbcount', 'iotdata.created_at as created_at',
                            'vlocdev.name as lname', 'iotdata.flagstatus', 'LinkLocDev.name as dname')
                            ->orderBy('iotdata.created_at','desc')
                            ->orderBy('iotdata.hbcount', 'desc')
                            ->orderBy('iotdata.spo2','asc')
                            ->orderBy('iotdata.temp','desc')
                            ->paginate(50);
            }

            
            $ddLocation = array();
                foreach(session('GDevId') as $d){
                    $ddLocation[$d] = $d;
                }

        }
        
        //dd($allData);
        $recCountTotal = DB::table('iotdata')->whereIn('iotdata.deviceid',session('GDevId'))
                    ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
                    ->join('vlocdev','vlocdev.serial_no','iotdata.deviceid')
                    ->select('iotdata.identifier as identifier',
                    'iotdata.created_at as created_at')
                    ->orderBy('iotdata.created_at','desc')
                    ->orderBy('iotdata.hbcount', 'desc')
                    ->orderBy('iotdata.spo2','asc')
                    ->orderBy('iotdata.temp','desc')
                    ->get()->count();
        
        $recCountHighAll = DB::table('iotdata')->whereIn('iotdata.deviceid',session('GDevId'))
        ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
        ->where('temp','>',env('CUTOFF_TEMP'))->where('spo2','<',env('CUTOFF_SPO2'))->where('hbcount','>',env('CUTOFF_PULSE'))
        ->join('vlocdev','vlocdev.serial_no','iotdata.deviceid')
        ->select('iotdata.identifier as identifier',
        'iotdata.temp as temp', 'iotdata.spo2 as spo2',
        'iotdata.hbcount as hbcount', 'iotdata.created_at as created_at',
        'vlocdev.name as lname')
        ->get()->count();

        $recCountHighTemp = DB::table('iotdata')->whereIn('iotdata.deviceid',session('GDevId'))
        ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
        ->join('vlocdev','vlocdev.serial_no','iotdata.deviceid')
        ->where('temp','>',env('CUTOFF_TEMP'))
        ->select('iotdata.temp as temp','iotdata.created_at as created_at')
        ->get()->count();

        $recCountLowSpo2 = DB::table('iotdata')->whereIn('iotdata.deviceid',session('GDevId'))
        ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
        ->join('vlocdev','vlocdev.serial_no','iotdata.deviceid')
        ->where('spo2','<',env('CUTOFF_SPO2'))
        ->select('iotdata.spo2 as spo2', 'iotdata.created_at as created_at')
        ->get()->count();

        $recCountHighPulse = DB::table('iotdata')->whereIn('iotdata.deviceid',session('GDevId'))
        ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
        ->join('vlocdev','vlocdev.serial_no','iotdata.deviceid')
        ->where('hbcount','>',env('CUTOFF_PULSE'))
        ->select('iotdata.hbcount as hbcount', 'iotdata.created_at as created_at')
        ->get()->count();

        //dd(route('/reports/allDataLocationReport'));

        return view('reports.allData', compact('allData','recCountHighTemp','recCountLowSpo2','recCountHighPulse','recCountHighAll','recCountTotal','ddLocation'));

    }


    /**
     * All Abnormal Data Report
     * Shows data of users with All Abnormal Parameters on daily basis
     */
    public function GenerateAllAbnormalReport(){
        //getting the logged in User
       if(count(session('GDevId')) == 0){
            return view('/reports/AllAbnormalReport')->with('error', 'No device associated with your location');
        }

        $allAbnormal15Days = IotData::whereIn('deviceid',session('GDevId'))
        ->where('created_at','>=',Carbon::today()->subDays(15))
        ->where('created_at','<=',Carbon::today()->addDays(1))
        ->where('spo2','<',env('CUTOFF_SPO2'))
        ->where('temp','>',env('CUTOFF_TEMP'))
        ->where('hbcount','>',env('CUTOFF_PULSE'))
        ->select(DB::raw('count(*) as count'),DB::raw('Date(created_at) as date'))
        ->groupBy(DB::raw('Date(created_at)'))
        ->orderBy(DB::raw('Date(created_at)'),'desc')
        ->get();

        //dd($lowSpo215Days);
        //creating data for chart
        $lbl = collect([]);
        $values = collect([]);
        foreach($allAbnormal15Days as $abnormal){
            $lbl->push($abnormal->date);
            $values->push($abnormal->count);
        }
        //dd($lbl);
        //dd($values);


        $abnormalChart = new ReportChartLine();
        //get array for labels
        $abnormalChart->labels($lbl);
        //dd($spo2Chart);
        $abnormalChart->dataset('All Abnormal Parameters', 'bar',$values)
            ->backgroundColor('red');
            //->options=[legend->position('right')];
        //$spo2Chart->title('SPO2 data for last 15 days');


        return view('reports.AllAbnormalReport',compact('allAbnormal15Days', 'abnormalChart'));
        
    }

    public function AllAbnormalDetailsByDate($date){
      
        if(count(session('GDevId'))==0){
            return view('/reports/AllAbnormalReportByDate')->with('error', 'No device associated with your location');
        }

        $abnormalOnDate = IotData::whereIn('iotdata.deviceid',session('GDevId'))
        ->where(DB::raw('Date(iotdata.created_at)'),'=',new Carbon($date))
        ->where('iotdata.spo2','<',env('CUTOFF_SPO2'))
        ->where('iotdata.temp', '>', env('CUTOFF_TEMP'))
        ->where('iotdata.hbcount', '>', env('CUTOFF_PULSE'))
        ->join('vlocdev','iotdata.deviceid','vlocdev.serial_no')
        ->select('iotdata.identifier','iotdata.temp','iotdata.spo2','iotdata.hbcount','iotdata.created_at','vlocdev.name')
        ->orderBy(DB::raw('Date(iotdata.created_at)','desc'))
        ->orderBy('vlocdev.name','desc')
        ->paginate(50);

        //dd($abnormalOnDate);
        
        //dd($lowSpo2OnDate);
        return view('/reports/AllAbnormalDetailsByDate',compact('date','abnormalOnDate'));
    }


    /**
     * SPO2 Low Data Report
     * Shows data of users with SPO2 below env('CUTOFF_SPO2') on daily basis
     */
    public function GenerateSPO2LowReport(){
        //getting the logged in User
       if(count(session('GDevId')) == 0){
            return view('/reports/SPO2Report')->with('error', 'No device associated with your location');
        }

        $lowSpo215Days = IotData::whereIn('deviceid',session('GDevId'))
        ->where('created_at','>=',Carbon::today()->subDays(15))
        ->where('created_at','<=',Carbon::today()->addDays(1))
        ->where('spo2','<',env('CUTOFF_SPO2'))
        ->select(DB::raw('count(*) as count'),DB::raw('Date(created_at) as date'))
        ->groupBy(DB::raw('Date(created_at)'))
        ->orderBy(DB::raw('Date(created_at)'),'desc')
        ->get();

        //dd($lowSpo215Days);
        //creating data for chart
        $lbl = collect([]);
        $values = collect([]);
        foreach($lowSpo215Days as $spo2){
            $lbl->push($spo2->date);
            $values->push($spo2->count);
        }
        //dd($lbl);
        //dd($values);


        $spo2Chart = new ReportChartLine();
        //get array for labels
        $spo2Chart->labels($lbl);
        //dd($spo2Chart);
        $spo2Chart->dataset('Low SPO2 data', 'bar',$values)
            ->backgroundColor('red');
            //->options=[legend->position('right')];
        //$spo2Chart->title('SPO2 data for last 15 days');


        return view('reports.SPO2Report',compact('lowSpo215Days', 'spo2Chart'));
        
    }

    public function SPO2DetailsByDate($date){
      
        if(count(session('GDevId'))==0){
            return view('/reports/SPO2ReportByDate')->with('error', 'No device associated with your location');
        }

        $lowSpo2OnDate = IotData::whereIn('iotdata.deviceid',session('GDevId'))
        ->where(DB::raw('Date(iotdata.created_at)'),'=',new Carbon($date))
        ->where('iotdata.spo2','<',env('CUTOFF_SPO2'))
        ->join('vlocdev','iotdata.deviceid','vlocdev.serial_no')
        ->select('iotdata.identifier','iotdata.temp','iotdata.spo2','iotdata.hbcount','iotdata.created_at','vlocdev.name')
        ->orderBy(DB::raw('Date(iotdata.created_at)','desc'))
        ->orderBy('vlocdev.name','desc')
        ->paginate(50);
        
        //dd($lowSpo2OnDate);
        return view('/reports/SPO2ReportByDate',compact('date','lowSpo2OnDate'));
    }

    /**
     * Function to generate High Temperature report for last 15 days
     */
    public function GenerateTempHighReport(){
        //getting the logged in User
        
        
        if(count(session('GDevId'))==0){
            return view('/reports/TempReport')->with('error', 'No device associated with your location');
        }

        $highTemp15Days = IotData::whereIn('deviceid',session('GDevId'))
        ->where('created_at','>=',Carbon::today()->subDays(15))
        ->where('created_at','<=',Carbon::today()->addDays(1))
        ->where('temp','>',env('CUTOFF_TEMP'))
        ->select(DB::raw('count(*) as count'),DB::raw('Date(created_at) as date'))
        ->groupBy(DB::raw('Date(created_at)'))
        ->orderBy(DB::raw('Date(created_at)','desc'))
        ->get();

        //dd($lowSpo215Days);
        //creating data for chart
        $lbl = collect([]);
        $values = collect([]);
        foreach($highTemp15Days as $temp){
            $lbl->push($temp->date);
            $values->push($temp->count);
        }
        //dd($lbl);
        //dd($values);


        $tempChart = new ReportChartLine();
        //get array for labels
        $tempChart->labels($lbl);
        //dd($spo2Chart);
        $tempChart->dataset('High Temperature Data', 'bar',$values)
            ->backgroundColor('red');
        //$spo2Chart->title('SPO2 data for last 15 days');


        return view('reports.TempReport',compact('highTemp15Days', 'tempChart'));
        
    }

    /**
     * High Temperature report by date
     */

     public function TempDetailsByDate($date){
        if(count(session('GDevId'))==0){
            return view('/reports/TempDetailsByDate')->with('error', 'No device associated with your location');
        }

        $highTempOnDate = IotData::whereIn('iotdata.deviceid',session('GDevId'))
        ->where(DB::raw('Date(iotdata.created_at)'),'=',new Carbon($date))
        ->where('iotdata.temp','>',env('CUTOFF_TEMP'))
        ->join('vlocdev','iotdata.deviceid','vlocdev.serial_no')
        ->select('iotdata.identifier','iotdata.temp','iotdata.spo2','iotdata.hbcount','iotdata.created_at', 'vlocdev.name')
        ->orderBy(DB::raw('Date(iotdata.created_at)','desc'))
        ->orderBy('vlocdev.name', 'asc')
        ->paginate(50);
        //dd($lowSpo2OnDate);

        //if the user is registered, the name should be shown
        
        //dd($lowSpo2OnDate);
        return view('/reports/TempDetailsByDate',compact('date','highTempOnDate'));
   
     }

     /*
     * Function to generate High Pulse Rate report for last 15 days
     */
    public function GenerateHbcountHighReport(){
        //getting the logged in User
        
        
        if(count(session('GDevId'))==0){
            return view('/reports/HbcountReport')->with('error', 'No records');
        }

        $highHbcount15Days = IotData::whereIn('deviceid',session('GDevId'))
        ->where('created_at','>=',Carbon::today()->subDays(15))
        ->where('created_at','<=',Carbon::today()->addDays(1))
        ->where('hbcount','>',env('CUTOFF_PULSE'))
        ->select(DB::raw('count(*) as count'),DB::raw('Date(created_at) as date'))
        ->groupBy(DB::raw('Date(created_at)'))
        ->orderBy(DB::raw('Date(created_at)'), 'desc')
        ->get();

        //dd($lowSpo215Days);
        //creating data for chart
        $lbl = collect([]);
        $values = collect([]);
        foreach($highHbcount15Days as $rec){
            $lbl->push($rec->date);
            $values->push($rec->count);
        }
        //dd($lbl);
        //dd($values);


        $hbcountChart = new ReportChartLine();
        //get array for labels
        $hbcountChart->labels($lbl);
        //dd($spo2Chart);
        $hbcountChart->dataset('High Pulse Rate Data', 'bar',$values)
            ->backgroundColor('red');
        //$spo2Chart->title('SPO2 data for last 15 days');


        return view('reports.HbcountReport',compact('highHbcount15Days', 'hbcountChart'));
        
    }

    /**
     * High HB rate report by date
     */

     public function HbcountDetailsByDate($date){
        if(count(session('GDevId'))==0){
            return view('/reports/HbcountDetailsByDate')->with('error', 'No data available!');
        }

        $highHbcountOnDate = IotData::whereIn('iotdata.deviceid',session('GDevId'))
        ->where(DB::raw('Date(iotdata.created_at)'),'=',new Carbon($date))
        ->where('iotdata.hbcount','>',env('CUTOFF_PULSE'))
        ->join('vlocdev','iotdata.deviceid','vlocdev.serial_no')
        ->select('iotdata.identifier','iotdata.temp','iotdata.spo2','iotdata.hbcount','iotdata.created_at', 'vlocdev.name')
        ->orderBy(DB::raw('Date(iotdata.created_at)'),'desc')
        ->orderBy('vlocdev.name','asc')
        ->paginate(50);
        //dd($lowSpo2OnDate);

        //if the user is registered, the name should be shown
        
        //dd($lowSpo2OnDate);
        return view('/reports/HbcountDetailsByDate',compact('date','highHbcountOnDate'));
   
     }


     /**
      * Report to show data of any user for last 15 days 
      */
      // TODO - NEED TO ADD LOTS OF SECURITY HERE AS THIS CAN BE MISUSED 
      public function UserReportSearch(Request $request){
        

        $id = $request->input('identifier');
        $this -> validate($request, [
            'identifier' => 'required|gt:0|digits:10'
        ]);
        //dd ($id);

        //check if this user is registered with any location 
        $uRName = RegUser::where('phoneno','=',$id)
            ->where('isactive','=',true)
            ->first();

        //getting list of distinct devices where user has visited 
        $userVisitDevices  = IotData::distinct('deviceid')
            ->where('identifier','=',$id)
            ->where('created_at','>',Carbon::today()->subDays(45))
            ->pluck('deviceid');
            
        //dd($userVisitDevices);

        //dd($uName); -- if not a registered user, this will return null
        //session([
        //    'GDevId' => $devNameList,
        //]);
        
        if($uRName != null){
            //get the devices and the location where the user belongs to 
            $uLocation = Device::where('device.isactive','=',true)
                ->join('LinkLocDev','device.id','LinkLocDev.deviceid')
                ->where('LinkLocDev.locationid','=', $uRName->locationid)
                ->where('LinkLocDev.isactive','=',true)
                ->pluck('device.serial_no')->toArray();
            //dd($uLocation, session('GDevId')->toArray());   

            //check for the user role based access to the data
            if(Auth::user()->hasRole('Super Admin')){
                //dd('in SuperAdmin');
                //dd($uRName->id);
                return redirect('reguser/'.$uRName->id);
                //$r = new RegUsersController();
                //$r->show($uRName->id);
            }
            else if(Auth::user()->hasRole(['Location Admin','Site Admin'])){
                //restrict the user search only if the user has visit one of the location devices
                $intersectLocations = array_intersect(session('GDevId')->toArray(),$uLocation); 
                //dd($intersectLocations, $uLocation, session('GDevId')->toArray());
                //if the data in iotdata matches any of these devices, then we will show else the user never visited this locaiton so no need
                $visitData = IotData::whereIn('deviceid',$intersectLocations)
                ->where('identifier','=',$uRName->phoneno)
                ->pluck('id')->count();
                //->get();

                //dd($visitData);
                if($visitData > 0){
                    return redirect('reguser/'.$uRName->id);
                }
            }
            else if(Auth::user()->hasRole(['App User'])){
                //restrict to their own data only
            }
        }
        else{
            //check for the user role based access to the data
            if(Auth::user()->hasRole('Super Admin')){
                //we should show all the data for that user
                return $this->UserReport($id);
            }
            else if(Auth::user()->hasRole(['Location Admin','Site Admin'])){
                //restrict the user search only if the user has visit one of the location devices
                $visitData = IotData::whereIn('deviceid',session('GDevId'))
                ->where('identifier','=',$id)
                ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
                ->pluck('id')->count();
                //->get();

                //dd($visitData);
                if($visitData > 0){
                    return $this->UserReport($id);
                } 
                else{
                    return view('reports.userReport')->with('error','No data available for this user');
                }
            }
            else if(Auth::user()->hasRole(['App User'])){
                //restrict to their own data only
            }
        }
        //dd('done execution');
      }

      
      public function UserReport($identifier){

        //The identifier should be 10 digit number
        /**/
          //dd($identifier);
        
        $iotData = IotData::where('iotdata.identifier','=',$identifier)
            //->where('iotdata.created_at','<=',Carbon::today()->addDays(1))
            ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
            ->join('vlocdev','iotdata.deviceid','vlocdev.serial_no')
            ->select('iotdata.identifier', 'iotdata.temp', 'iotdata.spo2', 'iotdata.hbcount', 'iotdata.created_at', 'vlocdev.name')
            ->orderBy('created_at','desc')
            ->get();
        //dd($iotData);

        if(count($iotData) > 0){
            $lbl = collect([]);
            $valuesTemp = collect([]);
            $valuesSpo2 = collect([]);
            foreach($iotData as $data){
                $lbl->push($data->created_at->format('Y-m-d h:i:s'));
                $valuesTemp->push($data->temp);
                $valuesSpo2->push($data->spo2);
            }
        //dd($lbl);
        //dd($values);


            $spo2Chart = new ReportChartLine();
            //get array for labels
            $spo2Chart->labels($lbl);
        //dd($spo2Chart);
            $spo2Chart->dataset('SPO2 data', 'bar',$valuesSpo2)
                ->backgroundColor('red');
            
            $tempChart = new ReportChartLine();
            $tempChart->labels($lbl);
            $tempChart->dataset('Temperature data','bar',$valuesTemp)
                ->backgroundColor('blue');
            return view('reports.userReport',compact('identifier','iotData','spo2Chart','tempChart'));
        }
        else{
            return view('reports.userReport',compact('identifier','iotData'))->with('error','No data available for this user');
        }
      }
    /**
     * 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
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
        //
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
