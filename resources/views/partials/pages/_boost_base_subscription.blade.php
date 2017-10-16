<!-- <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12 text-right">
        <select id="boostbaseSubscriptionSelect" class="selectpicker" data-width="fit">
          <option value="usd">USD</option>
          <option value="gbp">GBP</option>
          <option value="eur">EUR</option>
        </select>
        <i class="fa fa-info-circle text-orange tipsoConversion"></i>
      </div>
    </div> -->
    <h3>{{ trans('home.price_p6') }}</h3>
    <br>
    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12 text-center">
        <span class="text-orange">{{ trans('home.price_p2') }}</span><br>
        <h4>{{ trans('home.price_p3') }}</h4>
      </div>
    </div>
    <br>
    <!-- Table title -->
    <div class="small">
        <div class="col-lg-2 col-sm-2 col-12 m-t-35 base_plan_container" style="padding: 0px;">
        <table class="table table-bordered">
            <thead>
              <tbody class="text-center subscriptionBody">
                <tr class="info">
                  <td>Extra Emails</td>
                </tr>
                <tr class="info">
                  <td>Price per email</td>
                </tr>
                <tr>
                  <td id="active-botton">
                    <i class="fa fa-info-circle no-border infoIcon"></i><span class="seeAllFeatures"> See all feature details on our <a class="helpPages" target="_blank" href="http://help.trendle.io/">Help pages</a></span>
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
                  <td><span class="emailQuantity_s" data-email-quantity="1000">1,000</span></td>
                </tr>
                <tr class="info">
                  <td><span class="priceEmail_s"></span></td>
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
                  <td><span class="emailQuantity_m" data-email-quantity="5000">5,000</span></td>
                </tr>
                <tr class="info">
                  <td><span class="priceEmail_m"></span></td>
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
                  <td><span class="emailQuantity_l" data-email-quantity="50000">50,000</span></td>
                </tr>
                <tr class="info">
                  <td><span class="priceEmail_l"></span></td>
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

  <div class="large">
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
                <td><a class="btn pricing-button signup-color" href="/register">Sign Up</a></td>
                <td style="border-bottom: none !important;"><a class="btn pricing-button signup-color" href="/register">Sign Up</a></td>
                <td><a class="btn pricing-button signup-color" href="/register">Sign Up</a></td>
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
  <script type="text/javascript">
    var s_gbp_boost,
        m_gbp_boost,
        l_gbp_boost,
        s_eur_boost,
        m_eur_boost,
        l_eur_boost,
        xs_gbp,
        s_gbp,
        m_gbp,
        l_gbp,
        xl_gbp,
        xs_eur,
        s_eur,
        m_eur,
        l_eur,
        xl_eur,
        fbaDiyRate_eur,
        fbaDiyRate_gbp,
        currency;

    $(document).ready(function(){
      $.ajax({
          type: "GET",
          url: 'convertBoostBSBaseSubs',
          success: function(result){
            response = JSON.parse(result);
            s_gbp_boost = response.s_gbp_crm;
            m_gbp_boost = response.m_gbp_crm;
            l_gbp_boost = response.l_gbp_crm;
            s_eur_boost = response.s_eur_crm;
            m_eur_boost = response.m_eur_crm;
            l_eur_boost = response.l_eur_crm;

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

            fbaDiyRate_gbp = response.gbp_fba;
            fbaDiyRate_eur = response.eur_fba;

            currency = "usd";
            convertBaseSubsRate(currency);
            convertCrmRate(currency);
            convertFbaRate(currency);

            $("#baseSubscriptionSelect").on('hidden.bs.select', function (e) {
              currency = $(this).val();
              convertBaseSubsRate(currency);
              convertCrmRate(currency);
              convertFbaRate(currency);
            });
        }
      });

    })


    function convertBaseSubsRate(currency){
        var xs = "20";
        var s = "50";
        var m = "100";
        var l = "200";
        var xl = "400";

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

    function convertCrmRate(currency){
        var s = "5";
        var m = "10";
        var l = "25";
        var emailQuantity_s = $('.emailQuantity_s').attr('data-email-quantity');
        var emailQuantity_m = $('.emailQuantity_m').attr('data-email-quantity');
        var emailQuantity_l = $('.emailQuantity_l').attr('data-email-quantity');

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
         
          s = parseFloat(s[0]+''+s[1].substring(0,3));
          m = parseFloat(m[0]+''+m[1].substring(0,3));
          l = parseFloat(l[0]+''+l[1].substring(0,3));

          $('.priceEmail_s').html(roundNumber(s/parseFloat(emailQuantity_s),4) );
          $('.priceEmail_m').html(roundNumber(m/parseFloat(emailQuantity_m),4) );
          $('.priceEmail_l').html(roundNumber(l/parseFloat(emailQuantity_l),4) );

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

          s = parseFloat(s[0]+''+s[1].substring(0,3));
          m = parseFloat(m[0]+''+m[1].substring(0,3));
          l = parseFloat(l[0]+''+l[1].substring(0,3));

          $('.priceEmail_s').html(parseFloat(roundNumber(s/parseFloat(emailQuantity_s),4)) );
          $('.priceEmail_m').html(parseFloat(roundNumber(m/parseFloat(emailQuantity_m),4)) );
          $('.priceEmail_l').html(parseFloat(roundNumber(l/parseFloat(emailQuantity_l),4)) );

        }
    }

    function convertFbaRate(currency){
        if (currency == 'gbp') {
          var currency = "£";
          var rate = fbaDiyRate_gbp.toString();
          rate = rate.split('.');
          (rate[1]) ? rate[1] = '.'+rate[1] : rate[1] = '';
          $('.fbaRateDiy').html(currency+''+rate[0]+''+rate[1].substring(0,3))

        }else if(currency == 'eur'){
          var currency = "€";
          var rate = fbaDiyRate_eur.toString();
          rate = rate.split('.');
          (rate[1]) ? rate[1] = '.'+rate[1] : rate[1] = '';
          $('.fbaRateDiy').html(currency+''+rate[0]+''+rate[1].substring(0,3))

        }else if(currency == 'usd'){
          var fbaDiyRate_usd = 30;
          var currency = "$";
          var rate = fbaDiyRate_usd.toString();
          rate = rate.split('.');
          (rate[1]) ? rate[1] = '.'+rate[1] : rate[1] = '';
          $('.fbaRateDiy').html(currency+''+rate[0]+''+rate[1].substring(0,3))

        }
    }
    
    $('.tipsoConversion').tipso({
      content: 'Our pricing is based in USD. We use the previous day mid-market exchange rate for customers who wish to pay in EUR or GBP',
      background: '#7f7f7f',
    });
  </script>