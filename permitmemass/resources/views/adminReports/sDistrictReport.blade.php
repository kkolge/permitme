@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($repCollect) > 0)
    <p class="h1">Taluka wise 15 days historical data for {{ $district }}</p>
        <!-- lets show the charts at the top -->
        <div class="card-deck">
            <div class="card bg-light text-center">
                <div class="card-header ">
                    All Abnormal
                </div>
                <div class="card-body ">
                    {!! $AllAbnormalChart->container() !!}
                </div>
            </div>
            <div class="card bg-light text-center">
                <div class="card-header ">
                    High Heart Rate
                </div>
                <div class="card-body ">
                    {!! $HbcountChart->container() !!}
                </div>
            </div>
            <div class="card bg-light text-center">
                <div class="card-header ">
                    Low SPO2
                </div>
                <div class="card-body ">
                    {!! $Spo2Chart->container() !!}
                </div>
            </div>
            <div class="card bg-light text-center">
                <div class="card-header ">
                    High Temperature
                </div>
                <div class="card-body ">
                    {!! $TempChart->container() !!}
                </div>
            </div>
        </div>
        {!! $TempChart->script() !!}
        {!! $Spo2Chart->script() !!}
        {!! $HbcountChart->script() !!}
        {!! $AllAbnormalChart->script() !!}
    <!-- End of charts -->
    <br/>
        <table class="table table-striped table-bordered">
            <tr class="text-center">
                <th>Serial No </th>
                <th>Taluka</th>
                <th>All Abnormal </th>
                <th>High Pulse Rate (per min)</th>
                <th>Low Spo2 (%))</th>
                <th>High Temperature (&#8457;))</th>
                <th> Total Scans</th>
            </tr>
        @foreach($repCollect as $c)
            <tr class="text-center">
                <td>{{$counter++}} </td>
                <td><a href="/adminReports/sTalukaReport?source={{$state.'.'.$district.".".$c['taluka']}}&type=HighTemp"> <div class="btn-link">{{ $c['taluka'] }}</div></a></td>
                <td>{{$c['allAbnormal']}}</td>
                <td>{{$c['hbCount']}}</td>
                <td >{{$c['spo2Count']}}</td>
                <td >{{$c['tempCount']}}</td>
                <td>{{$c['totalScan']}} </td>
            </tr>
        @endforeach
        </table>
        
        <p class="small">
                Normal Range: &nbsp; &nbsp; &nbsp; &nbsp;
                Pulse Rate < {{env('CUTOFF_PULSE')}} per min &nbsp; &nbsp; &nbsp; &nbsp;
                SPO2 > {{env('CUTOFF_SPO2')}}% &nbsp; &nbsp; &nbsp; &nbsp;
                Temperature < {{env('CUTOFF_TEMP')}} &#8457; (Wrist temperature is 3.3&#8451; / 5.9&#8457; lower than core body temperature)
        </p> <br/><br/>
    @else
        <p class="h1">No Data available!</p>        
    @endif
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-primary">Back</a>
            </div>
        </div>
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection