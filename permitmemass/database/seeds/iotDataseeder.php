<?php

use App\Device;
use Illuminate\Database\Seeder;
use App\IotData;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class iotDataseeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dev = Device::pluck('serial_no');
        foreach($dev as $d){
        //dd($d);
            for ($i=0; $i<100; $i++){
                $data = new IotData();
                $data->identifier = rand(9900000001,9900001000);
                $data->deviceid = $d;
                $data->temp = rand(87, 99);
                $data->spo2 = rand(85,100);
                $data->hbcount = rand(65,160);
                $data->created_at = Carbon::now()
                                    ->subDays(rand(0,15))
                                    ->format('Y-m-d H:i:s');
                
                $data->save();

                
                $totalFlagCount = 0;
                if ($data->hbcount > env('CUTOFF_PULSE')){
                    $totalFlagCount = $totalFlagCount + 1;
                }
                if($data->spo2 < env('CUTOFF_SPO2')){
                    $totalFlagCount = $totalFlagCount + 2;
                }
                if($data->temp > env('CUTOFF_TEMP')){
                    $totalFlagCount = $totalFlagCount + 4;
                }
                if($totalFlagCount > 0){
                    $data->flagstatus = true;
                }
                $data->save();
    
                $err = DB::statement('call after_iotdata_insert(?,?,?)',[$data->deviceid, $totalFlagCount, $data->created_at]);
                echo ('completed device '.$d);
            }
        }
    }
}
