@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($repCollect) > 0)
    <h1> <font size="+2">15 Days Historical data for {{ $district }} (taluka wise)</font> </h1>
    <br/>
        <table class="table table-striped table-bordered">
            <tr>
                <th style="width:20%">Serial No </th>
                <th style="width:20%">Taluka</th>
                <th style="width:20%">High Temperature Count</th>
                <th style="width:20%">Low Spo2 Count</th>
                <th style="width:20%"> Total Scans</th>
            </tr>
        @foreach($repCollect as $c)
            <tr>
                <td>{{$counter++}} </td>
                <td>{{ $c['taluka'] }}</td>
                <td ><a href="/adminReports/sTalukaReport?source={{$state.'.'.$district.".".$c['taluka']}}&type=HighTemp"> <div class="btn btn-link">{{$c['tempCount']}}</div> </a> </td>
                <td ><a href="/adminReports/sTalukaReport?source={{$state.'.'.$district.".".$c['taluka']}}&type=LowSpo2"> <div class="btn btn-link">{{$c['spo2Count']}}</div> </a> </td>
                <td>{{$c['totalScan']}} </td>
            </tr>
        @endforeach
        </table>
        <p>
            <div class="flex">
                <div class="w-1/2">
                    {!! $TempChart->container() !!}
                </div>
                <div class="w-1/2">
                    {!! $Spo2Chart->container() !!}
                </div>
            </div>
            {!! $TempChart->script() !!}
            {!! $Spo2Chart->script() !!}
            
        </p>
    
    
    @else
        <h1><font size="+2">No Data available!</font></h1>        
    @endif
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-primary">Back</a>
            </div>
        </div>
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection