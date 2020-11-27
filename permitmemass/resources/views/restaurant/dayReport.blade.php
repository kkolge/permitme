@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
   
    @if (count($visitReportByDay) > 0)
    <h1> <font size="+2">Day Report for your restaurant </font> </h1>
        <table class="table table-striped table-bordered">
            <tr>
                <th>Serial No </th>
                <th>Date</th>
                <th>No. of Visitors</th>
            </tr>
        @foreach($visitReportByDay as $data)
            <tr>
                <td>{{$counter++}} </td>
                <td>{{$data->date}}</td>
                <td><a href="/restaurant/{{$data->date}}/dayReportDetail" class="btn btn-link">{{$data->count}}</a></td>
            </tr>
        @endforeach
        </table>
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