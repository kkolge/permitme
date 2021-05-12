@extends('layouts.app')

@section('content')
    <p class="h1">Link User to Location</p>
    {!! Form::open(['action' => 'LinkLocUserController@store', 'method' => 'POST']) !!}
        <div class="form-group">
            
                <div class="px-md-5">
                    {{Form::label('Location','Location')}}
                    {{Form::select('Location',$loc,null, ['class'=>'form-control', 'placeholder'=>'Select'])}}
                </div><br/>
                <div class="px-md-5">           
                    {{Form::label('User','User')}}
                    {{Form::select('User',$user,null,['class'=>'form-control','placeholder'=>'Select'])}}
                </div><br/>
                <div class="px-md-5">
                    {{Form::label('designation','Designation')}}
                    {{Form::select('designation',['Chairman'=>'Chairman', 
                        'Secretary'=>'Secretary', 
                        'Treasurer'=>'Treasurer',
                        'Management' => 'Management',
                        'Officer' => 'Officer',
                        'HR' => 'HR',
                        'Other' => 'Other'],null,['placeholder'=>'Select','class'=>'form-control'])}}
                </div><br/>
                <div class="px-md-5">
                    {{Form::label('phoneno', 'Phone No.')}}
                    {{Form::text('phoneno','',['placeholder'=>'Phone No.', 'class'=>'form-control'])}}
                </div><br/>
                <div class="px-md-5">
                    {{Form::label('isactive', 'Status')}} <br/>
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