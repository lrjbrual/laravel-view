@extends('layouts.user')
@section('title', '| Ads Recommendation')

@section('user')
  <!-- Content Header (Page header) -->
  <header class="head">
      <div class="main-bar row">
          <div class="col-lg-6 col-sm-4">
              <h4 class="nav_top_align">
                  <img class="refunds-header-icon" src="{{ url('/images/icons/ads-analytics-icon-black.png') }}">
                  Ads Recommendation
              </h4>
          </div>
          @include('partials._helpicon')
      </div>
  </header>

  <div class="col-md-12">
    @include('partials._flashmessage')
  </div>
  @if($amzChecker)
    @include('partials.adsrecommendation._ads_recommendation')
  @else
    @include('partials.adsperformance._amz_modal')
  @endif

@endsection
