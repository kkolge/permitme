@extends('layouts.app')

@section('content')

<?php use Illuminate\Pagination\LengthAwarePaginator; ?>

<?php $counterAllAbnormal = 1; $counterHighTemp = 1; $counterLowSpo2 = 1; $counterHighHbCount = 1  ?>

    <p class="h1">Historical data for Location {{ $location }}</p>
    
    
    <!-- Start making the tabs -->
    <ul class="nav nav-pills nav-fill mb-3" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link  active" id="tab-allAbnormal" data-toggle="pill" href="#tab-allAbnormal-Data" role="tab" aria-controls="home" aria-selected="true">
                All Abnormal
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link " id="tab-highTemp" data-toggle="pill" href="#tab-highTemp-Data" role="tab" aria-controls="profile" aria-selected="false">
                High Temperature
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link " id="tab-lowSpo2" data-toggle="pill" href="#tab-lowSpo2-Data" role="tab" aria-controls="profile" aria-selected="false" >
                Low SPO2
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link " id="tab-highHeartRate" data-toggle="pill" href="#tab-highHeartRate-Data" role="tab" aria-controls="profile" aria-selected="false" >
                High Pulse Rate
            </a>
        </li>
    </ul>
<!-- Tabs navs -->

<!-- Tabs content -->
    <div class="tab-content" id="myTab-content">
        <div class="tab-pane fade show active" id="tab-allAbnormal-Data" role="tabpanel" aria-labelledby="ex2-tab-1">
            @if (count($iotDataAllAbnormal) > 0)
                <table class="table table-striped table-bordered">
                    <tr>
                    <th>Serial No </th>
                    <th>Identifier</th>
                    <th>Pulse Rate (per min)</th>
                    <th>SPO2 (%)</th>
                    <th>Temperature (&#8457;)</th>
                    <th>Captured at</th>
                </tr>
                @foreach($iotDataAllAbnormal as $data)
                    <tr>
                        <td>{{$counterAllAbnormal++}} </td>
                        <td><a href='/reports/userSearch?identifier={{$data->identifier}}'><div class="btn-link">{{$data->identifier}}</div></a></td>
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
                {{ $iotDataAllAbnormal->appends(Request::except('page'))->fragment('tab-allAbnormal')->links() }}
            @else
                <p class="h2">No Data available!</p>        
            @endif
        </div>
        <div class="tab-pane fade " id="tab-highTemp-Data" role="tabpanel" aria-labelledby="ex2-tab-2" >
            @if (count($iotDataHighTemp) > 0)
                <table class="table table-striped table-bordered">
                    <tr>
                    <th>Serial No </th>
                    <th>Identifier</th>
                    <th>Pulse Rate (per min)</th>
                    <th>SPO2 (%)</th>
                    <th>Temperature (&#8457;)</th>
                    <th>Captured at</th>
                </tr>
                @foreach($iotDataHighTemp as $data)
                    <tr>
                        <td>{{$counterHighTemp++}} </td>
                        <td><a href='/reports/userSearch?identifier={{$data->identifier}}'><div class="btn-link">{{$data->identifier}}</div></a></td>
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
                {{ $iotDataHighTemp->appends(Request::except('page'))->fragment('tab-highTemp')->links() }}
            @else
                <p class="h2">No Data available!</p>        
            @endif
        </div>
        <div class="tab-pane fade " id="tab-lowSpo2-Data" role="tabpanel" aria-labelledby="ex2-tab-2" >
            @if (count($iotDataLowSpo2) > 0)
                <table class="table table-striped table-bordered">
                    <tr>
                    <th>Serial No </th>
                    <th>Identifier</th>
                    <th>Pulse Rate (per min)</th>
                    <th>SPO2 (%)</th>
                    <th>Temperature (&#8457;)</th>
                    <th>Captured at</th>
                </tr>
                @foreach($iotDataLowSpo2 as $data)
                    <tr>
                        <td>{{$counterLowSpo2++}} </td>
                        <td><a href='/reports/userSearch?identifier={{$data->identifier}}'><div class="btn-link">{{$data->identifier}}</div></a></td>
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
                {{ $iotDataLowSpo2->appends(Request::except('page'))->fragment('tab-lowSpo2')->links() }}
            @else
                <p class="h2">No Data available!</p>        
            @endif
        </div>
        <div class="tab-pane fade " id="tab-highHeartRate-Data" role="tabpanel" aria-labelledby="ex2-tab-2" >
            @if (count($iotDataHighHbcount) > 0)
                <table class="table table-striped table-bordered">
                    <tr>
                    <th>Serial No </th>
                    <th>Identifier</th>
                    <th>Pulse Rate (per min)</th>
                    <th>SPO2 (%)</th>
                    <th>Temperature (&#8457;)</th>
                    <th>Captured at</th>
                </tr>
                @foreach($iotDataHighHbcount as $data)
                    <tr>
                        <td>{{$counterHighHbCount++}} </td>
                        <td><a href='/reports/userSearch?identifier={{$data->identifier}}'><div class="btn-link">{{$data->identifier}}</div></a></td>
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
                {{ $iotDataHighHbcount->appends(Request::except('page'))->fragment('tab-highHeartRate')->links() }}
            @else
                <p class="h2">No Data available!</p>        
            @endif
        </div>
    
    </div>
<!-- Tabs content -->

    <!-- End making the Tags -->
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-primary">Back</a>
            </div>
        </div>
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
    
@endsection