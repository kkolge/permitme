@extends('layouts.app')

@section('content')
<?php $counter = 1 ?>
    
    @if (count($highTemp15Days) > 0)
    <h1> <font size="+2">High Temperature Report for last 15 days </font></h1>
        <div class="px-md-5">
            {!! $tempChart->container() !!}
            {!! $tempChart->script() !!}
        
        </div>
        <br/>
        <div class="px-md-5">
            <table class="table table-striped table-bordered">
                <tr>
                    <th>Serial No </th>
                    <th>Date</th>
                    <th>Count</th>
                </tr>
            @foreach($highTemp15Days as $data)
                <tr>
                    <td>{{$counter++}} </td>
                    <td>{{$data->date}}</td>
                    <td><a href="/reports/{{$data->date}}/TempDetailsByDate" class="btn btn-link">{{$data->count}}</a></td>
                </tr>
            @endforeach
            </table>
        </div>
    
    
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