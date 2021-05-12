<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Device;
use App\Society;
use App\LinkLocUser;
use App\LinkLocDev;
use App\User;
use App\IotData;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Controllers\Exception;
use App\vLocDev;
use Exception as GlobalException;
use PhpParser\Node\Expr\Throw_;
use DB;
use App\Charts\ReportChartLine;
use App\RegUser;

//use Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        /* Add data to session about the user
        * adding following data
        * 1. Location name
        * 2. Devices linked to location 
        * 3. LATER - IotData table name 
        * 4. Designation
        * 
        * To get the relevant data we need user ->linkLocUser -> Location ->LinkLocDev -> dev 
        */
        //getting the linked locaion id from the user if
        //dd(Auth::user()->id);
        try { 
            /* Ketan Change; for Roles based data - BEGIN - 28 Apr 21; */
            if (Auth::user()->hasRole('Super Admin')){
                //select all locations for data
                $linkLocId = 0;
                //dd($linkLocId);
                $loc = Society::where('isactive','=',true)->pluck('id')->toArray();//find($linkLocId->locationid);//->toArray();
                //dd($loc);
                
                session([
                    'GDesignation' => 'Super Admin',
                    'GlocationName' => 'ALL',
                    'GlocationId'=> 0,
                    'GnoOfResidents' => 'ALL',
                    'Gcity' => 'All',
                    'GisActive' => true,
                ]);
            }
            else if (Auth::user()->hasRole('Location Admin')){
                //select data for all sites under that location
                $linkLocId = LinkLocUser::where('userid','=',Auth::user()->id)->first();
                //dd($linkLocId);
                $loc1 = Society::where('id','=',$linkLocId->locationid)->first();; 
                //dd($loc1);
                $loc2 = Society::where('parent','=',$linkLocId->locationid)->get();
                //dd($loc1, array($loc1->id), $loc2->pluck('id')->toArray());
                //dd($loc1, $loc2);

                if(count($loc2) > 0){
                    $loc = array_merge(array($loc1->id),$loc2->pluck('id')->toArray());
                    $noOfResi = $loc1->noofresidents;
                    foreach ($loc2 as $l){
                        $noOfResi = $noOfResi + $l['noofresidents'];
                    }
                }
                //dd($loc1, $loc2, $loc, $noOfResi);
                //dd();
                session([
                    'GDesignation' => 'Location Admin',
                    'GlocationName' => $loc1->name,
                    'GlocationId'=> $loc1->id,
                    'GnoOfResidents' => $noOfResi,
                    'Gcity' => $loc1->city,
                    'GisActive' => true,
                ]);
            }
            else if (Auth::user()->hasRole('Site Admin')){
                // Select data for only that site
                $linkLocId = LinkLocUser::where('userid','=',Auth::user()->id)->first();
                //dd($linkLocId);
                $loc1 = Society::find($linkLocId->locationid);
                $loc = array($loc1->id);
                //dd($loc['id']);

                session([
                    'GDesignation' => 'Site Admin',
                    'GlocationName' => $loc1->name,
                    'GlocationId'=> $loc1->id,
                    'GnoOfResidents' => $loc1->noofresidents,
                    'Gcity' => $loc1->city,
                    'GisActive' => true,
                ]);
            }
            else if(Auth::user()->hasRole('App User')){
                //no location to be selected. This is a complete different process. Treat this separately
            }
            else {
                //Error - 
                throwException(new \Exception( "Role not defiled"));
            }
            //END OF ROLE BASED PROCESS


            //dd('Post role', $linkLocId);
            $devIdList = LinkLocDev::whereIn('locationid',$loc)
                ->where ('isactive','=',true)
                ->select('deviceid')
                ->get()
                ->toArray();
                //dd ($devIdList);
                $noofDev = count($devIdList);
            //Ketan Change for Roles based data - End - 28 Apr 21
            
            if(count($devIdList) > 0){
                $devNameList = (Device::whereIn('id',$devIdList)
                    ->where ('isactive','=',true)
                    ->pluck('serial_no'))->unique();
                    //->get()
                    //->toArray();
            }
            //dd($devNameList);
            session([
                'GDevId' => $devNameList,
            ]);
   
        }
        catch (\Exception $e){
            //do nothing
        }
        /*session([
            'GDesignation' => $linkLocId->designation,
            'GlocationName' =>$loc->name,
            'GlocationId'=>$loc->id,
            'GnoOfResidents' => $loc->noofresidents,
            'Gcity' => $loc->city,
            'GisActive' => $loc->isactive,
        ]); */
        //Above session needs to be set for all above roles
        
        //getting data for dashboard
        //Total scaned till date
        $iotScanAll =IotData::whereIn('deviceid',$devNameList)
            ->select('identifier','deviceid','temp','spo2','hbcount','created_at','flagstatus')
            ->get(); 
        //This is the master data set for all data 

        //Total Scanned till data
        $totScan = $iotScanAll->count();
        //dd($totScan);
        
        //Total Scanned Today
        //$totScanToday = $iotScanAll->where('created_at','>=', Carbon::today()) ->count();
        //dd($totScanToday);

        //Getting The abnormal Data set
        $iotScanAbnormal = $iotScanAll->where('flagstatus','=',true);
        //We should use this for getting all abnormal numbers to work with a smaller dataset 


        //Total Scanned with SPO2 below env('CUTOFF_SPO2')
        $totScanSPO2All = $iotScanAbnormal->where('spo2','<',env('CUTOFF_SPO2'));
        $totScanSPO2 = $totScanSPO2All->count();

        //dd($totScanSPO2, $totScanSPO21);
       
        
        //Total Scanned with SPO2 beow env('CUTOFF_SPO2') TODAY
        //$totScanTodaySPO2 = $totScanSPO2All->where('created_at','>=',Carbon::today())->count();


        
        
        //Total Scanned with Temp above env('CUTOFF_TEMP')
        $totScanTempAll = $iotScanAbnormal->where('temp','>',env('CUTOFF_TEMP'));
        $totScanTemp = $totScanTempAll->count();

        //dd($totScanTemp);
        
        
        //Total Scanned with Temp above env('CUTOFF_TEMP') TODAY
        //$totScanTodayTemp = $totScanTempAll->where('created_at','>=',Carbon::today())->count();

        //dd($totScanTodayTemp);

        //Total Scanned with pulse above env('CUTOFF_PULSE')
        $totScanPulseAll = $iotScanAbnormal->where('hbcount','>',env('CUTOFF_PULSE'));
        $totScanPulse = $totScanPulseAll->count();

        //dd($totScanPulse);

        //Total Scanned with pulse above env('CUTOFF_PULSE') TODAY
        //$totScanPulseToday = $totScanPulseAll->where('created_at','>=',Carbon::today())->count();

        
        //All abnormal scans
        $totScanAllAbnormalAll = $iotScanAbnormal
            ->where('hbcount','>',env('CUTOFF_PULSE'))
            ->where('spo2','<',env('CUTOFF_SPO2'))
            ->where('temp','>',env('CUTOFF_TEMP'));
        $totScanAllAbnormal = $totScanAllAbnormalAll->count();


        //All Scans abnormal today
        //$totScanAllAbnormalToday = $totScanAllAbnormalAll->where('created_at','>=',Carbon::today())->count();


        //Now getting additional parameters for version 3
        //Total no of locations - Active
        $totActiveLocations = count($loc);
        //Total no of devices  - Active 
        $totActiveDevices = $devNameList->count();
        //Total unique users 
        $totUniqueUsers = $iotScanAll->unique('identifier')->count();
        //Total No of registered users
        
        $locIds = DB::table('vlocdev')->whereIn('serial_no',session('GDevId'))->pluck('locationid')->unique();
        $totalRegUsers = RegUser::whereIn('locationid', $locIds)->where('isactive','=',true)->count();
            //dd($totalRegUsers);
        
        //dd($totalRegUsers);

        //Getting trends

        //All Abnormal Trend
        //dd($totScanAllAbnormalAll);
        $top5ByAllAbnormalDev = $totScanAllAbnormalAll
            ->where('created_at','>=',Carbon::today()->subDays(7))
            ->groupBy('deviceid')
            ->map(function($value){
                return(count($value));    
            })
            ->sortDesc()
            ->take(5)
            ->toArray();


        $lblAllAbnormalChart = collect([]);
        $valAllAbnormalChart = collect([]);
        if(Auth::user()->hasRole(['Super Admin', 'Location Admin'])){
            foreach($top5ByAllAbnormalDev as $r=>$r_value){
                
                $locName = vLocDev::where('serial_no','=',$r)->pluck('name')->toArray();
                //echo($locName[0]."   ".$r_value);
                if($lblAllAbnormalChart->contains($locName[0])){
                    $key = $lblAllAbnormalChart->search($locName[0]);
                    //echo ($key."<br/>");
                    $valAllAbnormalChart[$key] = $valAllAbnormalChart[$key] + $r_value;
                }
                else{
                    $lblAllAbnormalChart->push($locName[0]);
                    $valAllAbnormalChart->push($r_value);
                }
                //dd($locName[0], $r, $r_value);
                
            }
        }
        else if(Auth::user()->hasRole(['Site Admin'])){
            foreach($top5ByAllAbnormalDev as $r=>$r_value){
                //Geting the location where the device is installed
                $setName = Device::where('serial_no','=',$r)
                    ->join('LinkLocDev','device.id','LinkLocDev.deviceid')
                    ->pluck('LinkLocDev.name');
                $lblAllAbnormalChart->push($setName[0].' '.$r);
                $valAllAbnormalChart->push($r_value);
                //dd($locName[0], $r, $r_value);
            }
        }
        //dd($lblAllAbnormalChart, $valAllAbnormalChart);

        //Generating the Pulse Chart for top 5 locations
        $allAbnormalChart = new ReportChartLine();
        $colAllAbnormal = array();
        for ( $i = 0; $i<5; $i++){
            $colAllAbnormal[$i] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }
        //get array for labels
        $allAbnormalChart->labels($lblAllAbnormalChart); 
        //dd($spo2Chart);
        $allAbnormalChart->dataset('All Abnormal data', 'doughnut',$valAllAbnormalChart)
            ->backgroundColor($colAllAbnormal);
        

        
        //Temperature Trend
        $top5ByTempDev = $totScanTempAll
        ->where('created_at','>=',Carbon::today()->subDays(7))
        ->groupBy('deviceid')
        ->map(function($value){
            return(count($value));    
        })
        ->sortDesc()
        ->take(5)
        ->toArray();

        //Adding data for charts
        $lblTempChart = collect([]);
        $valTempChart = collect([]);
        if(Auth::user()->hasRole(['Super Admin', 'Location Admin'])){
            foreach($top5ByTempDev as $r=>$r_value){
                $locName = vLocDev::where('serial_no','=',$r)->pluck('name')->toArray();
                if($lblTempChart->contains($locName[0])){
                    $key = $lblTempChart->search($locName[0]);
                    //echo ($key."<br/>");
                    $valTempChart[$key] = $valTempChart[$key] + $r_value;
                }
                else{
                    $lblTempChart->push($locName[0]);
                    $valTempChart->push($r_value);
                }
                
               
            }
        }
        else if(Auth::user()->hasRole(['Site Admin'])){
            foreach($top5ByTempDev as $r=>$r_value){
                $setName = Device::where('serial_no','=',$r)
                    ->join('LinkLocDev','device.id','LinkLocDev.deviceid')
                    ->pluck('LinkLocDev.name');
                 $lblTempChart->push($setName[0].' '.$r);
                $valTempChart->push($r_value);
                //dd($locName[0], $r, $r_value);
            }
        }
        //dd($top5ByTemp);

        //Generating the Temp Chart for top 5 locations
        $tempChart = new ReportChartLine();
        $colTemp = array();
        for ( $i = 0; $i<5; $i++){
            $colTemp[$i] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }
        //get array for labels
        $tempChart->labels($lblTempChart); 
        //dd($spo2Chart);
        $tempChart->dataset('Temperature data', 'doughnut',$valTempChart)
            ->backgroundColor($colTemp);
            //->options= ["legend->position('bottom')"];

   
        //SPO2 Trend
        $top5BySPO2Dev = $totScanSPO2All
        ->where('created_at','>=',Carbon::today()->subDays(7))
        ->groupBy('deviceid')
        ->map(function($value){
            return(count($value));    
        })
        ->sortDesc()
        ->take(5)
        ->toArray();

        //dd($top5BySPO2Dev);
        $lblSpo2Chart = collect([]);
        $valSpo2Chart = collect([]);
        if(Auth::user()->hasRole(['Super Admin', 'Location Admin'])){
            foreach($top5BySPO2Dev as $r=>$r_value){
                $locName = vLocDev::where('serial_no','=',$r)->pluck('name')->toArray();
                if($lblSpo2Chart->contains($locName[0])){
                    $key = $lblSpo2Chart->search($locName[0]);
                    //echo ($key."<br/>");
                    $valSpo2Chart[$key] = $valSpo2Chart[$key] + $r_value;
                }
                else{
                    $lblSpo2Chart->push($locName[0]);
                    $valSpo2Chart->push($r_value);
                }
                
                //dd($locName[0], $r, $r_value);
            }
        }
        else if(Auth::user()->hasRole(['Site Admin'])){

            foreach($top5BySPO2Dev as $r=>$r_value){
                $setName = Device::where('serial_no','=',$r)
                    ->join('LinkLocDev','device.id','LinkLocDev.deviceid')
                    ->pluck('LinkLocDev.name');
                $lblSpo2Chart->push($setName[0].' '.$r);
                $valSpo2Chart->push($r_value);
                //dd($locName[0], $r, $r_value);
            }
        }
        //Generating the Spo2 Chart for top 5 locations
        $spo2Chart = new ReportChartLine();
        $colSpo2 = array();
        for ( $i = 0; $i<5; $i++){
            $colSpo2[$i] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }
        //get array for labels
        $spo2Chart->labels($lblSpo2Chart); 
        //dd($spo2Chart);
        $spo2Chart->dataset('SPO2 data', 'doughnut',$valSpo2Chart)
            ->backgroundColor($colSpo2);



        //Pulse Rate Trend
        $top5ByPulseDev = $totScanPulseAll
        ->where('created_at','>=',Carbon::today()->subDays(7))
        ->groupBy('deviceid')
        ->map(function($value){
            return(count($value));    
        })
        ->sortDesc()
        ->take(5)
        ->toArray();

        

        $lblPulseChart = collect([]);
        $valPulseChart = collect([]);
        if(Auth::user()->hasRole(['Super Admin', 'Location Admin'])){
            foreach($top5ByPulseDev as $r=>$r_value){
                $locName = vLocDev::where('serial_no','=',$r)->pluck('name')->toArray();
                if($lblPulseChart->contains($locName[0])){
                    $key = $lblPulseChart->search($locName[0]);
                    //echo ($key."<br/>");
                    $valPulseChart[$key] = $valPulseChart[$key] + $r_value;
                }
                else{
                    $lblPulseChart->push($locName[0]);
                    $valPulseChart->push($r_value);
                }
                
               
            }
        }
        else if(Auth::user()->hasRole(['Site Admin'])){
            foreach($top5ByPulseDev as $r=>$r_value){
                $setName = Device::where('serial_no','=',$r)
                    ->join('LinkLocDev','device.id','LinkLocDev.deviceid')
                    ->pluck('LinkLocDev.name');
                $lblPulseChart->push($setName[0].' '.$r);
                $valPulseChart->push($r_value);
                //dd($locName[0], $r, $r_value);
            }
        }
        //Generating the Pulse Chart for top 5 locations
        $pulseChart = new ReportChartLine();
        $colPulse = array();
        for ( $i = 0; $i<5; $i++){
            $colPulse[$i] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }
        //get array for labels
        $pulseChart->labels($lblPulseChart); 
        //dd($spo2Chart);
        $pulseChart->dataset('Pulse Rate data', 'doughnut',$valPulseChart)
            ->backgroundColor($colPulse);
        //dd($valPulseChart);
        
        

        return view('home',compact('totScan',
            //'totScanToday',
            'totScanSPO2',
            //'totScanTodaySPO2',
            'totScanTemp',
            //'totScanTodayTemp',
            'totScanPulse',
            //'totScanPulseToday',
            'totScanAllAbnormal',
            //'totScanAllAbnormalToday',
            'loc', 'noofDev','totalRegUsers','totUniqueUsers',
            'top5BySPO2Dev', 'top5ByTempDev', 'top5ByPulseDev',
            'tempChart', 'spo2Chart', 'pulseChart', 'allAbnormalChart'
        ));
    }
}
