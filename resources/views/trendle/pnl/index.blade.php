@extends('layouts.user')
@section('title', '| P&L')

@section('user')
  <!-- Content Header (Page header) -->
  <header class="head">
      <div class="main-bar row">
          <div class="col-lg-6 col-sm-4">
              <h4 class="nav_top_align">
                  <img class="refunds-header-icon" src="{{ url('/images/icons/profitability-analytics-icon-small.png') }}">
                  P&L
              </h4>
          </div>
          @include('partials._helpicon')
      </div>
  </header>

  @include('partials.pnl._filter')
  @include('partials.pnl._chart')
  @include('partials.pnl._table_header')
  @include('partials.pnl._revenue')
  @include('partials.pnl._cost')
  @include('partials.pnl._gross_margin')

  <script src="/js/pnl.js"></script>
@endsection
