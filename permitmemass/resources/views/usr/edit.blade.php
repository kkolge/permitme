@extends('layouts.app')

@section('content')
    <p class="h1">Edit permanent user</p>
    {!! Form::open(['action' => ['SystemUsersController@update',$user->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
        <div class="form-group">
            <div class="px-md-5">
                {{Form::label('name', 'Name')}}
                {{Form::text('name', $user->name, ['class' => 'form-control', 'readonly'=>'true'])}}
            </div><br/>
            <div class="px-md-5">
                {{Form::label('email', 'Email')}}
                {{Form::text('email', $user->email, ['class' => 'form-control', 'readonly' => 'true'])}}
            </div><br/>
            <div class="flex">
                <div class="w-1/2 px-md-5">
                    {{Form::label('password', 'Password')}}
                    {{Form::password('password', ['class' => 'awesome'])}}
                </div><br/>
                <div class="w-1/2 px-md-5">
                    {{Form::label('cpassword', 'Confirm Password')}}
                    {{Form::password('cpassword', ['class' => 'awesome'])}}
                </div>
            </div>
        </div>
        {{Form::hidden('_method','PUT')}}
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