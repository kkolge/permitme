@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($roles) > 0)
    <p class="h1"> List of Roles </p>
        <table class="table table-sm table-bordered table-responsive bg-transparent text-center">
            <tr >
                <th class="cols-1">Serial No </th>
                <th class="cols-2">Name</th>
                <th class="cols-2">Guard name</th>
                <th class="cols-2">Created at</th>
                <th class="cols-3">Actions</th>
            </tr>
           
        @foreach($roles as $role)
            <tr class="text-light">
                <td class="cols-1"> {{$counter++}} </td>
                <td class="cols-2">{{$role->name}}</td>
                <td class="cols-2">{{$role->guard_name}}</td>
                <td class="cols-2">{{$role->created_at}}</td>
                <td class="cols-3">
                    <span>
                        <span><a href='/roles/{{$role->id}}/edit' class="btn btn-link">Edit</a></span>
                        <span><form action="{{ route('roles.destroy', $role->id)}}" method="post">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" onclick="return confirm('Are you sure?')" type="submit">Delete</button>
                        </form>
                        </span>
                    </span>
                </td>
            </tr>
        @endforeach
        </table>
        {{$roles->links()}}
    @else
        <p class="h1">No Roles added!</p>     
    @endif
    
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="roles/create" class="btn btn-primary">Add Role </a>
            </div>
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>

    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection