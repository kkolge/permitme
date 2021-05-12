@extends('layouts.app')

@section('content')
<?php $counter = 1 ?>
    
    @if (count($highHbcount15Days) > 0)
    <p class="h1"> High Pulse Rate Report for last 15 days </p>
        <div class="px-md-5">
            {!! $hbcountChart->container() !!}
            {!! $hbcountChart->script() !!}
        
        </div>
        <br/>
        <p class="h2"> Details </p>
        <div class="px-md-5">
            <table class="table table-striped table-bordered">
                <tr>
                    <th>Serial No </th>
                    <th>Date</th>
                    <th>Count</th>
                </tr>
            @foreach($highHbcount15Days as $data)
                <tr>
                    <td>{{$counter++}} </td>
                    <td>{{$data->date}}</td>
                    <td><a href="/reports/{{$data->date}}/HbcountDetailsByDate" class="btn-link">{{$data->count}}</a></td>
                </tr>
            @endforeach
            </table>
            <p class="small">
                Normal Range: &nbsp; &nbsp; &nbsp; &nbsp;
                Pulse Rate < {{env('CUTOFF_PULSE')}} per min &nbsp; &nbsp; &nbsp; &nbsp;
                SPO2 > {{env('CUTOFF_SPO2')}}% &nbsp; &nbsp; &nbsp; &nbsp;
                Temperature < {{env('CUTOFF_TEMP')}}&#8457; (Wrist temperature is 3.3&#8451; / 5.9&#8457; lower than core body temperature)
            </p> <br/><br/>
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
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>

@endsection