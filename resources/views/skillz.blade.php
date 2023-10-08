@extends('boots.boot')
@section('content')
    <div class="container">
        <form action="/skillz/process" class="form" method="POST">
            @csrf
            <div class="form-group">
                <label for="skills"><h4 class="fw-lighter">Enter Your Skillz</h4></label>
                <textarea class="form-control" id="skills" name="skills" rows="3"
                placeholder="Enter Your skillz separated by commas"
                ></textarea>
            </div>
            <button class="btn btn-warning w-100" type="submit">Search</button>
        </form>
    </div>
@endsection()