@extends('layouts.app')

@section('content')
    <p class="h1">Add device</p>
    <br/>
    {!! Form::open(['action' => 'DeviceController@store', 'method' => 'POST']) !!}
        <div class="form-group">
            <div class="px-md-5">
                {{Form::label('serialno', 'Serial No')}}
                {{Form::text('serialno', '', ['class' => 'form-control', 'placeholder' => 'Serial No'])}}
            </div>
            <br/>
            <div class="px-md-5">
                {{Form::label('deviceType', 'Device Type')}}
                {{Form::select('deviceType', $devType, null,['class' => 'form-control', 'placeholder' => 'Device Type'])}}
            </div>
            <br/>
            <div class="px-md-5">
                {{Form::label('isactive', 'Status')}}
                <br>
                {{Form::radio('isactive',1, false, ['placeholder' => 'Status'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                {{Form::radio('isactive',0, true, ['placeholder' => 'Status', 'checked' => 'true'])}} No
            </div>
        </div>
        <div class="flex">
            <div class="mx-auto">
                {{Form::submit('Submit', ['class'=>'btn btn-primary'])}}
            </div>
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>

    {!! Form::close() !!}
    <p>
       
    </p>
@endsection