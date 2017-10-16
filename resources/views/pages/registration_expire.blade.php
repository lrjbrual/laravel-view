@extends('layouts.master')
@section('title', '| Email Verification')

@section('content')
  <div class="container margin-top-60">
    <div class="col-md-12">
      @include('partials._flashmessage')
    </div>
      <div class="col-md-8 col-md-offset-2 col-sm-12 col-xs-12 verify_container text-center">
          <div class="row">
            <div class="col-md-12">
                <h2 class="">Your registration has expired</h2>
            </div>
          </div>
          <div class="row submit_container">
              <a href="/register"><button class="btn btn-lg btn_verify">Register new account</button></a>
          </div>
      </div>
</div>
@endsection
