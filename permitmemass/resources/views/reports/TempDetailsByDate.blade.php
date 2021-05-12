@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($highTempOnDate) > 0)
    <p class="h1">High Temperature Report for {{ $date }}</p>
        <table class="table table-striped table-bordered">
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
        @foreach($highTempOnDate as $data)
            <tr>
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
        <p class="small">
                Normal Range: &nbsp; &nbsp; &nbsp; &nbsp;
                Pulse Rate < {{env('CUTOFF_PULSE')}} per min &nbsp; &nbsp; &nbsp; &nbsp;
                SPO2 > {{env('CUTOFF_SPO2')}}% &nbsp; &nbsp; &nbsp; &nbsp;
                Temperature < {{env('CUTOFF_TEMP')}} &#8457; (Wrist temperature is 3.3&#8451; / 5.9&#8457; lower than core body temperature)
            </p> <br/><br/>
        {{$highTempOnDate->links()}}
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