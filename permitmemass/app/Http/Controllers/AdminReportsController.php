<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\IotData;
use App\Charts\ReportChartLine;
use App\Society;
use App\LinkLocDev;
use App\Device;
use App\vLocDev;
use Carbon\Carbon;
use DB;
use PdfReport;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
//use App\Helpers;
//use App\Location;

class AdminReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        
    }
    /**
     * This controller is used to generate reports for Administrator 
     * These are primarily search based reports 
     */


     public function sReport() {

        if(!Auth::user()->hasRole(['Super Admin', 'Location Admin'])){
            abort(403);
        }
        
        $loc = Society::where('location.isactive','=',true)
            ->join('LinkLocDev','location.id','LinkLocDev.locationid')
            ->join('device', 'device.id', 'LinkLocDev.deviceid')
            ->where('device.isactive','=',true)
            ->where('LinkLocDev.isactive','=',true)
            ->whereIn('device.serial_no',session('GDevId')->toArray())
            ->select (DB::raw("CONCAT(location.state,'.',location.district,'.',location.taluka,'.',location.city,'.',location.pincode,'.',location.name) AS name"))
            ->orderBy('name','asc')
            ->pluck('name','name')
            ->filter();
         
        $pin = Society::where('location.isactive','=',true)
            ->join('LinkLocDev','location.id','LinkLocDev.locationid')
            ->join('device', 'device.id', 'LinkLocDev.deviceid')
            ->where('device.isactive','=',true)
            ->where('LinkLocDev.isactive','=',true)
            ->whereIn('device.serial_no',session('GDevId')->toArray())
            ->distinct('pincode')
            ->select (DB::raw("CONCAT(location.state,'.',location.district,'.',location.taluka,'.',location.city,'.',location.pincode) AS name"))
            ->orderBy('name','asc')
            ->pluck('name','name')
            ->filter();

        $city = Society::where('location.isactive','=',true)
            ->join('LinkLocDev','location.id','LinkLocDev.locationid')
            ->join('device', 'device.id', 'LinkLocDev.deviceid')
            ->where('device.isactive','=',true)
            ->where('LinkLocDev.isactive','=',true)
            ->whereIn('device.serial_no',session('GDevId')->toArray())
            ->distinct('city')
            ->select (DB::raw("CONCAT(location.state,'.',location.district,'.',location.taluka,'.',location.city) AS name"))
            ->pluck('name','name')
            ->filter();

        $taluka = Society::where('location.isactive','=',true)
            ->join('LinkLocDev','location.id','LinkLocDev.locationid')
            ->join('device', 'device.id', 'LinkLocDev.deviceid')
            ->where('device.isactive','=',true)
            ->where('LinkLocDev.isactive','=',true)
            ->whereIn('device.serial_no',session('GDevId')->toArray())
            ->distinct('taluka')
            ->select (DB::raw("CONCAT(location.state,'.',location.district,'.',location.taluka) AS name"))
            ->orderBy('name','asc')
            ->pluck('name')
            ->filter();
        
        $district = Society::where('location.isactive','=',true)
            ->join('LinkLocDev','location.id','LinkLocDev.locationid')
            ->join('device', 'device.id', 'LinkLocDev.deviceid')
            ->where('device.isactive','=',true)
            ->where('LinkLocDev.isactive','=',true)
            ->whereIn('device.serial_no',session('GDevId')->toArray())
            ->distinct('district')
            ->select (DB::raw("CONCAT(location.state,'.',location.district) AS name"))
            ->orderBy('name','asc')
            ->pluck('name')
            ->filter();

        $state = Society::where('location.isactive','=','1')
            ->join('LinkLocDev','location.id','LinkLocDev.locationid')
            ->join('device', 'device.id', 'LinkLocDev.deviceid')
            ->where('device.isactive','=',true)
            ->where('LinkLocDev.isactive','=',true)
            ->whereIn('device.serial_no',session('GDevId')->toArray())
            ->distinct('location.state')
            ->orderBy('location.state','asc')
            ->pluck('location.state','location.state')
            ->filter();
        //dd($state);
        return view ('adminReports/sReport',compact('loc','pin','city','taluka','district','state'));
     }

     public function sLocationReport(Request $request){
         //echo ($request);
        if(!Auth::user()->hasRole(['Super Admin', 'Location Admin'])){
            abort(403);
        }
         //return 'in sLocationReport';
        $source = $request->input('source');
        $type = $request->input('type');
        $src = explode('.',$source);

        $state = '';
        $district = '';
        $taluka = '';
        $city = '';
        $pincode = '';
        $location = '';
         
        if(count($src) == 6){
            $state = $src[0];
            $district = $src[1];
            $taluka = $src[2];
            $city = $src[3];
            $pincode = $src[4];
            $location = $src[5];
        }
        else{
            
            abort(403);
        }

       
         /*$devs = vlocdev::where('state','=',$state)
            ->where('district','=',$district)
            ->where('taluka','=',$taluka)
            ->where('city','=',$city)
            ->where('pincode','=',$pincode)
            ->where('name','=',$location)
            ->where('locactive','=',true)
            ->where('linkactive','=',true)
            ->where('devactive','=',true)
            ->select('serial_no')
            ->pluck('serial_no')
            ->toArray();
        //dd($devs);
        */

        //changed for V2
        $devs = Society::distinct('location.name')
            ->join('LinkLocDev','location.id','LinkLocDev.locationid')
            ->join('device', 'device.id', 'LinkLocDev.deviceid')
            ->where('device.isactive','=',true)
            ->where('LinkLocDev.isactive','=',true)
            ->where('location.isactive','=',true)
            ->whereIn('device.serial_no',session('GDevId')->toArray())  
            ->select('device.serial_no')
            ->where('location.name','=',$location)
            ->where('location.pincode','=',$pincode)
            ->where('location.city','=',$city)
            ->where('location.taluka','=',$taluka)
            ->where('location.state','=', $state)
            ->where('location.district','=',$district)
            ->orderBy('device.serial_no', 'asc')
            ->get();
        //dd($devs);

        //V2 - we are making 4 tabs on the page that will show following data 
        // Tab 1 - all abnormal parameters
        // Tab 2 - High temperature 
        // Tab 3 - Low SPO2
        // Tab 4 - High Heart Rate

        //Data for Tab 1 All abnormal
/*        $messages = Message::where('to', Auth::id())
                ->orderBy('created_at', 'DESC')
                ->distinct('from')
                ->paginate(10);
*/
        $iotAllData = iotData::wherein('deviceid', $devs)
        ->where('created_at','>=',Carbon::today()->subDays(15))
        ->where('flagstatus','=',true)
        ->orderBy('created_at','desc')
        ->orderBy('identifier','asc')
        ->select('identifier', 'temp', 'spo2','hbcount', 'created_at')
        ->get();

        $iotDataAllAbnormal1 = ($iotAllData->where('hbcount','>',env('CUTOFF_PULSE'))
        ->where('spo2','<',env('CUTOFF_SPO2'))
        ->where('temp','>',env('CUTOFF_TEMP')))->unique('identifier');
        $iotDataAllAbnormal = $this->paginate($iotDataAllAbnormal1,20,$pageNo=0);
        $iotDataAllAbnormal->setPath('/adminReports/sLocationReport');

        //Data for Tab 2 - High Temperature
        $iotDataHighTemp1 = ($iotAllData->where('temp','>',env('CUTOFF_TEMP')))->unique('identifier');
        $iotDataHighTemp = $this->paginate($iotDataHighTemp1,20,$pageNo=0);
        $iotDataHighTemp->setPath('/adminReports/sLocationReport');

        // Data for Tab 3 - Low SPO2
        $iotDataLowSpo21 = ($iotAllData->where('spo2','<',env('CUTOFF_SPO2')))->unique('identifier');
        $iotDataLowSpo2 =$this->paginate($iotDataLowSpo21,20,$pageNo=0);
        $iotDataLowSpo2->setPath('/adminReports/sLocationReport');

        // Data for Tab 4 - High Heart Rate
        $iotDataHighHbcount1 = ($iotAllData->where('hbcount','>',env('CUTOFF_PULSE')))->unique('identifier');
        $iotDataHighHbcount = $this->paginate($iotDataHighHbcount1,20,$pageNo=0);
        $iotDataHighHbcount->setPath('/adminReports/sLocationReport');

        return view ('adminReports.sLocationReport',compact('iotDataAllAbnormal','iotDataHighTemp','iotDataLowSpo2','iotDataHighHbcount','location'));
       
     }

    public function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        //dd('in paginate',$page, $items);
        $k =new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
        //dd($k);
        return($k);
    }

     /**
      * Function to get data for Pincode by Location 
      */
   
      public function sPincodeReport(Request $request){
        if(!Auth::user()->hasRole(['Super Admin','Location Admin'])){
            abort(403);
        }
        $source = $request->input('source');
        //dd($source);
        //$type = $request->input('type');
        $src = explode('.',$source);
        $state = '';
        $district = '';
        $taluka= '';
        $city = '';
        $pincode = '';
        if(count($src) == 5){
            $state = $src[0];
            $district = $src[1];
            $taluka = $src[2];
            $city = $src[3];
            $pincode = $src[4];
        }
        else{
            abort(403);
        }
        //dd($source, $state, $district, $taluka, $city, $pincode);

        $repCollect = collect();

       
        //changed for V2
        $lname = Society::distinct('location.name')
            ->join('LinkLocDev','location.id','LinkLocDev.locationid')
            ->join('device', 'device.id', 'LinkLocDev.deviceid')
            ->where('device.isactive','=',true)
            ->where('LinkLocDev.isactive','=',true)
            ->where('location.isactive','=',true)
            ->whereIn('device.serial_no',session('GDevId')->toArray())  
            ->select('location.name')
            ->where('location.pincode','=',$pincode)
            ->where('location.city','=',$city)
            ->where('location.taluka','=',$taluka)
            ->where('location.state','=', $state)
            ->where('location.district','=',$district)
            ->orderBy('location.name', 'asc')
            ->get();
        //dd($lname);

        foreach ($lname as $n){
           
            $devNameList = vLocDev::whereIn('vlocdev.serial_no',session('GDevId')->toArray())
                ->where('vlocdev.name','=',$n->name)
                ->where('vlocdev.pincode','=',$pincode)
                ->where('vlocdev.city','=',$city)
                ->where('vlocdev.taluka','=',$taluka)
                ->where('vlocdev.district','=',$district)
                ->where('vlocdev.state','=',$state)
                ->where('vlocdev.locactive','=',true)
                ->where('vlocdev.linkactive','=',true)
                ->where('vlocdev.devactive','=',true)
                ->select('vlocdev.serial_no')
                ->get();
        //dd($devNameList);

                //All data for last 15 days with against devices assigned to that location(s)
                $iotDataAll = IotData::whereIn('deviceid',$devNameList)
                ->where('created_at','>=',Carbon::today()->subDays(15))
                ->get();
               
                //Filter all abnormal scans
                $iotDataAbnormal = $iotDataAll->where('flagstatus','=',true);
    
                //Total scanned records 
                $iotData = $iotDataAll->count();
    
                //Total records with high temp
                $iotDataTemp = $iotDataAbnormal->where('temp','>',env('CUTOFF_TEMP'))->count();
    
                //Total records for Low SPO2
                $iotDataSpo2 = $iotDataAbnormal->where('spo2','<',env('CUTOFF_SPO2'))->count();
    
                //Total records for high Pulse Rate
                $iotDataHbcount = $iotDataAbnormal->where('hbcount','<',env('CUTOFF_PULSE'))->count();
    
    
                //Total all abnormal records
                $iotDataAllAbnormal = $iotDataAbnormal
                ->where('hbcount','>',env('CUTOFF_PULSE'))
                ->where('spo2','<',env('CUTOFF_SPO2'))
                ->where('temp','>',env('CUTOFF_TEMP'))
                ->count();
    
            //now filling in the table 
            $f = array('name'=>$n->name, 'tempCount'=>$iotDataTemp, 'spo2Count'=>$iotDataSpo2, 'hbCount' => $iotDataHbcount, 'allAbnormal' => $iotDataAllAbnormal,'totalScan'=>$iotData);
            $repCollect ->push($f);
        }
        //dd($repCollect);

        $lbl = collect([]);
        $valuesTemp = collect([]);
        $valuesSpo2 = collect([]);
        $valuesHbcount = collect([]);
        $valuesAllAbnormal = collect([]);
        
        foreach($repCollect as $data){
            $lbl->push($data['name']);
            $valuesTemp->push($data['tempCount']);
            $valuesSpo2->push($data['spo2Count']);
            $valuesHbcount->push($data['hbCount']);
            $valuesAllAbnormal->push($data['allAbnormal']);
        }
        //dd($lbl);
        //dd($values);
        //getting count of taluka to generate colors 
        $col = array(count($repCollect));
        for ( $i = 0; $i<count($repCollect); $i++){
            $col[$i] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }

        $TempChart = new ReportChartLine();
        $TempChart->labels($lbl);
        $TempChart->dataset($pincode.": High Temperature by Location", 'doughnut',$valuesTemp)
            ->backgroundColor($col);
        $TempChart->title($state.': High Temperature by Location');
        $TempChart->options([
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
        

        $Spo2Chart = new ReportChartLine();
        $Spo2Chart->labels($lbl);
        $Spo2Chart->dataset($pincode.": Low Spo2 by Location", 'doughnut',$valuesSpo2)
            ->backgroundColor($col);
        $Spo2Chart->title($state.': Low Spo2 by Location');
        $Spo2Chart->options([
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

        $HbcountChart = new ReportChartLine();
        $HbcountChart->labels($lbl);
        $HbcountChart->dataset($pincode.": High Pulse Rate by Location", 'doughnut',$valuesHbcount)
            ->backgroundColor($col);
        $HbcountChart->title($state.': High Pulse Rate by Location');
        $HbcountChart->options([
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

        $AllAbnormalChart = new ReportChartLine();
        $AllAbnormalChart->labels($lbl);
        $AllAbnormalChart->dataset($pincode.": All Abnormal by Location", 'doughnut',$valuesAllAbnormal)
            ->backgroundColor($col);
        $AllAbnormalChart->title($state.': All Abnormal by Location');
        $AllAbnormalChart->options([
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

        return view('adminReports.sPincodeReport', compact('repCollect','state','district','taluka','city','pincode','TempChart','Spo2Chart', 'HbcountChart','AllAbnormalChart'));
        
      }

     /**
      * Function to get data for City by Pincode
      */

      public function sCityReport(Request $request){
        if(!Auth::user()->hasRole(['Super Admin','Location Admin'])){
            abort(403);
        }
        $source = $request->input('source');
        $type = $request->input('type');
        $src = explode('.',$source);
        $state = '';
        $district = '';
        $taluka= '';
        $city = '';
        if(count($src) == 4){
            $state = $src[0];
            $district = $src[1];
            $taluka = $src[2];
            $city = $src[3];
        }
        else{
            abort(400);
        }

        //dd($source, $state, $district, $taluka, $city, $type);

        $repCollect = collect();

        //changed for V2
        $pincodes = Society::distinct('location.pincode')
            ->join('LinkLocDev','location.id','LinkLocDev.locationid')
            ->join('device', 'device.id', 'LinkLocDev.deviceid')
            ->where('device.isactive','=',true)
            ->where('LinkLocDev.isactive','=',true)
            ->where('location.isactive','=',true)
            ->whereIn('device.serial_no',session('GDevId')->toArray())  
            ->select('location.pincode')
            ->where('location.city','=',$city)
            ->where('location.taluka','=',$taluka)
            ->where('location.state','=', $state)
            ->where('location.district','=',$district)
            ->orderBy('location.pincode', 'asc')
            ->get();

        //dd($pincodes);

        foreach ($pincodes as $p){
            
            //now lets get the devices attached to all these locations 
            $devNameList = vLocDev::whereIn('vlocdev.serial_no',session('GDevId')->toArray())
                ->where('vlocdev.pincode','=',$p->pincode)
                ->where('vlocdev.city','=',$city)
                ->where('vlocdev.taluka','=',$taluka)
                ->where('vlocdev.district','=',$district)
                ->where('vlocdev.state','=',$state)
                ->where('vlocdev.locactive','=',true)
                ->where('vlocdev.linkactive','=',true)
                ->where('vlocdev.devactive','=',true)
                ->select('vlocdev.serial_no')
                ->get();


                //All data for last 15 days with against devices assigned to that location(s)
            $iotDataAll = IotData::whereIn('deviceid',$devNameList)
            ->where('created_at','>=',Carbon::today()->subDays(15))
            ->get();
           
            //Filter all abnormal scans
            $iotDataAbnormal = $iotDataAll->where('flagstatus','=',true);

            //Total scanned records 
            $iotData = $iotDataAll->count();

            //Total records with high temp
            $iotDataTemp = $iotDataAbnormal->where('temp','>',env('CUTOFF_TEMP'))->count();

            //Total records for Low SPO2
            $iotDataSpo2 = $iotDataAbnormal->where('spo2','<',env('CUTOFF_SPO2'))->count();

            //Total records for high Pulse Rate
            $iotDataHbcount = $iotDataAbnormal->where('hbcount','<',env('CUTOFF_PULSE'))->count();


            //Total all abnormal records
            $iotDataAllAbnormal = $iotDataAbnormal
            ->where('hbcount','>',env('CUTOFF_PULSE'))
            ->where('spo2','<',env('CUTOFF_SPO2'))
            ->where('temp','>',env('CUTOFF_TEMP'))
            ->count();

            //now filling in the table 
            $f = array('pincode'=>$p->pincode, 'tempCount'=>$iotDataTemp, 'spo2Count'=>$iotDataSpo2, 'hbCount'=> $iotDataHbcount, 'allAbnormal' => $iotDataAllAbnormal,'totalScan'=>$iotData);
            $repCollect ->push($f);
        }
        //dd($repCollect);
        
        $lbl = collect([]);
        $valuesTemp = collect([]);
        $valuesSpo2 = collect([]);
        $valuesHbcount = collect([]);
        $valuesAllAbnormal = collect([]);
        
        foreach($repCollect as $data){
            $lbl->push($data['pincode']);
            $valuesTemp->push($data['tempCount']);
            $valuesSpo2->push($data['spo2Count']);
            $valuesHbcount->push($data['hbCount']);
            $valuesAllAbnormal->push($data['allAbnormal']);
        }
        //dd($lbl);
        //dd($values);
        //getting count of taluka to generate colors 
        $col = array(count($repCollect));
        for ( $i = 0; $i<count($repCollect); $i++){
            $col[$i] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }

        $TempChart = new ReportChartLine();
        $TempChart->labels($lbl);
        $TempChart->dataset($city. ": High Temperature by Pincode", 'doughnut',$valuesTemp)
            ->backgroundColor($col);
        $TempChart->title($state.': High Temperature by Pincode');
        $TempChart->options([
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
        

        $Spo2Chart = new ReportChartLine();
        $Spo2Chart->labels($lbl);
        $Spo2Chart->dataset($city. ": Low SPO2 by Pincode", 'doughnut',$valuesSpo2)
            ->backgroundColor($col);
        $Spo2Chart->title($state.': Low SPO2 by Pincode');
        $Spo2Chart->options([
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

        $HbcountChart = new ReportChartLine();
        $HbcountChart->labels($lbl);
        $HbcountChart->dataset($city. ": High Pulse Rate by Pincode", 'doughnut',$valuesHbcount)
            ->backgroundColor($col);
        $HbcountChart->title($state.': High Pulse Rate by Pincode');
        $HbcountChart->options([
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

        $AllAbnormalChart = new ReportChartLine();
        $AllAbnormalChart->labels($lbl);
        $AllAbnormalChart->dataset($city. ": All Abnormal by Pincode", 'doughnut',$valuesAllAbnormal)
            ->backgroundColor($col);
        $AllAbnormalChart->title($state.': All Abnormal by Pincode');
        $AllAbnormalChart->options([
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

        return view('adminReports.sCityReport', compact('repCollect','state','type','district','taluka','city','TempChart','Spo2Chart', 'HbcountChart','AllAbnormalChart'));

    }

     
     /**
      * Function to get data for Taluka
      */
    public function sTalukaReport(Request $request){
        if(!Auth::user()->hasRole(['Super Admin','Location Admin'])){
            abort(403);
        }
        $source = $request->input('source');
        $type = $request->input('type');
        $src = explode('.',$source);
        $state = '';
        $district = '';
        $taluka='';
        if(count($src) == 3){
            $state = $src[0];
            $district = $src[1];
            $taluka = $src[2];
        }
        else{
            abort(403);
        }
        //dd($source, $state, $district, $taluka, $type);

        $repCollect = collect();

        /*$city = Society::distinct('city')
            ->select('city')
            ->where('state','=', $state)
            ->where('district','=',$district)
            ->where('taluka','=', $taluka)
            ->where('isactive','=',true)
            ->orderBy('city', 'asc')
            ->paginate(15);
        */
        //changed for V2
        $city = Society::distinct('location.city')
            ->join('LinkLocDev','location.id','LinkLocDev.locationid')
            ->join('device', 'device.id', 'LinkLocDev.deviceid')
            ->where('device.isactive','=',true)
            ->where('LinkLocDev.isactive','=',true)
            ->where('location.isactive','=',true)
            ->whereIn('device.serial_no',session('GDevId')->toArray())  
            ->select('location.city')
            ->where('location.taluka','=',$taluka)
            ->where('location.state','=', $state)
            ->where('location.district','=',$district)
            ->orderBy('location.city', 'asc')
            ->get();
        //dd($city);

        foreach ($city as $c){
            //get the list of location id's attached to each location 
            
            //Changed for V2

            $devNameList = vLocDev::whereIn('vlocdev.serial_no',session('GDevId')->toArray())
                ->where('vlocdev.city','=',$c->city)
                ->where('vlocdev.taluka','=',$taluka)
                ->where('vlocdev.district','=',$district)
                ->where('vlocdev.state','=',$state)
                ->where('vlocdev.locactive','=',true)
                ->where('vlocdev.linkactive','=',true)
                ->where('vlocdev.devactive','=',true)
                ->select('vlocdev.serial_no')
                ->get();
            //dd($devNameList);

            //All data for last 15 days with against devices assigned to that location(s)
            $iotDataAll = IotData::whereIn('deviceid',$devNameList)
                ->where('created_at','>=',Carbon::today()->subDays(15))
                ->get();
               

            //Filter all abnormal scans
            $iotDataAbnormal = $iotDataAll->where('flagstatus','=',true);

            //Total scanned records 
            $iotData = $iotDataAll->count();

            //Total records with high temp
            $iotDataTemp = $iotDataAbnormal->where('temp','>',env('CUTOFF_TEMP'))->count();

            //Total records for Low SPO2
            $iotDataSpo2 = $iotDataAbnormal->where('spo2','<',env('CUTOFF_SPO2'))->count();

            //Total records for high Pulse Rate
            $iotDataHbcount = $iotDataAbnormal->where('hbcount','<',env('CUTOFF_PULSE'))->count();


            //Total all abnormal records
            $iotDataAllAbnormal = $iotDataAbnormal
            ->where('hbcount','>',env('CUTOFF_PULSE'))
            ->where('spo2','<',env('CUTOFF_SPO2'))
            ->where('temp','>',env('CUTOFF_TEMP'))
            ->count();

            //now filling in the table 
            $f = array('city'=>$c->city, 'tempCount'=>$iotDataTemp, 'spo2Count'=>$iotDataSpo2, 'hbCount'=>$iotDataHbcount , 'allAbnormal'=> $iotDataAllAbnormal, 'totalScan'=>$iotData);
            $repCollect ->push($f);
        }
        //dd($repCollect);

        $lbl = collect([]);
        $valuesTemp = collect([]);
        $valuesSpo2 = collect([]);
        $valuesHbcount = collect([]);
        $valuesAllAbnormal = collect([]);
        
        foreach($repCollect as $data){
            $lbl->push($data['city']);
            $valuesTemp->push($data['tempCount']);
            $valuesSpo2->push($data['spo2Count']);
            $valuesHbcount->push($data['hbCount']);
            $valuesAllAbnormal->push($data['allAbnormal']);
        }
        //dd($lbl);
        //dd($values);
        //getting count of taluka to generate colors 
        $col = array(count($repCollect));
        for ( $i = 0; $i<count($repCollect); $i++){
            $col[$i] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }

        $TempChart = new ReportChartLine();
        $TempChart->labels($lbl);
        $TempChart->dataset($taluka. ": High Temperature by City", 'doughnut',$valuesTemp)
            ->backgroundColor($col);
        $TempChart->title($state.': High Temperature by City');
        $TempChart->options([
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
        

        $Spo2Chart = new ReportChartLine();
        $Spo2Chart->labels($lbl);
        $Spo2Chart->dataset($taluka. ": Low Spo2 by City", 'doughnut',$valuesSpo2)
            ->backgroundColor($col);
        $Spo2Chart->title($state.': Low Spo2 by City');
        $Spo2Chart->options([
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

        $HbcountChart = new ReportChartLine();
        $HbcountChart->labels($lbl);
        $HbcountChart->dataset($taluka. ": Low Spo2 by City", 'doughnut',$valuesHbcount)
            ->backgroundColor($col);
        $HbcountChart->title($state.': Low Spo2 by City');
        $HbcountChart->options([
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

        $AllAbnormalChart = new ReportChartLine();
        $AllAbnormalChart->labels($lbl);
        $AllAbnormalChart->dataset($taluka. ": All Abnormal by City", 'doughnut',$valuesAllAbnormal)
            ->backgroundColor($col);
        $AllAbnormalChart->title($state.': All Abnormal by City');
        $AllAbnormalChart->options([
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

        return view('adminReports.sTalukaReport', compact('repCollect','state','type','district','taluka','TempChart','Spo2Chart', 'HbcountChart','AllAbnormalChart'));

    }

     /**
      * function to get data for a district 
      */
     public function sDistrictReport(Request $request){
        if(!Auth::user()->hasRole(['Super Admin','Location Admin'])){
            abort(403);
        }
         //dd($request->all());
        $source = $request->input('source');
        $type = $request->input('type');
        $src = explode('.',$source);
        $state = '';
        $district = '';
        if (count($src) == 2){
            //this is from report
            $state = $src[0];
            $district = $src[1];
        }
        else{
            abort(403);
        }
        //V2 - This else is not needed as the data is coming in the form of state.district
        //else{
            //This is from sReport -- This needs to me seamless. Cannot work in pieces.
            //$district = $src[0];
        //}
        //dd($source, $type, $state, $district);

        //getting list of Talukas under that state 
        $repCollect = collect();

 
        $taluka = Society::distinct('location.taluka')
            ->join('LinkLocDev','location.id','LinkLocDev.locationid')
            ->join('device', 'device.id', 'LinkLocDev.deviceid')
            ->where('device.isactive','=',true)
            ->where('LinkLocDev.isactive','=',true)
            ->where('location.isactive','=',true)
            ->whereIn('device.serial_no',session('GDevId')->toArray())  
            ->select('location.taluka')
            ->where('location.state','=', $state)
            ->where('location.district','=',$district)
            ->orderBy('location.taluka', 'asc')
            ->get();
        //dd($taluka);

        foreach ($taluka as $t){
            //writing for V2
            $devNameList = vLocDev::whereIn('vlocdev.serial_no',session('GDevId')->toArray())
                ->where('vlocdev.taluka','=',$t->taluka)
                ->where('vlocdev.district','=',$district)
                ->where('vlocdev.state','=',$state)
                ->where('vlocdev.locactive','=',true)
                ->where('vlocdev.linkactive','=',true)
                ->where('vlocdev.devactive','=',true)
                ->select('vlocdev.serial_no')
                ->get();

                //dd($devNameList->toArray(),session('GDevId'));
           

            //Getting all relevant data for the location. We can filter the data based on this one 
            //All data for last 15 days with against devices assigned to that location(s)
            $iotDataAll = IotData::whereIn('deviceid',$devNameList)
                ->where('created_at','>=',Carbon::today()->subDays(15))
                ->get();

            //Filter all abnormal scans
            $iotDataAbnormal = $iotDataAll->where('flagstatus','=',true);

            //Total scanned records 
            $iotData = $iotDataAll->count();

            //Total records with high temp
            $iotDataTemp = $iotDataAbnormal->where('temp','>',env('CUTOFF_TEMP'))->count();

            //Total records for Low SPO2
            $iotDataSpo2 = $iotDataAbnormal->where('spo2','<',env('CUTOFF_SPO2'))->count();

            //Total records for high Pulse Rate
            $iotDataHbcount = $iotDataAbnormal->where('hbcount','<',env('CUTOFF_PULSE'))->count();


            //Total all abnormal records
            $iotDataAllAbnormal = $iotDataAbnormal
            ->where('hbcount','>',env('CUTOFF_PULSE'))
            ->where('spo2','<',env('CUTOFF_SPO2'))
            ->where('temp','>',env('CUTOFF_TEMP'))
            ->count();


            //now filling in the table 
            $f = array('taluka'=>$t->taluka, 'tempCount'=>$iotDataTemp, 'spo2Count'=>$iotDataSpo2, 'hbCount'=> $iotDataHbcount, 'allAbnormal'=> $iotDataAllAbnormal ,'totalScan'=>$iotData);
            $repCollect ->push($f);
        }
        //dd($repCollect);

        $lbl = collect([]);
        $valuesTemp = collect([]);
        $valuesSpo2 = collect([]);
        $valuesHbcount = collect([]);
        $valuesAllAbnormal = collect([]);
        
        foreach($repCollect as $data){
            $lbl->push($data['taluka']);
            $valuesTemp->push($data['tempCount']);
            $valuesSpo2->push($data['spo2Count']);
            $valuesHbcount->push($data['hbCount']);
            $valuesAllAbnormal->push($data['allAbnormal']);
        }
    //dd($lbl);
    //dd($valuesAllAbnormal);
        //getting count of taluka to generate colors 
        $col = array(count($repCollect));
        for ( $i = 0; $i<count($repCollect); $i++){
            $col[$i] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }

        $TempChart = new ReportChartLine();
        $TempChart->labels($lbl);
        $TempChart->dataset($district. ": High Temperature by Taluka", 'doughnut',$valuesTemp)
            ->backgroundColor($col);
        $TempChart->title($state.': High Temperature by Taluka');
        $TempChart->options([
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
        

        $Spo2Chart = new ReportChartLine();
        $Spo2Chart->labels($lbl);
        $Spo2Chart->dataset($district. ": Low SPO2 by Taluka", 'doughnut',$valuesSpo2)
            ->backgroundColor($col);
        $Spo2Chart->title($state.': Low SPO2 by Taluka');
        $Spo2Chart->options([
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

        $HbcountChart = new ReportChartLine();
        $HbcountChart->labels($lbl);
        $HbcountChart->dataset($district. ": High Pulse Rate by Taluka", 'doughnut',$valuesHbcount)
            ->backgroundColor($col);
        $HbcountChart->title($state.': High Pulse Rate by Taluka');
        $HbcountChart->options([
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

        $AllAbnormalChart = new ReportChartLine();
        $AllAbnormalChart->labels($lbl);
        $AllAbnormalChart->dataset($district. ": High Pulse Rate by Taluka", 'doughnut',$valuesAllAbnormal)
            ->backgroundColor($col);
        $AllAbnormalChart->title($state.': High Pulse Rate by Taluka');
        $AllAbnormalChart->options([
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
        
        return view('adminReports.sDistrictReport', compact('repCollect','state','type','district','taluka','TempChart','Spo2Chart','HbcountChart','AllAbnormalChart'));


     }

     public function sUserReport(Request $request) {
        
        //return ('in user report');
        if(!Auth::user()->hasRole(['Super Admin', 'Location Admin'])){
            abort(403);
        }

        $identifier = $request->input('identifier');
        $this -> validate($request, [
            'identifier' => 'required|digits:10'
        ]);
        
        if(Auth::user()->hasRole(['Super Admin', 'Location Admin'])){
        $iotData = IotData::where('iotdata.identifier','=',$identifier)
            ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
            ->whereIn('iotdata.deviceid',session('GDevId'))
            ->join('vlocdev','iotdata.deviceid','vlocdev.serial_no')
            ->select('iotdata.identifier','iotdata.temp','iotdata.spo2', 'iotdata.hbcount','iotdata.created_at', 'iotdata.flagstatus', 'vlocdev.name as name')
            ->orderBy('created_at','desc')
            ->orderBy('vlocdev.name','desc')
            ->paginate(50);
        }
        elseif(Auth::user()->hasRole('Site Admin')){
            abort(403);

            $iotData = IotData::where('iotdata.identifier','=',$identifier)
            ->whereIn('iotdata.deviceid',session('GDevId'))
            ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
            ->select('iotdata.identifier','iotdata.temp','iotdata.spo2', 'iotdata.hbcount','iotdata.created_at', 'iotdata.flagstatus', 'iotdata.deviceid as name')
            ->orderBy('created_at','desc')
            ->orderBy('iotdata.deviceid','desc')
            ->paginate(50);
        }
        //dd($iotData);

        if(count($iotData) > 0){
            $lbl = collect([]);
            $valuesTemp = collect([]);
            $valuesSpo2 = collect([]);
            $valuesHbcount = collect([]);

            foreach($iotData as $data){
                $lbl->push($data->created_at->format('Y-m-d h:i:s'));
                $valuesTemp->push($data->temp);
                $valuesSpo2->push($data->spo2);
                $valuesHbcount->push($data->hbcount);
            }
        //dd($lbl);
        //dd($values);

            //Generating SPO2 Chart
            $spo2Chart = new ReportChartLine();
            $spo2Chart->labels($lbl);
            $spo2Chart->dataset('Low SPO2', 'bar',$valuesSpo2)
                ->backgroundColor('red');
            $spo2Chart->title('Low SPO2');
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
            
            //Generating Temperature chart
            $tempChart = new ReportChartLine();
            $tempChart->labels($lbl);
            $tempChart->dataset('High Temperature','bar',$valuesTemp)
                ->backgroundColor('blue');
            $tempChart->title('High Temperature');
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

            //Generating HB Count chart
            $hbcountChart = new ReportChartLine();
            $hbcountChart->labels($lbl);
            $hbcountChart->dataset('High Pulse Rate','bar',$valuesHbcount)
                ->backgroundColor('orange');
            $hbcountChart->title('High Pulse Rate');
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

            return view('adminReports.sUserReportv',compact('identifier','iotData','spo2Chart','tempChart', 'hbcountChart'));
        }
        else{
            return view('adminReports.sUserReportv',compact('identifier','iotData'))->with('error','No data available for this user');
        }
     }

     //function to get report by state 
     public function sStateReport(Request $request){
        if(!Auth::user()->hasRole(['Super Admin','Location Admin'])){
            abort(403);
        }
        $state = $request->input('state');
        //$type = $request->input('type');
        //dd($state);
        //getting list of Districts under that state 
        $repCollect = collect();

        $district = Society::distinct('location.district')
            ->join('LinkLocDev','location.id','LinkLocDev.locationid')
            ->join('device', 'device.id', 'LinkLocDev.deviceid')
            ->where('device.isactive','=',true)
            ->where('LinkLocDev.isactive','=',true)
            ->where('location.isactive','=',true)
            ->whereIn('device.serial_no',session('GDevId')->toArray())
            ->select('location.district')
            ->where('location.state','=', $state)
            ->orderBy('location.district', 'asc')
            ->get();
        //dd($district);

        foreach ($district as $c){
            //writing for V2
            $devNameList = vLocDev::where('vlocdev.district','=',$c->district)
                ->whereIn('vlocdev.serial_no',session('GDevId')->toArray())
                ->where('vlocdev.locactive','=',true)
                ->where('vlocdev.linkactive','=',true)
                ->where('vlocdev.devactive','=',true)
                ->select('vlocdev.serial_no')
                ->get();

                //dd($devNameList->toArray(),session('GDevId'));

            //Getting all relevant data for the location. We can filter the data based on this one 
            //All data for last 15 days with against devices assigned to that location(s)
            $iotDataAll = IotData::whereIn('deviceid',$devNameList)
                ->where('created_at','>=',Carbon::today()->subDays(15))
                ->get();

            //Filter all abnormal scans
            $iotDataAbnormal = $iotDataAll->where('flagstatus','=',true);

            //Total scanned records 
            $iotData = $iotDataAll->count();

            //Total records with high temp
            $iotDataTemp = $iotDataAbnormal->where('temp','>',env('CUTOFF_TEMP'))->count();

            //Total records for Low SPO2
            $iotDataSpo2 = $iotDataAbnormal->where('spo2','<',env('CUTOFF_SPO2'))->count();

            //Total records for high Pulse Rate
            $iotDataHbcount = $iotDataAbnormal->where('hbcount','<',env('CUTOFF_PULSE'))->count();


            //Total all abnormal records
            $iotDataAllAbnormal = $iotDataAbnormal
            ->where('hbcount','>',env('CUTOFF_PULSE'))
            ->where('spo2','<',env('CUTOFF_SPO2'))
            ->where('temp','>',env('CUTOFF_TEMP'))
            ->count();
                        

            //now filling in the table 
            $f = array('district'=>$c->district, 'tempCount'=>$iotDataTemp, 'spo2Count'=>$iotDataSpo2, 'hbCount'=>$iotDataHbcount, 'allAbnormal'=>$iotDataAllAbnormal, 'totalScan'=>$iotData);
            $repCollect ->push($f);

        }
        //$repCollect->paginate(15);
                //dd($repCollect);
        $lbl = collect([]);
        $valuesTemp = collect([]);
        $valuesSpo2 = collect([]);
        $valuesHbcount = collect([]);
        $valuesAllAbnormal = collect([]);
        
        foreach($repCollect as $data){
            $lbl->push($data['district']);
            $valuesTemp->push($data['tempCount']);
            $valuesSpo2->push($data['spo2Count']);
            $valuesHbcount->push($data['hbCount']);
            $valuesAllAbnormal->push($data['allAbnormal']);
        }
    //dd($lbl);
    //dd($values);
        //getting count of cities to generate colors 
        $col = array(count($repCollect));
        for ( $i = 0; $i<count($repCollect); $i++){
            $col[$i] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }

        $TempChart = new ReportChartLine();
        $TempChart->labels($lbl);
        $TempChart->dataset($state.': High Temperature by District', 'doughnut',$valuesTemp)
            ->backgroundColor($col);
        $TempChart->title($state.': High Temperature by District');
        $TempChart->options([
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
        

        $Spo2Chart = new ReportChartLine();
        $Spo2Chart->labels($lbl);
        $Spo2Chart->dataset($state.': Low Spo2 by Distict', 'doughnut',$valuesSpo2)
            ->backgroundColor($col);
        $Spo2Chart->title($state.': Low Spo2 by Distict');
        $Spo2Chart->options([
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

        $HbcountChart = new ReportChartLine();
        $HbcountChart->labels($lbl);
        $HbcountChart->dataset($state.': High Pulse Rate by Distict', 'doughnut',$valuesHbcount)
            ->backgroundColor($col);
        $HbcountChart->title($state.': High Pulse Rate by Distict');
        $HbcountChart->options([
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

        $AllAbnormalChart = new ReportChartLine();
        $AllAbnormalChart->labels($lbl);
        $AllAbnormalChart->dataset($state.': All Abnormal by Distict', 'doughnut',$valuesAllAbnormal)
            ->backgroundColor($col);
        $AllAbnormalChart->title($state.': All Abnormal by Distict');
        $AllAbnormalChart->options([
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

            return view('adminReports.sStateReport', compact('repCollect','state','TempChart','Spo2Chart','HbcountChart','AllAbnormalChart'));
    }

 }
