<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12 padding-pricing">
    <div class="col-md-12">
      <h3 class="text-center section-title text-orange">{{ ucfirst(trans('home.pricing')) }}</h5>
      <h6 class="text-center"><i>{{ ucfirst(trans('home.vat_exclusive')) }} </i><i class="fa fa-info-circle text-orange tipsoPrice"></i><br><br>
        <i><small style="font-size: 12px;color:#333">Choose to pay in USD, GBP or EUR!</small></h4></i>
      </h6>
      <h4 class="text-center">{{ trans('home.price_p1') }} 
      <h4 class="text-center">{{ trans('home.price_p7') }}</h4>
    </div>
    
    <div class="row m-t-35">
      <div class="col-md-12">
        @include('partials.pages._base_subscription')
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
  <hr>
      @include('partials.pages._advertising')
  </div>
</div>
<div class="row">
  <div class="col-md-12">
  <hr>
      @include('partials.pages._boost_base_subscription')
  </div>
</div>
<!-- <div class="row">
  <div class="col-md-12">
    <h2>{{ ucfirst(trans('home.SR_PricingHeader')) }}</h2>
    <div class="col-md-2 col-sm-12 col-xs-12 padding-0">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="text-center">
              <h4><span id=""><br></span> </h4>
            </th>
          </tr>
          <tbody>
            <tr class="info">
              <td>
                <div id="fba-text-fee">
                  {{ ucfirst(trans('home.SR_PricingLabel')) }}
                </div>
              </td>
            </tr>
            <tr>
              <td id="padding-15">
                <br>
              </td>
            </tr>
          </tbody>
        </thead>
      </table>
    </div>
    <div class="col-md-2 col-sm-12 col-xs-12 padding-0">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="text-center background-orange text-white">
              <h4><span id="s-reviews-all">£5</span> </h4>
            </th>
          </tr>
          <tbody class="text-center">
            <tr>
              <td class="text-center info">
                <span><small>{{ ucfirst(trans('home.SR_PricingAllProducts')) }}</small></span>
              </td>
            </tr>
            <tr>
              <td>
                <a class="btn pricing-button signup-color" href="/register">Sign Up</a>
              </td>
            </tr>
          </tbody>
        </thead>
      </table>
    </div>
  </div>
</div> -->

<!-- Temporary Hide -->
<!-- <div class="row">
  <hr>
  <h2 class="text-center section-title text-orange">{{ ucfirst(trans('home.comingsoonpricing')) }}</h2>
</div>

<div class="row">
    <h2>{{ ucfirst(trans('home.PR_PricingHeader')) }}</h2>
    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <span class="text-orange">{{ ucfirst(trans('home.PR_PricingDescription')) }}</span><br>
      </div>
      <br><br>
    </div>
    <div class="col-md-2 col-sm-12 col-xs-12 padding-0">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="text-center">
              <h4><span id=""><br></span> </h4>
            </th>
          </tr>
          <tbody>
            <tr class="info">
              <td><div id="">{{ ucfirst(trans('home.PR_PricingLabel')) }}</div></td>
            </tr>
            <tr>
              <td id="padding-15">
                <br>
              </td>
            </tr>
          </tbody>
        </thead>
      </table>
    </div>
    <div class="col-md-2 col-sm-12 col-xs-12 padding-0">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="text-center background-orange text-white">
              <h4><span id="p-reviews-all">£25</span> </h4>
            </th>
          </tr>
          <tbody class="text-center">
            <tr>
              <td class="text-center info">
                <span>{{ ucfirst(trans('home.PR_PricingAllProducts')) }}</span>
              </td>
            </tr>
            <tr>
              <td>
                <a class="btn pricing-button signup-color" href="/register">Sign Up</a>
              </td>
            </tr>
          </tbody>
        </thead>
      </table>
    </div>
    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <span class="text-orange">{{ ucfirst(trans('home.PR_PricingDescription2')) }}</span><br><br>
      </div>
    </div>
    <div class="col-md-2 col-sm-12 col-xs-12 padding-0">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="text-center">
              <h4><span id=""><br></span> </h4>
            </th>
          </tr>
          <tbody>
            <tr class="info">
              <td><div id="">{{ ucfirst(trans('home.PR_PricingLabel')) }}</div></td>
            </tr>
            <tr>
              <td id="padding-15">
                <br>
              </td>
            </tr>
          </tbody>
        </thead>
      </table>
    </div>
    <div class="col-md-2 col-sm-12 col-xs-12 padding-0">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="text-center background-orange text-white">
              <h4><span id="p-reviews-xs">£25</span> </h4>
            </th>
          </tr>
          <tbody class="text-center">
            <tr>
              <td class="text-center info">
                <span>250 {{ ucfirst(trans('home.PR_PricingProductsText')) }}</span>
              </td>
            </tr>
            <tr>
              <td>
                <a class="btn pricing-button signup-color" href="/register">Sign Up</a>
              </td>
            </tr>
          </tbody>
        </thead>
      </table>
    </div>
    <div class="col-md-2 col-sm-12 col-xs-12 padding-0">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="text-center background-orange text-white">
              <h4><span id="p-reviews-s">£50</span> </h4>
            </th>
          </tr>
          <tbody class="text-center">
            <tr>
              <td class="text-center info">
                <span>500 {{ ucfirst(trans('home.PR_PricingProductsText')) }}</span>
              </td>
            </tr>
            <tr>
              <td>
                <a class="btn pricing-button signup-color" href="/register">Sign Up</a>
              </td>
            </tr>
          </tbody>
        </thead>
      </table>
    </div>
    <div class="col-md-2 col-sm-12 col-xs-12 padding-0">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="text-center background-orange text-white">
              <h4><span id="p-reviews-m">£75</span> </h4>
            </th>
          </tr>
          <tbody class="text-center">
            <tr>
              <td class="text-center info">
                <span>1000 {{ ucfirst(trans('home.PR_PricingProductsText')) }}</span>
              </td>
            </tr>
            <tr>
              <td>
                <a class="btn pricing-button signup-color" href="/register">Sign Up</a>
              </td>
            </tr>
          </tbody>
        </thead>
      </table>
    </div>
    <div class="col-md-2 col-sm-12 col-xs-12 padding-0">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="text-center background-orange text-white">
              <h4><span id="p-reviews-l">£100</span> </h4>
            </th>
          </tr>
          <tbody class="text-center">
            <tr>
              <td class="text-center info">
                <span>2000 {{ ucfirst(trans('home.PR_PricingProductsText')) }}</span>
              </td>
            </tr>
            <tr>
              <td>
                <a class="btn pricing-button signup-color" href="/register">Sign Up</a>
              </td>
            </tr>
          </tbody>
        </thead>
      </table>
    </div>
    <div class="col-md-2 col-sm-12 col-xs-12 padding-0">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="text-center background-orange text-white">
              <h4><span id="p-reviews-xl">£250</span> </h4>
            </th>
          </tr>
          <tbody class="text-center">
            <tr>
              <td class="text-center info">
                <span>5000 {{ ucfirst(trans('home.PR_PricingProductsText')) }}</span>
              </td>
            </tr>
            <tr>
              <td>
                <a class="btn pricing-button signup-color" href="/register">Sign Up</a>
              </td>
            </tr>
          </tbody>
        </thead>
      </table>
    </div>
</div>

