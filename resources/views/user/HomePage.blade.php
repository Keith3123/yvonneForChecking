@extends('layouts.app')

@section('title', "Home")

@section('content')
    @include('partials.homepage.hero')
    @include('partials.homepage.about')
    @include('partials.homepage.products')
@endsection
