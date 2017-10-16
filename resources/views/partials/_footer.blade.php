<footer id="top-footer">
 		<section class="container">
 			<div class="row">
 				<div class="col-md-3 col-sm-2 col-xs-12">
          <a class="navbar-brand font-logo"  href="/">
            <span id="nav-color-orange">Trendle</span>
            <span id="nav-color-blue">Analytics</span>
          </a>
 				</div>
 				<div class="col-md-4 col-sm-4 col-xs-12">
 					<h4 class="heading">{{ trans('home.footer_disclaimer') }}</h4>
 					<p class="white-text">{{ trans('home.footer_disclaimer_msg') }}</p>
 				</div>
 				<div class="col-md-2 col-sm-2 col-xs-12">
 					<ul class="ul-format-1">
 						<!-- <li>
 							<a href="/about" class="link-color">About Us</a>
 						</li> -->
 						<li>
 							<a href="/contact" class="link-color">Contact Us</a>
 						</li>
 						<li>
 							<a href="http://help.trendle.io/" target="_blank" class="link-color">FAQs</a>
 						</li>
 						<li>
 							<a href="{{ url('/pdf/trendle_analytics_privacy_policy.pdf') }}" target="_blank" class="link-color">Privacy Policy</a>
 						</li>
            <li>
              <a href="{{ url('/pdf/trendle_analytics_terms_and_conditions.pdf') }}" target="_blank" class="link-color">Terms & Conditions</a>
            </li>
            <li>
              <a href="https://trendlecheck.tapfiliate.com/programs/trendle-1/signup/" class="link-color">Affiliate</a>
            </li>
 					</ul>
 				</div>
 				<div class="col-md-3 col-sm-3 col-xs-12 text-center">
   					<h4 class="heading text-center">{{ trans('home.footer_newsletter') }}</h4>
  					<input type="email" class="form-control input-darkest" placeholder="{{ trans('home.footer_subscribe_placeholder') }}..." name="fld-email" id="fld-email">
  					<a class="btn btn-md-12 btn-sm-12 btn-subscribe">{{ trans('home.footer_subscribe') }}</a>
            <!-- <i class="credit-card fa fa-cc-amex fa-3x" aria-hidden="true"></i> -->
            <!-- <i class="credit-card fa fa-cc-mastercard fa-3x" aria-hidden="true"></i>
            <i class="credit-card fa fa-cc-discover fa-3x" aria-hidden="true"></i>
            <i class="credit-card fa fa-cc-stripe fa-3x" aria-hidden="true"></i> -->
   				</div>
   			</div>
   			<div class="row">
   				<div class="col-md-12 col-sm-12 col-xs-12" id="copyright">
   					<span>{{ trans('home.footer_copyright') }}</span>
   				</div>
   			</div>
 		</section>
 	</footer>
