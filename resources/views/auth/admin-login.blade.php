@extends('layouts.master')
@section('title', '| Admin Login')

@section('content')
  <div class="containter">
    <div class="col-md-4 col-md-offset-4 col-sm-8 col-offet-sm-2 col-xs-8 col-xs-offset-2 login-box">
      <h4 class="text-center welcome-text">Welcome Back Admin!</h4>
      <form role="form" method="POST" action="{{ route('admin.login.submit') }}">
        {{ csrf_field() }}
         <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }} col-md-10 col-md-offset-1">
            <input type="text" class="form-control col-md-6" id="name" placeholder="Name" name="name" value="{{ old('name') }}">
              @if ($errors->has('name'))
                  <span class="help-block">
                      <strong>{{ $errors->first('name') }}</strong>
                  </span>
              @endif
          </div>
          <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }} col-md-10 col-md-offset-1">
            <input type="email" class="form-control col-md-6" id="exampleInputEmail1" placeholder="Email" name="email" value="{{ old('email') }}">
              @if ($errors->has('email'))
                  <span class="help-block">
                      <strong>{{ $errors->first('email') }}</strong>
                  </span>
              @endif
          </div>
          <div class="form-group {{ $errors->has('password') ? ' has-error' : '' }} col-md-10 col-md-offset-1">
            <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password" name="password"  required>
              @if ($errors->has('password'))
                  <span class="help-block">
                      <strong>{{ $errors->first('password') }}</strong>
                  </span>
              @endif
          </div>
          <div class="form-group">
              <div class="col-md-10 col-md-offset-1">
                  <div class="checkbox">
                      <label>
                          <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Password
                      </label>
                  </div>
              </div>
          </div>
          <button type="submit" class="btn btn-login col-md-8 col-md-offset-2 col-xs-10 col-xs-offset-1">Login</button>
      </form>
        {{-- <a href="{{ url('/password/reset') }}" class="forgot-password-orange">
          <u>Forgot Password?</u>
        </a> --}}
    </div>
  </div>
@endsection
