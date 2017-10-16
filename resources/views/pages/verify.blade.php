@extends('layouts.master')
@section('title', '| Email Verification')

@section('content')
  <div class="container margin-top-60">
    <br />
    <div class="col-md-12">
      @include('partials._flashmessage')
    </div>
    <br>
      <div class="col-md-8 col-md-offset-2 col-sm-12 col-xs-12 verify_container text-center">
        <form method="POST" action="/verify" class="form-inline">
        <i class="fa fa-envelope-o email_verication" style="font-size: 72pt;color:#FF5722;"></i>
          <div class="row">
            <div class="col-md-12">
                <h4 class="">Hi {{ ucfirst(trans($fname)) }},</h4>
                <p>Thank you for creating a <strong style="color:#FF5722">Trendle</strong> <strong style="color:#00ADB5">Analytics</strong> account. We've sent you an email to verify your email address. Please click on the link in the email to verify. Make sure to check your spam folder.</p>
            </div>
          </div>
          {{ csrf_field() }}
          <input type="hidden" id="check" name="email"  value="{{ $email }}">
          <input type="hidden" name="token" value="{{ $reg_roken }}" readonly>
          <input type="hidden" name="fname" value="{{ $fname }}" readonly>
          <input type="hidden" name="lname" value="{{ $lname }}" readonly>
        </form>
      </div>
</div>
<script type="text/javascript">
  fbq('track', 'CompleteRegistration');
</script>

<!-- Google Code for Sign up form completed Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 851470613;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "6AztCI6HvnIQldKBlgM";
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/851470613/?label=6AztCI6HvnIQldKBlgM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>

@endsection
