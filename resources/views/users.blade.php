@extends('boots.boot')
@section('content')
    <h4 class="text-warning mx-auto">Collaborators At Flawless</h4>
    <table class="table table-striped table-hover table-bordered table-sm">
        <thead>
            <th>UNIQUE IDENTIFIER</th>
            <th>NAME</th>
            <th>EMAIL</th>
            <th>CONTACT</th>
        </thead>
        @foreach ($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td><a href="/chatify/{{$user->id}}"> <i class="fa fa-telegram" aria-hidden="true"></i> </a></td>
            </tr>
        @endforeach
    </table>
@endsection()