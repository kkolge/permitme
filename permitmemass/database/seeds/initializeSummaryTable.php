<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\IotData;
//use Illuminate\Support\Carbon;

class initializeSummaryTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Step 1 clear the summary table 
        DB::raw('truncate table iotdatasummary');

        //Step 2 - read each row from IotData and initialize the seeders 
        $iot = IotData::all();
        print ("processing total of: ".$iot->count()." records");
        $count = 1;
        foreach ($iot as $data){
            if(($count % 100) == 0){
                print ($count."\n");
            }
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

            $err = DB::statement('call after_iotdata_insert(?,?,?)',[$data->deviceid, $totalFlagCount, $data->created_at]);
            $count++;
        }
        print("Done processing: ".$count."\n");
        
    }
}
