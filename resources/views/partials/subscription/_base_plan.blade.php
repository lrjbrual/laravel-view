<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/tipso/css/tipso.min.css')}}"/>
<!-- <div class="row m-b-15">
    <div class="col-md-2 col-sm-3 col-xs-3 pull-right">
      <select id="baseSubscriptionSelect" class="form-control" data-width="fit">
        <option value="usd">USD</option>
        <option value="gbp">GBP</option>
        <option value="eur">EUR</option>
      </select>
    </div>
  </div> -->
  <div class="row col-md-12 subs-currency">Preferred Currency:
    @if($currency != "")
      <span class=" subs-message">{{ strtoupper($currency) }}</span>
    @else
      <span class=" subs-message">USD (default)</span>
    @endif
  </div>
  <div class="small">
        <div class="col-lg-2 col-sm-2 col-12 m-t-35 base_plan_container" style="padding: 0px;">
        <table class="table table-bordered tableSmall">
            <thead>
            </thead>
              <tbody class="text-center subscriptionBody">
                <tr class="info">
                  <td>Emails</td>
                </tr>
                <tr class="info">
                  <td>Seller Reviews</td>
                </tr>
                <tr class="info">
                  <td>Ads Campaigns
                    <i class="fa fa-info-circle tipso no-border infoIcon" data-background="#ececea" data-color="#5a5a5a" data-tipso="Includes Reporting, Upload changes directly to Amazon, Add Product Costs and Auto Pilot Algorithms" data-titleContent="Ads Campaigns" data-position="top"></i>
                  </td>
                </tr>
                <tr class="info">
                  <td>Profitability Analytics</td>
                </tr>
                <tr>
                  <td id="active-botton">
                    <i class="fa fa-info-circle no-border infoIcon"></i><span class="seeAllFeatures"> See all feature details on our <a style="color:#23527C;text-decoration:underline !important;font-weight:900 !important;" href="http://help.trendle.io/" target="_blank">Help Pages</a></span>
                  </td>
                </tr>
              </tbody>
        </table>
    </div>
    <div class="col-lg-2 col-sm-2 col-12 m-t-35 base_plan_container" style="padding: 0px;">
        <table class="table table-bordered tableSmall">
            <thead>
              <tr>
                <th class="card-header no-radius  text-center background-orange text-white">
                  <h4 class="text-white">XS</h4>
                  <p class="priceSubscription"><span class="xsPriceBase"></span></p>
                </th>
              </tr>
            </thead>
              <tbody class="text-center subscriptionBody">
                <tr class="info">
                  <td>100</td>
                </tr>
                <tr class="info">
                  <td><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                </tr>
                <tr class="info">
                  <td>Reporting Only</td>
                </tr>
                <tr class="info">
                  <td><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                </tr>
                <tr>
                  <td>
                    <a class="btn pricing-button signup-color pricing-button-active" href="/register">Sign Up</a>
                    <br>
                  </td>
                </tr>
              </tbody>
        </table>
    </div>
    <div class="col-lg-2 col-sm-2 col-12 m-t-35 base_plan_container" style="padding: 0px;">
         <table class="table table-bordered tableSmall">
            <thead>
              <tr>
                <th class="card-header no-radius  text-center background-orange text-white">
                  <h4 class="text-white">S</h4>
                  <p class="priceSubscription"><span class="sPriceBase"></span></p>
                </th>
              </tr>
            </thead>
              <tbody class="text-center subscriptionBody">
                <tr class="info">
                  <td>300</td>
                </tr>
                <tr class="info">
                  <td><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                </tr>
                <tr class="info">
                      <td>+ Upload 
                      <br>+ Product Costs
                      <br>+ Recommendation engine</td>
                </tr>
                <tr class="info">
                  <td><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                </tr>
                <tr>
                  <td>
                    <a class="btn pricing-button signup-color pricing-button-active" href="/register">Sign Up</a>
                    <br>
                  </td>
                </tr>
              </tbody>
        </table>
    </div>
    <div class="col-lg-2 col-sm-2 col-12 m-t-35 base_plan_container" style="padding: 0px;">
         <table class="table table-bordered tableSmall">
            <thead>
              <tr>
                <th class="card-header no-radius  text-center background-orange text-white">
                  <h4 class="text-white">M</h4>
                  <p class="priceSubscription"><span class="mPriceBase"></span></p>
                </th>
              </tr>
            </thead>
              <tbody class="text-center subscriptionBody">
                <tr class="info">
                  <td>2,000</td>
                </tr>
                <tr class="info">
                  <td><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                </tr>
                <tr class="info">
                  <td>+ Auto Pilot Algorithm</td>
                </tr>
                <tr class="info">
                  <td><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                </tr>
                <tr>
                  <td>
                    <a class="btn pricing-button signup-color pricing-button-active" href="/register">Sign Up</a>
                    <br>
                  </td>
                </tr>
              </tbody>
        </table>
    </div>
    <div class="col-lg-2 col-sm-2 col-12 m-t-35 base_plan_container" style="padding: 0px;">
         <table class="table table-bordered tableSmall">
            <thead>
              <tr>
                <th class="card-header no-radius  text-center background-orange text-white">
                  <h4 class="text-white">L</h4>
                  <p class="priceSubscription"><span class="lPriceBase"></span></p>
                </th>
              </tr>
            </thead>
              <tbody class="text-center subscriptionBody">
                <tr class="info">
                  <td>5,000</td>
                </tr>
                <tr class="info">
                  <td><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                </tr>
                <tr class="info">
                  <td><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                </tr>
                <tr class="info">
                  <td><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                </tr>
                <tr>
                  <td>
                    <a class="btn pricing-button signup-color pricing-button-active" href="/register">Sign Up</a>
                    <br>
                  </td>
                </tr>
              </tbody>
        </table>
    </div>
    <div class="col-lg-2 col-sm-2 col-12 m-t-35 base_plan_container" style="padding: 0px;">
         <table class="table table-bordered tableSmall">
            <thead>
              <tr>
                <th class="card-header no-radius  text-center background-orange text-white">
                  <h4 class="text-white">XL</h4>
                  <p class="priceSubscription"><span class="xlPriceBase"></span></p>
                </th>
              </tr>
            </thead>
              <tbody class="text-center subscriptionBody">
                <tr class="info">
                  <td>10,000</td>
                </tr>
                <tr class="info">
                  <td><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                </tr>
                <tr class="info">
                  <td><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                </tr>
                <tr class="info">
                  <td><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                </tr>
                <tr>
                  <td>
                    <a class="btn pricing-button signup-color pricing-button-active" href="/register">Sign Up</a>
                    <br>
                  </td>
                </tr>
              </tbody>
        </table>
    </div>
  </div>

  <div class="large">
  <div class="table-responsive" style="padding:2px;">
    <table class="table baseSubscriptionTable" style="width: 100%">
        <thead>
            <tr>
                <th class="no-border"></th>
                <th class="no-border"></th>
                <th class="no-border"></th>
                <th class="no-border" style="background: #FF5722;height: 20px;"></th>
                <th class="no-border"></th>
                <!-- <th class="no-border"></th> -->
            </tr>
            <tr class="headerSubscription">
                <th width="20%" class="card-header text-center align_middle"></th>
                <th width="20%" class="card-header background-orange text-center text-white">Start-up<p class="priceSubscription"><span class="xsPriceBase"></span></p></th>
                <th width="20%" class="card-header background-orange text-center text-white">Growing Business<p class="priceSubscription"><span class="sPriceBase"></span></p></th>
                <th width="20%" class="card-header background-orange text-center text-white" style="border-top: none !important;">Pro<p class="priceSubscription"><span class="mPriceBase"></span></p></th>
                <th width="20%" class="card-header background-orange text-center text-white">High-Flyer<p class="priceSubscription"><span class="lPriceBase"></span></p></th>
              <!--   <th width="16.66%" class="card-header background-orange text-center text-white">XL<p class="priceSubscription"><span class="xlPriceBase"></span></p></th> -->
            </tr>
        </thead>
        <tbody>
            <tr class="bodySubscription with_bg">
                <td class="text-center">Emails <i class="fa fa-info-circle text-orange tipsoEmails"></i></td>
                <td class="text-center">1,000</td>
                <td class="text-center">3,000</td>
                <td class="text-center">10,000</td>
                <td class="text-center">40,000</td>
                <!-- <td class="text-center">40,000</td> -->
            </tr>

            <tr class="bodySubscription with_bg">
                <td class="text-center">Seller Reviews</td>
                <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                <!-- <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td> -->
            </tr>

            <tr class="bodySubscription with_bg">
                <td class="text-center">Product Reviews</td>
                <td class="text-center">Updated every 10 days </td>
                <td class="text-center">Updated every 5 days</td>
                <td class="text-center">Updated every 2 days</td>
                <td class="text-center">Updated every day</td>
                <!-- <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td> -->
            </tr>

            <tr class="bodySubscription with_bg">
                <td class="text-center">Ads Performance</td>
                <td class="text-center">Reporting Only</td>
                <td class="text-center">+ Upload<br>+ Recommendation engine</td>
                <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                <!-- <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td> -->
            </tr>

            <tr class="bodySubscription with_bg">
                <td class="text-center">Profitability Analytics<br><small class="text-orange commingSoonSmall"><i>(Coming Soon)</i></small></td>
                <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                <!-- <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td> -->
            </tr>

            <tr class="bodySubscription with_bg">
                <td class="text-center">Inventory Analytics<br><small class="text-orange commingSoonSmall"><i>(Coming Soon)</i></small></td>
                <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td>
                <!-- <td class="text-center"><i class="fa fa-check fa-lg color-green" aria-hidden="true"></i></td> -->
            </tr>

            {{ Form::open(array('method' => 'POST', 'url' => url('selectBaseSubscription'))) }}
            <tr class="bodySubscription">
                <td><i class="fa fa-info-circle no-border infoIcon"></i><span class="seeAllFeatures"> See all feature details on our <a class="helpPages" target="_blank" href="http://help.trendle.io/">Help pages</a></span></td>
                <td><input  required="" {{ $xs_checker }} class="packages" data-id="1" data-country-id="826" data-symbol="£" =""="" data-ischeck="no" name="base_subscription" type="radio" value="XS"></td>
                <td><input  required="" {{ $s_checker }} class="packages" data-id="2" data-country-id="826" data-symbol="£" =""="" data-ischeck="no" name="base_subscription" type="radio" value="S"></td>
                <td style="border-bottom: none !important;"><input  required="" {{ $m_checker }} class="packages" data-id="3" data-country-id="826" data-symbol="£" =""="" data-ischeck="no" name="base_subscription" type="radio" value="M"></td>
                <td><input  required="" {{ $l_checker }} class="packages" data-id="4" data-country-id="826" data-symbol="£" =""="" data-ischeck="no" name="base_subscription" type="radio" value="L"></td>
                <!-- <td><input  required="" {{ $xl_checker }} class="packages" data-id="5" data-country-id="826" data-symbol="£" =""="" data-ischeck="no" name="base_subscription" type="radio" value="XL"></td> -->
            </tr>

            <tr class="">
                <td class="no-border"></td>
                <td class="no-border"></td>
                <td class="no-border"></td>
                <td class="footerMidSubscription"></td>
                <td class="no-border"></td>
                <!-- <td class="no-border"></td> -->
            </tr>
        </tbody>
    </table>
    </div>
    <div class="text-right m-t-35">
    <input type="hidden" id="is_trial" name="is_trial" value="{{ $is_trial }}">
    @if ($bs == '')
      {{ Form::submit('Select Base Subscription', array('id' => 'confirmed-activate', 'class' => 'btn btn-success')) }}
    @else
      {{ Form::submit('Update Base Subscription', array('id' => 'confirmed-activate', 'class' => 'btn btn-success')) }}
    @endif   
    </div>
    {{ Form::close() }}
  
  </div>
  <script type="text/javascript" src="{{asset('assets/vendors/tipso/js/tipso.min.js')}}"></script>
  <script type="text/javascript">
    var fbaDiyRate_eur,
        fbaDiyRate_gbp;

    $.ajax({
        type: "GET",
        url: 'convertBS',
        success: function(result){
          response = JSON.parse(result);
          xs_gbp = response.xs_gbp;
          s_gbp = response.s_gbp;
          m_gbp = response.m_gbp;
          l_gbp = response.l_gbp;
          xl_gbp = response.xl_gbp;
          xs_eur = response.xs_eur;
          s_eur = response.s_eur;
          m_eur = response.m_eur;
          l_eur = response.l_eur;
          xl_eur = response.xl_eur;

          setBsPlanRate();
      }
    });

    $('.tipsoEmails').tipso({
      content: 'Not sure how many emails you need? Send unlimited emails during your Free Trial!',
      background: '#7f7f7f',
    });

    $('.xsProductInfo').tipso({
      background: '#7f7f7f',
    })

    function setBsPlanRate(){
      var xs = "20";
      var s = "50";
      var m = "100";
      var l = "200";
      var xl = "400";
      var currency = $('#preferredCurrencyEmail').attr('data-preferred-currency');

      xs = xs.split('.');
      (xs[1]) ? xs[1] = '.'+xs[1] : xs[1] = '';

      s = s.split('.');
      (s[1]) ? s[1] = '.'+s[1] : s[1] = '';

      m = m.split('.');
      (m[1]) ? m[1] = '.'+m[1] : m[1] = '';

      l = l.split('.');
      (l[1]) ? l[1] = '.'+l[1] : l[1] = '';

      xl = xl.split('.');
      (xl[1]) ? xl[1] = '.'+xl[1] : xl[1] = '';

      $('.xsPriceBase').html('<span class="priceNoDecimal">'+currency+xs[0]+'</span><span class="priceDecimal">'+xs[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');

      $('.sPriceBase').html('<span class="priceNoDecimal">'+currency+s[0]+'</span><span class="priceDecimal">'+s[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');

      $('.mPriceBase').html('<span class="priceNoDecimal">'+currency+m[0]+'</span><span class="priceDecimal">'+m[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');

      $('.lPriceBase').html('<span class="priceNoDecimal">'+currency+l[0]+'</span><span class="priceDecimal">'+l[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');

      $('.xlPriceBase').html('<span class="priceNoDecimal">'+currency+xl[0]+'</span><span class="priceDecimal">'+xl[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');


        if (currency == "gbp") {
          var xs = xs_gbp.toString();
          var s = s_gbp.toString();
          var m = m_gbp.toString();
          var l = l_gbp.toString();
          var xl = xl_gbp.toString();
          var currency = "£";

          xs = xs.split('.');
          (xs[1]) ? xs[1] = '.'+xs[1] : xs[1] = '';

          s = s.split('.');
          (s[1]) ? s[1] = '.'+s[1] : s[1] = '';

          m = m.split('.');
          (m[1]) ? m[1] = '.'+m[1] : m[1] = '';

          l = l.split('.');
          (l[1]) ? l[1] = '.'+l[1] : l[1] = '';

          xl = xl.split('.');
          (xl[1]) ? xl[1] = '.'+xl[1] : xl[1] = '';

          $('.xsPriceBase').html('<span class="priceNoDecimal">'+currency+xs[0]+'</span><span class="priceDecimal">'+xs[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');

          $('.sPriceBase').html('<span class="priceNoDecimal">'+currency+s[0]+'</span><span class="priceDecimal">'+s[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');

          $('.mPriceBase').html('<span class="priceNoDecimal">'+currency+m[0]+'</span><span class="priceDecimal">'+m[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');

          $('.lPriceBase').html('<span class="priceNoDecimal">'+currency+l[0]+'</span><span class="priceDecimal">'+l[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');

          $('.xlPriceBase').html('<span class="priceNoDecimal">'+currency+xl[0]+'</span><span class="priceDecimal">'+xl[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');
         
        } else if(currency == "eur"){
          var xs = xs_eur.toString();
          var s = s_eur.toString();
          var m = m_eur.toString();
          var l = l_eur.toString();
          var xl = xl_eur.toString();
          var currency = "€";

          xs = xs.split('.');
          (xs[1]) ? xs[1] = '.'+xs[1] : xs[1] = '';

          s = s.split('.');
          (s[1]) ? s[1] = '.'+s[1] : s[1] = '';

          m = m.split('.');
          (m[1]) ? m[1] = '.'+m[1] : m[1] = '';

          l = l.split('.');
          (l[1]) ? l[1] = '.'+l[1] : l[1] = '';

          xl = xl.split('.');
          (xl[1]) ? xl[1] = '.'+xl[1] : xl[1] = '';

          $('.xsPriceBase').html('<span class="priceNoDecimal">'+currency+xs[0]+'</span><span class="priceDecimal">'+xs[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');

          $('.sPriceBase').html('<span class="priceNoDecimal">'+currency+s[0]+'</span><span class="priceDecimal">'+s[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');

          $('.mPriceBase').html('<span class="priceNoDecimal">'+currency+m[0]+'</span><span class="priceDecimal">'+m[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');

          $('.lPriceBase').html('<span class="priceNoDecimal">'+currency+l[0]+'</span><span class="priceDecimal">'+l[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');

          $('.xlPriceBase').html('<span class="priceNoDecimal">'+currency+xl[0]+'</span><span class="priceDecimal">'+xl[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');
         
        } else if(currency == "usd"){
          var xs = "20";
          var s = "50";
          var m = "100";
          var l = "200";
          var xl = "400";
          var currency = "$";

          xs = xs.split('.');
          (xs[1]) ? xs[1] = '.'+xs[1] : xs[1] = '';

          s = s.split('.');
          (s[1]) ? s[1] = '.'+s[1] : s[1] = '';

          m = m.split('.');
          (m[1]) ? m[1] = '.'+m[1] : m[1] = '';

          l = l.split('.');
          (l[1]) ? l[1] = '.'+l[1] : l[1] = '';

          xl = xl.split('.');
          (xl[1]) ? xl[1] = '.'+xl[1] : xl[1] = '';

          $('.xsPriceBase').html('<span class="priceNoDecimal">'+currency+xs[0]+'</span><span class="priceDecimal">'+xs[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');

          $('.sPriceBase').html('<span class="priceNoDecimal">'+currency+s[0]+'</span><span class="priceDecimal">'+s[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');

          $('.mPriceBase').html('<span class="priceNoDecimal">'+currency+m[0]+'</span><span class="priceDecimal">'+m[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');

          $('.lPriceBase').html('<span class="priceNoDecimal">'+currency+l[0]+'</span><span class="priceDecimal">'+l[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');

          $('.xlPriceBase').html('<span class="priceNoDecimal">'+currency+xl[0]+'</span><span class="priceDecimal">'+xl[1].substring(0,3)+'</span> <span class="priceDecimal"> / month</span>');
         
        }
    }

    $(".tipso").tipso();
    $(document).ready(function(){
        

        $("#confirmed-activate").click(function(){        
          $("#confirmed-activate").prop('disabled', true);
            if ($('#is_trial').val() == "1") {
              swal({
                title: "You're in your Free Trial period.",
                text: "During this time you have access to the full application. When your trial ends you will be subscribed to the package you have just selected. You can change this at any time. You will not be charged until your Free Trial period ends.",
                confirmButtonColor: '#00ADB5'
              }).done();
            }
            $("#confirmed-activate").prop('disabled', false);
        });
    })
  </script>