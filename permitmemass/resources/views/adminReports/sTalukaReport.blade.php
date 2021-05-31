@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($repCollect) > 0)
    <p class="h1">City wise 15 days historical data for {{ $taluka }}</p>
        <!-- lets show the charts at the top -->
        <div class="card-deck">
            <div class="card bg-transparent text-center">
            <!--    <div class="card-header ">
                    All Abnormal
                </div> -->
                <div class="card-body ">
                    {!! $AllAbnormalChart->container() !!}
                </div>
            </div>
            <div class="card bg-transparent text-center">
            <!--    <div class="card-header ">
                    High Heart Rate
                </div> -->
                <div class="card-body ">
                    {!! $HbcountChart->container() !!}
                </div>
            </div>
            <div class="card bg-transparent text-center">
            <!--    <div class="card-header ">
                    Low SPO2
                </div> -->
                <div class="card-body ">
                    {!! $Spo2Chart->container() !!}
                </div>
            </div>
            <div class="card bg-transparent text-center">
            <!--    <div class="card-header ">
                    High Temperature
                </div> -->
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
        <table class="table table-sm table-bordered table-responsive bg-transparent text-center">
            <tr>
                <th>Serial No </th>
                <th>City</th>
                <th>All Abnormal</th>
                <th>High Pulse Rate (per min)</th>
                <th>Low Spo2 (%)</th>
                <th>High Temperature (&#8457;))</th>
                <th> Total Scans</th>
            </tr>
        @foreach($repCollect as $c)
            <tr class='text-light'>
                <td>{{$counter++}} </td>
                <td><a href="/adminReports/sCityReport?source={{$state.'.'.$district.'.'.$taluka.'.'.$c['city']}}&type=HighTemp"> <div class="btn-link">{{ $c['city'] }}</div> </a> </td>
                <td>{{ $c['allAbnormal']}}</td>
                <td>{{ $c['hbCount'] }}</td>
                <td >{{$c['spo2Count']}}</td>
                <td >{{$c['tempCount']}}</td>
                <td>{{$c['totalScan']}} </td>
            </tr>
        @endforeach
        </table>
        <br/>
    @else
        <p class="h1">No Data available!</p>        
    @endif
  
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-primary">Back</a>
            </div>
        </div>
        @include('inc.parameters')
    </p>

@endsection