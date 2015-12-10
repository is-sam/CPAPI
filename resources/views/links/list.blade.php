@extends('links.global')

@section('content')
    @foreach($links as $link)
        <p>{{ $link->url }}</p>
    @endforeach
@endsection