@extends('layouts.user')
@section('title', '| Product Reviews')

@section('user')
<header class="head">
    <div class="main-bar row">
        <div class="col-lg-6 col-sm-4">
            <h4 class="nav_top_align">
                <i class="fa fa-star"></i>
                Product Reviews
            </h4>
        </div>
        @include('partials._helpicon')
    </div>
</header>

@include('partials.productreview._page')
@endsection
