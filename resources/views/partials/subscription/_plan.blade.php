<div class="row">
      <!-- <div class="col-md-12 col-sm-12 col-xs-12 text-right">
        <select id="boostbaseSubscriptionSelect" class="selectpicker" data-width="fit">
          <option value="usd">USD</option>
          <option value="gbp">GBP</option>
          <option value="eur">EUR</option>
        </select>
      </div> -->
    </div>
    <!-- <h2>{{ trans('home.price_p4') }}</h2>
    <br>
    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12 text-center">
        <span class="text-orange">{{ trans('home.price_p2') }}</span><br>
        <h4>{{ trans('home.price_p3') }}</h4>
      </div>
    </div>
    <br> -->
    <!-- Table title -->
    <div class="small">
        <div class="col-lg-2 col-sm-2 col-12 m-t-35 base_plan_container" style="padding: 0px;">
        <table class="table table-bordered">
            <thead>
              <tbody class="text-center subscriptionBody">
                <tr class="info">
                  <td>Email ( one off )</td>
                </tr>
                <tr class="info">
                  <td>Price per email</td>
                </tr>
                <tr>
                  <td id="active-botton">
                    <i class="fa fa-info-circle no-border infoIcon"></i><span class="seeAllFeatures"> See all feature details on our  <a style="color:#23527C;text-decoration:underline !important;font-weight:900 !important;" href="http://help.trendle.io/" target="_blank">Help Pages</a></span>
                  </td>
                </tr>
              </tbody>
            </thead>
        </table>
    </div>
    <div class="col-lg-2 col-sm-2 col-12 m-t-35 base_plan_container" style="padding: 0px;">
         <table class="table table-bordered">
            <thead>
              <tr>
                <th class="card-header no-radius  text-center background-orange text-white">
                  <h4>S</h4>
                  <p class="priceSubscription"><span class="sPriceBaseBoost"></span></p>
                </th>
              </tr>
              <tbody class="text-center subscriptionBody">
                <tr class="info">
                  <td>250</td>
                </tr>
                <tr class="info">
                  <td><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                </tr>
                <tr class="info">
                  <td>0.02</td>
                </tr>
                <tr>
                  <td>
                    <a class="btn pricing-button signup-color pricing-button-active" href="/register">Sign Up</a>
                    <br>
                  </td>
                </tr>
              </tbody>
            </thead>
        </table>
    </div>
    <div class="col-lg-2 col-sm-2 col-12 m-t-35 base_plan_container" style="padding: 0px;">
         <table class="table table-bordered">
            <thead>
              <tr>
                <th class="card-header no-radius  text-center background-orange text-white">
                  <h4>M</h4>
                  <p class="priceSubscription"><span class="mPriceBaseBoost"></span></p>
                </th>
              </tr>
              <tbody class="text-center subscriptionBody">
                <tr class="info">
                  <td>2,000</td>
                </tr>
                <tr class="info">
                  <td>0.005</td>
                </tr>
                <tr>
                  <td>
                    <a class="btn pricing-button signup-color pricing-button-active" href="/register">Sign Up</a>
                    <br>
                  </td>
                </tr>
              </tbody>
            </thead>
        </table>
    </div>
    <div class="col-lg-2 col-sm-2 col-12 m-t-35 base_plan_container" style="padding: 0px;">
         <table class="table table-bordered">
            <thead>
              <tr>
                <th class="card-header no-radius  text-center background-orange text-white">
                  <h4>L</h4>
                  <p class="priceSubscription"><span class="lPriceBaseBoost"></span></p>
                </th>
              </tr>
              <tbody class="text-center subscriptionBody">
                <tr class="info">
                  <td>10,000</td>
                </tr>
                <tr class="info">
                  <td>0.0025</td>
                </tr>
                <tr>
                  <td>
                    <a class="btn pricing-button signup-color pricing-button-active" href="/register">Sign Up</a>
                    <br>
                  </td>
                </tr>
              </tbody>
            </thead>
        </table>
    </div>
  </div>
  @foreach($pillar_with_plans as $key => $pillar)
  <div class="large">
  <div class="table-responsive" style="padding:2px;">
    <table class="table baseSubscriptionTable" style="width: 100%">
        <thead>
            <tr>
                <th class="no-border"></th>
                <th class="no-border"></th>
                <th class="no-border" style="background: #FF5722;height: 20px;"></th>
                <th class="no-border"></th>
            </tr>
            <tr class="headerSubscription">
                <th width="16.66%" class="card-header text-center align_middle"></th>
                <th width="16.66%" class="card-header background-orange text-center text-white">S<p class="priceSubscription"><span class="sPriceBaseBoost"></span></p></th>
                <th width="16.66%" class="card-header background-orange text-center text-white" style="border-top: none !important;">M<p class="priceSubscription"><span class="mPriceBaseBoost"></span></p></th>
                <th width="16.66%" class="card-header background-orange text-center text-white">L<p class="priceSubscription"><span class="lPriceBaseBoost"></span></p></th>
            </tr>
        </thead>
        <tbody>
            <tr class="bodySubscription with_bg">
                <td class="text-center">Extra Emails</td>
                <td class="text-center"><span class="emailQuantity_s" data-email-quantity="1000">1,000</span></td>
                <td class="text-center"><span class="emailQuantity_m" data-email-quantity="5000">5,000</span></td>
                <td class="text-center"><span class="emailQuantity_l" data-email-quantity="50000">50,000</span></td>
            </tr>

            <tr class="bodySubscription with_bg">
                <td class="text-center">Price per email</td>
                <td class="text-center"><span class="priceEmail_s"></span></td>
                <td class="text-center"><span class="priceEmail_m"></span></td>
                <td class="text-center"><span class="priceEmail_l"></span></td>
            </tr>

            <tr class="bodySubscription">
                <td><i class="fa fa-info-circle no-border infoIcon"></i><span class="seeAllFeatures"> See all feature details on our <a class="helpPages" target="_blank" href="http://help.trendle.io/">Help pages</a></span></td>
                <td>
                  @foreach($plans_currency as $plan)
                    @if($plan->plan->size == 'S')
                      {!! Form::radio('package'.$pillar->id, 500, false, ['class' => 'packages emailPackage', 'data-id' => $plan->plan_id, 'data-country-id' => $plan->country_id, 'data-symbol' => '$', ($plan->plan_id == $dataIscheck) ? 'checked' : '', 'data-ischeck' => ($plan->plan_id == $dataIscheck) ? 'yes' : 'no']) !!}
                      <span class="pccs dontdisplay"></span>
                      <!-- <span class="pccs">{{ $currency_symbol }}</span><span class="pcca{{$plan->plan_id}}">{{ Helpers::formatPlanAmount($plan->amount) }}</span> -->
                    @endif
                  @endforeach
                </td>
                <td style="border-bottom: none !important;">
                  @foreach($plans_currency as $plan)
                    @if($plan->plan->size == 'M')
                      {!! Form::radio('package'.$pillar->id, 1000, false, ['class' => 'packages emailPackage', 'data-id' => $plan->plan_id, 'data-country-id' => $plan->country_id, 'data-symbol' => '$', ($plan->plan_id == $dataIscheck) ? 'checked' : '', 'data-ischeck' => ($plan->plan_id == $dataIscheck) ? 'yes' : 'no']) !!}
                      <span class="pccs dontdisplay"></span>
                      <!-- <span class="pccs">{{ $currency_symbol }}</span><span class="pcca{{$plan->plan_id}}">{{ Helpers::formatPlanAmount($plan->amount) }}</span> -->
                    @endif
                  @endforeach
                </td>
                <td>
                  @foreach($plans_currency as $plan)
                    @if($plan->plan->size == 'L')
                      {!! Form::radio('package'.$pillar->id, 2500, false, ['class' => 'packages emailPackage', 'data-id' => $plan->plan_id, 'data-country-id' => $plan->country_id, 'data-symbol' => '$', ($plan->plan_id == $dataIscheck) ? 'checked' : '', 'data-ischeck' => ($plan->plan_id == $dataIscheck) ? 'yes' : 'no']) !!}
                      <span class="pccs dontdisplay"></span>
                      <!-- <span class="pccs">{{ $currency_symbol }}</span><span class="pcca{{$plan->plan_id}}">{{ Helpers::formatPlanAmount($plan->amount) }}</span> -->
                    @endif
                  @endforeach
                </td>
            </tr>

            <tr class="">
                <td class="no-border"></td>
                <td class="no-border"></td>
                <td class="footerMidSubscription"></td>
                <td class="no-border"></td>
            </tr>
        </tbody>
    </table>
    </div>
  </div>
  @endforeach
  <script type="text/javascript">
    var s_gbp_boost;
    var m_gbp_boost;
    var l_gbp_boost;
    var s_eur_boost;
    var m_eur_boost;
    var l_eur_boost;

    $(document).ready(function(){


      $.ajax({
          type: "GET",
          url: 'convertBoostBS',
          success: function(result){
            response = JSON.parse(result);
            s_gbp_boost = response.s_gbp;
            m_gbp_boost = response.m_gbp;
            l_gbp_boost = response.l_gbp;
            s_eur_boost = response.s_eur;
            m_eur_boost = response.m_eur;
            l_eur_boost = response.l_eur;

            setEmailPackRate(s_gbp_boost,m_gbp_boost,l_gbp_boost,s_eur_boost,m_eur_boost,l_eur_boost);
        }
      });

    })

    

    function setEmailPackRate(s_gbp_boost,m_gbp_boost,l_gbp_boost,s_eur_boost,m_eur_boost,l_eur_boost){
      var s = "5";
      var m = "10";
      var l = "25";
      var currency = $('#preferredCurrencyEmail').attr('data-preferred-currency');
      var promotype = $('#promoType').attr('data-promo-type');
      var promovalue = $('#promoType').attr('data-promo-value');
      var emailQuantity_s = $('.emailQuantity_s').attr('data-email-quantity');
      var emailQuantity_m = $('.emailQuantity_m').attr('data-email-quantity');
      var emailQuantity_l = $('.emailQuantity_l').attr('data-email-quantity');

      s = s.split('.');
      (s[1]) ? s[1] = '.'+s[1] : s[1] = '';

      m = m.split('.');
      (m[1]) ? m[1] = '.'+m[1] : m[1] = '';

      l = l.split('.');
      (l[1]) ? l[1] = '.'+l[1] : l[1] = '';

      $('.sPriceBaseBoost').html('<span class="priceNoDecimal">'+currency+s[0]+'</span><span class="priceDecimal">'+s[1]+'</span> <span class="priceNoDecimal"></span>');

      $('.mPriceBaseBoost').html('<span class="priceNoDecimal">'+currency+m[0]+'</span><span class="priceDecimal">'+m[1]+'</span> <span class="priceNoDecimal"></span>');

      $('.lPriceBaseBoost').html('<span class="priceNoDecimal">'+currency+l[0]+'</span><span class="priceDecimal">'+l[1]+'</span> <span class="priceNoDecimal"></span>');

      var counter = 0
      $('.emailPackage').each(function(){
          switch (counter){
            case 0:
                $(this).val(parseInt(s));
            break;
            case 1:
                $(this).val(parseInt(m));
            break;
            case 2:
                $(this).val(parseInt(l));
            break;
          }
          counter ++;
      })

      $('#currency').html(currency);

        if (currency == "gbp") {
          var s = s_gbp_boost.toString();
          var m = m_gbp_boost.toString();
          var l = l_gbp_boost.toString();
          var currency = "£";

          s = s.split('.');
          (s[1]) ? s[1] = '.'+s[1] : s[1] = '';

          m = m.split('.');
          (m[1]) ? m[1] = '.'+m[1] : m[1] = '';

          l = l.split('.');
          (l[1]) ? l[1] = '.'+l[1] : l[1] = '';

          $('.sPriceBaseBoost').html('<span class="priceNoDecimal">'+currency+s[0]+'</span><span class="priceDecimal">'+s[1].substring(0,3)+'</span> <span class="priceNoDecimal"></span>');
          $('.mPriceBaseBoost').html('<span class="priceNoDecimal">'+currency+m[0]+'</span><span class="priceDecimal">'+m[1].substring(0,3)+'</span> <span class="priceNoDecimal"></span>');
          $('.lPriceBaseBoost').html('<span class="priceNoDecimal">'+currency+l[0]+'</span><span class="priceDecimal">'+l[1].substring(0,3)+'</span> <span class="priceNoDecimal"></span>');
          
          $('#currency').html(currency);

          var counter = 0
          $('.emailPackage').each(function(){
              switch (counter){
                case 0:
                    $(this).val(s[0]+''+s[1].substring(0,3));
                    if($(this).is(':checked') == true){
                      $('#total-sum-amount').html(s_gbp_boost);
                      $('#total').attr('data-stripe-amount',s_gbp_boost);
                      $('#total').html('data-stripe-amount',s_gbp_boost);
                    }
                break;
                case 1:
                    $(this).val(m[0]+''+m[1].substring(0,3));
                    if($(this).is(':checked') == true){
                      $('#total-sum-amount').html(m_gbp_boost);
                      $('#total').attr('data-stripe-amount',m_gbp_boost);
                      $('#total').html('data-stripe-amount',m_gbp_boost);
                    }
                break;
                case 2:
                    $(this).val(l[0]+''+l[1].substring(0,3));
                    if($(this).is(':checked') == true){
                      $('#total-sum-amount').html(l_gbp_boost);
                      $('#total').attr('data-stripe-amount',l_gbp_boost);
                      $('#total').html('data-stripe-amount',l_gbp_boost);
                    }
                break;
              }
              counter ++;
          })

          s = parseFloat(s[0]+''+s[1].substring(0,3));
          m = parseFloat(m[0]+''+m[1].substring(0,3));
          l = parseFloat(l[0]+''+l[1].substring(0,3));

          $('.priceEmail_s').html(parseFloat(roundNumber(s/parseFloat(emailQuantity_s),4)) );
          $('.priceEmail_m').html(parseFloat(roundNumber(m/parseFloat(emailQuantity_m),4)) );
          $('.priceEmail_l').html(parseFloat(roundNumber(l/parseFloat(emailQuantity_l),4)) ); 
         
        } else if(currency == "eur"){
          var s = s_eur_boost.toString();
          var m = m_eur_boost.toString();
          var l = l_eur_boost.toString();
          var currency = "€";

          s = s.split('.');
          (s[1]) ? s[1] = '.'+s[1] : s[1] = '';

          m = m.split('.');
          (m[1]) ? m[1] = '.'+m[1] : m[1] = '';

          l = l.split('.');
          (l[1]) ? l[1] = '.'+l[1] : l[1] = '';

          $('.sPriceBaseBoost').html('<span class="priceNoDecimal">'+currency+s[0]+'</span><span class="priceDecimal">'+s[1].substring(0,3)+'</span> <span class="priceNoDecimal"></span>');
          $('.mPriceBaseBoost').html('<span class="priceNoDecimal">'+currency+m[0]+'</span><span class="priceDecimal">'+m[1].substring(0,3)+'</span> <span class="priceNoDecimal"></span>');
          $('.lPriceBaseBoost').html('<span class="priceNoDecimal">'+currency+l[0]+'</span><span class="priceDecimal">'+l[1].substring(0,3)+'</span> <span class="priceNoDecimal"></span>');        

          $('#currency').html(currency);

          var counter = 0
          $('.emailPackage').each(function(){
              switch (counter){
                case 0:
                    $(this).val(s[0]+''+s[1].substring(0,3));
                    if($(this).is(':checked') == true){
                      $('#total-sum-amount').html(s_eur_boost);
                      $('#total').attr('data-stripe-amount',s_eur_boost);
                      $('#total').html('data-stripe-amount',s_eur_boost);
                    }
                break;
                case 1:
                    $(this).val(m[0]+''+m[1].substring(0,3));
                    if($(this).is(':checked') == true){
                      $('#total-sum-amount').html(m_eur_boost);
                      $('#total').attr('data-stripe-amount',m_eur_boost);
                      $('#total').html('data-stripe-amount',m_eur_boost);
                    }
                break;
                case 2:
                    $(this).val(l[0]+''+l[1].substring(0,3));
                    if($(this).is(':checked') == true){
                      $('#total-sum-amount').html(l_eur_boost);
                      $('#total').attr('data-stripe-amount',l_eur_boost);
                      $('#total').html('data-stripe-amount',l_eur_boost);
                    }
                break;
              }
              counter ++;
          })

          s = parseFloat(s[0]+''+s[1].substring(0,3));
          m = parseFloat(m[0]+''+m[1].substring(0,3));
          l = parseFloat(l[0]+''+l[1].substring(0,3));

          $('.priceEmail_s').html(parseFloat(roundNumber(s/parseFloat(emailQuantity_s),4)) );
          $('.priceEmail_m').html(parseFloat(roundNumber(m/parseFloat(emailQuantity_m),4)) );
          $('.priceEmail_l').html(parseFloat(roundNumber(l/parseFloat(emailQuantity_l),4)) ); 
         
        } else if(currency == "usd"){
          var s = "5";
          var m = "10";
          var l = "25";
          var currency = "$";

          s = s.split('.');
          (s[1]) ? s[1] = '.'+s[1] : s[1] = '';

          m = m.split('.');
          (m[1]) ? m[1] = '.'+m[1] : m[1] = '';

          l = l.split('.');
          (l[1]) ? l[1] = '.'+l[1] : l[1] = '';

          $('.sPriceBaseBoost').html('<span class="priceNoDecimal">'+currency+s[0]+'</span><span class="priceDecimal">'+s[1].substring(0,3)+'</span> <span class="priceNoDecimal"></span>');
          $('.mPriceBaseBoost').html('<span class="priceNoDecimal">'+currency+m[0]+'</span><span class="priceDecimal">'+m[1].substring(0,3)+'</span> <span class="priceNoDecimal"></span>');
          $('.lPriceBaseBoost').html('<span class="priceNoDecimal">'+currency+l[0]+'</span><span class="priceDecimal">'+l[1].substring(0,3)+'</span> <span class="priceNoDecimal"></span>');

          $('#currency').html(currency);

          var counter = 0
          $('.emailPackage').each(function(){
              switch (counter){
                case 0:
                    $(this).val(s[0]+''+s[1].substring(0,3));
                    if($(this).is(':checked') == true){
                      $('#total-sum-amount').html(s);
                      $('#total').attr('data-stripe-amount',s);
                      $('#total').html('data-stripe-amount',s);
                    }
                break;
                case 1:
                    $(this).val(m[0]+''+m[1].substring(0,3));
                    if($(this).is(':checked') == true){
                      $('#total-sum-amount').html(m);
                      $('#total').attr('data-stripe-amount',m);
                      $('#total').html('data-stripe-amount',m);
                    }
                break;
                case 2:
                    $(this).val(l[0]+''+l[1].substring(0,3));
                    if($(this).is(':checked') == true){
                      $('#total-sum-amount').html(l);
                      $('#total').attr('data-stripe-amount',l);
                      $('#total').html('data-stripe-amount',l);
                    }
                break;
              }
              counter ++;
          })

          s = parseFloat(s[0]+''+s[1].substring(0,3));
          m = parseFloat(m[0]+''+m[1].substring(0,3));
          l = parseFloat(l[0]+''+l[1].substring(0,3));

          $('.priceEmail_s').html(parseFloat(roundNumber(s/parseFloat(emailQuantity_s),4)) );
          $('.priceEmail_m').html(parseFloat(roundNumber(m/parseFloat(emailQuantity_m),4)) );
          $('.priceEmail_l').html(parseFloat(roundNumber(l/parseFloat(emailQuantity_l),4)) ); 

        }
    }
    
    $(".tipso").tipso();
  </script>

@include('partials.subscription._planscript')
