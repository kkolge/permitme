<?php

use Illuminate\Database\Seeder;
use App\Device;

class deviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $isActive = false;
        for($i=0; $i<5; $i++){
            $dev = new Device();
            $dev->serial_no = "DEVICE".$i;
            $dev->isactive = $isActive;
            $dev->save();
            $isActive = !$isActive;
        }
        //
    }
}
