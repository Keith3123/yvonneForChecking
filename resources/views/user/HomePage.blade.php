@extends('layouts.app')

@section('partials.footer')
@endsection

@section('content')
    @include('partials.homepage.hero')
    @include('partials.homepage.categories')
    @include('partials.homepage.products', ['featuredProducts' => $featuredProducts])


    @include('partials.homepage.about')

    {{-- CTA before About/Footer --}}
    @include('partials.homepage.cta')
    @include('partials.homepage.testimonials')

    
@endsection
