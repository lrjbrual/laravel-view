@extends('layouts.user')
@section('title', '| Company Profile')

@section('user')
<!-- Content Header (Page header) -->
<header class="head">
    <div class="main-bar row">
        <div class="col-lg-6 col-sm-4">
            <h4 class="nav_top_align">
                <i class="fa fa-building"></i>
                Account Settings
            </h4>
        </div>
        @include('partials._helpicon')
    </div>
</header>

<div class="col-md-12">
  @include('partials._flashmessage')
</div>

<div class="col-md-5">
  <h3 class="m-t-25 col-md-12 color-orange">Contact</h3>

  {{ Form::model($seller, array('route' => array('company.update', $seller->id), 'method' => 'PUT', 'class' => 'form-horizontal')) }}
      <div class="form-group row m-t-35">
        <div class="col-lg-3 col-md-3 col-xl-3 text-lg-right">
            <label for="firstname" class="col-form-label">First Name</label>
        </div>
        <div class="col-lg-8 col-md-8 col-xl-9">
            <div class="input-group">
                <span class="input-group-addon">
                <i class="fa fa-user"></i>
            </span>
            {!! Form::text('firstname', $seller->firstname,  ['class' => 'form-control', 'placeholder' => 'First Name']) !!}
            </div>
        </div>
      </div>

      <div class="form-group row">
        <div class="col-lg-3 col-md-3 col-xl-3 text-lg-right">
            <label for="lastname" class="col-form-label">Last Name</label>
        </div>
        <div class="col-lg-8 col-md-8 col-xl-9">
            <div class="input-group">
                <span class="input-group-addon">
                <i class="fa fa-user"></i>
            </span>
            {!! Form::text('lastname', $seller->lastname,  ['class' => 'form-control margin-form-control', 'placeholder' => 'Lastname']) !!}
            </div>
        </div>
      </div>

      <div class="form-group row">
        <div class="col-lg-3 col-md-3 col-xl-3 text-lg-right">
            <label for="lastname" class="col-form-label">Email</label>
        </div>
        <div class="col-lg-8 col-md-8 col-xl-9">
            <div class="input-group">
                <span class="input-group-addon">
                <i class="fa fa-envelope"></i>
            </span>
            {!! Form::email('email', $seller->email,  ['class' => 'form-control margin-form-control', 'placeholder' => 'your@email.com', 'readonly']) !!}
            </div>
        </div>
      </div>

      <div class="form-group row">
          <div class="col-lg-5 push-xl-3 push-lg-4">
              <input class="btn btn-primary btn-block layout_btn_prevent" type="submit" value="Update">
          </div>
      </div>
  {{ Form::close() }}
</div>

<div class="col-md-5">
  <h3 class="m-t-25 col-md-12 color-orange">Change Password</h3>

  {{ Form::open(array('action' => 'Trendle\CompanyController@changePassword')) }}
    @if (count($errors) > 0)
      <div class="alert alert-danger">
          <ul>
              @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
              @endforeach
          </ul>
      </div>
    @endif

    <div class="form-group row m-t-35">
      <div class="col-lg-3 col-md-3 col-xl-3 text-lg-right">
          <label for="password" class="col-form-label">Password</label>
      </div>
      <div class="col-lg-8 col-md-8 col-xl-9">
          <div class="input-group">
              <span class="input-group-addon">
              <i class="fa fa-lock"></i>
          </span>
          {!! Form::password('password', ['class' => 'form-control margin-form-control', 'placeholder' => 'New Password']) !!}
          </div>
      </div>
    </div>

    <div class="form-group row">
      <div class="col-lg-3 col-md-3 col-xl-3 text-lg-right">
          <label for="password" class="col-form-label">Confirm Password</label>
      </div>
      <div class="col-lg-8 col-md-8 col-xl-9">
          <div class="input-group">
              <span class="input-group-addon">
              <i class="fa fa-lock"></i>
          </span>
          {!! Form::password('password_confirmation', ['class' => 'form-control margin-form-control', 'placeholder' => 'Confirm Password']) !!}
          </div>
      </div>
    </div>

    <div class="form-group row">
        <div class="col-lg-5 push-xl-3 push-lg-4">
            <input class="btn btn-primary btn-block layout_btn_prevent" type="submit" value="Change Password">
        </div>
    </div>
  {{ Form::close() }}

  <h3 class="m-t-25 col-md-12 color-orange">Delete Account</h3>
  <div class="form-group row">
    <div class="col-md-5 push-xl-3 push-lg-4">
      <a href="account/delete-account" class="btn btn-orange btn-delete btn-block">Delete Account</a>
    </div>
  </div>
</div>
@endsection
