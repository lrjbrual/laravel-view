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
        <div class="panel-heading">Reset Password Request</div>
        <div class="panel-body">
          <p style="margin:1em 0;padding:0;color:#575757;font-family:Helvetica;font-size:15px;line-height:125%;text-align:left">
          You are receiving this email because we received a password reset request for your account.</p>
      <center><a style="border-radius:3px;font-size:15px;text-decoration:none;color:white;padding:12px 5px 12px 5px;border:1px #1871B5 solid;width:200px;max-width:200px;font-family:helvetica;margin:6px auto;display:block;background-color:#1878B5;text-align:center" href="{{ url('/password/reset/'.$reset_token) }}" target="_blank" ><strong>Reset Password</strong></a></center>
      <p>If you did not request a password reset, no further action is required.</p>
      <br>
      <br>
      <p style="margin:0em 0;padding-bottom:5px;color:#575757;font-family:Helvetica;font-size:20px;line-height:125%;text-align:left">Trendle.io Team</p>
      <a style="text-decoration:none" href="http://www.trendle.io" target="_blank" >www.trendle.io</a>
        </div>
      </div>
    </div>
  </body>
</html>