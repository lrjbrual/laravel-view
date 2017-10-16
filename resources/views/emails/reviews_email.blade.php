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
          Hi {{ $first }},
          <br/><br/>
          You've receive new {{ $type }} reviews. To view these in detail, please log in to trendle.io.<br/><br/>
          Below is a summary of the new reviews you have received:<br/><br/>
          <table style="width:100%">
          <tr>
            <th>Country</th>
            <th>Date of Review</th> 
            <th>SKU/ASIN</th>
            <th>Star Rating</th>
          </tr>
          @foreach ($array as $arr)
          <tr>
            <th>{{ $arr->country }}</th>
            <th>{{ $arr->review_date }}</th> 
            <th>{{ $arr->sku }}</th>
            <th>{{ $arr->star}}</th>
          </tr>
          @endforeach
          </table>
          <br/><br/>
          That's it for today! Happy selling!
          <br>
          The Trendle Analytics Team
        </div>
      </div>
    </div>
  </body>
</html>