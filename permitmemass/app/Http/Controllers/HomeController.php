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
use App\Charts\ReportChartLine;
use App\RegUser;
use Illuminate\Support\Facades\DB;

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
      
        /* Ketan Change; for Roles based data - BEGIN - 28 Apr 21; */
        if (Auth::user()->hasRole('Super Admin')){
            //select all locations for data
            $linkLocId = 0;
            //dd($linkLocId);
            //$st1 = microtime(true); 
            $loc = Society::where('isactive','=',true)->pluck('id')->toArray();//find($linkLocId->locationid);//->toArray();
            //dd($loc);
            //$et1 = microtime(true); 
            //dd($et1, $st1);
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
            //$st2 = microtime(true); 
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
            //$et2 = microtime(true); 
            //dd($st2, $et2);
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
           
        //getting data for dashboard
        //Total scaned till date
        $dataCounts = DB::table('iotdatasummary')
            ->selectRaw('sum(highpulserate) as highPulseRate, 
            sum(lowspo2) as lowSpo2, 
            sum(hightemp) as highTemp,
            sum(highpulseratelowspo2) as highPulseLowSpo2,
            sum(highpulseratehightemp) as highPulseHighTemp,
            sum(lowspo2hightemp) as lowSpo2HighTemp,
            sum(allabnormal) as allAbnormal,
            sum(allnormal) as allNormal')
            ->first();
        //dd($dataCounts);

        
        $iotScanAll =IotData::whereIn('deviceid',$devNameList)
            ->select('identifier','deviceid','temp','spo2','hbcount','created_at','flagstatus')
            ->get(); 
        //This is the master data set for all data 
       
        //Total Scanned till data
        //$totScan = $iotScanAll->count();
        //Ketan change V3
        $totScan = $dataCounts->highPulseRate 
            + $dataCounts->lowSpo2 
            + $dataCounts->highTemp 
            + $dataCounts->highPulseLowSpo2 
            + $dataCounts->highPulseHighTemp
            + $dataCounts->lowSpo2HighTemp
            + $dataCounts->allAbnormal
            + $dataCounts->allNormal;
        // //dd($totScan);
       
        //Total Scanned Today
        //$totScanToday = $iotScanAll->where('created_at','>=', Carbon::today()) ->count();
        //dd($totScanToday);

        //Getting The abnormal Data set
        $iotScanAbnormal = $iotScanAll->where('flagstatus','=',true);
        
        //We should use this for getting all abnormal numbers to work with a smaller dataset 


        //Total Scanned with SPO2 below env('CUTOFF_SPO2')
        $totScanSPO2All = $iotScanAbnormal->where('spo2','<',env('CUTOFF_SPO2'));
       
        //Ketan changed for V3
        //$totScanSPO2 = $totScanSPO2All->count();
        $totScanSPO2 = $dataCounts->lowSpo2  
            + $dataCounts->highPulseLowSpo2  
            + $dataCounts->lowSpo2HighTemp 
            + $dataCounts->allAbnormal;
       
        //dd($totScanSPO2, $totScanSPO21);
       
        
        //Total Scanned with SPO2 beow env('CUTOFF_SPO2') TODAY
        //$totScanTodaySPO2 = $totScanSPO2All->where('created_at','>=',Carbon::today())->count();


        
        
        //Total Scanned with Temp above env('CUTOFF_TEMP')
        
        $totScanTempAll = $iotScanAbnormal->where('temp','>',env('CUTOFF_TEMP'));
        
        //Ketan change for V3
        //$totScanTemp = $totScanTempAll->count();
        $totScanTemp = $dataCounts->highTemp 
            + $dataCounts->highPulseHighTemp
            + $dataCounts->lowSpo2HighTemp
            + $dataCounts->allAbnormal;
        
        //dd($totScanTemp);
        
        
        //Total Scanned with Temp above env('CUTOFF_TEMP') TODAY
        //$totScanTodayTemp = $totScanTempAll->where('created_at','>=',Carbon::today())->count();

        //dd($totScanTodayTemp);

        //Total Scanned with pulse above env('CUTOFF_PULSE')
        $totScanPulseAll = $iotScanAbnormal->where('hbcount','>',env('CUTOFF_PULSE'));
        
        //Ketan Changed for V3
        //$totScanPulse = $totScanPulseAll->count();
        $totScanPulse = $dataCounts->highPulseRate 
            + $dataCounts->highPulseLowSpo2 
            + $dataCounts->highPulseHighTemp
            + $dataCounts->allAbnormal;
        //dd($totScanPulse);

        //Total Scanned with pulse above env('CUTOFF_PULSE') TODAY
        //$totScanPulseToday = $totScanPulseAll->where('created_at','>=',Carbon::today())->count();

        
        //All abnormal scans
        $totScanAllAbnormalAll = $iotScanAbnormal
            ->where('hbcount','>',env('CUTOFF_PULSE'))
            ->where('spo2','<',env('CUTOFF_SPO2'))
            ->where('temp','>',env('CUTOFF_TEMP'));
        
        //Ketan Changes for V3
        //$totScanAllAbnormal = $totScanAllAbnormalAll->count();
        $totScanAllAbnormal = $dataCounts->allAbnormal;
        
        //dd($st1, $st2, $st3, $st4, $st5, $st6, $st7, $st8, $st9, $st10, $st11, $st12);

        //All Scans abnormal today
        //$totScanAllAbnormalToday = $totScanAllAbnormalAll->where('created_at','>=',Carbon::today())->count();


        //Now getting additional parameters for version 3
        //Total no of locations - Active
        $totActiveLocations = count($loc);
        //Total no of devices  - Active 
        $totActiveDevices = $devNameList->count();
        
        //Total unique users 
        //$totUniqueUsers = $iotScanAll->unique('identifier')->count();
        $totUniqueUsers = DB::table('iotdata')->selectRaw('distinct(identifier)')->count();
       
        //Total No of registered users
        
        //$locIds = DB::table('vlocdev')->whereIn('serial_no',session('GDevId'))->pluck('locationid')->unique();
        $locIds = DB::table('vlocdev')->whereIn('serial_no',session('GDevId'))->selectRaw('distinct(locationid) as l')->pluck('l');

        $totalRegUsers = RegUser::whereIn('locationid', $locIds)->where('isactive','=',true)->count();
            //dd($totalRegUsers);
        
        //dd($totalRegUsers);

        //Getting trends

        //All Abnormal Trend
        //dd($totScanAllAbnormalAll);
        // $top5ByAllAbnormalDev = $totScanAllAbnormalAll
        //     ->where('created_at','>=',Carbon::today()->subDays(7))
        //     ->groupBy('deviceid')
        //     ->map(function($value){
        //         return(count($value));    
        //     })
        //     ->sortDesc()
        //     ->take(5)
        //     ->toArray();
        //Ketan change for V3

        //create a collection of location name and device name
        $locDevAssociation = vLocDev::where('locactive','=',true)
            ->where('linkactive','=',true)
            ->where('devactive','=',true)
            ->pluck('name','serial_no');//->get()->toArray();
        //dd($locDevAssociation);

        $deviceNameAssociation = Device::whereIn('serial_no',$devNameList)
        ->join('LinkLocDev','device.id','LinkLocDev.deviceid')
        ->pluck('LinkLocDev.name', 'device.serial_no');


        $top5ByAllAbnormalDev = DB::table('iotdatasummary')
            ->where('fordate', '>=',Carbon::today()->subDays(7))
            ->whereIn('device',$devNameList)
            ->selectRaw('sum(allabnormal) as allabnormal, device')
            ->groupBy('device')
            ->orderBy('device')
            ->pluck('allabnormal','device');
        //dd($top5ByAllAbnormalDev);   
        
        //start processing from here
        $tempLocCollectionAbnormal = array();
        if(Auth::user()->hasRole(['Super Admin', 'Location Admin'])){
        /* Ketan commented for V3
            foreach($top5ByAllAbnormalDev as $r=>$r_value){
                
                //Ketan change V3
                //$locName = vLocDev::where('serial_no','=',$r)->pluck('name')->toArray();
                $locName = $locDevAssociation[$r];
                //echo($locName[0]."   ".$r_value);
                if($lblAllAbnormalChart->contains($locName)){
                    $key = $lblAllAbnormalChart->search($locName);
                    //echo ($key."<br/>");
                    $valAllAbnormalChart[$key] = $valAllAbnormalChart[$key] + $r_value;
                }
                else{
                    $lblAllAbnormalChart->push($locName);
                    $valAllAbnormalChart->push($r_value);
                }
                //dd($locName[0], $r, $r_value);
                
            }
            */
            foreach($top5ByAllAbnormalDev as $k=>$k_value){
                $locName = $locDevAssociation[$k];
                if(array_key_exists($locName,$tempLocCollectionAbnormal)){
                    $tempLocCollectionAbnormal[$locName] = $tempLocCollectionAbnormal[$locName] + $k_value;
                }
                else{
                    $tempLocCollectionAbnormal[$locName]=$k_value;
                }
            }
           
        }
        else if(Auth::user()->hasRole(['Site Admin'])){
            foreach($top5ByAllAbnormalDev as $k=>$k_value){
                $devName = $deviceNameAssociation[$k];
                if(array_key_exists($devName,$tempLocCollectionAbnormal)){
                    $tempLocCollectionAbnormal[$devName] = $tempLocCollectionAbnormal[$devName] + $k_value;
                }
                else{
                    $tempLocCollectionAbnormal[$devName]=$k_value;
                }
            }
            /*foreach($top5ByAllAbnormalDev as $r=>$r_value){
                //Geting the location where the device is installed
                $setName = Device::where('serial_no','=',$r)
                    ->join('LinkLocDev','device.id','LinkLocDev.deviceid')
                    ->pluck('LinkLocDev.name');
                $lblAllAbnormalChart->push($setName[0].' '.$r);
                $valAllAbnormalChart->push($r_value);
                //dd($locName[0], $r, $r_value);
            }*/
        }
        arsort($tempLocCollectionAbnormal);
        //dd(array_slice($tempLocCollectionAbnormal,0,5), $top5ByAllAbnormalDev, $locDevAssociation->sort());
        $lblAllAbnormalChart = collect([]);
        $valAllAbnormalChart = collect([]);
        foreach(array_slice($tempLocCollectionAbnormal,0,5) as $r=>$r_value){
            $lblAllAbnormalChart->push($r);
            $valAllAbnormalChart->push($r_value);
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
              
        $allAbnormalChart->title('All Abnormal data');
        $allAbnormalChart->options([
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
                    'gridLines' => ['display' => false]
                ],
                'xAxes' => [
                    'display' => false,
                    'ticks' => ['beginAtZero' => true],
                    'gridLines' => ['display' => false]
                ],
            ],
            //'plugins' => '{datalabels: {color: \'red\'}, title: {display: true}}',
        ]);
        

        //dd($sb1, $sb2, $sb3);
        //Temperature Trend
        /*$top5ByTempDev = $totScanTempAll
        ->where('created_at','>=',Carbon::today()->subDays(7))
        ->groupBy('deviceid')
        ->map(function($value){
            return(count($value));    
        })
        ->sortDesc()
        ->take(5)
        ->toArray();*/
        $top5ByTempDev = DB::table('iotdatasummary')
            ->where('fordate', '>=',Carbon::today()->subDays(7))
            ->whereIn('device',$devNameList)
            ->selectRaw('sum(allabnormal) + sum(hightemp) + sum(highpulseratehightemp) + sum(lowspo2hightemp) as highTemp, device')
            ->groupBy('device')
            ->orderBy('device')
            ->pluck('highTemp','device');
        //dd($top5ByTempDev, $top5ByAllAbnormalDev);

        $tempLocCollectionTemp = array();
        if(Auth::user()->hasRole(['Super Admin', 'Location Admin'])){
            //Ketan commented for V3
            /*foreach($top5ByTempDev as $r=>$r_value){
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
                
               
            }*/
            foreach($top5ByTempDev as $k=>$k_value){
                $locName = $locDevAssociation[$k];
                if(array_key_exists($locName,$tempLocCollectionTemp)){
                    $tempLocCollectionTemp[$locName] = $tempLocCollectionTemp[$locName] + $k_value;
                }
                else{
                    $tempLocCollectionTemp[$locName]=$k_value;
                }
            }
            
        }
        else if(Auth::user()->hasRole(['Site Admin'])){
            foreach($top5ByTempDev as $k=>$k_value){
                $devName = $deviceNameAssociation[$k];
                if(array_key_exists($devName,$tempLocCollectionTemp)){
                    $tempLocCollectionTemp[$devName] = $tempLocCollectionTemp[$devName] + $k_value;
                }
                else{
                    $tempLocCollectionTemp[$devName]=$k_value;
                }
            }
            //Ketan Commented for V3
            /*foreach($top5ByTempDev as $r=>$r_value){
                $setName = Device::where('serial_no','=',$r)
                    ->join('LinkLocDev','device.id','LinkLocDev.deviceid')
                    ->pluck('LinkLocDev.name');
                 $lblTempChart->push($setName[0].' '.$r);
                $valTempChart->push($r_value);
                //dd($locName[0], $r, $r_value);
            }*/
        }
        //dd($top5ByTemp);
        arsort($tempLocCollectionTemp);
        $lblTempChart = collect([]);
        $valTempChart = collect([]);
        foreach(array_slice($tempLocCollectionTemp,0,5) as $r=>$r_value){
            $lblTempChart->push($r);
            $valTempChart->push($r_value);
        }
        //Generating the Temp Chart for top 5 locations
        $tempChart = new ReportChartLine();
        $colTemp = array();
        for ( $i = 0; $i<5; $i++){
            $colTemp[$i] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }
        //get array for labels
        $tempChart->labels($lblTempChart); 
        //dd($spo2Chart);
        $tempChart->dataset('High Temperature', 'doughnut',$valTempChart)
            ->backgroundColor($colTemp);
        $tempChart->title('High Temperature');
        $tempChart->options([
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
                    'gridLines' => ['display' => false]
                ],
                'xAxes' => [
                    'display' => false,
                    'ticks' => ['beginAtZero' => true],
                    'gridLines' => ['display' => false]
                ],
            ],
            //'plugins' => '{datalabels: {color: \'red\'}, title: {display: true}}',
        ]);
            //->options= ["legend->position('bottom')"];

   
        //SPO2 Trend
        //Ketan commented for V3
        /*$top5BySPO2Dev = $totScanSPO2All
        ->where('created_at','>=',Carbon::today()->subDays(7))
        ->groupBy('deviceid')
        ->map(function($value){
            return(count($value));    
        })
        ->sortDesc()
        ->take(5)
        ->toArray();
        */
        $top5BySPO2Dev = DB::table('iotdatasummary')
        ->where('fordate', '>=',Carbon::today()->subDays(7))
        ->whereIn('device',$devNameList)
        ->selectRaw('sum(allabnormal) + sum(lowspo2) + sum(highpulseratelowspo2) + sum(lowspo2hightemp) as lowSpo2, device')
        ->groupBy('device')
        ->orderBy('device')
        ->pluck('lowSpo2','device');

        //dd($top5BySPO2Dev);
        $tempLocCollectionSPO2 = array();
        if(Auth::user()->hasRole(['Super Admin', 'Location Admin'])){
            //Ketan Commented for V3
            /*
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
            */
            foreach($top5BySPO2Dev as $k=>$k_value){
                $locName = $locDevAssociation[$k];
                if(array_key_exists($locName,$tempLocCollectionSPO2)){
                    $tempLocCollectionSPO2[$locName] = $tempLocCollectionSPO2[$locName] + $k_value;
                }
                else{
                    $tempLocCollectionSPO2[$locName]=$k_value;
                }
            }
        }
        else if(Auth::user()->hasRole(['Site Admin'])){
            foreach($top5BySPO2Dev as $k=>$k_value){
                $devName = $deviceNameAssociation[$k];
                if(array_key_exists($devName,$tempLocCollectionSPO2)){
                    $tempLocCollectionSPO2[$devName] = $tempLocCollectionSPO2[$devName] + $k_value;
                }
                else{
                    $tempLocCollectionSPO2[$devName]=$k_value;
                }
            }
            //Ketan Commented for V3
            /*
            foreach($top5BySPO2Dev as $r=>$r_value){
                $setName = Device::where('serial_no','=',$r)
                    ->join('LinkLocDev','device.id','LinkLocDev.deviceid')
                    ->pluck('LinkLocDev.name');
                $lblSpo2Chart->push($setName[0].' '.$r);
                $valSpo2Chart->push($r_value);
                //dd($locName[0], $r, $r_value);
            }
            */
        }
        arsort($tempLocCollectionSPO2);
        //Generating the Spo2 Chart for top 5 locations
        $lblSpo2Chart = collect([]);
        $valSpo2Chart = collect([]);
        foreach(array_slice($tempLocCollectionSPO2,0,5) as $r=>$r_value){
            $lblSpo2Chart->push($r);
            $valSpo2Chart->push($r_value);
        }
        $spo2Chart = new ReportChartLine();
        $colSpo2 = array();
        for ( $i = 0; $i<5; $i++){
            $colSpo2[$i] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }
        //get array for labels
        $spo2Chart->labels($lblSpo2Chart); 
        //dd($spo2Chart);
        $spo2Chart->dataset('Low SPO2', 'doughnut',$valSpo2Chart)
            ->backgroundColor($colSpo2);
        $spo2Chart->title('Low SPO2');
        $spo2Chart->options([
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
                    'gridLines' => ['display' => false]
                ],
                'xAxes' => [
                    'display' => false,
                    'ticks' => ['beginAtZero' => true],
                    'gridLines' => ['display' => false]
                ],
            ],
            //'plugins' => '{datalabels: {color: \'red\'}, title: {display: true}}',
        ]);


        //Pulse Rate Trend
        //Ketan Comment for V3

        /*$top5ByPulseDev = $totScanPulseAll
        ->where('created_at','>=',Carbon::today()->subDays(7))
        ->groupBy('deviceid')
        ->map(function($value){
            return(count($value));    
        })
        ->sortDesc()
        ->take(5)
        ->toArray(); */
        $top5ByPulseDev = DB::table('iotdatasummary')
            ->where('fordate', '>=',Carbon::today()->subDays(7))
            ->whereIn('device',$devNameList)
            ->selectRaw('sum(allabnormal) + sum(highpulserate) + sum(highpulseratelowspo2) + sum(highpulseratehightemp) as highPulseRate, device')
            ->groupBy('device')
            ->orderBy('device')
            ->pluck('highPulseRate','device');

        

        $tempLocCollectionPulse = array();
        if(Auth::user()->hasRole(['Super Admin', 'Location Admin'])){
            foreach($top5ByPulseDev as $k=>$k_value){
                $locName = $locDevAssociation[$k];
                if(array_key_exists($locName,$tempLocCollectionPulse)){
                    $tempLocCollectionPulse[$locName] = $tempLocCollectionPulse[$locName] + $k_value;
                }
                else{
                    $tempLocCollectionPulse[$locName]=$k_value;
                }
            }
            /*foreach($top5ByPulseDev as $r=>$r_value){
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
            }*/
        }
        else if(Auth::user()->hasRole(['Site Admin'])){
            foreach($top5ByPulseDev as $k=>$k_value){
                $devName = $deviceNameAssociation[$k];
                if(array_key_exists($devName,$tempLocCollectionPulse)){
                    $tempLocCollectionPulse[$devName] = $tempLocCollectionPulse[$devName] + $k_value;
                }
                else{
                    $tempLocCollectionPulse[$devName]=$k_value;
                }
            }
            //Ketan comment for V3
            /*foreach($top5ByPulseDev as $r=>$r_value){
                $setName = Device::where('serial_no','=',$r)
                    ->join('LinkLocDev','device.id','LinkLocDev.deviceid')
                    ->pluck('LinkLocDev.name');
                $lblPulseChart->push($setName[0].' '.$r);
                $valPulseChart->push($r_value);
                //dd($locName[0], $r, $r_value);
            }*/

        }
        arsort($tempLocCollectionPulse);
        //Generating the Pulse Chart for top 5 locations
        $lblPulseChart = collect([]);
        $valPulseChart = collect([]);
        foreach(array_slice($tempLocCollectionPulse,0,5) as $r=>$r_value){
            $lblPulseChart->push($r);
            $valPulseChart->push($r_value);
        }
        $pulseChart = new ReportChartLine();
        $colPulse = array();
        for ( $i = 0; $i<5; $i++){
            $colPulse[$i] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }
        //get array for labels
        $pulseChart->labels($lblPulseChart); 
        //dd($spo2Chart);
        $pulseChart->dataset('High Pulse Rate', 'doughnut',$valPulseChart)
            ->backgroundColor($colPulse);
        $pulseChart->title('High Pulse Rate');
        $pulseChart->options([
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
                    'gridLines' => ['display' => false]
                ],
                'xAxes' => [
                    'display' => false,
                    'ticks' => ['beginAtZero' => true],
                    'gridLines' => ['display' => false]
                ],
            ],
            //'plugins' => '{datalabels: {color: \'red\'}, title: {display: true}}',
        ]);
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
