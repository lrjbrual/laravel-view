@extends('layouts.user')
@section('title', '| Billing')

@section('header_styles')
    <!--Plugin style-->
    <link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/modal/css/component.css')}}"/>
    <link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/bootstrap-tagsinput/css/bootstrap-tagsinput.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('assets/vendors/animate/css/animate.min.css')}}" />
    <!-- end of plugin styles -->
    <link type="text/css" rel="stylesheet" href="{{asset('assets/css/pages/portlet.css')}}"/>
    <link type="text/css" rel="stylesheet" href="{{asset('assets/css/pages/advanced_components.css')}}"/>
@stop

@section('user')
  <!-- Content Header (Page header) -->
  <header class="head">
      <div class="main-bar row">
          <div class="col-lg-6 col-sm-4">
              <h4 class="nav_top_align">
                  <i class="fa fa-credit-card"></i>
                  Billing
              </h4>
          </div>
          @include('partials._helpicon')
      </div>
  </header>

  <div class="col-md-12">
    @include('partials._flashmessage')
  </div>

  @include('partials.billing._page')
@endsection
