<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12 padding-features">
    <h2 class="text-center section-title text-orange">{{ ucfirst(trans('home.features')) }}</h2><br>
    <div class="col-md-12 m-b-20">
      <p class="text-center featuretext">
        {{ ucfirst(trans('home.feature_text1')) }}
      </p>
      <br>
      <p class="text-center featuretext">
        {{ ucfirst(trans('home.feature_text2')) }}
      </p>
      <br>
      <p class="text-center featuretext">
        {{ ucfirst(trans('home.feature_text3')) }}
      </p>
      <br>
      <p class="text-center featuretext">
        {{ ucfirst(trans('home.feature_text4')) }}
      </p>
      <br>
      <p class="text-center featuretext">
        {{ ucfirst(trans('home.feature_text5')) }}
      </p>
    <hr>
      
    </div>
    <div class="row">
      <div class="col-md-6 col-xs-12">
      <div class="col-md-6 col-sm-6 col-xs-6 text-center features-top">
        <div class="icon-container">
          <a href="#marketplaces"><img class="feature-icons" src="{{ url('/images/icons/marketplaces.png') }}"></a>
        </div>
          <h4>{{ trans('home.feature1a') }}<br><span id="marketplace-countries">{{ trans('home.feature1b') }}</span></h4>
      </div>
      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 text-center features-top">
        <div class="icon-container">
          <a href="#autoemails"><img class="feature-icons" src="{{ url('/images/icons/crm.png') }}"></a>
        </div>
          <h4>{{ trans('home.feature2a') }}<br>{{ trans('home.feature2b') }}</h4>
      </div>
    </div>
    <div class="col-md-6 col-xs-12">
      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 text-center features-top">
        <div class="icon-container">
          <a href="#refunds"><img class="feature-icons" src="{{ url('/images/icons/refunds.png') }}"></a>
        </div>
          <h4>{{ trans('home.feature3') }}</h4>
      </div>
      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 text-center features-top">
        <div class="icon-container">
          <a href="#advertising"><img class="feature-icons" src="{{ url('/images/icons/advertising-analytics.png') }}"></a>
        </div>
          <h4>{{ trans('home.feature4a') }}</h4>
      </div>
    </div>
    </div>
    <div class="row">
      <div class="col-md-6 col-xs-12">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 text-center features-top">
          <div class="icon-container">
            <a href="#sellerreviews"><i class="fa fa-star text-orange"></i></a>
          </div>
            <h4>{{ trans('home.feature5a') }}<br>{{ trans('home.feature5b') }}</h4>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 text-center features-top">
          <div class="icon-container">
            <a href="#profitablity"><img class="feature-icons" src="{{ url('/images/icons/profitability-analytics.png') }}"></a>
          </div>
            <h4>{{ trans('home.feature6') }}</h4>
        </div>
      </div>
      <div class="col-md-6 col-xs-12">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 text-center features-top">
          <div class="icon-container">
            <a href="#inventory"><img class="feature-icons" src="{{ url('/images/icons/inventory.png') }}"></a>
          </div>
            <h4>{{ trans('home.feature7a') }} {{ trans('home.feature7b') }}</h4>
        </div>
        <div class="col-lg-6 col-md-6 col-xs-6 text-center features-top">
          <div class="icon-container">
            <a href="#keyword"><img class="feature-icons" src="{{ url('/images/icons/keyword-ranking.png') }}"></a>
          </div>
            <h4>{{ trans('home.feature8') }}</h4>
        </div>
      </div>
    </div>
  </div>
</div>
<hr>
<div class="row feature-row" id="marketplaces">
  <div class="col-lg-6 col-md-6 hidden-sm hidden-xs text-center">
      <br>
      <img id="feature-marketplaces" src="{{ url('/images/marketplaces.png') }}">
  </div>
  <div class="col-md-6 col-sm-12 col-xs-12">
      <h2>{{ trans('home.feat_head1') }}</h2>
      <ul class="fa-ul feature-ul">
        <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li1a') }}<br>{{ trans('home.feat_li1a1') }}</li>
        <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li1b') }}</li>
        <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li1c') }}</li>
        <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li1d') }}</li>
      </ul>
      <a class="btn btn-radius btn-skin btn-lg signup-color" href="/register">{{ trans('home.landingbtn') }}</a>
  </div>
</div>

<div class="row feature-row" id="autoemails">
  <div class="col-md-6 col-sm-12 col-xs-12">
    <h3>{{ trans('home.feat_head2') }}</h3>
    <ul class="fa-ul">
      <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li2a') }}</li>
      <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li2b') }}</li>
      <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li2c') }}</li>
      <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li2d') }}</li>
      <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li2e') }}</li>
    </ul>
    <a class="btn btn-radius btn-skin btn-lg signup-color" href="/register">{{ trans('home.landingbtn') }}</a>
  </div>
  <div class="col-lg-6 col-md-6 hidden-sm hidden-xs text-center">
    <br>
    <img id="feature-campaign" src="{{ url('/images/campaign.png') }}">
  </div>
