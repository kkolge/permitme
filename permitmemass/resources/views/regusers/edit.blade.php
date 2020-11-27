@extends('layouts.app')

@section('content')
    <h1><font size="+2">Edit permanent user</font></h1><br/>
    {!! Form::open(['action' => ['RegUsersController@update',$user->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
        <div class="form-group">
            <div class="px-md-5">
            {{Form::label('name', 'Name')}}
            {{Form::text('name', $user->name, ['class' => 'form-control', 'placeholder' => 'Name'])}}
            </div><br/>
            <div class="px-md-5">
            {{Form::label('phoneno', 'Phone No.')}}
            {{Form::text('phoneno', $user->phoneno, ['class' => 'form-control', 'placeholder' => 'Phone No.'])}}
            </div><br/>
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
            <div class="px-md-5">
                {{Form::label('tagid', 'Tag ID')}}
                {{Form::text('tagid', $user->tagid, ['class' => 'form-control', 'placeholder' => 'XX XX XX XX XX XX XX'])}}
            </div>
            
            <div class="px-md-5">
                {{Form::label('aadharno', 'Aadhar No')}}
                {{Form::text('aadharno', $user->AadharNo, ['class' => 'form-control', 'placeholder' => 'Aadhar No'])}}
            </div>
            <div class="px-md-5">
                {{Form::label('isactive', 'Status')}}<br/>
                @if ($user->isactive == '1')
                    {{Form::radio('isactive', 1, true, ['placeholder' => 'Status', 'checked' => 'checked'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                    {{Form::radio('isactive', 0, false, ['placeholder' => 'Status'])}} No
                @else
                    {{Form::radio('isactive', 1, false, ['placeholder' => 'Status'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                    {{Form::radio('isactive', 0, true, ['placeholder' => 'Status', 'checked' => 'checked'])}} No
                @endif
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
