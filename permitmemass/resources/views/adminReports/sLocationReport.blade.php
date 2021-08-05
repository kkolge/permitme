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
            <div class="d-flex">
                <div>{{ $iotDataAllAbnormal->appends(Request::except('page'))->fragment('tab-allAbnormal')->links() }}</div>
                <div class="ml-auto"><a href="{{ URL::previous() }}" class="btn btn-primary">Back</a></div>
            </div>
            <br/>
                <table class="table table-sm table-bordered table-responsive bg-transparent text-center">
                    <tr>
                    <th>Serial No </th>
                    <th>Identifier</th>
                    <th>Pulse Rate (per min)</th>
                    <th>SPO2 (%)</th>
                    <th>Temperature (&#8457;)</th>
                    <th>Captured at</th>
                </tr>
                @foreach($iotDataAllAbnormal as $data)
                    <tr class="text-light">
                        <td>{{$counterAllAbnormal++}} </td>
                        <td><a href='/reports/userSearch?identifier={{$data->identifier}}'><div class="btn-link">{{$data->identifier}}</div></a></td>
                        <td>{{$data->hbcount}}</td>
                        <td>{{$data->spo2}}</td>
                        <td>{{$data->temp}}</td>
                        <td>{{$data->created_at}}</td>
                    </tr>
                @endforeach
                </table>
               <br/>
                {{ $iotDataAllAbnormal->appends(Request::except('page'))->fragment('tab-allAbnormal')->links() }}
            @else
                <p class="h2">No Data available!</p>        
            @endif
        </div>
        <div class="tab-pane fade " id="tab-highTemp-Data" role="tabpanel" aria-labelledby="ex2-tab-2" >
            @if (count($iotDataHighTemp) > 0)
                <div class="d-flex">
                    <div>{{ $iotDataHighTemp->appends(Request::except('page'))->fragment('tab-allAbnormal')->links() }}</div>
                    <div class="ml-auto"><a href="{{ URL::previous() }}" class="btn btn-primary">Back</a></div>
                </div>
                <br/>
                <table class="table table-sm table-bordered table-responsive bg-transparent text-center">
                    <tr>
                    <th>Serial No </th>
                    <th>Identifier</th>
                    <th>Pulse Rate (per min)</th>
                    <th>SPO2 (%)</th>
                    <th>Temperature (&#8457;)</th>
                    <th>Captured at</th>
                </tr>
                @foreach($iotDataHighTemp as $data)
                    <tr class="text-light">
                        <td>{{$counterHighTemp++}} </td>
                        <td><a href='/reports/userSearch?identifier={{$data->identifier}}'><div class="btn-link">{{$data->identifier}}</div></a></td>
                        <td>{{$data->hbcount}}</td>
                        <td>{{$data->spo2}}</td>
                        <td>{{$data->temp}}</td>
                        <td>{{$data->created_at}}</td>
                    </tr>
                @endforeach
                </table>
                <br/>
                {{ $iotDataHighTemp->appends(Request::except('page'))->fragment('tab-highTemp')->links() }}
            @else
                <p class="h2">No Data available!</p>        
            @endif
        </div>
        <div class="tab-pane fade " id="tab-lowSpo2-Data" role="tabpanel" aria-labelledby="ex2-tab-2" >
            @if (count($iotDataLowSpo2) > 0)
                <div class="d-flex">
                    <div>{{ $iotDataLowSpo2->appends(Request::except('page'))->fragment('tab-allAbnormal')->links() }}</div>
                    <div class="ml-auto"><a href="{{ URL::previous() }}" class="btn btn-primary">Back</a></div>
                </div>
                <br/>
                <table class="table table-sm table-bordered table-responsive bg-transparent text-center">
                    <tr>
                    <th>Serial No </th>
                    <th>Identifier</th>
                    <th>Pulse Rate (per min)</th>
                    <th>SPO2 (%)</th>
                    <th>Temperature (&#8457;)</th>
                    <th>Captured at</th>
                </tr>
                @foreach($iotDataLowSpo2 as $data)
                    <tr class="text-light">
                        <td>{{$counterLowSpo2++}} </td>
                        <td><a href='/reports/userSearch?identifier={{$data->identifier}}'><div class="btn-link">{{$data->identifier}}</div></a></td>
                        <td>{{$data->hbcount}}</td>
                        <td>{{$data->spo2}}</td>
                        <td>{{$data->temp}}</td>
                        <td>{{$data->created_at}}</td>
                    </tr>
                @endforeach
                </table>
               <br/>
                {{ $iotDataLowSpo2->appends(Request::except('page'))->fragment('tab-lowSpo2')->links() }}
            @else
                <p class="h2">No Data available!</p>        
            @endif
        </div>
        <div class="tab-pane fade " id="tab-highHeartRate-Data" role="tabpanel" aria-labelledby="ex2-tab-2" >
            @if (count($iotDataHighHbcount) > 0)
            <div class="d-flex">
                    <div>{{ $iotDataHighHbcount->appends(Request::except('page'))->fragment('tab-allAbnormal')->links() }}</div>
                    <div class="ml-auto"><a href="{{ URL::previous() }}" class="btn btn-primary">Back</a></div>
                </div>
                <br/>
                <table class="table table-sm table-bordered table-responsive bg-transparent text-center">
                    <tr>
                    <th>Serial No </th>
                    <th>Identifier</th>
                    <th>Pulse Rate (per min)</th>
                    <th>SPO2 (%)</th>
                    <th>Temperature (&#8457;)</th>
                    <th>Captured at</th>
                </tr>
                @foreach($iotDataHighHbcount as $data)
                    <tr class="text-light">
                        <td>{{$counterHighHbCount++}} </td>
                        <td><a href='/reports/userSearch?identifier={{$data->identifier}}'><div class="btn-link">{{$data->identifier}}</div></a></td>
                        <td>{{$data->hbcount}}</td>
                        <td>{{$data->spo2}}</td>
                        <td>{{$data->temp}}</td>
                        <td>{{$data->created_at}}</td>
                    </tr>
                @endforeach
                </table>
                <br/>
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
                <a href="/adminReports/sLocationReport?source={{$source}}&type=download" class="btn btn-info">Download</a>
            </div>
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-primary">Back</a>
            </div>
        </div>
        @include('inc.parameters')
    </p>

    
@endsection