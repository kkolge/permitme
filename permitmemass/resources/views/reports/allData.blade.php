@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<!-- creating form for capturing the user id -->
{!! Form::open(['action' => ['ReportsController@UserReportSearch'], 'method' => 'POST']) !!}
    <div class="form-group">
        <table class="table table-sm">
            <tr>
                <td> <font size="+1">{!! Form::label('identifier','Identifier') !!}</font></td>
                <td>{!! Form::text('identifier','',['class'=>'form-control', 'placeholder'=>'Identifier']) !!}</td>
                <td>{{Form::submit('Get Data', ['class'=>'btn btn-primary'])}}</td>
            </tr>
        </table>
        <hr/>
    </div>
{!! Form::close() !!}
<!-- end form -->

<?php $counter = 1; ?>
    
    @if (count($allData) > 0)
    <h1> <font size="+2">Data collected by all sensors </font> </h1>
        <table class="table table-striped table-bordered">
            <tr>
                <th>Serial No </th>
                <th>Location</th>
                <th>Phone No.</th>
                <th>Temperature</th>
                <th>SPO2 </th>
                <th>Heart Beat</th>
                <th>Captured at</th>
            </tr>
        @foreach($allData as $data)
            <tr>
                <td>{{$counter++}} </td>
                <td>{{$data->lname}}</td>
                <td>{{$data->identifier}}</td>
                <td>{{$data->temp}}</td>
                <td>{{$data->spo2}}</td>
                <td>{{$data->hbcount}}</td>
                <td>{{$data->created_at}}</td>
            </tr>
        @endforeach
        </table>
        {{$allData->links()}}
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