@if(!$has_subscription)
  @include('partials.subscription._error_modal')
@endif
<div class="subscriptionTour">
<div class="row">
  <div class="col-md-12 m-t-20" style="margin-bottom: 50px;">
    <h3 class="col-md-6 subscription-title">Manage your subscriptions</h3>
    <div class="col-md-6 text-right">
        <div class="col-md-12">
          @if ($voucher == false)
            <input type="text" id="coupon" value="" placeholder="Promo Code">
            <button id="verifycoupon" class="btn btn-orange btn-primary" type="button">Verify Code</button>
            <h4>{{ $conversion }}</h4>
          @else
            <h4>Promo currently active : {{ $voucher }}</h4>
            @if ($pc->discount_type == 'percent')
              <h4>Discount: {{ $pc->discount_value }} %</h4>
            @else
              <h4>Discount Value: {{ $promocode_amount }} {{ $currency_symbol}}</h4>
            @endif
            
            @if ($pc->voucher_type == 'date')
              <h4>Days Left: {{ $days_left }}</h4>
            @endif
            <h4>{{ $conversion }}</h4>
          @endif
        </div>
    </div>
</div>
</div>

<div class="col-md-12">
  @if (Session::has('success'))
    <div class="alert alert-success m-t-5 button-rectangle"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>{{ Session::get('success') }}</div>
  @endif
</div>

<div class="row">
  <div class="col-lg-12">

    <!-- BASE SUBS -->
    <div class="col-lg-4 m-b-15">

    <div class="card subcCard">
      <div class="card-header bg-white">{{trans('home.price_p5')}}</div>
      <div class="card-block">
            
          @if ($bs == '')
            <div class="">
              <h5 class="">Click the button below to manage your <strong>Base Subscription</strong></h5>
              <button class="btn btn-raised btn-md btn-success adv_cust_mod_btn m-t-10" data-toggle="modal" data-target="#large">Select Subscription</button>        
            </div>
            <div class="modal fade" id="large" tabindex="-1" role="dialog" aria-labelledby="modalLabelLarge"
                         aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="modalLabelLarge">{{trans('home.price_p5')}}</h4>
                        </div>
                        <div class="modal-body">
                          @include('partials.subscription._base_plan')
                        </div>
                    </div>
                </div>
            </div>
          @else

            <div class="">
              @if ($is_trial == 1)
                <h5 class="">You are in free trial period. Your pending subscription, <strong>{{ $bs }}</strong> subscription, will be activated after the free trial period ends.</h5>
              @else
                <h5 class="">You are currently subscribed to <strong>{{ $bs }}</strong> subscription.</h5>
              @endif
              <button class="btn btn-raised btn-success adv_cust_mod_btn m-t-10" data-toggle="modal" data-target="#large">Change Base Subscription</button>        
            </div>
            <div class="modal fade" id="large" tabindex="-1" role="dialog" aria-labelledby="modalLabelLarge"
                         aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="modalLabelLarge">{{trans('home.price_p5')}}</h4>
                        </div>
                        <div class="modal-body">
                          @include('partials.subscription._base_plan')
                        </div>
                    </div>
                </div>
            </div>
          @endif  

      </div>
      
    </div>

    </div>
    <!-- END BASE SUBS -->

    <!-- FBA REFUNDS -->
    <div class="col-lg-4 m-b-15">
      
      <div class="card subcCard">
          <div class="card-header bg-white">{{trans('home.fbarefunds2')}}</div>
          <div class="card-block">
                  <h5 class="">Click the button below to manage your <strong>FBA Refunds Subscription</strong></h5>
                  <button class="btn btn-raised btn-success adv_cust_mod_btn m-t-10" data-toggle="modal" data-target="#fbamodal">Manage</button>        
                  <div class="modal fade" id="fbamodal" tabindex="-1" role="dialog" aria-labelledby="modalLabelLarge"
                               aria-hidden="true">
                      <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                              <div class="modal-header">
                                  <h4 class="modal-title" id="modalLabelLarge">{{trans('home.fbarefunds2')}}</h4>
                              </div>
                              <div class="modal-body">
                                
                                <div class="row">
                                  <div class="col-md-12">
                                    <div class="fbaSubsContainer m-t-15">
                                      @include('partials.subscription._fba_table')
                                    </div>
                                  </div>
                                </div>

                                <div class="m-t-15">
                                  <p>FBA Refunds is an optional feature that is billed on top of your monthly base subscription. For more information please view this video or visit our <a style="color:#FF8000" target="_blank" href="">Help pages</a>.</p>
                      
                                </div>
                                <br>
                                <br>
                                To activate or de-activate this optional feature, please toggle this button:
                                <input type="checkbox" id="subscription-refunds-switch" class="js-switch sm_toggle_checked" {{ $active_checker }}/>
                                <span class="radio_switchery_padding" id="subscription-refunds-switch-msg"></span>
                                @if ($active_checker == 'checked')
                                  <input type="submit" id="refunds-setup" value="Update Filing Preference" class="btn btn-warning m-l-10" data-toggle="modal" data-target="#refunds-setup-modal">
                                @endif

                              </div>
                          </div>
                      </div>
                  </div>
          </div>
      </div>

    </div>
    <!-- END FBA REFUNDS -->

    <!-- EMAIL SUBS -->
    <div class="col-lg-4 m-b-15">
      
      <div class="card subcCard">
          <div class="card-header bg-white">{{trans('home.price_p6')}}</div>
          <div class="card-block">
                    @if(Auth::user()->seller->is_trial == 1)
                      <div class="m-b-15">
                          <div class="alert alert-warning alert-dismissable trialWarning">
                              <button type="button" class="close" data-dismiss="alert"
                                      aria-hidden="true">×
                              </button>

                              Your free trial ends on {{ $user_trial_end }}. All emails sent until then are free. Any email packs you buy now will not be used until {{ $user_trial_end_nextday }}
                          </div>
                      </div>
                    @endif
                    <span>Current Email Balance: </span><span style="color:#00ADBC"> {{ $load }}</span>
                    <h5 class="m-t-10">Click the button below to manage your <strong>Automatic Email Campaign</strong></h5>
                    <button class="btn btn-raised btn-success adv_cust_mod_btn m-t-10" data-toggle="modal" data-target="#emailModal">Buy more emails</button>        
                  <div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="modalLabelLarge"
                               aria-hidden="true">
                      <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                              <div class="modal-header">
                                  <h4 class="modal-title" id="modalLabelLarge">{{trans('home.price_p6')}}</h4>
                              </div>
                              <div class="modal-body">
                                <!-- <div class="row m-b-15">
                                  <div class="col-md-2 col-sm-3 col-xs-3 pull-right">
                                    <select id="boostbaseSubscriptionSelect" class="form-control" data-width="fit">
                                      <option value="usd">USD</option>
                                      <option value="gbp">GBP</option>
                                      <option value="eur">EUR</option>
                                    </select>
                                  </div>
                                </div> -->
                               <div class="row">
                               <div class="col-md-12 subs-currency">Preferred Currency:
                                  @if($currency != "")
                                    <span class=" subs-message">{{ strtoupper($currency) }}</span>
                                  @else
                                    <span class=" subs-message">USD (default)</span>
                                  @endif
                                </div>
                                <div class="col-md-12 subs-currency">Current Email Balance:
                                  <span class=" subs-message">{{ $load }}</span>
                                </div>
                                </div>

                                <p class="text-orange text-center m-t-10">{{ trans('home.price_p2') }}</p>

                                <div class="m-t-15">
                                  @include('partials.subscription._plan')
                                </div>

                                <h6 class="text-center">
                                  <i>All our prices are exclusive of VAT</i>
                                </h6>

                                <div class="row">
                                  <span id="currency" class="dontdisplay"></span><span id="total" class="dontdisplay"></span>
                                  <span id="sizeEmail" data-plan-size="" class="dontdisplay"></span>
                                  @if($currency != "")
                                    <span id="preferredCurrencyEmail" data-preferred-currency="{{$currency}}" class="dontdisplay"></span>
                                  @else
                                    <span id="preferredCurrencyEmail" data-preferred-currency="usd" class="dontdisplay"></span>
                                  @endif
                                  <div class="col-md-12 text-right">
                                    <span class="package-text-total">Total Amount</span>
                                    <span class="package-container-total">
                                        <span id="total-sum-currency"></span><span id="total-sum-amount"></span>
                                    </span>
                                    <!-- &nbsp;&nbsp;<span class="package-text-total">+ VAT</span> -->
                                    <!-- <span class="package-container-total">
                                        <span id="vat-currency"></span><span id="vat-amount">0</span>
                                    </span> -->
                                    <br>
                                    <button id="purchase" class="btn btn-primary m-t-5" type="button" name="details">Buy Email Pack</button>
                                  </div>

                                  <div class="col-md-12 dontdisplay">
                                    <span id="coupon-id" data-coupon-id=""></span>
                                    <span id="coupon-discount"></span>
                                    <span id="coupon-duration"></span>
                                    @if ($voucher == false)
                                      <span id="promoType" data-promo-type=""></span>
                                      <span id="promovalue" data-promo-value=""></span>
                                    @else
                                      <span id="promoType" data-promo-type="{{$pc->discount_type}}"></span>
                                      <span id="promovalue" data-promo-value="{{$pc->discount_value}}"></span>
                                    @endif                                    
                                  </div>

                              </div>
                          </div>
                      </div>
                  </div>
          </div>
      </div>

    </div>
    <!-- END BASE SUBS -->

  </div>
</div>


<div class="col-md-12 m-t-25">
  
  <!-- <span id="show-hide-paypal-button">
  or

  {{ Form::open(array('url' => 'paypal/checkout', 'class' => 'btn-paypal')) }}
  {{ Form::hidden('paypal_currency') }}
  {{ Form::hidden('paypal_amount') }}
  {{ Form::hidden('paypal_product') }}
  {{ Form::hidden('plans') }}
  <script src="https://www.paypalobjects.com/api/button.js?"
       data-merchant="braintree"
       data-id="paypal-button"
       data-button="checkout"
       data-color="gold"
       data-size="medium"
       data-shape="pill"
       data-button_type="submit"
       data-button_disabled="false"
   ></script>
  {{ Form::close() }}
  </span> -->
  <!-- FBA REFUND -->

<!-- END -->
<!-- EMAIL SUBSCRIPTION -->
 
  </div>
<!-- END -->
</div>
</div>
<!-- FBA MODAL -->
<div class="modal fade" id="refunds-setup-modal" tabindex="-1" role="dialog" aria-labelledby="modalLabelSmall"
                     aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalLabelSmall">Update Filing Preference</h4>
            </div>

            <div class="modal-body">
              {{ Form::open(array('method' => 'POST', 'url' => url('refund/updateFbaMode'))) }}
                <p>To set-up FBA Refunds feature, please choose whether you would like to file claims with Amazon Seller Support yourself ("DIY" - Do It Yourself), or whether you would like our trained team to take care of this for you ("Managed Service").
                </p>
                <p>If you wish to file yourself (DIY), you will be billed $30 per month. If you wish for Trendle Analytics to file claims on your behalf (Managed Service), you will be billed 10% of the total amount claimed back per month. Think of it as a 'no win, no fee' principle. When you choose "Managed Service" option, our trained staff will begin to file cases on your behalf. Make sure to follow the instructions on the FBA Refunds page to enable our team to do this.
                </p>
                <p>For more details please visit our <a style="color:#FF8000" target="_blank" href="http://help.trendle.io/">Help pages</a> or <a style="color:#FF8000" target="_blank" href="">watch this video</a></p>
                <hr>
                <h5>Please choose your preferred option:</h5>
                <label class="custom-control custom-radio">
                    <input type="radio" name="fba_mode" class="custom-control-input radioDiy" value="MANAGE" {{ $manage_checker }}>
                    <span class="custom-control-indicator custom_checkbox_success"></span>
                    <span class="custom-control-description text-success">Managed Service: Let Trendle Analytics file cases on my behalf (Recommended)</span>
                </label>
                <br>
                <label class="custom-control custom-radio">
                    <input type="radio" name="fba_mode" class="custom-control-input radioDiy" value="DIY" {{ $diy_checker }}>
                    <span class="custom-control-indicator custom_checkbox_warning"></span>
                    <span class="custom-control-description text-warning">I want to file cases myself</span>
                </label>
            </div>          
            <div class="modal-footer">
                {{ Form::submit('Confirm', array('class' => 'btn btn-secondary')) }}
                <button class="btn  btn-secondary" data-dismiss="modal">Cancel</button>
              {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="refunds-modal-activate" tabindex="-1" role="dialog" aria-labelledby="modalLabelLarge" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title" id="modalLabelLarge">Activate FBA Refunds</h4>
            </div>
            <div class="modal-body">
                <p>To set-up FBA Refunds feature, please choose whether you would like to file claims with Amazon Seller Support yourself ("DIY" - Do It Yourself), or whether you would like our trained team to take care of this for you ("Managed Service").
                </p>
                {{-- <p>For more information, please visit our help section: <a style="color:#FF8000" target="_blank" href="http://help.trendle.io/">help.trendle.io</a></p> --}}
                <p>If you wish to file yourself (DIY), you will be billed $30 per month. If you wish for Trendle Analytics to file claims on your behalf (Managed Service), you will be billed 10% of the total amount claimed back per month. Think of it as a 'no win, no fee' principle. When you choose "Managed Service" option, our trained staff will begin to file cases on your behalf. Make sure to follow the instructions on the FBA Refunds page to enable our team to do this.
                </p>
                <p>For more details please visit our <a style="color:#FF8000" target="_blank" href="http://help.trendle.io/">Help pages</a> or <a style="color:#FF8000" target="_blank" href="">watch this video</a></p>
                <hr>
                {{ Form::open(array('method' => 'POST', 'url' => url('refund/activate'))) }}
                <h5>Please choose your preferred option:</h5>
                <label class="custom-control custom-radio">
                    <input type="radio" name="fba_mode" class="custom-control-input" value="MANAGE" required="" {{ $manage_checker }}>
                    <span class="custom-control-indicator custom_checkbox_success"></span>
                    <span class="custom-control-description text-success">Managed Service: Let Trendle Analytics file cases on my behalf (Recommended)</span>
                </label>
                <br>
                <label class="custom-control custom-radio">
                    <input type="radio" name="fba_mode" class="custom-control-input" value="DIY" required="" {{ $diy_checker }}>
                    <span class="custom-control-indicator custom_checkbox_warning"></span>
                    <span class="custom-control-description text-warning">I want to file cases myself</span>
                </label>
                <br>
                By clicking 'confirm' you agree to the above terms.
            </div>
            <div class="modal-footer">
                <div id="modal_footer_inside_form">
                  {{ Form::hidden('currency', $currency) }}
                  {{ Form::hidden('with', $with_records) }}
                  @if ($payment_method == 'card')
                   {{ Form::submit('Confirm', array('id' => 'confirmed-activate', 'class' => 'btn btn-secondary')) }}
                  @elseif ($payment_method == 'paypal')
                    {{ Form::submit('Confirm', array('id' => 'confirmed-activate', 'class' => 'btn btn-secondary')) }}
                  @else
                    <a href="{{ url('billing') }}?from=refund" id="cancel-activate" class="btn btn-secondary">Confirm</a>
                  @endif
                  <button id="cancel-activate" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
              {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="refunds-modal-deactivate" tabindex="-1" role="dialog" aria-labelledby="modalLabelLarge" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title text-white" id="modalLabelLarge">Deactivate FBA Refunds</h4>
            </div>
            <div class="modal-body">
              Please confirm that you want to de-active FBA Refunds.<br>
              Once confirmed, our team will complete the current open cases but will not raise any new cases.<br>
              You can re-activate FBA Refunds at any point in the future.<br>
              <br>
              By clicking 'Confirm' you agree to the above terms.
            </div>
            <div class="modal-footer">
              {{ Form::open(array('method' => 'POST', 'url' => url('refund/deactivate'))) }}
                {{ Form::submit('Confirm', array('id' => 'confirmed-activate', 'class' => 'btn btn-secondary')) }}
                <button id="cancel-deactivate" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
<!-- END -->

<div class="modal fade" id="modal-subscription-alert" role="dialog" aria-labelledby="modalLabelwarn">
  <div class="modal-dialog" role="document">
      <div class="modal-content">
          <div class="modal-header bg-warning">
              <h4 class="modal-title text-white" id="modalLabelwarn">Message</h4>
          </div>
          <div class="modal-body" id="modal-subscription-alert-body">

          </div>
          <div class="modal-footer">
              <button class="btn btn-warning" data-dismiss="modal">Close</button>
          </div>
      </div>
  </div>
</div>

<div class="modal fade" id="modal-subscription-alert-info" role="dialog" aria-labelledby="modalLabelinfo">
  <div class="modal-dialog" role="document">
      <div class="modal-content">
          <div class="modal-header bg-info">
              <h4 class="modal-title text-white" id="modalLabelinfo">Coverage</h4>
          </div>
          <div class="modal-body" id="modal-subscription-alert-info-body">

          </div>
          <div class="modal-footer">
              <button class="btn btn-info" data-dismiss="modal">Close</button>
          </div>
      </div>
  </div>

@if($current_plans)
  <input type="hidden" id="current_plans" value="{{ count($current_plans) > 1 ? implode(',', $current_plans) : $current_plans[0] }}">
  @else
  <input type="hidden" id="current_plans">
@endif



<script src="{{ url('js/packages.js') }}"></script>
<script src="{{ url('js/coupon.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $('#subscriptionErrorModal').modal('show');
  })
</script>