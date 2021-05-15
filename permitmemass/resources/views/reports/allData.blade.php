@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>

<span style="aligh:center;">
<!-- creating form for capturing the user id -->
{!! Form::open(['action' => ['ReportsController@UserReportSearch'], 'method' => 'POST']) !!}
    <div class="form-group">
        <div class="form-row">
            <div class=" col-3 text-right form-control-lg ">{!! Form::label('identifier','Search') !!}</div>
            <div class="col-4">{!! Form::text('identifier','',['class'=>'form-control', 'placeholder'=>'Search by Mobile Number (10 digits)']) !!}</div>
            <div class="col-2 text-left">{{Form::submit('Search', ['class'=>'btn btn-primary'])}}</div>
        </div>
        
        <hr/>
    </div>
{!! Form::close() !!}
<!-- end form -->
</span>


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
   
    <!-- end filters -->
    <div>
        <p class="h2">Detailed Data</p>
    </div>
    <div>
    <span>
        <!-- creating form for capturing the user id -->
        {!! Form::open(['url'=>Request::url(), 'method' => 'GET']) !!}
            <div class="form-group">
                <div class="form-row">
                    @if(Auth::user()->hasRole(['Super Admin','Location Admin']))
                        <div class="col-1 text-right form-control-lg" >{!! Form::label('location','Location') !!}</div>
                    @elseif(Auth::user()->hasRole(['Site Admin']))
                        <div class="col-1 text-right form-control-lg" >{!! Form::label('location','Device') !!}</div>
                    @endif
                    <div class="col-2" style="vertical-align:middle;">{!! Form::select('location',$ddLocation,null,['class'=>'form-control', 'placeholder'=>'Select']) !!}</div>                   
                    <div>{!! Form::submit('Submit', ['class'=>'btn btn-primary']) !!}</div>
                </div>
                
                <hr/>
            </div>
        {!! Form::close() !!}
<!-- end form -->
</span>
</div>
    <!-- End of new section -->
        <table class="table table-bordered">
            <tr>
                <th width="10%">Serial No </th>
                @if(Auth::user()->hasRole(['Super Admin','Location Admin']))
                    <th>Location</th>
                @elseif(Auth::user()->hasRole(['Site Admin']))
                    <th>Device</th>
                @endif
                <th>Identifier</th>
                <th>Pulse Beat (per min)</th>
                <th>SPO2 (%)</th>
                <th>Temperature (&#8457;)</th>
                <th>Captured at</th>
            </tr>
        @foreach($allData as $data)
            @if($data->flagstatus == true)
                <tr class="table-danger">
            @else
                <tr>
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
        <p class="small">
            Normal Range: &nbsp; &nbsp; &nbsp; &nbsp;
            Pulse Rate < {{env('CUTOFF_PULSE')}} per min &nbsp; &nbsp; &nbsp; &nbsp;
            SPO2 > {{env('CUTOFF_SPO2')}}%&nbsp; &nbsp; &nbsp; &nbsp;
            Temperature < {{env('CUTOFF_TEMP')}}&#8457; (Wrist temperature is 3.3&#8451; / 5.9&#8457; lower than core body temperature)
            <br/> <span class="text-danger">Rows in Red indicate abnormal values<span>
        </p> <br/><br/>
        {{$allData->links()}}
    @else
        <p class="h1">No Data available!</p>        
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