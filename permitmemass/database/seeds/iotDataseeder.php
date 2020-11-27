<?php

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
        for ($i=0; $i<100; $i++){
            $data = new IotData();
            $data->identifier = rand(8800000001,8800001000);
            $data->deviceid = 'DEVTEST22';
            $data->temp = rand(87, 102);
            $data->spo2 = rand(90,100);
            $data->hbcount = rand(65,120);
            $data->created_at = Carbon::now()
                                ->subDays(rand(0,15))
                                ->format('Y-m-d H:i:s');
            
            $data->save();
        }
    }
}
