@extends('layouts.app')

@section('content')
    <p class="h1">Edit permanent user</p>
    {!! Form::open(['action' => ['RegUsersController@update',$user->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
        <div class="form-group">
            <div class="flex">
            <div class="w-1/2 px-md-5">
                {{Form::label('name', 'Name')}}
                {{Form::text('name', $user->name, ['class' => 'form-control', 'placeholder' => 'Name'])}}
            </div>
            <div class="w-1/2 px-md-5">
                {{Form::label('phoneno', 'Phone No.')}}
                {{Form::text('phoneno', $user->phoneno, ['class' => 'form-control', 'placeholder' => 'Phone No.'])}}
            </div>
        </div>
        <br/>
        <div class="flex">
            <div class="w-1/2 px-md-5">
                {{Form::label('tagid', 'Tag ID')}}
                {{Form::text('tagid', $user->tagid, ['class' => 'form-control', 'placeholder' => 'XX XX XX XX XX XX XX'])}}
            </div>
            <div class="w-1/2 px-md-5">
                {{Form::label('aadharno', 'Aadhar No')}}
                {{Form::text('aadharno', $user->AadharNo, ['class' => 'form-control', 'placeholder' => 'Aadhar No'])}}
            </div>
        </div>
        <br/>
        <div class="flex">
            <div class="w-1/2 px-md-5">
                {{Form::label('resiarea', 'Residential Area')}}
                {{Form::text('resiarea', $user->resiarea, ['class' => 'form-control', 'placeholder' => 'Residential Area'])}}
            </div>
            <div class="w-1/2 px-md-5">
                {{Form::label('resilandmark', 'Residentia Landmark')}}
                {{Form::text('resilandmark', $user->resilandmark, ['class' => 'form-control', 'placeholder' => 'Residential Landmark'])}}
            </div>
        </div>
        <br/>
        <div class="flex">
            <div class="w-1/3 px-md-5">
                {{Form::label('vaccinated', 'Vaccinated')}}
                {{Form::checkbox('vaccinated', 1, $user->vaccinated)}}
            </div>
            <div class="w-1/3 px-md-5">
                {{Form::label('firstvaccin', 'First Vaccin')}}
                @if ($user->vaccinated == 0)
                    {{Form::date('firstvaccin', \Carbon\Carbon::now()->subYears(50))}}
                @else
                    {{Form::date('firstvaccin', new \Carbon\Carbon($user->firstvaccin))}}
                @endif
            </div>
            <div class="w-1/3 px-md-5">
                {{Form::label('secondvaccin', 'Second Vaccin')}}
                @if($user->vaccinated == 0)
                    {{Form::date('secondvaccin', \Carbon\Carbon::now()->subYears(50))}}
                @else
                    {{Form::date('secondvaccin', new \Carbon\Carbon($user->secondvaccin))}}
                @endif
            </div>
        </div>
        <br/>
        <div class="flex">
            <div class="w-1/2 px-md-5">
                {{Form::label('photo', 'Upload Photo')}}  
                {{Form::file('coverimage',['class' => 'form-control', 'placeholder'=>'Upload Photo'])}}
            </div>
            <div class="w-1/2 px-md-5">
               <img style="width:50%" src="/storage/coverimages/{{$user->coverimage}}">
            </div>
        </div>
        <br/>
        <div class="flex">
            <div class="w-1/2 px-md-5"> 
                {{Form::label('isactive', 'Status')}}<br/>
                @if ($user->isactive == '1')
                    {{Form::radio('isactive', 1, true, ['placeholder' => 'Status', 'checked' => 'checked'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                    {{Form::radio('isactive', 0, false, ['placeholder' => 'Status'])}} No
                @else
                    {{Form::radio('isactive', 1, false, ['placeholder' => 'Status'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                    {{Form::radio('isactive', 0, true, ['placeholder' => 'Status', 'checked' => 'checked'])}} No
                @endif
            </div>
            <div class="w-1/2 px-md-5"> 
            
            </div>
        </div>
    <!--
            <div class="px-md-5">
                <div class="flex">
                    <div class="w-1/2">
                        <div class="form-group">
                            {{Form::label('photo', 'Upload Photo')}}  
                            {{Form::file('coverimage',['class' => 'form-control', 'placeholder'=>'Upload Photo'])}}
                        </div>
                    </div>
                    <div class="mx-auto">
                        <img style="width:50%" src="/storage/coverimages/{{$user->coverimage}}">
                    </div>
                </div>
            </div>
    -->   
        
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
