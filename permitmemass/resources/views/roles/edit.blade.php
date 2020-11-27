@extends('layouts.app')

@section('content')
    <h1><font size="+2">Edit role</font></h1><br/>
    {!! Form::open(['action' => ['RolesController@update',$role->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
        <div class="form-group">
            <div class="form-group">
                <div class="px-md-5">
                    {{Form::label('name', 'Name')}}
                    {{Form::text('name', $role->name, ['class' => 'form-control', 'placeholder' => 'Name'])}}
                </div><br/>
                <div class="px-md-5">
                    {{Form::label('guard', 'Guard Name (optional)')}}
                    {{Form::text('guard', $role->guard, ['class' => 'form-control', 'placeholder' => 'Guard Name'])}}
                </div><br/>
            </div>
        </div>
        @if(count($allPerms) > 0){
            <div class="px-md-5">
            <table class="table table-striped table-bordered">
                <tr>
                    <th width="10%">Select </th>
                    <th>Permissions</th>
                </tr>
                @foreach($allPerms as $ap){
                    <tr>
                        <?php $found = false;?>
                        @foreach($perms as $p){
                            @if($ap->id == $p->id)
                                <?php $found=true ?>
                            @endif
                        }
                        
                        @if($found == true)
                            <td>{{Form::checkbox('permsSel[]',$p->id, 'checked' )}} </td>
                        @else
                            <td>{{Form::checkbox('permsSel[]',$p->id )}}</td>
                        @endif
                        
                        <td>{{$ap->name}}</td>
                    </tr>
                }
            </table>
            </div>
        }
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