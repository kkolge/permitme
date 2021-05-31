@extends('layouts.app')

@section('content')
<?php $counter = 1; ?>
    
    @if (count($iotData ?? []) > 0)
    <p class="h1">15 Days Historical data for {{ $identifier }}</p>
    <br/>
    
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
    <br/>
    <div class="d-flex">
        <div>{{$iotData->links()}}</div>
        <div class="ml-auto"><a href="{{ URL::previous() }}" class="btn btn-primary">Back</a></div>
    </div>
    <br/>
      
        <table class="table table-sm table-bordered table-responsive bg-transparent text-center">
            <tr>
                <th>Serial No </th>
                <th>Pulse Rate (per min)</th>
                <th>SPO2 (%)</th>
                <th>Temperature (&#8457;)</th>
                <th>Captured at</th>
            </tr>
        @foreach($iotData as $data)
            <tr class="text-light">
                <td>{{$counter++}} </td>
                <td>{{$data->hbcount}}</td>
                <td>{{$data->spo2}}</td>
                <td>{{$data->temp}}</td>
                <td>{{$data->created_at}}</td>
            </tr>
        @endforeach
        </table>
        {{$iotData->links()}}
        
        
        
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
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection