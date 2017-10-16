<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials._sassbootstrap')
    <style type="text/css">
      .panel-primary > .panel-heading {
        background-color: #00adb5;
        border-color: #00adb5;
      }
      .panel-primary {
        border-color: #00adb5;
      }
    </style>
  </head>
  <body>

    <div>
      <a class="navbar-brand font-logo"  href="#">
        <span id="nav-color-orange">Trendle</span>
        <span id="nav-color-blue">Analytics</span>
      </a>
    </div>

    <div class="container">
      <div class="panel panel-primary">
        <div class="panel-heading">You have new message from our contact form of trendle.io</div>
        <div class="panel-body">
          <p>Contact Us message.</p>
          <p>Subject: {{ $subject }}</p>
          <br>
          <p>From: {{ $name }} {{ $email }}</p>
          <br>
          <p>Message:</p>
          <p>{{ $bodyMessage}}</p>
        </div>
      </div>
    </div>
  </body>
</html>