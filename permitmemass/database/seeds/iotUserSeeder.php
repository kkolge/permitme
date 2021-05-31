<?php

use App\Device;
use Illuminate\Database\Seeder;
use App\IotData;
use Carbon\Carbon;

class iotUserSeeder extends Seeder
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
            for ($i=0; $i<2; $i++){
                $data = new IotData();
                $data->identifier = '1122334455';
                $data->deviceid = $d;
                $data->temp = rand(87, 95);
                $data->spo2 = rand(85,100);
                $data->hbcount = rand(65,160);
                $data->created_at = Carbon::now()
                                    ->subDays(rand(0,15))
                                    ->format('Y-m-d H:i:s');
                if($data->hbcount > 120 || $data->spo2 < 93 || $data->temp > 93.5){
                    $data->flagstatus = true;
                }
                else{
                    $data->flagstatus=false;
                }
                
                $data->save();
                echo ('completed device '.$d);
            }
        }
    }
}
