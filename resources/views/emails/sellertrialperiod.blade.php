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
      <p> Hi {{ $fname }},
      </p>
      @if ($message_body == "Your Free Trial Ends 3 days ago!")
        <div class="panel-heading">{{ $message_body }}</div>
        <div class="panel-body">
        	<p style="margin:1em 0;padding:0;color:#575757;font-family:Helvetica;font-size:15px;line-height:125%;text-align:left">
				  Your free trial has expired 3 days ago and your account will be deleted by the end of the day. To keep your account, please log in and enter a valid credit card number in the Billing section.</p>
			<ol style="margin:1em 0px 1em 30px;padding:0px;color:#575757;font-family:Helvetica;font-size:15px;line-height:125%;text-align:left">
				<li>Login to Trendle.io.</li>
				<li>Visit the "Billing" section under the Settings menu.</li>
				<li>Add your billing information and update the payment method.</li>
			</ol>
			<center><a style="border-radius:3px;font-size:15px;text-decoration:none;color:white;padding:12px 5px 12px 5px;border:1px #1871B5 solid;width:200px;max-width:200px;font-family:helvetica;margin:6px auto;display:block;background-color:#1878B5;text-align:center" href="http://trendle.io/billing" target="_blank" ><strong>Update Your Billing Info</strong></a></center>
			<p><strong>Have questions?</strong> We're available to chat! Contact our <a href="mailto:{{ env('CONTACT_EMAIL1') }}" target="_blank">support team</a>, and we\'ll help you customize Trendle.io to grow your business.</p>
			<br>
			<br>
			<p style="margin:0em 0;padding-bottom:5px;color:#575757;font-family:Helvetica;font-size:20px;line-height:125%;text-align:left">Trendle.io Team</p>
			<a style="text-decoration:none" href="http://www.trendle.io" target="_blank" >www.trendle.io</a>
        </div>
      @else
      	<div class="panel-heading">{{ $message_body }}</div>
        <div class="panel-body">
        	<p style="margin:1em 0;padding:0;color:#575757;font-family:Helvetica;font-size:15px;line-height:125%;text-align:left">
				  Avoid interruption in your service by adding your billing details.</p>
			<ol style="margin:1em 0px 1em 30px;padding:0px;color:#575757;font-family:Helvetica;font-size:15px;line-height:125%;text-align:left">
				<li>Login to Trendle.io.</li>
				<li>Visit the "Billing" section under the Settings menu.</li>
				<li>Add your billing information and update the payment method.</li>
			</ol>
			<center><a style="border-radius:3px;font-size:15px;text-decoration:none;color:white;padding:12px 5px 12px 5px;border:1px #1871B5 solid;width:200px;max-width:200px;font-family:helvetica;margin:6px auto;display:block;background-color:#1878B5;text-align:center" href="http://trendle.io/billing" target="_blank" ><strong>Update Your Billing Info</strong></a></center>
			<p><strong>Have questions?</strong> We're available to chat! Contact our <a href="mailto:{{ env('CONTACT_EMAIL1') }}" target="_blank">support team</a>, and we'll help you customize Trendle.io to grow your business.</p>
			<br>
			<br>
			<p style="margin:0em 0;padding-bottom:5px;color:#575757;font-family:Helvetica;font-size:20px;line-height:125%;text-align:left">Trendle.io Team</p>
			<a style="text-decoration:none" href="http://www.trendle.io" target="_blank" >www.trendle.io</a>
        </div>
      @endif
      </div>
    </div>
  </body>
</html>
