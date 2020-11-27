@extends('layouts.app')

@section('content')
    <h1><font size="+2">Add permanent users for your location</font></h1><br/>
    {!! Form::open(['action' => 'AssignUserRoleController@store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
        <div class="form-group">
            <div class="px-md-5">
                <div class="px-md-5">
                    {{Form::label('user','Select User')}}
                    {{Form::select('user',$users,null, ['class'=>'form-control', 'placeholder'=>'Select'])}}
                </div><br/>
                <div class="px-md-5">           
                    {{Form::label('role','Select Role')}}
                    {{Form::select('role',$roles,null,['class'=>'form-control','placeholder'=>'Select'])}}
                </div><br/>
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