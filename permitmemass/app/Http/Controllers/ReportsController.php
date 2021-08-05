<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AllDataReport;
use Illuminate\Support\Facades\DB;
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
use App\Http\Traits\ExportHelpers;

class ReportsController extends Controller
{
    use ExportHelpers;
    
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
       
        

        /* 
            Adding code for filters
        */
        
        $fLocation = request()->input('location') ?? '*'; // Input::get('location');
        //dd($fLocation, request());        
        

        //dd(session('GDevId'));
        if(Auth::user()->hasRole(['Super Admin','Location Admin'])){
            //Ketan add for report document 
            if(isset($_GET['type']) && !empty($_GET['type'])){
                if($_GET['type']== 'download'){
                    if($fLocation == '*'){
                        $allDataDownload = DB::table('iotdata')->whereIn('iotdata.deviceid',session('GDevId'))
                                    ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
                                    ->join('vlocdev','vlocdev.serial_no','iotdata.deviceid')
                                    ->select('iotdata.identifier as identifier',
                                    'iotdata.temp as temp', 'iotdata.spo2 as spo2',
                                    'iotdata.hbcount as hbcount', 'iotdata.created_at as created_at',
                                    'vlocdev.name as lname', DB::raw('(select case iotdata.flagstatus when 0 then "No" when 1 then "Yes" end) as flagstatus'))
                                    ->orderBy('iotdata.created_at','desc')
                                    ->orderBy('iotdata.hbcount', 'desc')
                                    ->orderBy('iotdata.spo2','asc')
                                    ->orderBy('iotdata.temp','desc')
                                    ->get();
                                    //dd(Carbon::today()->subDays(15));
                    }
                    else{
                        $allDataDownload = DB::table('iotdata')->whereIn('iotdata.deviceid',session('GDevId'))
                                    ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
                                    ->join('vlocdev','vlocdev.serial_no','iotdata.deviceid')
                                    ->where('vlocdev.name','=',$fLocation)
                                    ->select('iotdata.identifier as identifier',
                                    'iotdata.temp as temp', 'iotdata.spo2 as spo2',
                                    'iotdata.hbcount as hbcount', 'iotdata.created_at as created_at',
                                    'vlocdev.name as lname', DB::raw('(select case iotdata.flagstatus when 0 then "No" when 1 then "Yes" end) as flagstatus'))
                                    ->orderBy('iotdata.created_at','desc')
                                    ->orderBy('iotdata.hbcount', 'desc')
                                    ->orderBy('iotdata.spo2','asc')
                                    ->orderBy('iotdata.temp','desc')
                                    ->get();
                    }
                    //dd($allDataDownload);
                    $colHeaders = array('Identifier','Temperature', 'SPO2', 'Pulse Rate', 'Recorded Time', 'Recorded At', 'Abnormal');
                    $listOfFields = array('identifier','temp','spo2', 'hbcount', 'created_at', 'lname', 'flagstatus');
                    $fileName = "AllDataReport.csv";
                    //dd('sending data to export controller');
                    $this->generateCSV($fileName, $colHeaders, $allDataDownload, 7, $listOfFields);
                }
            }

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
            if(isset($_GET['type']) && !empty($_GET['type'])){
                if($_GET['type']== 'download'){
                    if($fLocation == '*'){
                        $allDataDownload = DB::table('iotdata')->whereIn('iotdata.deviceid',session('GDevId'))
                            ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
                            ->join('vlocdev','vlocdev.serial_no','iotdata.deviceid')
                            ->join('device','device.serial_no','iotdata.deviceid')
                            ->join('LinkLocDev','LinkLocDev.deviceid','device.id')
                            ->where('LinkLocDev.isactive','=',true)
                            ->select('iotdata.identifier as identifier',
                            'iotdata.temp as temp', 'iotdata.spo2 as spo2',
                            'iotdata.hbcount as hbcount', 'iotdata.created_at as created_at',
                            'vlocdev.name as lname', DB::raw('(select case iotdata.flagstatus when 0 then "No" when 1 then "Yes" end) as flagstatus'), 'LinkLocDev.name as dname')
                            ->orderBy('iotdata.created_at','desc')
                            ->orderBy('iotdata.hbcount', 'desc')
                            ->orderBy('iotdata.spo2','asc')
                            ->orderBy('iotdata.temp','desc')
                            ->get();
                                    //dd(Carbon::today()->subDays(15));
                    }
                    else{
                        $allDataDownload = DB::table('iotdata')->whereIn('iotdata.deviceid',session('GDevId'))
                            ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
                            ->join('vlocdev','vlocdev.serial_no','iotdata.deviceid')
                            ->join('device','device.serial_no','iotdata.deviceid')
                            ->join('LinkLocDev','LinkLocDev.deviceid','device.id')
                            ->where('LinkLocDev.isactive','=',true)
                            ->where('vlocdev.serial_no','=',$fLocation)
                            ->select('iotdata.identifier as identifier',
                            'iotdata.temp as temp', 'iotdata.spo2 as spo2',
                            'iotdata.hbcount as hbcount', 'iotdata.created_at as created_at',
                            'vlocdev.name as lname', DB::raw('(select case iotdata.flagstatus when 0 then "No" when 1 then "Yes" end) as flagstatus'), 'LinkLocDev.name as dname')
                            ->orderBy('iotdata.created_at','desc')
                            ->orderBy('iotdata.hbcount', 'desc')
                            ->orderBy('iotdata.spo2','asc')
                            ->orderBy('iotdata.temp','desc')
                            ->paginate(50);
                    }
                    //dd($allDataDownload);
                    $colHeaders = array('Identifier','Temperature', 'SPO2', 'Pulse Rate', 'Recorded Time', 'Recorded At', 'Abnormal', 'Device Name');
                    $listOfFields = array('identifier','temp','spo2', 'hbcount', 'created_at', 'lname', 'flagstatus', 'dname');
                    $fileName = "AllDataReport.csv";
                    //dd('sending data to export controller');
                    $this->generateCSV($fileName, $colHeaders, $allDataDownload, 8, $listOfFields);
                }
            }

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

        //Generating download report 
        if(isset($_GET['type']) && !empty($_GET['type'])){
            if($_GET['type']== 'download'){
                $colHeaders = array('Date','Number of All Abnormal Scans');
                    $listOfFields = array('date','count');
                    $fileName = "AllAbnormalDataReport.csv";
                    //dd('sending data to export controller');
                    $this->generateCSV($fileName, $colHeaders, $allAbnormal15Days, 2, $listOfFields);
            }
        }


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
        $abnormalChart->title('All Abnormal Parameters');
            $abnormalChart->options([
                'responsive' => true,
                'title' => ['fontColor' => 'white'],
                'legend' => ['display' => true, 
                    'position' => 'bottom',
                    'align' => 'left',
                    'labels' => ['fontColor' => 'white', ],
                ],
                
                //'plugins' => '{datalabels: {color: \'red\'}, title: {display: true}}',
            ]);
            //->options=[legend->position('right')];
        //$spo2Chart->title('SPO2 data for last 15 days');


        return view('reports.AllAbnormalReport',compact('allAbnormal15Days', 'abnormalChart'));
        
    }

    public function AllAbnormalDetailsByDate($date){
      
        if(count(session('GDevId'))==0){
            return view('/reports/AllAbnormalReportByDate')->with('error', 'No device associated with your location');
        }

        //Generating download report 
        if(isset($_GET['type']) && !empty($_GET['type'])){
            if($_GET['type']== 'download'){
                //dd('in download');
                $abnormalOnDateReport = IotData::whereIn('iotdata.deviceid',session('GDevId'))
                ->where(DB::raw('Date(iotdata.created_at)'),'=',new Carbon($date))
                ->where('iotdata.spo2','<',env('CUTOFF_SPO2'))
                ->where('iotdata.temp', '>', env('CUTOFF_TEMP'))
                ->where('iotdata.hbcount', '>', env('CUTOFF_PULSE'))
                ->join('vlocdev','iotdata.deviceid','vlocdev.serial_no')
                ->select('iotdata.identifier','iotdata.temp','iotdata.spo2','iotdata.hbcount',DB::raw('DATE_FORMAT(iotdata.created_at, "%d-%m-%Y %H%i%s") as created_at'),'vlocdev.name')
                ->orderBy(DB::raw('Date(iotdata.created_at)','desc'))
                ->orderBy('vlocdev.name','desc')
                ->get();
                //dd($abnormalOnDateReport);
               
                $colHeaders = array('Identifier', 'Temperature', 'SPO2', 'Pulse Rate', 'Capture Time', 'Caputre Location');
                $listOfFields = array('identifier','temp', 'spo2', 'hbcount', 'created_at', 'name');
                $fileName = "AllAbnormalDataByDate.csv";
                //dd('sending data to export controller');
                $this->generateCSV($fileName, $colHeaders, $abnormalOnDateReport, 6, $listOfFields);
            }
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

        //Generating download report 
        if(isset($_GET['type']) && !empty($_GET['type'])){
            if($_GET['type']== 'download'){
                $colHeaders = array('Date','Number of High Pulse Rate Scans');
                    $listOfFields = array('date','count');
                    $fileName = "LowSPO2Report.csv";
                    //dd('sending data to export controller');
                    $this->generateCSV($fileName, $colHeaders, $lowSpo215Days, 2, $listOfFields);
            }
        }

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
        $spo2Chart->title('Low SPO2 Data');
        $spo2Chart->dataset('Low SPO2 data', 'bar',$values)
            ->backgroundColor('red');
        $spo2Chart->options([
            'responsive' => true,
            'title' => ['fontColor' => 'white'],
            'legend' => ['display' => true, 
                'position' => 'bottom',
                'align' => 'left',
                'labels' => ['fontColor' => 'white', ],
            ],
            
            //'plugins' => '{datalabels: {color: \'red\'}, title: {display: true}}',
        ]);
            //->options=[legend->position('right')];
        //$spo2Chart->title('SPO2 data for last 15 days');


        return view('reports.SPO2Report',compact('lowSpo215Days', 'spo2Chart'));
        
    }

    public function SPO2DetailsByDate($date){
      
        if(count(session('GDevId'))==0){
            return view('/reports/SPO2ReportByDate')->with('error', 'No device associated with your location');
        }

        if(isset($_GET['type']) && !empty($_GET['type'])){
            if($_GET['type']== 'download'){
                //dd('in download');
                $abnormalOnDateReport = IotData::whereIn('iotdata.deviceid',session('GDevId'))
                ->where(DB::raw('Date(iotdata.created_at)'),'=',new Carbon($date))
                ->where('iotdata.spo2', '>', env('CUTOFF_SPO2'))
                ->join('vlocdev','iotdata.deviceid','vlocdev.serial_no')
                ->select('iotdata.identifier','iotdata.temp','iotdata.spo2','iotdata.hbcount',DB::raw('DATE_FORMAT(iotdata.created_at, "%d-%m-%Y %H%i%s") as created_at'),'vlocdev.name')
                ->orderBy(DB::raw('Date(iotdata.created_at)','desc'))
                ->orderBy('vlocdev.name','desc')
                ->get();
                //dd($abnormalOnDateReport);
               
                $colHeaders = array('Identifier', 'Temperature', 'SPO2', 'Pulse Rate', 'Capture Time', 'Caputre Location');
                $listOfFields = array('identifier','temp', 'spo2', 'hbcount', 'created_at', 'name');
                $fileName = "LowSPO2ByDateReport.csv";
                //dd('sending data to export controller');
                $this->generateCSV($fileName, $colHeaders, $abnormalOnDateReport, 6, $listOfFields);
            }
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

        //Generating download report 
        if(isset($_GET['type']) && !empty($_GET['type'])){
            if($_GET['type']== 'download'){
                $colHeaders = array('Date','Number of High Pulse Rate Scans');
                    $listOfFields = array('date','count');
                    $fileName = "HighTemperatureReport.csv";
                    //dd('sending data to export controller');
                    $this->generateCSV($fileName, $colHeaders, $highTemp15Days, 2, $listOfFields);
            }
        }


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
        $tempChart->title('High Temperature Data');
        $tempChart->options([
            'responsive' => true,
            'title' => ['fontColor' => 'white'],
            'legend' => ['display' => true, 
                'position' => 'bottom',
                'align' => 'left',
                'labels' => ['fontColor' => 'white', ],
            ],
            
            //'plugins' => '{datalabels: {color: \'red\'}, title: {display: true}}',
        ]);
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

        if(isset($_GET['type']) && !empty($_GET['type'])){
            if($_GET['type']== 'download'){
                //dd('in download');
                $abnormalOnDateReport = IotData::whereIn('iotdata.deviceid',session('GDevId'))
                ->where(DB::raw('Date(iotdata.created_at)'),'=',new Carbon($date))
                ->where('iotdata.temp', '>', env('CUTOFF_TEMP'))
                ->join('vlocdev','iotdata.deviceid','vlocdev.serial_no')
                ->select('iotdata.identifier','iotdata.temp','iotdata.spo2','iotdata.hbcount',DB::raw('DATE_FORMAT(iotdata.created_at, "%d-%m-%Y %H%i%s") as created_at'),'vlocdev.name')
                ->orderBy(DB::raw('Date(iotdata.created_at)','desc'))
                ->orderBy('vlocdev.name','desc')
                ->get();
                //dd($abnormalOnDateReport);
               
                $colHeaders = array('Identifier', 'Temperature', 'SPO2', 'Pulse Rate', 'Capture Time', 'Caputre Location');
                $listOfFields = array('identifier','temp', 'spo2', 'hbcount', 'created_at', 'name');
                $fileName = "HighTemperatureByDateReport.csv";
                //dd('sending data to export controller');
                $this->generateCSV($fileName, $colHeaders, $abnormalOnDateReport, 6, $listOfFields);
            }
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

         //Generating download report 
        if(isset($_GET['type']) && !empty($_GET['type'])){
            if($_GET['type']== 'download'){
                $colHeaders = array('Date','Number of High Pulse Rate Scans');
                    $listOfFields = array('date','count');
                    $fileName = "HighPulseRateReport.csv";
                    //dd('sending data to export controller');
                    $this->generateCSV($fileName, $colHeaders, $highHbcount15Days, 2, $listOfFields);
            }
        }

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
        $hbcountChart->title('High Pulse Rate Data');
        $hbcountChart->dataset('High Pulse Rate Data', 'bar',$values)
            ->backgroundColor('red');
        $hbcountChart->options([
            'responsive' => true,
            'title' => ['fontColor' => 'white'],
            'legend' => ['display' => true, 
                'position' => 'bottom',
                'align' => 'left',
                'labels' => ['fontColor' => 'white', ],
            ],
            
            //'plugins' => '{datalabels: {color: \'red\'}, title: {display: true}}',
        ]);
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

        if(isset($_GET['type']) && !empty($_GET['type'])){
            if($_GET['type']== 'download'){
                //dd('in download');
                $abnormalOnDateReport = IotData::whereIn('iotdata.deviceid',session('GDevId'))
                ->where(DB::raw('Date(iotdata.created_at)'),'=',new Carbon($date))
                ->where('iotdata.hbcount', '>', env('CUTOFF_PULSE'))
                ->join('vlocdev','iotdata.deviceid','vlocdev.serial_no')
                ->select('iotdata.identifier','iotdata.temp','iotdata.spo2','iotdata.hbcount',DB::raw('DATE_FORMAT(iotdata.created_at, "%d-%m-%Y %H%i%s") as created_at'),'vlocdev.name')
                ->orderBy(DB::raw('Date(iotdata.created_at)','desc'))
                ->orderBy('vlocdev.name','desc')
                ->get();
                //dd($abnormalOnDateReport);
               
                $colHeaders = array('Identifier', 'Temperature', 'SPO2', 'Pulse Rate', 'Capture Time', 'Caputre Location');
                $listOfFields = array('identifier','temp', 'spo2', 'hbcount', 'created_at', 'name');
                $fileName = "HighPulseRateByDateReport.csv";
                //dd('sending data to export controller');
                $this->generateCSV($fileName, $colHeaders, $abnormalOnDateReport, 6, $listOfFields);
            }
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
        //dd($uRName, $userVisitDevices);
        if($uRName != null){
            //dd('in if');
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
                return redirect('reguser/'.$uRName->phoneno.'?type='.$type);
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
                    return redirect('reguser/'.$uRName->phoneno.'?type='.$type);
                }
                else{
                    return view ('reports.userReport')->with('error','No data available for this use');
                }
            }
            else if(Auth::user()->hasRole(['App User'])){
                //restrict to their own data only
            }
        }
        else{
            //dd('in else');
            //check for the user role based access to the data
            if(Auth::user()->hasRole('Super Admin')){
                //we should show all the data for that user
                return $this->UserReport($id, $type);
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
                    return $this->UserReport($id, $type);
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

      
      public function UserReport($identifier, $type){

        //The identifier should be 10 digit number
        /**/
          //dd($identifier);
        if($type == 'download'){
            $iotData = IotData::where('iotdata.identifier','=',$identifier)
            //->where('iotdata.created_at','<=',Carbon::today()->addDays(1))
            ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
            ->join('vlocdev','iotdata.deviceid','vlocdev.serial_no')
            ->select('iotdata.identifier', 'iotdata.temp', 'iotdata.spo2', 'iotdata.hbcount', 'iotdata.created_at', 'vlocdev.name')
            ->orderBy('created_at','desc')
            ->get();

            $colHeaders = array('Identifier', 'Temperature', 'SPO2', 'Pulse Rate', 'Capture Time', 'Caputre Location');
            $listOfFields = array('identifier','temp', 'spo2', 'hbcount', 'created_at', 'name');
            $fileName = "UserData.csv";
            //dd('sending data to export controller');
            $this->generateCSV($fileName, $colHeaders, $iotData, 6, $listOfFields);
        }
        
        
        $iotData = IotData::where('iotdata.identifier','=',$identifier)
            //->where('iotdata.created_at','<=',Carbon::today()->addDays(1))
            ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
            ->join('vlocdev','iotdata.deviceid','vlocdev.serial_no')
            ->select('iotdata.identifier', 'iotdata.temp', 'iotdata.spo2', 'iotdata.hbcount', 'iotdata.created_at', 'vlocdev.name')
            ->orderBy('created_at','desc')
            ->paginate(50);
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
            $spo2Chart->title('SPO2 data');
            //$spo2Chart->borderColor(false);
            $spo2Chart->dataset('SPO2 data', 'line',$valuesSpo2)
                ->backgroundColor('red');
            
            $spo2Chart->options([
                'responsive' => true,
                'title' => ['fontColor' => 'white'],
                'legend' => ['display' => true, 
                    'position' => 'bottom',
                    'align' => 'left',
                    'labels' => ['fontColor' => 'white', ],
                ],
                
                //'plugins' => '{datalabels: {color: \'red\'}, title: {display: true}}',
            ]);
            
            $tempChart = new ReportChartLine();
            $tempChart->labels($lbl);
            $tempChart->title('Temperature data');
            $tempChart->dataset('Temperature data','line',$valuesTemp)
                ->backgroundColor('blue');
            $tempChart->options([
                'responsive' => true,
                'title' => ['fontColor' => 'white'],
                'legend' => ['display' => true, 
                    'position' => 'bottom',
                    'align' => 'left',
                    'labels' => ['fontColor' => 'white', ],
                ],
                
                //'plugins' => '{datalabels: {color: \'red\'}, title: {display: true}}',
            ]);
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
