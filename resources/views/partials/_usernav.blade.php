
<!-- .navbar -->
 <nav class="navbar navbar-static-top">
    <div class="container-fluid">
        <a class="navbar-brand text-xs-center" href="/">
            <h3>
              <span id="nav-color-orange">Trendle</span>
              <span id="nav-color-blue">Analytics</span>
            </h3>
        </a>
        <div class="menu">
            <span class="toggle-left" id="menu-toggle">
                <i class="fa fa-bars"></i>
            </span>
        </div>
        <div class="topnav dropdown-menu-right float-xs-right">
          <div class="btn-group">
                <div class="user-settings no-bg">
                    <button type="button" class="btn btn-default no-bg micheal_btn" data-toggle="dropdown">
                        <strong>{{ Auth::user()->email }}</strong>
                        <span class="fa fa-sort-down white_bg"></span>
                    </button>
                    <div class="dropdown-menu admire_admin">
                        <a class="dropdown-item" href="{{ url('/logout') }}" onclick="event.preventDefault();
                                  document.getElementById('logout-form').submit();"><i class="fa fa-sign-out"></i>
                            Log Out
                            <form id="logout-form" action="{{ url('/logout') }}" method="POST">
                              {{ csrf_field() }}
                            </form>
                        </a>
                    </div>
                </div>
            </div>
        </div>
      </div>
    <!-- /.container-fluid
