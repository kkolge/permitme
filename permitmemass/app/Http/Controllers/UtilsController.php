<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UtilsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getLastDeviceToken()
    {

        if(!Auth::user()->hasRole('Super Admin')){
            abort(403);
        }

        //$lastDeviceToken = DB::raw('select * from devauth where id in (select max(id) from devauth group by deviceid) order by deviceid')->paginate(30);
        $lastDeviceToken = DB::table('devauth')
                                ->whereIn('id',function($query){
                                    $query->selectRaw("max( id )")->from("devauth")->groupBy("deviceid");
                                })
                                ->orderBy('id','desc')
                                ->paginate(25);
        //dd($lastDeviceToken);

        return view('utils.getlastdevicetoken', compact('lastDeviceToken'));

    }

    public function deviceTokenDetails(string $deviceid){
        if(!Auth::user()->hasRole('Super Admin')){
            abort(403);
        }

        //not sure how to validate this data here 
        $devRecs = DB::table('devauth')->where('deviceid','=',$deviceid)->orderBy('updated_at', 'desc')->paginate(50);

        return view('utils.getdevicetokendetails', compact('devRecs', 'deviceid'));
    }
}
