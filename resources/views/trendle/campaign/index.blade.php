@extends('layouts.user')
@section('title', '| Automatic Emails')

@section('user')
  <!-- Content Header (Page header) -->
  <header class="head">
      <div class="main-bar row">
          <div class="col-lg-6 col-sm-4">
              <h4 class="nav_top_align">
                  <i class="fa fa-envelope-o"></i>
                  Automatic Emails
              </h4>
          </div>
          @include('partials._helpiconWithTour')
      </div>
  </header>

  <div class="col-md-12">
    @include('partials._flashmessage')
  </div>

  @include('trendle.campaign.partials._page')
@endsection
