@extends('layouts.user')
@section('title', '| Merchant Refund')

@section('user')
  <!-- Content Header (Page header) -->
  <header class="head">
      <div class="main-bar row">
          <div class="col-lg-6 col-sm-4">
              <h4 class="nav_top_align">
                  <img class="refunds-header-icon" src="{{ url('/images/icons/refunds-icon-small.png') }}">
                  FBA Refunds
              </h4>
          </div>
          @include('partials._helpiconWithTour')
      </div>
  </header>

  @include('partials.fbarefund._page')
@endsection
