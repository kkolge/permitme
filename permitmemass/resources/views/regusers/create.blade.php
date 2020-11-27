@extends('layouts.app')

@section('content')
    <h1><font size="+2">Add permanent users for your location</font></h1><br/>
    {!! Form::open(['action' => 'RegUsersController@store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
        <div class="form-group">
            <div class="px-md-5">
                {{Form::label('name', 'Name')}}
                {{Form::text('name', '', ['class' => 'form-control', 'placeholder' => 'Name'])}}
            </div><br/>
            <div class="px-md-5">
                {{Form::label('phoneno', 'Phone No.')}}
                {{Form::text('phoneno', '', ['class' => 'form-control', 'placeholder' => 'Phone No.'])}}
            </div><br/>
            <div class="px-md-5">
                {{Form::label('photo', 'Upload Photo')}}
                {{Form::file('coverimage',['class' => 'form-control', 'placeholder'=>'Upload Photo'])}}
            </div><br/>
            <div class="px-md-5">
                {{Form::label('tagid', 'Tag ID')}}
                {{Form::text('tagid', '', ['class' => 'form-control', 'placeholder' => 'XX XX XX XX XX XX XX'])}}
            </div><br/>
            <div class="px-md-5">
                {{Form::label('aadharno', 'Aadhar No')}}
                {{Form::text('aadharno', '', ['class' => 'form-control', 'placeholder' => 'Aadhar No'])}}
            </div><br/>
            <div class="px-md-5">
                {{Form::label('isactive', 'Status')}}
                <br/>
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
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection
