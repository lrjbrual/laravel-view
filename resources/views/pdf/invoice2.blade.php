@extends('layouts.pdf')
@section('title', '| Billing')


@section('user')
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

    <div class="col-right">
      <h1 style="margin-top:16px;">
        INVOICE
      </h1>
      <p class="p1">
        Invoice # &nbsp; <b>{{ $data['invoice'] }}</b> <br/>
        Invoice Date &nbsp; <b>{{ $data['date'] }}</b> <br/>
        Invoice Amount &nbsp; <b>{{ $data['total_amount'] }}({{ $data['currency'] }})</b> <br/>
        Customer ID &nbsp; <b>{{ $data['seller_id'] }}</b> <br/>
        <span class="paid">PAID</span>
      </p>
      <div class="hr"></div>
    </div>
  </div>

  <div class="row">
    <div class="col-left">
    <p>
      <b>BILLED TO</b><br/>
      {{ $data['name'] }}<br/>
      {{ $data['company'] }}<br/>
      {{ $data['address'] }}<br/>
      {{ $data['city'] }}, {{ $data['state'] }} {{ $data['postal_code'] }}<br/>
      {{ $data['country'] }}<br/>
      {{ $data['vat_num'] }}<br/>
    </p>
    </div>
  </div>


  <div class="row">
    <table class="invoice-tbl">
    <thead>
      <tr>
        <th>DESCRIPTION</th>
        <th>AMOUNT</th>
      </tr>
    </thead>
    <tbody>
    @foreach ($data['items'] as $item)    
      <tr>
        <td>{{ $item['description_item'] }}</td>
        <td>{{ $data['symbol'].' '.$item['amount_item'] }}</td>
      </tr>
    @endforeach
    </tbody>
    <tfoot>
      <tr>
        <td><b>Sub-total</b></td>
        <td><b>{{ $data['symbol'].' '.$data['amount'] }}</b></td>
      </tr>

      @if ($data['promo'] == 1)
        <tr>
          <td>Promo Discount</td>
          <td>{{ $data['symbol'].' '.$data['promo_discount'] }}</td>
        </tr>
      @endif

      <tr>
        <td>VAT ({{ $data['rate'] }}%)</td>
        <td>{{ $data['symbol'].' '.$data['vat'] }}</td>
      </tr>
    </tfoot>
    <tfoot>
      <tr>
        <td>Total Amount</td>
        <td><b>{{ $data['symbol'].' '.$data['total_amount'] }}</b></td>
      </tr>
    </tfoot>
    </table>
    @if ($data['currency'] != 'usd')
    Conversion Rate: {{ $data['conversion'] }}
    @endif

  </div>
@endsection
