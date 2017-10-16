@extends('layouts.user')
@section('title', '| Marketplace')

@section('user')
  <!-- Content Header (Page header) -->
  <header class="head">
      <div class="main-bar row">
          <div class="col-lg-6 col-sm-4">
              <h4 class="nav_top_align">
                  <i class="fa fa-globe"></i>
                  Marketplaces
              </h4>
          </div>
          @include('partials._helpicon')
      </div>
  </header>

  @include('trendle.marketplace.partials._page')
@endsection
