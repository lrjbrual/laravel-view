@extends('layouts.master')
@section('title', '| Register')

@section('content')
  <div class="containter margin-register">
    <div class="col-md-4 col-md-offset-4 col-sm-8 col-offet-sm-2 col-xs-8 col-xs-offset-2 register-box">
      <h4 class="text-center free-trial-text">{{trans('home.landingReg')}}!</h4>
      <p class="text-center">Get Started in a few second</p>
      <form method="POST" action="{{ url('/register') }}">
        {{ csrf_field() }}
        <div class="form-group col-md-10 col-md-offset-1 {{ $errors->has('fname') ? ' has-error' : '' }}">
          <input type="text" class="form-control" id="exampleInputName1" placeholder="First Name" name="fname" value="{{ old('fname') }}" required autofocus>
            @if ($errors->has('fname'))
                <span class="help-block">
                    <strong>{{ $errors->first('fname') }}</strong>
                </span>
            @endif
        </div>
        <div class="form-group col-md-10 col-md-offset-1 {{ $errors->has('lname') ? ' has-error' : '' }}">
          <input type="text" class="form-control" id="exampleInputName1" placeholder="Last Name" name="lname" value="{{ old('lname') }}" required autofocus>
            @if ($errors->has('lname'))
                <span class="help-block">
                    <strong>{{ $errors->first('lname') }}</strong>
                </span>
            @endif
        </div>
        <div class="form-group col-md-10 col-md-offset-1 {{ $errors->has('company') ? ' has-error' : '' }}">
          <input type="text" class="form-control" id="companyfield" placeholder="Company" name="company" value="{{ old('company') }}" required>
          @if ($errors->has('company'))
              <span class="help-block">
                  <strong>{{ $errors->first('company') }}</strong>
              </span>
          @endif
        </div>
        <div class="form-group col-md-10 col-md-offset-1">
          <select class="form-control" name="country_id"equired>
            <option placeholder="Select Country" disabled="disabled" selected="selected">Select Country</option>
            <optgroup>
              @foreach($topCountries as $key => $country)
                  <option value="{{ $key }}">{{ $country }}</option>
              @endforeach
            </optgroup>
            <optgroup label="-----------------------------------------------------------">
              @foreach($countries as $key => $country)
                  <option value="{{ $key }}">{{ $country }}</option>
              @endforeach
            </optgroup>
          </select>
        </div>
        <div class="form-group col-md-10 col-md-offset-1 {{ $errors->has('email') ? ' has-error' : '' }}">
          <input type="email" class="form-control col-md-6" id="exampleInputEmail1" placeholder="your@email.com" name="email" value="{{ old('email') }}" required>
            @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
            @endif
        </div>
        <div class="form-group col-md-10 col-md-offset-1 {{ $errors->has('password') ? ' has-error' : '' }}">
          <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password" name="password" >
            @if ($errors->has('password'))
                <span class="help-block">
                    <strong>{{ $errors->first('password') }}</strong>
                </span>
            @endif
        </div>
        <div class="form-group col-md-10 col-md-offset-1">
          <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Confirm Password" name="password_confirmation" >
        </div>
        <div class="checkbox col-md-10 col-md-offset-1">
          <label>
            <input type="checkbox" required> I accept the terms of service and privacy policy
          </label>
        </div>
        <button type="submit" class="btn btn-register col-md-8 col-md-offset-2 col-xs-10 col-xs-offset-1">Register Now</button>
      </form>
    </div>
  </div>
  
<!-- Google Code for Sign up form viewed Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 851470613;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "yJNICI_0t3IQldKBlgM";
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/851470613/?label=yJNICI_0t3IQldKBlgM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>

@endsection
