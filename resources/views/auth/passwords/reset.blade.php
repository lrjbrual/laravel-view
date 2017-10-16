@extends('layouts.master')
@section('title', '| Reset Password')

@section('content')
    <div class="container reset-page">
        <div class="row">
            <div class="col-md-5 col-md-offset-4 col-sm-10 col-sm-offset-1 col-xs-10 col-xs-offset-1 reset-password-box">
                <h4 class="text-center text-orange">Reset Password</h4>
                            @if (session('status'))
                                <div class="alert alert-success">
                                    {{ session('status') }}
                                </div>
                            @endif
                        {!! Form::open(['url' => 'password/reset', 'method' => "POST"]) !!}
                        {{ csrf_field() }}
                        {{ Form::hidden('token', $token) }}

                        {{ Form::email('email', $email, ['class' => 'form-control from-reset-input', 'placeholder' => 'your email here', $errors->has('email') ? ' has-error' : '']) }}
                            @if ($errors->has('email'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        {{ Form::password('password', ['class' => 'form-control from-reset-input', 'placeholder' => 'your password', $errors->has('password') ? ' has-error' : '' ]) }}
                            @if ($errors->has('password'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        {{ Form::password('password_confirmation', ['class' => 'form-control', 'placeholder' => 'Confirm your password', $errors->has('password_confirmation') ? ' has-error' : '']) }}
                            @if ($errors->has('password_confirmation'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('password_confirmation') }}</strong>
                                </span>
                            @endif
                        {{ Form::submit('Reset Password', ['class' => 'btn btn-reset col-md-12 col-sm-12 col-xs-12']) }}
                        
                        {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection
