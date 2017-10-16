{{ app()->setLocale(session()->get('lang', 'en')) }}
@extends('layouts.master')
@section('title', '| Homepage')

@section('content')
  <div class="wrapper">
    @include('pages.banner') {{-- BANNER --}}
  </div>
  <div class="container" id="features">
    @include('partials.pages._features')
  </div>
  {{-- <div class="container" id="pricing">
    @include('partials.pages._pricing')
  </div> --}}

<script src="//static.tapfiliate.com/tapfiliate.js" type="text/javascript" async></script>
  <script type="text/javascript">
  window['TapfiliateObject'] = i = 'tap';
  window[i] = window[i] || function () {
      (window[i].q = window[i].q || []).push(arguments);
  };

  tap('create', '4569-a88ee8');
  tap('detectClick');
  fbq('track', 'ViewContent');
  fbq('track', 'Lead');
</script>

<!-- Google Code for Landing page visitor Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 851470613;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "TPN1CIiHvnIQldKBlgM";
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/851470613/?label=TPN1CIiHvnIQldKBlgM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>

@endsection
