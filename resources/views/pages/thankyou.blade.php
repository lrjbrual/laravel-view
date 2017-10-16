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
        <i class="fa fa-envelope-o email_verication" style="font-size: 72pt;color:#FF5722;"></i>
          <div class="row">
            <div class="col-md-12">
                <h4 class="">Hi {{ ucfirst(trans($fname)) }},</h4>
                <p>Thank you for confirming your email address.</p>
            </div>
          </div>
          <div class="row submit_container">
              <a href="/home"><button class="btn btn-lg btn_verify">Log in</button></a>
          </div>
          <input type="hidden" id="check" name="email"  value="{{ $email }}">
      </div>
</div>

<script src="//static.tapfiliate.com/tapfiliate.js" type="text/javascript" async></script>
    <script type="text/javascript">
      var conversion = $('#check').val();
      window['TapfiliateObject'] = i = 'tap';
      window[i] = window[i] || function () {
          (window[i].q = window[i].q || []).push(arguments);
      };

      tap('create', '4569-a88ee8');
      tap('conversion', conversion);
</script>

@endsection
