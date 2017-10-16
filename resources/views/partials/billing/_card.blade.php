<div class="form-group col-md-5 col-sm-12">
  <h3 class="header-text-payment color-orange m-t-25">Preferred Currency</h3>
  {{ Form::open(array('method' => 'POST',
        'url' => url('billing/storePreferredPayment'))) }}
  <div class="row">
    <!-- issue 121 | Jun Rhy | Remove payment method  -->
    <!-- <label class="col-md-5"><strong>Payment Method</strong></label> -->
    <div class="col-lg-7 col-md-7 col-xl-7">
        @if($billing)
        <!-- <div class="col-md-5">{{ Form::radio('payment_method', 'card', ($billing->payment_method == 'card') ? true : null) }} Card</div> -->
        <!-- <div class="col-md-5">{{ Form::radio('payment_method', 'paypal', ($billing->payment_method == 'paypal') ? true : null) }} Paypal</div> -->
        @else
        <!-- <div class="col-md-5">{{ Form::radio('payment_method', 'card', null) }} Card</div> -->
        <!-- <div class="col-md-5">{{ Form::radio('payment_method', 'paypal', null) }} Paypal</div> -->
        @endif
    </div>
  </div>
  <div class="row">
    <div class="row col-lg-8 col-md-8 col-xl-8">
        @if($billing)
        <div class="col-md-5">{{ Form::select('preferred_currency', ['usd' => 'USD','gbp' => 'GBP', 'eur' => 'EUR'], ($billing->preferred_currency) ? $billing->preferred_currency : null, ['class' => 'form-control']) }}</div>
        @else
        <div class="col-md-5">{{ Form::select('preferred_currency', ['usd' => 'USD','gbp' => 'GBP', 'eur' => 'EUR'], null, ['class' => 'form-control']) }}</div>
        @endif
        <input type="submit" class="btn btn-primary btn-submit-card col-md-5 col-sm-5 col-xs-5" value="Save">
    </div>
  </div>
    <input type="hidden" name="from" value="{{ (isset($from)) ? $from : '' }}">
    {{ Form::close() }}

  <h3 class="header-text-payment color-orange m-t-25">Current Plan</h3>
  Email Balance: {{ $load }}

  <h3 class="header-text-payment color-orange m-t-25">Payment Method</h3>

  @if($billing && $billing->hasStripeId())
      {{ $billing->card_brand }}<br>
      Number ending in {{ $billing->card_last_four }}<br>
      {{ sprintf('%02d', $billing->card_expiry_month) }} / {{ $billing->card_expiry_year }}<br>
      {{ $billing->card_holder_name }}
      <br><br>
      <button class="btn btn-primary popup-change-card col-md-12 col-sm-12 col-xs-12">Change Card Details</button>
  @else
      No payment method
      <br><br>
      <form action="billing/registerCard" method="POST" id="payment-form">
        <p class="header-text-plan color-blue">By clicking Save, you accept the Trendle.io agreement.</p>
          {!! Form::text('card_number', null,  ['data-stripe' => 'number', 'class' => 'form-control margin-form-control', 'placeholder' => 'Card Number']) !!}
          {!! Form::text('card_holder_name', null,  ['data-stripe' => 'holder', 'class' => 'form-control margin-form-control', 'placeholder' => 'Card holder name']) !!}
          <div class="row">
            <div class="col-md-4 col-sm-12 col-xs-12">
              {!! Form::selectRange('expiry_month', 1, 12, null, ['data-stripe' => 'exp_month', 'class' => 'form-control margin-form-control select-date', 'placeholder' => 'Expiry Month']) !!}
            </div>
            <div class="col-md-4 col-sm-12 col-xs-12">
              {!! Form::selectYear('expiry_year', $current_year, $current_year + 10, null, ['data-stripe' => 'exp_year', 'class' => 'form-control margin-form-control select-date', 'placeholder' => 'Expiry Year']) !!}
            </div>
            <div class="col-md-4 col-sm-12 col-xs-12">
              {!! Form::text('cvv', null,  ['data-stripe' => 'cvc', 'class' => 'form-control margin-form-control','placeholder' => 'CVV']) !!}
            </div>
          </div>
          {{ csrf_field() }}
          <input type="submit" id="btn-submit-card" class="btn btn-primary col-md-12 col-sm-12 col-xs-12" value="Save">
          <div class="text-center">
            <i class="fa fa-cc-amex color-blue fa-3x" aria-hidden="true"></i>
            <i class="fa fa-cc-mastercard color-blue fa-3x" aria-hidden="true"></i>
            <i class="fa fa-cc-discover color-blue fa-3x" aria-hidden="true"></i>
            <i class="fa fa-cc-stripe color-blue fa-3x" aria-hidden="true"></i>
          </div>
          <input type="hidden" name="from" value="{{ (isset($from)) ? $from : '' }}">
        </form>
    @endif

    <div class="modal fade" id="modal-change-card" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title" id="modalLabel">Change Card Details</h4>
                </div>
                <div class="modal-body">
                  <form action="billing/registerCard" method="POST" id="payment-form">
                      <p class="header-text-plan color-blue">By clicking Save, you accept the Trendle.io agreement.</p>
                      <input type="hidden" id="seller_id" name="seller_id" value="{{ Auth::user()->seller_id}}">
                      {!! Form::text('card_number', null,  ['data-stripe' => 'number', 'class' => 'form-control margin-form-control', 'placeholder' => 'Card Number']) !!}
                      {!! Form::text('card_holder_name', null,  ['data-stripe' => 'holder', 'class' => 'form-control margin-form-control', 'placeholder' => 'Card holder name']) !!}
                      <div class="row">
                        <div class="col-md-4 col-sm-12 col-xs-12">
                          {!! Form::selectRange('expiry_month', 1, 12, null, ['data-stripe' => 'exp_month', 'class' => 'form-control margin-form-control select-date', 'placeholder' => 'Expiry Month']) !!}
                        </div>
                        <div class="col-md-4 col-sm-12 col-xs-12">
                          {!! Form::selectYear('expiry_year', $current_year, $current_year + 10, null, ['data-stripe' => 'exp_year', 'class' => 'form-control margin-form-control select-date', 'placeholder' => 'Expiry Year']) !!}
                        </div>
                        <div class="col-md-4 col-sm-12 col-xs-12">
                          {!! Form::text('cvv', null,  ['data-stripe' => 'cvc', 'class' => 'form-control margin-form-control','placeholder' => 'CVV']) !!}
                        </div>
                      </div>
                      {{ csrf_field() }}
                      <p class="header-text-plan color-blue">These new card details will delete and replace any existing card details.</p>
                      <div class="row">
                        <div class="col-md-6">
                          <input type="submit" id="btn-submit-card" class="btn btn-primary btn-block" value="Save">
                        </div>
                        <div class="col-md-6">
                           <button id="btn-cancel-card" class="btn btn-orange btn-block" data-dismiss="modal">Cancel</button>
                        </div>
                      </div>
                      <div class="text-center">
                        <i class="fa fa-cc-amex color-blue fa-3x" aria-hidden="true"></i>
                        <i class="fa fa-cc-mastercard color-blue fa-3x" aria-hidden="true"></i>
                        <i class="fa fa-cc-discover color-blue fa-3x" aria-hidden="true"></i>
                        <i class="fa fa-cc-stripe color-blue fa-3x" aria-hidden="true"></i>
                      </div>
                      <input type="hidden" name="from" value="{{ (isset($from)) ? $from : '' }}">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ url('js/billing.js') }}"></script>
