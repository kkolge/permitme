@extends('layouts.app')

@section('content')
    <p class="h1">Add permanent users for your location</p>
    {!! Form::open(['action' => 'RegUsersController@store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
        <div class="form-group">
            <div class="flex">
                <div class="w-1/2 px-md-5">
                    {{Form::label('name', 'Name')}}
                    {{Form::text('name', '', ['class' => 'form-control', 'placeholder' => 'Name'])}}
                </div>
                <div class="w-1/2 px-md-5">
                    {{Form::label('phoneno', 'Phone No.')}}
                    {{Form::text('phoneno', '', ['class' => 'form-control', 'placeholder' => 'Phone No.'])}}
                </div>
            </div>
            <br/>
            <div class="flex">
                <div class="w-1/2 px-md-5">
                    {{Form::label('tagid', 'Tag ID')}}
                    {{Form::text('tagid', '', ['class' => 'form-control', 'placeholder' => 'XX XX XX XX XX XX XX'])}}
                </div>
                <div class="w-1/2 px-md-5">
                    {{Form::label('aadharno', 'Aadhar No')}}
                    {{Form::text('aadharno', '', ['class' => 'form-control', 'placeholder' => 'Aadhar No'])}}
                </div>
            </div>
            <br/>
            <div class="flex">
                <div class="w-1/2 px-md-5">
                    {{Form::label('resiarea', 'Residential Area')}}
                    {{Form::text('resiarea', '', ['class' => 'form-control', 'placeholder' => 'Residential Area'])}}
                </div>
                <div class="w-1/2 px-md-5">
                    {{Form::label('resilandmark', 'Residentia Landmark')}}
                    {{Form::text('resilandmark', '', ['class' => 'form-control', 'placeholder' => 'Residential Landmark'])}}
                </div>
            </div>
            <br/>
            <div class="flex">
                <div class="w-1/3 px-md-5">
                    {{Form::label('vaccinated', 'Vaccinated')}}
                    {{Form::checkbox('vaccinated', 1)}}
                </div>
                <div class="w-1/3 px-md-5">
                    {{Form::label('firstvaccin', 'First Vaccin')}}
                    {{Form::date('firstvaccin', \Carbon\Carbon::now()->subYears(50))}}
                </div>
                <div class="w-1/3 px-md-5">
                    {{Form::label('secondvaccin', 'Second Vaccin')}}
                    {{Form::date('secondvaccin', \Carbon\Carbon::now()->subYears(50))}}
                </div>
            </div>
            <br/>
            <div class="flex">
                <div class="w-1/2 px-md-5">
                    {{Form::label('photo', 'Upload Photo')}}
                    {{Form::file('coverimage',['class' => 'form-control', 'placeholder'=>'Upload Photo'])}}
                </div>
                <div class="w-1/2 px-md-5">
                    {{Form::label('isactive', 'Status')}}
                    <br/>
                    {{Form::radio('isactive',1, false, ['placeholder' => 'Status'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                    {{Form::radio('isactive',0, true, ['placeholder' => 'Status', 'checked' => 'true'])}} No
                </div>
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
