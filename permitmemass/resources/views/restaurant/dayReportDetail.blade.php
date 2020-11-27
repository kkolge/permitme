@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
   
    @if (count($visitReportByDay) > 0)
    <h1> <font size="+2">Day Report for your restaurant </font> </h1>
        
        <table class="table table-striped table-bordered">
            <tr>
                <th>Serial No </th>
                <th>Identifier</th>
                <th>Temperature</th>
                <th>SPO2</th>
                <th>Pulse Rate</th>
                <th>Recorded at</th>
            </tr>
        @foreach($visitReportByDay as $data)
            <tr>
                <td>{{$counter++}} </td>
                <td>{{$data->identifier}}</td>
                <td>{{$data->temp}}</td>
                <td>{{$data->spo2}}</td>
                <td>{{$data->hbcount}}</td>
                <td>{{$data->created_at}}</td>
            </tr>
        @endforeach
        </table>
        {{$visitReportByDay->links()}}
    @else
        <h1><font size="+2">No Data available!</font></h1>        
    @endif
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
            <div class="mx-auto">
                <a href="/restaurant/{{$date}}/export" class="btn btn-info">Download</a>
            </div>
        </div>
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection