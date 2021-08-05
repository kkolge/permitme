@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    @if (count($highHbcountOnDate) > 0)
    <p class="h1"> High Pulse Rate Report for {{ $date }} </p>
    <br/>
    <div class="d-flex">
        <div>{{$highHbcountOnDate->links()}}</div>
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
                <th>SPO2 (%) </th>
                <th>Temperature (&#8457;)</th>
                <th>Captured at</th>
            </tr>
        @foreach($highHbcountOnDate as $data)
            <tr class="text-white">
                <td>{{$counter++}} </td>
                <td><a href="/reports/{{$data->identifier}}/userReport" class="btn-link">{{$data->identifier}}</a></td>
                @if(Auth::user()->hasRole(['Super Admin', 'Location Admin']))
                    <td>{{$data->name}}</th>
                @endif
                <td>{{$data->hbcount}}</td>
                <td>{{$data->spo2}}</td>
                <td>{{$data->temp}}</td>
                <td>{{$data->created_at}}</td> 
            </tr>
        @endforeach
            
        </table>
        <br/>
        {{$highHbcountOnDate->links()}}
    @else
        <p class="h1">No Data available!</p>        
    @endif
    <p>
        <div class="d-flex">
            <div class="mx-auto">
                <a href="/reports/{{$date}}/HbcountDetailsByDate?type=download" class="btn btn-info">Download</a>
            </div>
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>    
        @include('inc.parameters')
    </p>
    
@endsection