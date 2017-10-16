@extends('layouts.master')
@section('title', '| Contact')

@section('content')
  <div class="container margin-top-60">
    <br />
    <div class="row col-md-offset-1">
    <div class="col-md-12">
      @include('partials._flashmessage')
    </div>
      <div class="col-md-6 col-md-offset-3 col-sm-12 col-xs-12 background-orange-contact">
        <h4 class="text-center">
          <u>Contact Us</u>
        </h4>
        <p class="text-center">We love to hear from you</p>
        <form action="{{ url('contact') }}" method="POST" class="padding-contact-top">
            {{ csrf_field() }}
            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                <input id="name" name="Full Name" class="form-control" placeholder="Your Full Name">
            </div>
            <div class="form-group col-md-12 col-sm-12 col-xs-12 {{ $errors->has('email') ? ' has-error' : '' }}">
                <input id="email" name="email" class="form-control" placeholder="Your email address *">
                @if ($errors->has('email'))
                    <span class="help-block">
                        <strong class="color-black">{{ $errors->first('email') }}</strong>
                    </span>
                @endif
            </div>
            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                <input id="subject" name="subject" class="form-control" placeholder="Your Subject Here">
            </div>

            <div class="form-group col-md-12 col-sm-12 col-xs-12 ">
                <textarea id="message" name="message" class="form-control" placeholder="Your Message Here"></textarea>
                <input type="submit" value="Send Message" class="btn btn-submit col-md-12 col-sm-12 col-xs-12">
            </div>
        </form>

      </div>
    </div>
    <br />
    <br />
    <br />
    <br />
</div>
@endsection
