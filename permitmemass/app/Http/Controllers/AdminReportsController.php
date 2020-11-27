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
        
        $loc = Society::where('isactive','=','1')
            ->select (DB::raw("CONCAT(state,'.',district,'.',taluka,'.',city,'.',pincode,'.',name) AS name"))
            ->orderBy('name','asc')
            ->pluck('name','name')
            ->filter();
         
        $pin = Society::where('isactive','=','1')
            ->distinct('pincode')
            ->select (DB::raw("CONCAT(state,'.',district,'.',taluka,'.',city,'.',pincode) AS name"))
            ->orderBy('name','asc')
            ->pluck('name','name')
            ->filter();

        $city = Society::where('isactive','=','1')
            ->distinct('city')
            ->select (DB::raw("CONCAT(state,'.',district,'.',taluka,'.',city) AS name"))
            ->pluck('name','name')
            ->filter();

        $taluka = Society::where('isactive','=','1')
            ->distinct('taluka')
            ->select (DB::raw("CONCAT(state,'.',district,'.',taluka) AS name"))
            ->orderBy('name','asc')
            ->pluck('name')
            ->filter();
        
        $district = Society::where('isactive','=','1')
            ->distinct('district')
            ->select (DB::raw("CONCAT(state,'.',district) AS name"))
            ->orderBy('name','asc')
            ->pluck('name')
            ->filter();

        $state = Society::where('isactive','=','1')
            ->distinct('state')
            ->orderBy('state','asc')
            ->pluck('state','state')
            ->filter();

        return view ('adminReports/sReport',compact('loc','pin','city','taluka','district','state'));
     }

     public function sLocationReport(Request $request){
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
         
        if(count($src) >1){
            $state = $src[0];
            $district = $src[1];
            $taluka = $src[2];
            $city = $src[3];
            $pincode = $src[4];
            $location = $src[5];
        }
        else{
            redirect ('adminReports.sReport')->with('error','No Data for this location.');
        }

       
         $devs = vlocdev::where('state','=',$state)
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

        $iotData = (iotData::wherein('deviceid', $devs)
            ->where('created_at','>=',Carbon::today()->subDays(15))
            ->where('created_at','<=',Carbon::today()->addDays(1))
            ->orderBy('identifier','asc')
            ->select('identifier', 'temp', 'spo2','hbcount', 'created_at')
            ->get())
            ->unique('identifier');

            $this->paginate($iotData,50,$pageNo=0);
        
            if(count($iotData) == 0){
            redirect ('adminReports.sReport')->with('error','No Data for this location.');
        }

         
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
         return view ('adminReports.sLocationReport',compact('iotData','location','spo2Chart','tempChart'));

     }

    public function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

     /**
      * Function to get data for Pincode by Location 
      */
   
      public function sPincodeReport(Request $request){
        $source = $request->input('source');
        //dd($source);
        //$type = $request->input('type');
        $src = explode('.',$source);
        $state = '';
        $district = '';
        $taluka= '';
        $city = '';
        $pincode = '';
        if(count($src) >1){
            $state = $src[0];
            $district = $src[1];
            $taluka = $src[2];
            $city = $src[3];
            $pincode = $src[4];
        }
        else{
            redirect ('adminReports.sReport')->with('error','No Data for this pincode.');
        }
        //dd($source, $state, $district, $taluka, $city, $pincode);

        $repCollect = collect();

        $lname = Society::distinct('name')
            ->select('name')
            ->where('state','=', $state)
            ->where('district','=',$district)
            ->where('taluka','=', $taluka)
            ->where('city','=', $city)
            ->where('pincode','=',$pincode)
            ->where('isactive','=',true)
            ->orderBy('name', 'asc')
            ->paginate(15);
        //dd($lname);

        foreach ($lname as $n){
            $devs = vlocdev::where('state','=',$state)
            ->where('district','=',$district)
            ->where('taluka','=',$taluka)
            ->where('city','=',$city)
            ->where('pincode','=',$pincode)
            ->where('name','=',$n->name)
            ->where('locactive','=',true)
            ->where('linkactive','=',true)
            ->where('devactive','=',true)
            ->select('serial_no')
            ->pluck('serial_no')
            ->toArray();
        //dd($devs);
        
                        $iotDataTemp = count(IotData::whereIn('deviceid',$devs)
                            ->where('created_at','>=',Carbon::today()->subDays(15))
                            ->where('created_at','<=',Carbon::today()->addDays(1))
                            ->where('temp','>=',94.5)
                            ->get());

                        $iotDataSpo2 = count(IotData::whereIn('deviceid',$devs)
                            ->where('created_at','>=',Carbon::today()->subDays(15))
                            ->where('created_at','<=',Carbon::today()->addDays(1))
                            ->where('spo2','<=',93)
                            ->get());

                        $iotData = count(IotData::whereIn('deviceid',$devs)
                            ->where('created_at','>=',Carbon::today()->subDays(15))
                            ->where('created_at','<=',Carbon::today()->addDays(1))
                            ->get());

            //now filling in the table 
            $f = array('name'=>$n->name, 'tempCount'=>$iotDataTemp, 'spo2Count'=>$iotDataSpo2, 'totalScan'=>$iotData);
            $repCollect ->push($f);
        }
        //dd($repCollect);

        $lbl = collect([]);
        $valuesTemp = collect([]);
        $valuesSpo2 = collect([]);
        
        foreach($repCollect as $data){
            $lbl->push($data['name']);
            $valuesTemp->push($data['tempCount']);
            $valuesSpo2->push($data['spo2Count']);
        }
        //dd($lbl);
        //dd($values);
        //getting count of taluka to generate colors 
        $col = array(count($repCollect));
        for ( $i = 0; $i<count($repCollect); $i++){
            $col[$i] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }

        $TempChart = new ReportChartLine();
        //get array for labels
        $TempChart->labels($lbl);
    //dd($spo2Chart);
        $TempChart->dataset('Data for Pincode '.$pincode. "with High Temperature by Location", 'pie',$valuesTemp)
            ->backgroundColor($col);
        

        $Spo2Chart = new ReportChartLine();
        //get array for labels
        $Spo2Chart->labels($lbl);
    //dd($spo2Chart);
        $Spo2Chart->dataset('Data for Pincode '.$pincode. "with Low Spo2 by Location", 'pie',$valuesSpo2)
            ->backgroundColor($col);
        return view('adminReports.sPincodeReport', compact('repCollect','state','district','taluka','city','pincode','TempChart','Spo2Chart'));
        
      }

     /**
      * Function to get data for City by Pincode
      */

      public function sCityReport(Request $request){
        $source = $request->input('source');
        $type = $request->input('type');
        $src = explode('.',$source);
        $state = '';
        $district = '';
        $taluka= '';
        $city = '';
        if(count($src) >1){
            $state = $src[0];
            $district = $src[1];
            $taluka = $src[2];
            $city = $src[3];
        }
        else{
            $city = $src[0];
        }

        //dd($source, $state, $district, $taluka, $city, $type);

        $repCollect = collect();

        $pincodes = Society::distinct('pincode')
            ->select('pincode')
            ->where('state','=', $state)
            ->where('district','=',$district)
            ->where('taluka','=', $taluka)
            ->where('city','=', $city)
            ->where('isactive','=',true)
            ->orderBy('pincode', 'asc')
            ->paginate(15);
        //dd($pincodes);

        foreach ($pincodes as $p){
            //get the list of location id's attached to each location 
            $lIDs = Society::where('pincode','=',$p->pincode)
                ->select ('id')
                ->where('state', '=', $state)
                ->where('district','=',$district)
                ->where('taluka','=',$taluka)
                ->where('city','=',$city)
                ->where ('isactive','=',true)
                ->get()->toArray();
                //This is the list of devices that are attached to Taluka

                //now lets get the devices attached to all these locations 
                $devLocList = LinkLocDev::whereIn('locationid',$lIDs)
                    ->where('isactive','=',true)
                    ->select('deviceid')
                    ->get()->toArray();
                    //here i have the list of all the location id's

                    $devNameList = Device::whereIn('id',$devLocList)
                        ->where('isactive','=',true)
                        ->select('serial_no')
                        ->get()->toArray();
                        //have the list of device names here 

                        $iotDataTemp = count(IotData::whereIn('deviceid',$devNameList)
                            ->where('created_at','>=',Carbon::today()->subDays(15))
                            ->where('created_at','<=',Carbon::today()->addDays(1))
                            ->where('temp','>=',94.5)
                            ->get());

                        $iotDataSpo2 = count(IotData::whereIn('deviceid',$devNameList)
                            ->where('created_at','>=',Carbon::today()->subDays(15))
                            ->where('created_at','<=',Carbon::today()->addDays(1))
                            ->where('spo2','<=',93)
                            ->get());

                        $iotData = count(IotData::whereIn('deviceid',$devNameList)
                            ->where('created_at','>=',Carbon::today()->subDays(15))
                            ->where('created_at','<=',Carbon::today()->addDays(1))
                            ->get());

            //now filling in the table 
            $f = array('pincode'=>$p->pincode, 'tempCount'=>$iotDataTemp, 'spo2Count'=>$iotDataSpo2, 'totalScan'=>$iotData);
            $repCollect ->push($f);
        }
        //dd($repCollect);
        
        $lbl = collect([]);
        $valuesTemp = collect([]);
        $valuesSpo2 = collect([]);
        
        foreach($repCollect as $data){
            $lbl->push($data['pincode']);
            $valuesTemp->push($data['tempCount']);
            $valuesSpo2->push($data['spo2Count']);
        }
        //dd($lbl);
        //dd($values);
        //getting count of taluka to generate colors 
        $col = array(count($repCollect));
        for ( $i = 0; $i<count($repCollect); $i++){
            $col[$i] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }

        $TempChart = new ReportChartLine();
        //get array for labels
        $TempChart->labels($lbl);
    //dd($spo2Chart);
        $TempChart->dataset('Data for City of '.$city. "with High Temperature by Pincode", 'pie',$valuesTemp)
            ->backgroundColor($col);
        

        $Spo2Chart = new ReportChartLine();
        //get array for labels
        $Spo2Chart->labels($lbl);
    //dd($spo2Chart);
        $Spo2Chart->dataset('Data for City of '.$city. "with Low Spo2 by Pincode", 'pie',$valuesSpo2)
            ->backgroundColor($col);
        return view('adminReports.sCityReport', compact('repCollect','state','type','district','taluka','city','TempChart','Spo2Chart'));

    }

     
     /**
      * Function to get data for Taluka
      */
    public function sTalukaReport(Request $request){
        $source = $request->input('source');
        $type = $request->input('type');
        $src = explode('.',$source);
        $state = '';
        $district = '';
        $taluka='';
        if(count($src) >1){
            $state = $src[0];
            $district = $src[1];
            $taluka = $src[2];
        }
        else{
            $taluka = $src[0];
        }
        //dd($source, $state, $district, $taluka, $type);

        $repCollect = collect();

        $city = Society::distinct('city')
            ->select('city')
            ->where('state','=', $state)
            ->where('district','=',$district)
            ->where('taluka','=', $taluka)
            ->where('isactive','=',true)
            ->orderBy('city', 'asc')
            ->paginate(15);
        //dd($city);

        foreach ($city as $c){
            //get the list of location id's attached to each location 
            $lIDs = Society::where('city','=',$c->city)
                ->select ('id')
                ->where('state', '=', $state)
                ->where('district','=',$district)
                ->where('taluka','=',$taluka)
                ->where ('isactive','=',true)
                ->get()->toArray();
                //This is the list of devices that are attached to Taluka

                //now lets get the devices attached to all these locations 
                $devLocList = LinkLocDev::whereIn('locationid',$lIDs)
                    ->where('isactive','=',true)
                    ->select('deviceid')
                    ->get()->toArray();
                    //here i have the list of all the location id's

                    $devNameList = Device::whereIn('id',$devLocList)
                        ->where('isactive','=',true)
                        ->select('serial_no')
                        ->get()->toArray();
                        //have the list of device names here 

                        $iotDataTemp = count(IotData::whereIn('deviceid',$devNameList)
                            ->where('created_at','>=',Carbon::today()->subDays(15))
                            ->where('created_at','<=',Carbon::today()->addDays(1))
                            ->where('temp','>=',94.5)
                            ->get());

                        $iotDataSpo2 = count(IotData::whereIn('deviceid',$devNameList)
                            ->where('created_at','>=',Carbon::today()->subDays(15))
                            ->where('created_at','<=',Carbon::today()->addDays(1))
                            ->where('spo2','<=',93)
                            ->get());

                        $iotData = count(IotData::whereIn('deviceid',$devNameList)
                            ->where('created_at','>=',Carbon::today()->subDays(15))
                            ->where('created_at','<=',Carbon::today()->addDays(1))
                            ->get());

            //now filling in the table 
            $f = array('city'=>$c->city, 'tempCount'=>$iotDataTemp, 'spo2Count'=>$iotDataSpo2, 'totalScan'=>$iotData);
            $repCollect ->push($f);
        }
        //dd($repCollect);

        $lbl = collect([]);
        $valuesTemp = collect([]);
        $valuesSpo2 = collect([]);
        
        foreach($repCollect as $data){
            $lbl->push($data['city']);
            $valuesTemp->push($data['tempCount']);
            $valuesSpo2->push($data['spo2Count']);
        }
        //dd($lbl);
        //dd($values);
        //getting count of taluka to generate colors 
        $col = array(count($repCollect));
        for ( $i = 0; $i<count($repCollect); $i++){
            $col[$i] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }

        $TempChart = new ReportChartLine();
        //get array for labels
        $TempChart->labels($lbl);
    //dd($spo2Chart);
        $TempChart->dataset('Data for Taluka of '.$taluka. "with High Temperature by City", 'pie',$valuesTemp)
            ->backgroundColor($col);
        

        $Spo2Chart = new ReportChartLine();
        //get array for labels
        $Spo2Chart->labels($lbl);
    //dd($spo2Chart);
        $Spo2Chart->dataset('Data for Taluka of '.$taluka. "with Low Spo2 by City", 'pie',$valuesSpo2)
            ->backgroundColor($col);
        return view('adminReports.sTalukaReport', compact('repCollect','state','type','district','taluka','TempChart','Spo2Chart'));

    }

     /**
      * function to get data for a district 
      */
     public function sDistrictReport(Request $request){
         //dd($request->all());
        $source = $request->input('source');
        $type = $request->input('type');
        $src = explode('.',$source);
        $state = '';
        $district = '';
        if (count($src) > 1){
            //this is from report
            $state = $src[0];
            $district = $src[1];
        }
        else{
            //This is from sReport
            $district = $src[0];
        }
        //dd($source, $type, $state, $district);

        //getting list of Talukas under that state 
        $repCollect = collect();

        $taluka = Society::distinct('taluka')
            ->select('taluka')
            ->where('state','=', $state)
            ->where('district','=',$district)
            ->where('isactive','=',true)
            ->orderBy('taluka', 'asc')
            ->paginate(15);
        //dd($taluka);

        foreach ($taluka as $t){
            //get the list of location id's attached to each location 
            $lIDs = Society::where('taluka','=',$t->taluka)
                ->select ('id')
                ->where('state', '=', $state)
                ->where('district','=',$district)
                ->where ('isactive','=',true)
                ->get()->toArray();
                //This is the list of devices that are attached to Taluka

                //now lets get the devices attached to all these locations 
                $devLocList = LinkLocDev::whereIn('locationid',$lIDs)
                    ->where('isactive','=',true)
                    ->select('deviceid')
                    ->get()->toArray();
                    //here i have the list of all the location id's

                    $devNameList = Device::whereIn('id',$devLocList)
                        ->where('isactive','=',true)
                        ->select('serial_no')
                        ->get()->toArray();
                        //have the list of device names here 

                        $iotDataTemp = count(IotData::whereIn('deviceid',$devNameList)
                            ->where('created_at','>=',Carbon::today()->subDays(15))
                            ->where('created_at','<=',Carbon::today()->addDays(1))
                            ->where('temp','>=',94.5)
                            ->get());

                        $iotDataSpo2 = count(IotData::whereIn('deviceid',$devNameList)
                            ->where('created_at','>=',Carbon::today()->subDays(15))
                            ->where('created_at','<=',Carbon::today()->addDays(1))
                            ->where('spo2','<=',93)
                            ->get());

                        $iotData = count(IotData::whereIn('deviceid',$devNameList)
                            ->where('created_at','>=',Carbon::today()->subDays(15))
                            ->where('created_at','<=',Carbon::today()->addDays(1))
                            ->get());

            //now filling in the table 
            $f = array('taluka'=>$t->taluka, 'tempCount'=>$iotDataTemp, 'spo2Count'=>$iotDataSpo2, 'totalScan'=>$iotData);
            $repCollect ->push($f);
        }
        //dd($repCollect);

        $lbl = collect([]);
        $valuesTemp = collect([]);
        $valuesSpo2 = collect([]);
        
        foreach($repCollect as $data){
            $lbl->push($data['taluka']);
            $valuesTemp->push($data['tempCount']);
            $valuesSpo2->push($data['spo2Count']);
        }
    //dd($lbl);
    //dd($values);
        //getting count of taluka to generate colors 
        $col = array(count($repCollect));
        for ( $i = 0; $i<count($repCollect); $i++){
            $col[$i] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }

        $TempChart = new ReportChartLine();
        //get array for labels
        $TempChart->labels($lbl);
    //dd($spo2Chart);
        $TempChart->dataset('Data for District of '.$district. "with High Temperature by Taluka", 'pie',$valuesTemp)
            ->backgroundColor($col);
        

        $Spo2Chart = new ReportChartLine();
        //get array for labels
        $Spo2Chart->labels($lbl);
    //dd($spo2Chart);
        $Spo2Chart->dataset('Data for District of '.$district. "with Low Spo2 by Taluka", 'pie',$valuesSpo2)
            ->backgroundColor($col);
        return view('adminReports.sDistrictReport', compact('repCollect','state','type','district','taluka','TempChart','Spo2Chart'));


     }

     public function sUserReport(Request $request) {

        //return ('in user report');

        $identifier = $request->input('identifier');
        $this -> validate($request, [
            'identifier' => 'required|gt:0'
        ]);

        $iotData = IotData::where('identifier','=',$identifier)
            ->orderBy('created_at','desc')
            ->paginate(10);
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
            return view('adminReports.sUserReportv',compact('identifier','iotData','spo2Chart','tempChart'));
        }
        else{
            return view('adminReports.sUserReportv',compact('identifier','iotData'))->with('error','No data available for this user');
        }
     }

     //function to get report by state 
     public function sStateReport(Request $request){
        $state = $request->input('state');
        $type = $request->input('type');
        //dd($state);
        //getting list of Districts under that state 
        $repCollect = collect();

        $district = Society::distinct('district')
            ->select('district')
            ->where('state','=', $state)
            ->where('isactive','=',true)
            ->orderBy('district', 'asc')
            ->paginate(15);
        //dd($city);

        foreach ($district as $c){
            //get the list of location id's attached to each location 
            $lIDs = Society::where('district','=',$c->district)
                ->select ('id')
                ->where('state', '=', $state)
                ->where ('isactive','=',true)
                ->get()->toArray();
                //This is the list of devices that are attached to city

                //now lets get the devices attached to all these locations 
                $devLocList = LinkLocDev::whereIn('locationid',$lIDs)
                    ->where('isactive','=',true)
                    ->select('deviceid')
                    ->get()->toArray();
                    //here i have the list of all the location id's

                    $devNameList = Device::whereIn('id',$devLocList)
                        ->where('isactive','=',true)
                        ->select('serial_no')
                        ->get()->toArray();
                        //have the list of device names here 

                        $iotDataTemp = count(IotData::whereIn('deviceid',$devNameList)
                            ->where('created_at','>=',Carbon::today()->subDays(15))
                            ->where('created_at','<=',Carbon::today()->addDays(1))
                            ->where('temp','>=',94.5)
                            ->get());

                        $iotDataSpo2 = count(IotData::whereIn('deviceid',$devNameList)
                            ->where('created_at','>=',Carbon::today()->subDays(15))
                            ->where('created_at','<=',Carbon::today()->addDays(1))
                            ->where('spo2','<=',93)
                            ->get());

                        $iotData = count(IotData::whereIn('deviceid',$devNameList)
                            ->where('created_at','>=',Carbon::today()->subDays(15))
                            ->where('created_at','<=',Carbon::today()->addDays(1))
                            ->get());

            //now filling in the table 
            $f = array('district'=>$c->district, 'tempCount'=>$iotDataTemp, 'spo2Count'=>$iotDataSpo2, 'totalScan'=>$iotData);
            $repCollect ->push($f);

        }
        //$repCollect->paginate(15);
                //dd($repCollect);
        $lbl = collect([]);
        $valuesTemp = collect([]);
        $valuesSpo2 = collect([]);
        
        foreach($repCollect as $data){
            $lbl->push($data['district']);
            $valuesTemp->push($data['tempCount']);
            $valuesSpo2->push($data['spo2Count']);
        }
    //dd($lbl);
    //dd($values);
        //getting count of cities to generate colors 
        $col = array(count($repCollect));
        for ( $i = 0; $i<count($repCollect); $i++){
            $col[$i] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }

        $TempChart = new ReportChartLine();
        //get array for labels
        $TempChart->labels($lbl);
    //dd($spo2Chart);
        $TempChart->dataset('Data for state of '.$state. "with High Temperature by District", 'pie',$valuesTemp)
            ->backgroundColor($col);
        

        $Spo2Chart = new ReportChartLine();
        //get array for labels
        $Spo2Chart->labels($lbl);
    //dd($spo2Chart);
        $Spo2Chart->dataset('Data for state of '.$state. "with Low Spo2 by Distict", 'pie',$valuesSpo2)
            ->backgroundColor($col);
        return view('adminReports.sStateReport', compact('repCollect','state','TempChart','Spo2Chart'));


    }

    /*
     public function sPincodeReport(Request $request){

        $pin = $request->input('pincode');
        if($pin == 'Select Pincode'){
            redirect ('adminReports.sReport')->with('error','Please select a Pincode');
        }       

        //getting locations with this pincode 
        $locations = Society::where('pincode','=',$pin)
            ->where ('isactive','=',true)
            ->pluck('id');
            //->toArray();
        //dd($locations);
        if(count($locations) == 0){
            redirect ('adminReports.sReport')->with('error','No active location for this pincode');
        }

        //looping thru location list
        $repData = collect();
                            
        //get list of devices linked to this location
        $devices = LinkLocDev::whereIn('locationid',$locations)->pluck('deviceid')->toArray();
        //dd($devices);

        if(count($devices) == 0){
            redirect('adminReports.sReport')->with('error','No Devices Linked to this Pincode.');
        }
        
        //get the list of device names for these locations
        $devNames = Device::whereIn('id',$devices)->pluck('serial_no')->toArray();
        //dd($devNames);
        
        //All Data
   /*     $iotDataAll = IotData::whereIn('iotdata.deviceid',$devNames)
            ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
            ->where('iotdata.created_at','<=',Carbon::today()->addDays(1))
            ->join('device','device.serial_no','=','iotdata.deviceid')
            ->where('device.isactive','=',true)
            ->join('LinkLocDev','LinkLocDev.deviceid','=','device.id')
            ->where ('LinkLocDev.isactive','=',true)
            ->join('location','location.id','=','LinkLocDev.locationid')
            ->where('location.isactive','=',true)
            ->select('location.name',DB::raw('count(*) as allRec'))
            ->groupBy('location.name')
            ->get();
            //->toArray();
        
        dd($iotDataAll);

        //Low SPO2 Data
        $iotDataLowSPO2 = IotData::whereIn('iotdata.deviceid',$devNames)
            ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
            ->where('iotdata.created_at','<=',Carbon::today()->addDays(1))
            ->where ('iotdata.spo2','<',93)
            ->join('device','device.serial_no','=','iotdata.deviceid')
            ->where('device.isactive','=',true)
            ->join('LinkLocDev','LinkLocDev.deviceid','=','device.id')
            ->where ('LinkLocDev.isactive','=',true)
            ->join('location','location.id','=','LinkLocDev.locationid')
            ->where('location.isactive','=',true)
            ->select('location.name',DB::raw('count(*) as allRec'))
            ->groupBy('location.name')
            ->get();
            //->toArray();
        
        dd($iotDataSPO2);
        
        //All Temp Data
        $iotDataHighTemp = IotData::whereIn('iotdata.deviceid',$devNames)
            ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
            ->where('iotdata.created_at','<=',Carbon::today()->addDays(1))
            ->where ('iotdata.temp','>',99.6)
            ->join('device','device.serial_no','=','iotdata.deviceid')
            ->where('device.isactive','=',true)
            ->join('LinkLocDev','LinkLocDev.deviceid','=','device.id')
            ->where ('LinkLocDev.isactive','=',true)
            ->join('location','location.id','=','LinkLocDev.locationid')
            ->where('location.isactive','=',true)
            ->select('location.name',DB::raw('count(*) as allRec'))
            ->groupBy('location.name')
            ->get();
            //->toArray();
        
        dd($iotDataAllTemp);
        

        
        $iotData = IotData::whereIn('iotdata.deviceid',$devNames)
                ->where('iotdata.created_at','>=',Carbon::today()->subDays(15))
                ->where('iotdata.created_at','<=',Carbon::today()->addDays(1))
                ->join('device','device.serial_no','=','iotdata.deviceid')
                ->where('device.isactive','=',true)
                ->join('LinkLocDev','LinkLocDev.deviceid','=','device.id')
                ->where ('LinkLocDev.isactive','=',true)
                ->join('location','location.id','=','LinkLocDev.locationid')
                ->where('location.isactive','=',true)
                ->select('location.pincode as pincode', 'location.name as name',
                    'iotdata.identifier as identifier','iotdata.deviceid as device',
                    'iotdata.temp as temperature', 'iotdata.spo2 as spo2',
                    'iotdata.hbcount as heartbeat', 'iotdata.created_at as date' )
                ->orderBy('location.pincode', 'asc')
                ->orderBy('location.name','asc')
                ->orderBy('iotdata.identifier','asc')
                ->orderBy('iotdata.created_at','desc')
                //->groupBy('location.pincode','location.name','iotdata.deviceid','iotdata.identifier',
                //    'iotdata.temp', 'iotdata.spo2', 'iotdata.hbcount', 'iotdata.created_at')
                ->get();
            
            dd($iotData);
        $title = "Screening data by Pincode";
        $meta = [
            'Data for Pincode ' => $pin
        ];
        $columns = [
            'Pincode' => 'pincode',
            'Location Name' => 'name', 
            'Identifier' => 'identifier',
            'Device ID' => 'device',
            'Temperature' => 'temperature',
            'SPO2' => 'spo2',
            'Heart beat' => 'heartbeat',
            'Captured at' => 'date',
            'Status' => function($iotData){
                $ret = '';
                if($iotData->spo2 < 93){
                    $ret = $ret.'Low SpO2';
                }
                if($iotData->temperature > 99.6){
                    if($ret != ''){
                        $ret = $ret.'/n';
                    }
                    $ret = $ret.'High Temp';
                }
                return $ret;
            }
        ];
           
       return PdfReport::of($title, $meta, $iotData, $columns)
            ->editColumn('Captured at', [
                'displayAs' => function($iotData){
                    return Carbon::create($iotData->date)->format('d-M-Y');
                },
                'class' => 'left'
            ])
            ->editColumn('Status', [
                'class' => 'right bold red'
            ])
            ->groupBy('Pincode')
            //->limit (50)
            ->stream();

        /*
        
        //get list of devices linked to that location
        

        

        $iotData = IotData::whereIn('deviceid',$devNameList)->paginate(50);
        //dd($iotData);
        if(count($iotData) == 0){
            redirect('adminReports.sReport')->with('error','No Data for this Pincode.');
        }

         
        $lbl = collect([]);
        $valuesTemp = collect([]);
        $valuesSpo2 = collect([]);
        foreach($iotData as $data){
            $lbl->push($data->created_at->format('Y-m-d h:i:s'));
            $valuesTemp->push($data->temp);
            $valuesSpo2->push($data->spo2);
        }
        
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
        return view ('adminReports.sPincodeReport',compact('iotData','pin','spo2Chart','tempChart'));
        
     }
*/



    /*public function sCityReport(Request $request){
        $city = $request->input('city');
        if($city == 'Select City'){
            redirect ('adminReports.sReport')->with('error','Please select a City');
        }       

        //getting location name
        $lName = Society::where('city','=',$city)->pluck('name');
        //dd($lName);
        if(count($lName) == 0){
            redirect ('adminReports.sReport')->with('error','No active location for this City');
        }

        $loc = Society::where('city','=',$city)->pluck('id')->toArray();
        if(count($loc) == 0){
            redirect ('adminReports.sReport')->with('error','No active location for this City');
        }

        //get list of devices linked to that location
        $devList = LinkLocDev::whereIn('locationid',$loc)->pluck('deviceid')->toArray();
        //dd($devList);
        if(count($devList) == 0){
            redirect('adminReports.sReport')->with('error','No Data for this City.');
        }

        $devNameList = Device::whereIn('id',$devList)->pluck('serial_no')->toArray();
        //dd($devNameList);
        if(count($devNameList) == 0){
            redirect('adminReports.sReport')->with('error','No Data for this City.');
        }

        $iotData = IotData::whereIn('deviceid',$devNameList)->paginate(50);
        //dd($iotData);
        if(count($iotData) == 0){
            redirect('adminReports.sReport')->with('error','No Data for this City.');
        }

         
        $lbl = collect([]);
        $valuesTemp = collect([]);
        $valuesSpo2 = collect([]);
        foreach($iotData as $data){
            $lbl->push($data->created_at->format('Y-m-d h:i:s'));
            $valuesTemp->push($data->temp);
            $valuesSpo2->push($data->spo2);
        }
        
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
        return view ('adminReports.sCityReport',compact('iotData','pin','spo2Chart','tempChart', 'city'));

    } */  
 }
