@extends('layouts.user')
@section('title', '| Campaign Manager')

@section('user')
  <!-- Content Header (Page header) -->
  <header class="head">
      <div class="main-bar row">
          <div class="col-lg-6 col-sm-4">
              <h4 class="nav_top_align">
                  <i class="fa fa-tasks"></i>
                  Campaign Manager
              </h4>
          </div>
      </div>
  </header>

@include('partials.campaignmanager._campaign_manager_table')

@endsection
