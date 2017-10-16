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
          We have tried to bill {{ $currency_symbol }} {{ $fees_paid }}  but your card was declined.
          @if($daysCount != 0)
          Please check if the payment details entered are correct and still valid. Please also check if you have sufficient funds. Alternatively you can change your payment card.
          <br>
          We will try charging your card again tomorrow.
          <br>
          If your card still has insufficient funds in {{ $daysCount}} day/s from now then your account will be suspended.
          @else
          Please check if the payment details entered are correct and still valid. Please also check if you have sufficient funds. Alternatively you can change your payment card.
          @endif
          <br><br>
          The Trendle.io Team<br/>
          www.trendle.io<br/><br/>
          <h5>Note: If you need help, you can get a hold of us at <strong>{{ env('CONTACT_EMAIL1') }}</strong>, or simply reply to this email</h5><br/>
        </div>
      </div>
    </div>
  </body>
</html>