<!-- /.navbar -->
<!-- /.head -->
</div>
<!-- /#top -->
<div class="wrapper">
<div id="left">
    <div class="menu_scroll">
        <div class="left_media">
            <div class="media user-media bg-dark dker">
              <div class="user-info menu_hide col-md-12 text-center text-white">
                  {{ Auth::user()->seller->company }}
              </div>
            </div>
            <hr/>
        </div>
        <ul id="menu">
            <li {!! (Request::is('home')? 'class="active"':"") !!}>
                <a href="{{ URL::to('home') }} ">
                    <i class="fa fa-home"></i>
                    <span class="link-title menu_hide">&nbsp;Dashboard</span>
                </a>
            </li>
            <li {!! (Request::is('refund')? 'class="fbaRefundNav active"':'class="fbaRefundNav"') !!}>
                <a href="{{ URL::to('refund') }} ">
                    <i><img class="fa refunds-small-icon" src="{{ url('/images/icons/refunds.png') }}"></i>
                    <span class="link-title menu_hide">&nbsp;FBA Refunds</span>
                </a>
            </li>
            @if (Auth::user()->seller->is_trial == 1 || Auth::user()->seller->basesubscription->count() > 0)
            <!-- <li {!! (Request::is('sellerreview')? 'class="active"':"") !!}>
                <a href="{{ URL::to('sellerreview') }} ">
                    <i class="fa fa-star"></i>
                    <span class="link-title menu_hide">&nbsp;Seller Reviews</span>
                </a>
            </li> -->
            <li {!! (Request::is('sellerreview') || Request::is('productreview') ? 'class="active"':"") !!}>
                <a href="#">
                    <i class="fa fa-star"></i>
                    <span class="link-title menu_hide">&nbsp;Reviews</span>
                    <span class="fa arrow menu_hide"></span>
                </a>
                <ul>
                    <li {!! (Request::is('sellerreview') ? 'class="active"':"") !!}>
                        <a href="{{ URL::to('sellerreview') }} ">
                            <i class="fa fa-angle-right"></i>
                            <span class="link-title menu_hide">&nbsp;Seller</span>
                        </a>
                    </li>
                    <li {!! (Request::is('productreview') ? 'class="active"':"") !!}>
                        <a href="{{ URL::to('productreview') }} ">
                            <i class="fa fa-angle-right"></i>
                            <span class="link-title menu_hide">&nbsp;Product</span>
                        </a>
                    </li>
                </ul>
            </li>
            @endif
            <li {!! (Request::is('campaign')? 'class="automaticEmailNav active"':'class="automaticEmailNav"') !!}>
                <a href="{{ URL::to('campaign') }} ">
                    <i class="fa fa-envelope-o"></i>
                    <span class="link-title menu_hide">&nbsp;Automatic Emails</span>
                </a>
            </li>
            <li {!! (Request::is('pnl')? 'class="active"':"") !!}>
                <a href="{{ URL::to('pnl') }} ">
                    <i><img class="fa refunds-small-icon" src="{{ url('/images/icons/profitability-analytics.png') }}"></i>
                    <span class="link-title menu_hide">&nbsp;P&L</span>
                </a>
            </li>
                    <!-- <li {!! (Request::is('adsperformance') || Request::is('productscosts') || Request::is('adsrecommendation') ? 'class="active"' : '') !!}>
                        <a href="#">
                            <i><img class="fa refunds-small-icon" src="{{ url('/images/icons/ads-analytics-icon.png') }}"></i>
                            <span class="link-title menu_hide">&nbsp; Advertising</span>
                            <span class="fa arrow menu_hide"></span>
                        </a>
                        <ul>
                            <li {!! (Request::is('adsperformance')? 'class="active"':"") !!}>
                                <a href="{{ URL::to('adsperformance') }} ">
                                    <i class="fa fa-angle-right"></i>
                                    &nbsp; Performance
                                </a>
                            </li>
                        </ul>
                    </li> -->
                    <li {!! (Request::is('adsperformance') || Request::is('productscosts') || Request::is('adsrecommendation') || Request::is('campaignmanager') ? 'class="active"' : '') !!}>
                        <a href="#">
                            <i><img class="fa refunds-small-icon" src="{{ url('/images/icons/ads-analytics-icon.png') }}"></i>
                            <span class="link-title menu_hide">&nbsp; Advertising</span>
                            <span class="fa arrow menu_hide"></span>
                        </a>
                        <ul>
                            <li {!! (Request::is('adsperformance')? 'class="active"':"") !!}>
                                <a href="{{ URL::to('adsperformance') }} ">
                                    <i class="fa fa-angle-right"></i>
                                    &nbsp; Performance
                                </a>
                            </li>
                            <li {!! (Request::is('productscosts')? 'class="active"':"") !!}>
                                <a href="{{ URL::to('productscosts') }} ">
                                    <i class="fa fa-angle-right"></i>
                                    &nbsp; Products Costs
                                </a>
                            </li>
                            <li {!! (Request::is('adsrecommendation')? 'class="active"':"") !!}>
                                <a href="{{ URL::to('adsrecommendation') }} ">
                                    <i class="fa fa-angle-right"></i>
                                    &nbsp; Recommendation
                                </a>
                            </li>
                            <li {!! (Request::is('campaignmanager')? 'class="active"':"") !!}>
                                <a href="{{ URL::to('campaignmanager') }} ">
                                    <i class="fa fa-angle-right"></i>
                                    &nbsp; Campaign Manager
                                </a>
                            </li>
                        </ul>
                    </li>

            <li {!! (Request::is('company') || Request::is('marketplace') || Request::is('billing') || Request::is('subscription') ? 'class="settingsNav settingsNavTour active"'  : 'class="settingsNav settingsNavTour"') !!}>
                <a href="#">
                    <i class="fa fa-gear"></i>
                    <span class="link-title menu_hide">&nbsp; Settings</span>
                    <span class="fa arrow menu_hide"></span>
                </a>
                <ul>
                    <li {!! (Request::is('company')? 'class="active"':"") !!}>
                        <a href="{{ URL::to('company') }} ">
                            <i class="fa fa-angle-right"></i>
                            &nbsp; Account Settings
                        </a>
                    </li>
                    <li {!! (Request::is('marketplace')? 'class="marketPlaceTour active"':'class="marketPlaceTour"') !!}>
                        <a href="{{ URL::to('marketplace') }} ">
                            <i class="fa fa-angle-right"></i>
                            &nbsp; Marketplaces
                        </a>
                    </li>
                    <li {!! (Request::is('billing')? 'class="billingTourNav active"':'class="billingTourNav"') !!}>
                        <a href="{{ URL::to('billing') }} ">
                            <i class="fa fa-angle-right"></i>
                            &nbsp; Billing
                        </a>
                    </li>
                    <li {!! (Request::is('subscription')? 'class="subscriptionTourNav active"':'class="subscriptionTourNav"') !!}>
                        <a href="{{ URL::to('subscription') }} ">
                            <i class="fa fa-angle-right"></i>
                            &nbsp; Subscription
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
        <!-- /#menu -->
    </div>
</div>
<!-- /#left -->
