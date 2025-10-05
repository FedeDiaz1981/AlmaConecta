@extends('layouts.app')

@section('title',$profile->display_name)

@section('content')
@if($profile->template_key === 'b')
    @include('profiles.templates.b')
@else
    @include('profiles.templates.a')
@endif
@endsection
