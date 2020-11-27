<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LinkBedPatient;
use App\Bed;
use App\RegUser;
use App\IotData;
use App\Society;
use App\LinkLocDev;
use App\Device;
use App\vLocDev;
use Carbon\Carbon;
use DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class HospitalDeshboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        //get beds associated with the location 
        //get user attached to bed 
        //get latest parameters and order by 
        //send it to dashboard
        $loc = session('GlocationId');
        $link = LinkBedPatient::where('linkHospitalBedUser.locationid','=',$loc)
            ->where('linkHospitalBedUser.isactive','=',true)
            ->join('reguser','reguser.id','=','linkHospitalBedUser.patientId')
            ->join('bedMaster','bedMaster.id','=','linkHospitalBedUser.bedid')
            ->select('reguser.name','linkHospitalBedUser.created_at as admitDate', 
                'bedMaster.bedNo','reguser.id as userId')
            ->orderBy('bedMaster.bedNo', 'asc')
            ->get();

        //dd($link);
        $data = collect([]);
        foreach($link as $lnk){
            //dd($lnk);
            $iData = IotData::where('identifier','=',$lnk->userId)
                ->orderBy('created_at','desc')
                ->take(1)
                ->first();
            //dd($iData);
            if($iData != null){
                $data->push([$lnk->name, $lnk->bedNo, date_format(date_create($lnk->admitDate),'d-m-Y'),
                    $iData->temp, $iData->spo2, $iData->hbcount, $iData->created_at]);
            }
        }
        //dd($data);
        return view('/hospital/dashboard', compact('data'));
    }
}
