@extends('layouts.user')
@section('title', '| Seller Reviews')

@section('user')
<header class="head">
    <div class="main-bar row">
        <div class="col-lg-6 col-sm-4">
            <h4 class="nav_top_align">
                <i class="fa fa-star"></i>
                Seller Reviews
            </h4>
        </div>
        @include('partials._helpicon')
    </div>
</header>

@include('trendle.sellerreview.partials._page')
@endsection
