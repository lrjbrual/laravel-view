{{ app()->setLocale(session()->get('lang', 'en')) }}
@extends('layouts.master')
@section('title', '| Register')

@section('content')
  <div class="containter">
    <div class="col-md-4 col-md-offset-4 col-sm-8 col-offet-sm-2 col-xs-8 col-xs-offset-2 register-box">
      <h4 class="text-center free-trial-text">14days Free Trial!</h4>
      <p class="text-center">Get Started in a few second</p>
      <form>
        <div class="form-group col-md-10 col-md-offset-1">
          <input type="text" class="form-control" id="exampleInputName2" placeholder="First Name">
        </div>
        <div class="form-group col-md-10 col-md-offset-1">
          <input type="text" class="form-control" id="exampleInputName2" placeholder="Last Name">
        </div>
        <div class="form-group col-md-10 col-md-offset-1">
          <input type="text" class="form-control" id="exampleInputName2" placeholder="Your Company Name">
        </div>
        <div class="form-group col-md-10 col-md-offset-1">
          <select class="form-control">
            <option>Philippines</option>
            <option>United Kingdom</option>
            <option>Singapore</option>
          </select>
        </div>
        <div class="form-group col-md-10 col-md-offset-1">
          <input type="email" class="form-control col-md-6" id="exampleInputEmail1" placeholder="your@email.com">
        </div>
        <div class="form-group col-md-10 col-md-offset-1">
          <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
        </div>
        <div class="form-group col-md-10 col-md-offset-1">
          <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Confirm Password">
        </div>
        <div class="checkbox col-md-10 col-md-offset-1">
          <label>
            <input type="checkbox"> I accept the terms of service and privacy policy
          </label>
        </div>
        <button type="submit" class="btn btn-register col-md-8 col-md-offset-2 col-xs-10 col-xs-offset-1">Register Now</button>
      </form>
    </div>
  </div>
@endsection
