@extends('layouts.app')

@section('content')
    <h1><font size="+2">Edit Location User Link</font></h1><br/>
    {!! Form::open(['action' => ['LinkLocUserController@update', $lnk], 'method' => 'POST']) !!}
        <div class="form-group">
            <div class="px-md-5">
                {{Form::label('Location','Location')}}
                {{Form::select('Location',$loc,null, ['class'=>'form-control'])}}
            </div><br/>
            <div class="px-md-5">
                {{Form::label('User','User')}}
                {{Form::select('User',$user,null,['class'=>'form-control'])}}
            </div><br/>
            <div class="px-md-5">
                {{Form::label('designation','Designation')}}
                {{Form::select('designation',['Chairman'=>'Chairman', 
                    'Secretary'=>'Secretary', 'Treasurer'=>'Treasurer',
                    'Officer' => 'Officer',
                        'HR' => 'HR',
                        'Other' => 'Other'],$links->designation,['class'=>'form-control'])}}
            </div><br/>
            <div class="px-md-5">
                {{Form::label('phoneno', 'Phone No.')}}
                {{Form::text('phoneno',$links->phoneno1,['placeholder'=>'Phone No.', 'class'=>'form-control'])}}
            </div><br/>
            <div class="px-md-5">
                {{Form::label('isactive', 'Status')}}
                <br/>
                @if ($lnk->isactive == '1')
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