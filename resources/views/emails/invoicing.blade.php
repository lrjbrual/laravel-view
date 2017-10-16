<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials._pdfinclude')
    <style>
      @import url(http://fonts.googleapis.com/css?family=Roboto:100,300,400,900,700,500,300,100);
      *{
        margin: 0;
        box-sizing: border-box;

      }
      body{
        background: white;
        font-family: 'Roboto', sans-serif;
        background-image: url('');
        background-repeat: repeat-y;
        background-size: 100%;
      }
      ::selection {background: #f31544; color: #FFF;}
      ::moz-selection {background: #f31544; color: #FFF;}
      h1{
        font-size: 1.5em;
        color: #222;
      }
      h2{font-size: .9em;}
      h3{
        font-size: 1.2em;
        font-weight: 300;
        line-height: 2em;
      }
      h4{
        font-size: .9em;
      }
      p{
        font-size: .7em;
        color: #666;
        line-height: 1.2em;
      }

      #invoiceholder{
        width:100%;
        hieght: 100%;
        padding-top: 50px;
      }

      #invoice{
        position: relative;
      /*   top: -290px; */
        margin: 0 auto;
        width: 800px;
        background: #FFF;
      }

      [id*='invoice-']{ /* Targets all id with 'col-' */
        border-bottom: 1px solid #EEE;
        padding: 30px;
      }

      #invoice-top{min-height: 120px;}
      #invoice-mid{min-height: 120px;}
      #invoice-bot{ min-height: 400px;}

      .title{
        float: right;
      }
      .title p{text-align: left;}
      #project{margin-left: 52%;}
      table{
        width: 100%;
        border-collapse: collapse;
      }
      td{
        padding: 5px 0 5px 15px;
      /*   border: 1px solid #EEE */
      }
      .tabletitle{
        padding: 5px;
        background: #EEE;
      }
      /* .service{border: 1px solid #EEE;} */
      .item{width: 60%;}
      .itemtext{font-size: .9em;}
    </style>
  </head>
  <body>
  {{-- <div class="container">
    <div class="row">
      <div class="col-left">
        <p>
          Lock Softwares Ltd<br/>
          Suite 010, Hurlingham Studios, Ranelagh Gardens<br/>
          London, SW63PA<br/>
          United Kingdom<br/>
          VAT No. GB265794065<br/>
        </p>
        <div class="hr"></div>
      </div>

      <div class="col-right" style="float:right;">
        <h1 style="margin-top:16px;">
          INVOICE
        </h1>
        <p class="p1">
            Invoice # &nbsp; <b>{{ $invoice }}</b> <br/>
            Invoice Date &nbsp; <b>{{ $date }}</b> <br/>
            Invoice Amount &nbsp; <b>{{ $total_amount }}({{ $currency }})</b> <br/>
            Customer ID &nbsp; <b>{{ $seller_id }}</b> <br/>
            <span class="paid">PAID</span>
         </p>
         <div class="hr"></div>
      </div>
    </div>

    <div class="row">
      <div class="col-left">
      <p>
        <b>BILLED TO</b><br/>
        {{ $name }}<br/>
        {{ $company }}<br/>
        {{ $address }}<br/>
        {{ $city }}, {{ $state }} {{ $postal_code }}<br/>
        {{ $country }}<br/>
        {{ $vat_num }}<br/>
      </p>
      </div>
    </div>


    <div class="row">
      <table class="invoice-tbl">
      <thead>
        <tr>
          <th>DESCRIPTION</th>
          <th>AMOUNT({{ $currency }})</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>{{ $description }}</td>
          <td>{{ $amount }}</td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td><b>Sub-total</b></td>
          <td><b>{{ $amount }}</b></td>
        </tr>
        <tr>
          <td>Promo</td>
          <td>{{ $promo }}</td>
        </tr>
        <tr>
          <td>VAT</td>
          <td>{{ $vat }}</td>
        </tr>
      </tfoot>
      <tfoot>
        <tr>
          <td>Total Amount({{ $currency }})</td>
          <td><b>{{ $total_amount }}</b></td>
        </tr>

      </tfoot>
      </table>
      <div class="row">
          <br/>
          <a target="_blank" href="{{ $baseurl . '/billinginvoice/' . $token }}">Click here</a>
          to download the pdf version of your invoice.<br/><br/>
          If you\'re having trouble clicking the hyperlink above, please copy and paste the following link into your browser.<br/>
          {{ $baseurl . '/billinginvoice/' . $token }}
          <br/><br/>
          The Trendle.io Team<br/>
          www.trendle.io<br/><br/>
          <h5>Note: If you need help, you can get a hold of us at {{ env('CONTACT_EMAIL1') }}<br/>
          Replies to this email will not reach us.</h5><br/>
      </div>
    </div>
  </div> --}}
  <div id="invoiceholder">
  <div id="invoice" class="effect2">
      <div id="invoice-bot">
        <h4>Dear {{ $name }},</h4>
        <br />
        <h2>Thank you for using Trendle Analytics. Please find below a summary of your invoice. Your chosen payment method has already be used to pay for this. No additional payments are required.</p></h2>
      <br /><br />
      <div id="invoice-top">
        <div class="title">
          <h1>Invoice #{{ $invoice }}</h1>
          <p>Issued:{{ $date }}
          </p>
        </div><!--End Title-->
      </div><!--End InvoiceTop-->

        <div id="table">
          <table>
            <tr class="tabletitle">
              <td class="item"><h2>Description</h2></td>
              <td></td>
              <td></td>
              <td class="subtotal"><h2>Amount ({{ $currency }})</h2></td>
            </tr>

            @foreach ($items as $item)
              <tr class="service">
                <td class="tableitem"><p class="itemtext">{{ $item['description_item'] }}</p></td>
                <td></td>
                <td></td>
                <td class="tableitem">
                  <p class="itemtext">{{ $symbol.' '.$item['amount_item'] }}</p>
                </td>
              </tr>
            @endforeach

            <tr class="tabletitle">
              <td></td>
              <td></td>
              <td class="Rate"><h2>Sub Total</h2></td>
              <td class="payment"><h2>{{ $symbol.' '.$amount }}</h2></td>
            </tr>
            @if ($promo == 1)
              <tr>
                <td></td>
                <td></td>
                <td class="Rate"><h2>Promo Discount</h2></td>
                <td class="payment"><h2>{{ $symbol.' '.$promo_discount }}</h2></td>
              </tr>
            @endif
            <tr>
              <td></td>
              <td></td>
              <td class="Rate"><h2>VAT ({{ $rate }}%)</h2></td>
              <td class="payment"><h2>{{ $symbol.' '.$vat }}</h2></td>
            </tr>
            <tr>
              <td></td>
              <td></td>
              <td class="Rate"><h3><b>Total</b></h3></td>
              <td class="payment"><h2>{{ $symbol.' '.$total_amount }}</h2></td>
            </tr>

          </table>
        </div><!--End Table-->

        <div class="container">
          <br/>
          For a pdf copy of your invoice, please
          <a target="_blank" href="{{ $baseurl . '/billinginvoice/' . $token }}">Click here</a>
            or copy and paste the following link in your browser: <br/><br/>
            {{ $baseurl . '/billinginvoice/' . $token }}
            <br/>
            <br/>
        </div>
      </div><!--End InvoiceBot-->
    </div><!--End Invoice-->
  </div><!-- End Invoice Holder-->
  </body>
</html>
