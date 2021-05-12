@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($iotData ?? []) > 0)
    <p class="h1"> 15 Days Historical data for {{ $identifier }}</p>
    <br/>
        <table class="table table-striped table-bordered">
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
                <tr class="table-danger">
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
        
    
    <p>
        <div class="flex">
            <div class="w-1/3">
                {!! $hbcountChart->container() !!}
            </div>
            <div class="w-1/3">
                {!! $spo2Chart->container() !!}
            </div>
            <div class="w-1/3">
                {!! $tempChart->container() !!}
            </div>
        </div>
        {!! $tempChart->script() !!}
        {!! $spo2Chart->script() !!}
        {!! $hbcountChart->script() !!}
    </p>
    <p class="small">
            Normal Range: &nbsp; &nbsp; &nbsp; &nbsp;
            Pulse Rate < {{env('CUTOFF_PULSE')}} per min &nbsp; &nbsp; &nbsp; &nbsp;
            SPO2 > {{env('CUTOFF_SPO2')}}% &nbsp; &nbsp; &nbsp; &nbsp;
            Temperature < {{env('CUTOFF_TEMP')}} &#8457; (Wrist temperature is 3.3&#8451; / 5.9&#8457; lower than core body temperature)
        </p> <br/><br/>
    @else
        <p class="h1">No Data available!</p>        
    @endif
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-primary">Back</a>
            </div>
        </div>
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection