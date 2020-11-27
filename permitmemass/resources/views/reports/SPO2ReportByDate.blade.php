@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($lowSpo2OnDate) > 0)
    <h1> <font size="+2">Low SPO2 Report for {{ $date }} </font> </h1><br/>
        <table class="table table-striped table-bordered">
            <tr>
                <th>Serial No </th>
                <th>Identifier</th>
                <th>Temperature</th>
                <th>SPO2 </th>
                <th>Heart Beat</th>
                <th>Captured at</th>
            </tr>
        @foreach($lowSpo2OnDate as $data)
            <tr>
                <td>{{$counter++}} </td>
                <td><a href="/reports/{{$data->identifier}}/userReport" class="btn btn-link">{{$data->identifier}}</a></td>
                <td>{{$data->temp}}</td>
                <td>{{$data->spo2}}</td>
                <td>{{$data->hbcount}}</td>
                <td>{{$data->created_at}}</td>
            </tr>
        @endforeach
        </table>
        {{$lowSpo2OnDate->links()}}
    @else
        <h1><font size="+2">No Data available!</font></h1>        
    @endif
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>    
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection