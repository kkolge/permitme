@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($iotData) > 0)
    <h1> <font size="+2">15 Days Historical data for {{ $identifier }}</font> </h1>
    <br/>
        <table class="table table-striped table-bordered">
            <tr>
                <th>Serial No </th>
                <th>Temperature (F)</th>
                <th>SPO2 (%)</th>
                <th>Heart Beat (per min)</th>
                <th>Captured at</th>
            </tr>
        @foreach($iotData as $data)
            <tr>
                <td>{{$counter++}} </td>
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
    @else
        <h1><font size="+2">No Data available!</font></h1>        
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