<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\LinkLocUser;
use App\LinkLocDev;
use DB;
use App\Device;
use App\IotData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class dayReportDetailExport implements FromCollection, WithHeadings, WithColumnFormatting
{
    private $date;

    public function __construct($date)
    {
        $this->date = $date;

    }
    //generating Heading
    public function headings() : array{
        return [
            'Identifier',
            'Temperature',
            'SPO2',
            'Pulse Rate',
            'Recorded At'
        ];
    }
    //With Formatting
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_DATE_TIME1,
        ];
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $user = Auth::user()->id;
        //get the location that user is attached to 
        $loc = LinkLocUser::where('userid','=',$user)->get()->first();
        //dd($loc);
        //getting list of linked devices
        $devIdList = LinkLocDev::where('locationid','=',$loc->locationid)
            ->select('deviceid')
            ->get()
            ->toArray();
        //dd($devIdList);
        
        if(count($devIdList) > 0){
            $devNameList = Device::whereIn('id',$devIdList)
                ->select('serial_no')
                ->get()
                ->toArray();
        }
        else {
            return ['ERROR'];
        }

        $visitReportByDay = IotData::whereIn('deviceid',$devNameList)
        ->where(DB::raw('Date(created_at)'),'=',new Carbon($this->date))
        ->select('identifier','temp','spo2','hbcount','created_at')
        ->orderBy(DB::raw('Date(created_at)'))
        ->paginate(50);

        
      //dd($allData);
        return $visitReportByDay;
    }
}
