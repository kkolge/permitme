<?php

use App\Device;
use Illuminate\Database\Seeder;
use App\IotData;
use Carbon\Carbon;

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
                $data->temp = rand(87, 95);
                $data->spo2 = rand(85,100);
                $data->hbcount = rand(65,160);
                $data->created_at = Carbon::now()
                                    ->subDays(rand(0,15))
                                    ->format('Y-m-d H:i:s');
                
                $data->save();
                echo ('completed device '.$d);
            }
        }
    }
}
