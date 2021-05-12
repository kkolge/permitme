@extends('layouts.app')

@section('content')


    @if(Session::get('GisActive') == true)
        <!-- Row 1 - general information -->
        <div class="card-deck">
            <div class="card text-white bg-success text-center ">
                <div class="card-header ">Total Unique Users</div>
                <div class="card-body "><span class="display-4">{{ $totUniqueUsers }}</span></div>
            </div>
            <div class="card text-white bg-success text-center ">
                <div class="card-header">No. of Users</div>
                
                <div class="card-body"><u><a href="/reguser" class="display-4">{{ $totalRegUsers }}</a></u></div>
                
            </div> 
            @if(Auth::user()->hasRole(['Super Admin']))
                <div class="card text-white bg-danger text-center ">
                    <div class="card-header">No. of Unregistered Users</div>
                    <div class="card-body"><span class="display-4">{{ $totUniqueUsers - $totalRegUsers }}</span></div>
                </div>
            @endif
            <div class="card text-white bg-success text-center  ">
                <div class="card-header">No of Locations</div>
                <div class="card-body "><u><a href="/location" class=" display-4" >{{ count($loc) }}</a></u></div>
            </div> 
            <div class="card text-white bg-success text-center ">
                <div class="card-header">No of Devices</div>
                <div class="card-body"><u><a href="/device" class="display-4">{{ $noofDev }}</a></u></div>
            </div>
        </div>
        <br/>
         <!-- Row 2 - Graphs -->
        <div class="card-deck">
            <div class="card bg-light text-center">
                <div class="card-header ">
                    @if(Auth::user()->hasRole(['Super Admin','Location Admin']))
                        Top 5 Locations by All Abnormal data (last 7 days)
                    @elseif(Auth::user()->hasRole(['Site Admin']))
                        Top 5 Devices by All Abnormal data (last 7 days)
                    @endif
                </div>
                <div class="card-body ">
                    {!! $allAbnormalChart->container() !!}
                    {!! $allAbnormalChart->script() !!}
                </div>
            </div>
            <div class="card bg-light text-center">
                <div class="card-header ">
                    @if(Auth::user()->hasRole(['Super Admin','Location Admin']))
                        Top 5 Locations by Pulse Rate <br/> above {{env('CUTOFF_PULSE')}} (last 7 days)
                    @elseif(Auth::user()->hasRole(['Site Admin']))
                        Top 5 Devices by Pulse Rate above {{env('CUTOFF_PULSE')}} (last 7 days)
                    @endif
                </div>
                <div class="card-body ">
                    {!! $pulseChart->container() !!}
                    {!! $pulseChart->script() !!}
                </div>
            </div>
            <div class="card bg-light text-center">
                <div class="card-header ">
                    @if(Auth::user()->hasRole(['Super Admin','Location Admin']))
                        Top 5 Locations by SPO2 <br/> below {{env('CUTOFF_SPO2')}} (last 7 days)
                    @elseif(Auth::user()->hasRole(['Site Admin']))
                        Top 5 Devices by SPO2 below {{env('CUTOFF_SPO2')}} (last 7 days)
                    @endif
                </div>
                <div class="card-body ">
                    {!! $spo2Chart->container() !!}
                    {!! $spo2Chart->script() !!}
                </div>
            </div>
            <div class="card bg-light text-center">
                <div class="card-header ">
                    @if(Auth::user()->hasRole(['Super Admin','Location Admin']))
                        Top 5 Locations by Temperature <br/> above {{env('CUTOFF_TEMP')}} (last 7 days)
                    @elseif(Auth::user()->hasRole(['Site Admin']))
                        Top 5 Devices by Temperature above {{env('CUTOFF_TEMP')}} (last 7 days)
                    @endif
                </div>
                <div class="card-body ">
                    {!! $tempChart->container() !!}
                    {!! $tempChart->script() !!}
                </div>
            </div>
        </div>
        <br/>


         <!-- Row 3 - All Screening data -->
         <div class="card-deck">
            <div class="card text-white bg-success text-center ">
                <div class="card-header ">Screened Till Date</div>
                <div class="card-body "><u><a href="/reports/allDataLocationReport" class="display-4">{{$totScan}} </a></u></div>
            </div> 
            <div class="card text-white bg-danger text-center ">
                <div class="card-header ">All Abnormal Till Date</div>
                <div class="card-body "><u><a href="/reports/AllAbnormalReport" class="display-4">{{$totScanAllAbnormal}}</a></u></div>
            </div> 
            <div class="card text-white bg-info text-center ">
                <div class="card-header ">Abnormal Pulse Rate</div>
                <div class="card-body "><u><a href="/reports/HbcountReport" class="display-4">{{$totScanPulse}}</a></u></div>
            </div> 
            <div class="card text-white bg-info text-center ">
                <div class="card-header ">Abnormal SPO2</div>
                <div class="card-body "><u><a href="/reports/SPO2Report" class="display-4">{{$totScanSPO2}}</a></u></div>
            </div>
            <div class="card text-white bg-info text-center ">
                <div class="card-header ">Abnormal Temperature</div>
                <div class="card-body "><u><a href="/reports/TempReport" class="display-4">{{$totScanTemp}}</a></u></div>
            </div> 
        </div>
        <br/>

        
        <br/>
        
        <!-- End of all Rows -->
                
            <p class="small">
                Normal Range: &nbsp; &nbsp; &nbsp; &nbsp;
                Pulse Rate < {{env('CUTOFF_PULSE')}} per min &nbsp; &nbsp; &nbsp; &nbsp;
                SPO2 > {{env('CUTOFF_SPO2')}}% &nbsp; &nbsp; &nbsp; &nbsp;
                Temperature < {{env('CUTOFF_TEMP')}}&#8457;  (Wrist temperature is 3.3&#8451; / 5.9&#8457; lower than core body temperature)
            </p> <br/><br/>
        <p>
        @else
            <strong>Your location is not active. Please contact service provider.</strong>
        @endif
    </div>

<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection
