
@extends('layouts.master')
@section('title', '| Reset Password')

@section('content')
<div class="container reset-page">
    <div class="row">
        <div class="col-md-5 col-md-offset-4 col-sm-10 col-sm-offset-1 col-xs-10 col-xs-offset-1 reset-box">
        <h4 class="text-center text-orange">Reset Your Password</h4>
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif
            <form class="form-horizontal" role="form" method="POST" action="{{ url('/password/email') }}">
                {{ csrf_field() }}
                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                    <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
                        <input id="email" type="email" class="form-control" name="email" placeholder="your@email.com" value="{{ old('email') }}" required>
                        @if ($errors->has('email'))
                            <span class="help-block">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                        @endif
                        <button type="submit" class="btn btn-reset col-md-12 col-sm-12 col-xs-12">
                            Send Password Reset Link
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection