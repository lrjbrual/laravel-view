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

    <div class="container">
      <div class="panel panel-primary">
        <div class="panel-body">
          Dear {{ $fname . ' ' . $lname }},
          <br/><br/>
          We are delighted you have decided to try out Trendle.io.
          <a target="_blank" href="{{ $baseurl . '/verify_confirmation/' . $token }}">Click here</a>
          to activate your account and start your free trial. This link is valid for 24 hours.<br/><br/>
          If you're having trouble clicking the hyperlink above, please copy and paste the following link into your browser.<br/>
          {{ $baseurl . '/verify_confirmation/' . $token }}
          <br/><br/>
          The Trendle.io Team<br/>
          www.trendle.io<br/><br/>
          <h5>Note: If you need help, you can get a hold of us at <strong>{{ env('CONTACT_EMAIL1') }}</strong>, or simply reply to this email</h5><br/>
        </div>
      </div>
    </div>
  </body>
</html>
