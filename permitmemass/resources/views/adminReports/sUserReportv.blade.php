@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($iotData ?? []) > 0)
    <p class="h1"> 15 Days Historical data for {{ $identifier }}</p>
    <br/>
   
    <div class="card-deck">
            <div class="card bg-transparent text-center">
            <!--    <div class="card-header ">
                    All Abnormal
                </div> -->
                <div class="card-body ">
                    {!! $hbcountChart->container() !!}
                </div>
            </div>
            <div class="card bg-transparent text-center">
            <!--    <div class="card-header ">
                    High Heart Rate
                </div> -->
                <div class="card-body ">
                    {!! $spo2Chart->container() !!}
                </div>
            </div>
            <div class="card bg-transparent text-center">
            <!--    <div class="card-header ">
                    Low SPO2
                </div> -->
                <div class="card-body ">
                    {!! $tempChart->container() !!}
                </div>
            </div>
            {!! $tempChart->script() !!}
            {!! $spo2Chart->script() !!}
            {!! $hbcountChart->script() !!}
        </div>
        
        <br/>
        <table class="table table-sm table-bordered table-responsive bg-transparent text-center">
            <tr>
                <th>Serial No </th>
                @if(Auth::user()->hasRole(['Super Admin', 'Location Admin']))
                    <th>Location</th>
                @else if(Auth::user()->hasRole(['Site Admin'])
                    <th>Device</th>
                @endif
                <th>Temperature (&#8457;)</th>
                <th>SPO2 (%)</th>
                <th>Pulse Rate (per min)</th>
                <th>Captured at</th>
            </tr>
        @foreach($iotData as $data)
            @if($data->flagstatus == true)
                <tr class="text-danger">
            @else
                <tr class="text-light">
            @endif
                <td>{{$counter++}} </td>
                <td>{{$data->name}}</td>
                <td>{{$data->temp}}</td>
                <td>{{$data->spo2}}</td>
                <td>{{$data->hbcount}}</td>
                <td>{{$data->created_at}}</td>
            </tr>
        @endforeach
        </table>
        {{$iotData->appends(request()->except('page'))->links()}}
        
    
    
    <br/>
    @else
        <p class="h1">No Data available!</p>        
    @endif
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="/adminReports/sUserReport?identifier={{$identifier}}&type=download" class="btn btn-info">Download</a>
            </div>
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-primary">Back</a>
            </div>
        </div>
        @include('inc.parameters')
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection