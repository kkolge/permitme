@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    @if (count($lowSpo2OnDate) > 0)
        <p class="h1"> Low SPO2 Report for {{ $date }} </p>
        <br/>
        <div class="d-flex">
            <div>{{$lowSpo2OnDate->links()}}</div>
            <div class="ml-auto"><a href="{{ URL::previous() }}" class="btn btn-info">Back</a></div>
        </div>
        <br/>
        <table class="table table-sm table-bordered table-responsive bg-transparent text-center">
            <tr>
                <th>Serial No </th>
                <th>Identifier</th>
                @if(Auth::user()->hasRole(['Super Admin', 'Location Admin']))
                    <th>Location</th>
                @endif
                <th>Pulse Rate (per min)</th>
                <th>SPO2 (%)</th>
                <th>Temperature (&#8457;)</th>
                <th>Captured at</th>
            </tr>
        @foreach($lowSpo2OnDate as $data)
            <tr class="text-light">
                <td>{{$counter++}} </td>
                <td><a href="/reports/{{$data->identifier}}/userReport" class="btn-link">{{$data->identifier}}</a></td>
                @if(Auth::user()->hasRole(['Super Admin', 'Location Admin']))
                    <td >{{$data->name}}</td>
                @endif
                <td>{{$data->hbcount}}</td>
                <td>{{$data->spo2}}</td>
                <td>{{$data->temp}}</td>
                <td>{{$data->created_at}}</td> 
            </tr>
        @endforeach
        </table>
        <br/>
        {{$lowSpo2OnDate->links()}}
    @else
        <p class="h1">No Data available!</p>        
    @endif
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="/reports/{{$date}}/SPO2DetailsByDate?type=download" class="btn btn-info">Download</a>
            </div>
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>  
        @include('inc.parameters')  
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection