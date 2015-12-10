@extends('links/global')

@section('content')
    <form action="" method="post">
        <div class="form-group">
            <label for="url" class="title">Link to shorten</label>
            <input type="text" name="url" id="url" class="form-control" placeholder="http://example.com">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
        </div>
        <div class="form-group">
            <input type="submit" id="submit" class="btn btn-info" value="Shorten">
        </div>
    </form>
@endsection