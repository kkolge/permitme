@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>




<?php $counter = 1; ?>
    
    @if (count($allData) > 0)
    <p class="h1">Last 15 days data for your location (all devices)  </p>
    <!-- New section added with cummary -->
    <div class="card-deck">
            <div class="card text-white bg-success text-center ">
                <div class="card-header ">Number of Records</div>
                <div class="card-body ">{{$recCountTotal}}</div>
            </div> 
       
            <div class="card text-white bg-danger text-center ">
                <div class="card-header ">All Abnormal Parameters</div>
                <div class="card-body ">{{$recCountHighAll}}</div>
            </div> 
       
            <div class="card text-white bg-danger text-center ">
                <div class="card-header ">High Temperature</div>
                <div class="card-body ">{{$recCountHighTemp}}</div>
            </div> 
       
            <div class="card text-white bg-danger text-center ">
                <div class="card-header ">Low SPO2</div>
                <div class="card-body ">{{$recCountLowSpo2}}</div>
            </div> 
       
            <div class="card text-white bg-danger text-center ">
                <div class="card-header ">High Pulse Rate</div>
                <div class="card-body ">{{$recCountHighPulse}}</div>
            </div> 
       
    </div>
    <br/>
    <br/>
    <!-- end filters -->
    <div class="row ">
        <div class="row align-middle">
            <!-- creating form for capturing the user id -->
            {!! Form::open(['action' => ['ReportsController@UserReportSearch'], 'method' => 'POST']) !!}
                <div class="form-group">
                    <div class="form-row">
                        <div class="text-right form-control-lg ">{!! Form::label('identifier','Search') !!}</div>
                        <div class="">{!! Form::text('identifier','',['class'=>'form-control', 'placeholder'=>'Search by Mobile Number (10 digits)']) !!}</div>
                        <div class="text-left">{{Form::submit('Search', ['class'=>'btn btn-primary'])}}</div>
                    </div>
                    
                    <hr/>
                </div>
            {!! Form::close() !!}
            <!-- end form -->
        </div>

        <div class="row ml-auto align-middle">
            {!! Form::open(['url'=>Request::url(), 'method' => 'GET']) !!}
            <div class="form-row">
                @if(Auth::user()->hasRole(['Super Admin','Location Admin']))
                        <div class="text-right form-control-lg" >{!! Form::label('location','Filter by Location') !!}</div>
                    @elseif(Auth::user()->hasRole(['Site Admin']))
                        <div class="text-right form-control-lg" >{!! Form::label('location','Filter by Device') !!}</div>
                    @endif
                    <div >{!! Form::select('location',$ddLocation,null,['class'=>'form-control', 'placeholder'=>'Select']) !!}</div>                   
                    <div class="tet-left">{!! Form::submit('Submit', ['class'=>'btn btn-primary']) !!}</div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    <br/>
    <div class="row d-flex">
        <div>{{$allData->links()}}</div>
        <div class="ml-auto"> <a href="{{ URL::previous() }}" class="btn btn-info">Back</a></div>
    </div>
    <br/>
    
    <!-- End of new section -->
        <table class="table table-sm table-bordered table-responsive bg-transparent text-center">
            <tr>
                <th>Serial No </th>
                @if(Auth::user()->hasRole(['Super Admin','Location Admin']))
                    <th>Location</th>
                @elseif(Auth::user()->hasRole(['Site Admin']))
                    <th>Device</th>
                @endif
                <th>Identifier</th>
                <th>Pulse Rate (per min)</th>
                <th>SPO2 (%)</th>
                <th>Temperature (&#8457;)</th>
                <th>Captured at</th>
            </tr>
        @foreach($allData as $data)
            @if($data->flagstatus == true)
                <tr class="text-danger">
            @else
                <tr class="text-light">
            @endif
                <td>{{$counter++}} </td>
                @if(Auth::user()->hasRole(['Super Admin','Location Admin']))
                    <td>{{$data->lname}}</td>
                @elseif(Auth::user()->hasRole(['Site Admin']))
                    <td>{{$data->dname}}</td>
                @endif
                <td>{{$data->identifier}}</td>
                <td>{{$data->hbcount}}</td>
                <td>{{$data->spo2}}</td>
                <td>{{$data->temp}}</td>
                <td>{{$data->created_at}}</td>
            </tr>
        @endforeach
        </table>
        
        {{$allData->links()}}
        @include('inc.parameters')
    @else
        <p class="h1">No Data available!</p>        
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