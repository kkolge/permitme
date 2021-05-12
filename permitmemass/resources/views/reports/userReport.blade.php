@extends('layouts.app')

@section('content')
<?php $counter = 1; ?>
    
    @if (count($iotData ?? []) > 0)
    <p class="h1">15 Days Historical data for {{ $identifier }}</p>
    <br/>
        <table class="table table-striped table-bordered">
            <tr>
                <th>Serial No </th>
                <th>Pulse Rate (per min)</th>
                <th>SPO2 (%)</th>
                <th>Temperature (&#8457;)</th>
                <th>Captured at</th>
            </tr>
        @foreach($iotData as $data)
            <tr>
                <td>{{$counter++}} </td>
                <td>{{$data->hbcount}}</td>
                <td>{{$data->spo2}}</td>
                <td>{{$data->temp}}</td>
                <td>{{$data->created_at}}</td>
            </tr>
        @endforeach
        </table>
        
        <p>
            <div class="flex">
                <div class="w-1/2">
                    {!! $tempChart->container() !!}
                </div>
                <div class="w-1/2">
                    {!! $spo2Chart->container() !!}
                </div>
            </div>
            {!! $tempChart->script() !!}
            {!! $spo2Chart->script() !!}
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