</div>

<div class="row feature-row" id="refunds">
  <div class="col-lg-6 col-md-6 hidden-sm hidden-xs text-center">
    <br>
    <img id="feature-refund" src="{{ url('/images/refunds.png') }}">
  </div>
  <div class="col-md-6 col-sm-12 col-xs-12">
      <h3>{{ trans('home.feat_head3') }}</h3>
      <ul class="fa-ul">
        <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li3a') }}</li>
        <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li3b') }}</li>
        <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li3c') }}</li>
        <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li3d') }}</li>
      </ul>
      <a class="btn btn-radius btn-skin btn-lg signup-color" href="/register">{{ trans('home.landingbtn') }}</a>
  </div>
</div>

<div class="row feature-row" id="advertising">
  <div class="col-md-6 col-sm-12 col-xs-12">
      <h3>{{ trans('home.feat_head9') }}</h3>
      <ul class="fa-ul">
        <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li9a') }}</li>
        <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li9b') }}</li>
        <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li9c') }}</li>
      </ul>
      <a class="btn btn-radius btn-skin btn-lg signup-color" href="/register">{{ trans('home.landingbtn') }}</a>
  </div>
  <div class="col-md-6 hidden-sm hidden-xs text-center">
    <br><br>
    <img id="feature-advertising-analytics" src="{{ url('/images/advertising-analytics2.png') }}">
  </div>
</div>

<div class="row feature-row" id="sellerreviews">
  <div class="col-lg-6 col-md-6 hidden-sm hidden-xs text-left">
    <br>
    <img id="feature-reviews" src="{{ url('/images/seller-reviews.png') }}">
  </div>
  <div class="col-md-6 col-sm-12 col-xs-12">
    <h3>{{ trans('home.feat_head4') }}</h3>
    <ul class="fa-ul">
      <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li4a') }}</li>
      <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li4b') }}</li>
      <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li4c') }}</li>
      <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li4d') }}</li>
    </ul>
    <a class="btn btn-radius btn-skin btn-lg signup-color" href="/register">{{ trans('home.landingbtn') }}</a>
  </div>
</div>

<div class="row feature-row">
  <div class="col-md-6 col-md-6 col-sm-12 col-xs-12">
      <h3>{{ trans('home.feat_head5') }}</h3>
      <ul class="fa-ul">
        <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li5a') }}</li>
        <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li5b') }}</li>
        <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li5c') }}</li>
      </ul>
      <a class="btn btn-radius btn-skin btn-lg signup-color" href="/register">{{ trans('home.landingbtn') }}</a>
  </div>
  <div class="col-lg-6 col-md-6 hidden-sm hidden-xs text-center">
    <br><br>
    <img id="feature-product-review" src="{{ url('/images/product-reviews.png') }}">
  </div>
</div>

<hr>
<h2 class="text-center section-title text-orange">{{ trans('home.comingsoonfeature') }}</h2>

<div class="row feature-row" id="profitablity">
  <div class="col-lg-6 col-md-6 hidden-sm hidden-xs text-left">
    <img id="feature-profitability" src="{{ url('/images/profit-analytics.png') }}">
  </div>
  <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
      <h3>{{ trans('home.feat_head6') }}</h3>
    <ul class="fa-ul">
      <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li6a') }}</li>
      <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li6b') }}</li>
      <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li6c') }}</li>
      <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li6d') }}</li>
      <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li6e') }}</li>
    </ul>
  </div>
</div>

<div class="row feature-row" id="inventory">
  <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
      <h3>{{ trans('home.feat_head7') }}</h3>
      <ul class="fa-ul">
        <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li7a') }}</li>
        <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li7b') }}</li>
        <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li7c') }}</li>
        <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li7d') }}</li>
      </ul>
  </div>
  <div class="col-lg-6 col-md-6 hidden-sm hidden-xs text-center">
    <br>
    <img id="feature-inventory" src="{{ url('/images/inventory-analytics.png') }}">
  </div>
</div>

<div class="row feature-row" id="keyword">
  <div class="col-lg-6 col-md-6 hidden-sm hidden-xs text-center">
    <br><br>
    <img id="feature-keyword" src="{{ url('/images/advertising-analytics.png') }}">
  </div>
  <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
      <h3>{{ trans('home.feat_head8') }}</h3>
    <ul class="fa-ul">
      <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li8a') }}</li>
      <li class="feature-li"><i class="fa fa-check-square"></i>{{ trans('home.feat_li8b') }}</li>
    </ul>
  </div>
</div>

<hr>
