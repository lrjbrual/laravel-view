@extends('layouts.user')
@section('title', '| Dashboard')

@section('user')
  <!-- Content Header (Page header) -->
  <header class="head">
      <div class="main-bar row">
          <div class="col-lg-6 col-sm-4">
              <h4 class="nav_top_align">
                  Dashboard
              </h4>
          </div>
          @include('partials._helpicon')
      </div>
  </header>

  @include('partials.dashboard._overview')
  @include('partials.dashboard._todo')
  <script type="text/javascript" src="{{asset('assets/js/pages/new_dashboard.js')}}"></script>

  @if(app('request')->input('action') == 'loggedin')
    <!-- Google Code for Logged in Conversion Page -->
    <script type="text/javascript">
    /* <![CDATA[ */
    var google_conversion_id = 851470613;
    var google_conversion_language = "en";
    var google_conversion_format = "3";
    var google_conversion_color = "ffffff";
    var google_conversion_label = "OkeTCMeHvnIQldKBlgM";
    var google_remarketing_only = false;
    /* ]]> */
    </script>
    <script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
    </script>
    <noscript>
    <div style="display:inline;">
    <img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/851470613/?label=OkeTCMeHvnIQldKBlgM&amp;guid=ON&amp;script=0"/>
    </div>
    </noscript>
  @endif
@endsection
