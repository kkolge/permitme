@extends('layouts.app')

@section('content')
<?php $counter = 1 ?>
    
    @if (count($allAbnormal15Days) > 0)
    <p class="h1"> All Abnormal Parameters Report for last 15 days </p>
        <div class="px-md-5">
            {!! $abnormalChart->container() !!}
            {!! $abnormalChart->script() !!}
        
        </div>
        <br/>
        <p class="h2"> Details </p>
        <div class="px-md-5">
            <table class="table table-sm table-bordered table-responsive bg-transparent text-center">
                <tr>
                    <th>Serial No </th>
                    <th>Date</th>
                    <th>Count</th>
                </tr>
            @foreach($allAbnormal15Days as $data)
                <tr class="text-light">
                    <td>{{$counter++}} </td>
                    <td>{{$data->date}}</td>
                    <td><a href="/reports/{{$data->date}}/AllAbnormalDetailsByDate" class="btn-link">{{$data->count}}</a></td>
                </tr>
            @endforeach
            </table>
          
        </div>
    
    
    @else
        <p class="h1"> No Data available!</p>        
    @endif
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>
        @include('inc.parameters')
    </p>
@endsection