@extends('layouts.user')
@section('title', '| Ads Performance')

@section('user')
  <!-- Content Header (Page header) -->
  <div class="bidUpdatePopUp">Bid was changed in the last few days which may impact your results, <a data-toggle="modal" data-target="#prodCostsGraphtModal" class="openGraphModal">Click here to view details </a><i class="fa fa-close closeBidUpdatePopUp"></i></div>
  <header class="head">
      <div class="main-bar row">
          <div class="col-lg-6 col-sm-4">
              <h4 class="nav_top_align">
                  <img class="refunds-header-icon" src="{{ url('/images/icons/ads-analytics-icon-black.png') }}">
                  Ads Performance
              </h4>
          </div>
          @include('partials._helpicon')
      </div>
  </header>

  <div class="col-md-12">
    @include('partials._flashmessage')
  </div>
  @if($amzChecker)
    @include('partials.adsperformance._flipcards')
    @include('partials.adsperformance._table')
  @else
    @include('partials.adsperformance._amz_modal')
  @endif
  
@endsection