<div class="row">
    <br>
    <h2>{{ ucfirst(trans('home.PR_PricingKeywordHeader')) }}</h2>
    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <span class="text-orange">{{ ucfirst(trans('home.PR_PricingKeywordDescription')) }}</span><br><br>
      </div>
    </div>
    <div class="col-md-2 col-sm-12 col-xs-12 padding-0">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="text-center">
              <h4><span id=""><br></span> </h4>
            </th>
          </tr>
          <tbody>
            <tr class="info">
              <td><div id="">{{ ucfirst(trans('home.PR_PricingKeywordLabel')) }}</div></td>
            </tr>
            <tr>
              <td id="padding-15">
                <br>
              </td>
            </tr>
          </tbody>
        </thead>
      </table>
    </div>
    <div class="col-md-2 col-sm-12 col-xs-12 padding-0">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="text-center background-orange text-white">
              <h4><span id="keywords-xs">£5</span> </h4>
            </th>
          </tr>
          <tbody class="text-center">
            <tr>
              <td class="text-center info">
                <span>10 {{ ucfirst(trans('home.PR_PricingKeywordText')) }}</span>
              </td>
            </tr>
            <tr>
              <td>
                <a class="btn pricing-button signup-color" href="/register">Sign Up</a>
              </td>
            </tr>
          </tbody>
        </thead>
      </table>
    </div>
    <div class="col-md-2 col-sm-12 col-xs-12 padding-0">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="text-center background-orange text-white">
              <h4><span id="keywords-s">£50</span> </h4>
            </th>
          </tr>
          <tbody class="text-center">
            <tr>
              <td class="text-center info">
                <span>100 {{ ucfirst(trans('home.PR_PricingKeywordText')) }}</span>
              </td>
            </tr>
            <tr>
              <td>
                <a class="btn pricing-button signup-color" href="/register">Sign Up</a>
              </td>
            </tr>
          </tbody>
        </thead>
      </table>
    </div>
    <div class="col-md-2 col-sm-12 col-xs-12 padding-0">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="text-center background-orange text-white">
              <h4><span id="keywords-m">£100</span> </h4>
            </th>
          </tr>
          <tbody class="text-center">
            <tr>
              <td class="text-center info">
                <span>500 {{ ucfirst(trans('home.PR_PricingKeywordText')) }}</span>
              </td>
            </tr>
            <tr>
              <td>
                <a class="btn pricing-button signup-color" href="/register">Sign Up</a>
              </td>
            </tr>
          </tbody>
        </thead>
      </table>
    </div>
    <div class="col-md-2 col-sm-12 col-xs-12 padding-0">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="text-center background-orange text-white">
              <h4><span id="keywords-l">£200</span> </h4>
            </th>
          </tr>
          <tbody class="text-center">
            <tr>
              <td class="text-center info">
                <span>1500 {{ ucfirst(trans('home.PR_PricingKeywordText')) }}</span>
              </td>
            </tr>
            <tr>
              <td>
                <a class="btn pricing-button signup-color" href="/register">Sign Up</a>
              </td>
            </tr>
          </tbody>
        </thead>
      </table>
    </div>
    <div class="col-md-2 col-sm-12 col-xs-12 padding-0">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="text-center background-orange text-white">
              <h4><span id="keywords-xl">£400</span> </h4>
            </th>
          </tr>
          <tbody class="text-center">
            <tr>
              <td class="text-center info">
                <span>3000 {{ ucfirst(trans('home.PR_PricingKeywordText')) }}</span>
              </td>
            </tr>
            <tr>
              <td>
                <a class="btn pricing-button signup-color" href="/register">Sign Up</a>
              </td>
            </tr>
          </tbody>
        </thead>
      </table>
    </div>
</div>

<div class="row">
    <br>
    <h2>{{ ucfirst(trans('home.PR_PricingAnalyticsHeader')) }}</h2>
    <div class="col-md-2 col-sm-12 col-xs-12 padding-0">
      <table class="table table-bordered">
        <thead>
          <tr>
            <td class="text-center">
              <span><br><br></span>
            </td>
          </tr>
          <tbody>
            <td class="info" style="padding-bottom:8px;">
              <span>
                <br><br>{{ ucfirst(trans('home.PR_PricingAnalyticsLabel')) }}<br><br><br>
              </span>
            </td>
            <tr>
              <td id="padding-15">
                <br>
              </td>
            </tr>
          </tbody>
        </thead>
      </table>
    </div>
    <div class="col-md-6 col-sm-12 col-xs-12 padding-0">
      <table class="table table-bordered">
        <thead>
          <tr>
            <td class="text-center background-orange text-white">
              <span>
                  {{ ucfirst(trans('home.PR_PricingAnalyticsHeader1')) }}<br>
                  {{ ucfirst(trans('home.PR_PricingAnalyticsHeader2')) }} <span id="analytics-start-price">£10</span> {{ ucfirst(trans('home.PR_PricingAnalyticsHeader3')) }} <span id="analytics-end-price">£100</span> {{ ucfirst(trans('home.PR_PricingAnalyticsHeader4')) }}
              </span>
            </td>
          </tr>
          <tbody class="text-center">
            <tr>
              <td class="text-center info">
                <span>{{ ucfirst(trans('home.PR_PricingAnalyticsDesc1')) }}<br>
                      {{ ucfirst(trans('home.PR_PricingAnalyticsDesc2')) }}<br>
                      {{ ucfirst(trans('home.PR_PricingAnalyticsDesc3')) }}<br><br>
                      + {{ ucfirst(trans('home.PR_PricingAnalyticsDesc4')) }}
                </span>
              </td>
            </tr>
            <tr>
              <td>
                <a class="btn pricing-button signup-color" style="width:180px;margin-left:25px;" href="/register">Sign Up</a>
              </td>
            </tr>
          </tbody>
        </thead>
      </table>
    </div>
</div>
<script src="{{ url('js/landing.js') }}"></script>
</div> -->

<script type="text/javascript">
$(document).ready(function() {
  $('.tipsoPrice').tipso({
    content: 'VAT will be added for EU based customers who do not provide a valid EU VAT number.',
    background: '#7f7f7f',
  });

  $("#pricing-currency").on('hidden.bs.select', function (e) {
    currency = $(this).val();

    if (currency == "gbp") {
      $("#crm-xs").html("£5");
      $("#crm-s").html("£10");
      $("#crm-m").html("£25");
      $("#crm-l").html("£40");
      $("#crm-xl").html("£60");

      $("#s-reviews-all").html("£5");

      $("#p-reviews-all").html("£25");
      $("#p-reviews-xs").html("£25");
      $("#p-reviews-s").html("£50");
      $("#p-reviews-m").html("£75");
      $("#p-reviews-l").html("£100");
      $("#p-reviews-xl").html("£250");

      $("#keywords-xs").html("£5");
      $("#keywords-s").html("£50");
      $("#keywords-m").html("£100");
      $("#keywords-l").html("£200");
      $("#keywords-xl").html("£400");

      $("#analytics-start-price").html("£10");
      $("#analytics-end-price").html("£100");
    } else if(currency == "eur"){
      $("#crm-xs").html("€6");
      $("#crm-s").html("€12");
      $("#crm-m").html("€30");
      $("#crm-l").html("€47");
      $("#crm-xl").html("€70");

      $("#s-reviews-all").html("€5");

      $("#p-reviews-all").html("€30");
      $("#p-reviews-xs").html("€30");
      $("#p-reviews-s").html("€60");
      $("#p-reviews-m").html("€89");
      $("#p-reviews-l").html("€120");
      $("#p-reviews-xl").html("€299");

      $("#keywords-xs").html("€6");
      $("#keywords-s").html("€60");
      $("#keywords-m").html("€120");
      $("#keywords-l").html("€240");
      $("#keywords-xl").html("€480");

      $("#analytics-start-price").html("€12");
      $("#analytics-end-price").html("€120");
    } else if(currency == "usd"){
      $("#crm-xs").html("$7");
      $("#crm-s").html("$13");
      $("#crm-m").html("$32");
      $("#crm-l").html("$50");
      $("#crm-xl").html("$75");

      $("#s-reviews-all").html("$5");

      $("#p-reviews-all").html("$32");
      $("#p-reviews-xs").html("$32");
      $("#p-reviews-s").html("$64");
      $("#p-reviews-m").html("$95");
      $("#p-reviews-l").html("$130");
      $("#p-reviews-xl").html("$310");

      $("#keywords-xs").html("$7");
      $("#keywords-s").html("$64");
      $("#keywords-m").html("$130");
      $("#keywords-l").html("$260");
      $("#keywords-xl").html("$520");

      $("#analytics-start-price").html("$13");
      $("#analytics-end-price").html("$130");
    }
  });
});
</script>
