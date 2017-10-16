<?php
foreach($mkpdata as $key=>$d){
  $sid_token[$d->marketplace_id]['sid']=$d->mws_seller_id;
  $sid_token[$d->marketplace_id]['token']=$d->mws_auth_token;
}


// print_r($mkpdata);
// die();
$remove_state[1] = '';
$remove_state[2] = '';
$verified_state[1] = '';
$verified_state[2] = '';

if(!isset($sid_token[1]['sid'])){
  $sid_token[1]['sid']='';
  $sid_token[1]['token']='';
  $remove_state[1] = 'disabled';
}else{
  $verified_state[1] = 'disabled';
}


if(!isset($sid_token[2]['sid'])){
  $sid_token[2]['sid']='';
  $sid_token[2]['token']='';
  $remove_state[2] = 'disabled';
}else{
  $verified_state[2] = 'disabled';
}

if( count($seller_amz['eu']) > 0 AND count($seller_amz['na']) > 0 ){
  $amz_disabled = 'disabled';
  $status = "Connected to NA and EU regions.";
}else if( count($seller_amz['eu']) == 0 AND count($seller_amz['na']) > 0 ){
  $amz_disabled = '';
  $status = "Connected to NA region.";
}else if( count($seller_amz['eu']) > 0 AND count($seller_amz['na']) == 0 ){
  $amz_disabled = '';
  $status = "Connected to EU region.";
}else{
  $amz_disabled = "";
  $status = "";
}
?>


@include('trendle.marketplace.partials._amazondiv')
  <div class="row marketplaceCountainerTour">
  <div class="col-lg-6 col-md-6 col-sm-12 col-sm-12 ">
    <div class="panel panel-primary marketplace">
      <div class="panel-heading">Amazon North America</div>
      <div class="panel-body col-md-12">
          1. Go to<br><a style="text-decoration: underline;" href="https://sellercentral.amazon.com/gp/mws/registration/register.html?devAuth=1&ie=UTF8&signInPageDisplayed=1&" target="_blank" >https://sellercentral.amazon.com/gp/mws/registration/register.html?devAuth=1&ie=UTF8&signInPageDisplayed=1&</a><br/>
          2. Log in using your Amazon seller account<br/>
          3. Select <span style="font-weight: bold;">"I want to use an application to access my Amazon seller account with MWS."</span><br/>
          4. In the <span style="font-weight: bold;">Application name</span> field, enter: <span style="font-weight: bold;">Trendle Analytics</span><br/>
          5. In the <span style="font-weight: bold;">Application's Developer Account Number</span> field, enter: <span style="font-weight: bold;">6508-7072-4770</span><br/>
          6. Click <span style="font-weight: bold;">Next</span>.<br/>
          7. Accept the Amazon MWS License Agreement and click <span style="font-weight: bold;">Next</span>.<br/>
          8. Enter your MWS Auth Token and Seller ID below and click <span style="font-weight: bold;">Add</span>.<br/>

          <div class="row">
            <div class="form-group siddiv">
              <div class=" col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <input type="text" class="form-control input-sm sid" placeholder="Seller ID" value="<?php echo $sid_token[1]['sid']; ?>" />
              </div>
            </div>
          </div>

          <div class="row">
            <div class="form-group authtokendiv">
              <div class=" col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <input type="text" class="form-control input-sm authtoken" placeholder="Auth Token" value="<?php echo $sid_token[1]['token']; ?>" />
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="pull-right">
                <button class="btn btn-primary verify_sc" country="na" channel="1" <?php echo $verified_state[1]; ?> >Add</button>
                <button class="btn btn-danger button-rectangle remove_sc" country="na" channel="1" <?php echo $remove_state[1]; ?> >Remove</button>
              </div>
            </div>
          </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6 col-md-6 col-sm-12 col-sm-12">
    <div class="panel panel-primary marketplace">
      <div class="panel-heading">Amazon EU</div>
      <div class="panel-body col-md-12">
        <p>
          1. Go to<br><a style="text-decoration: underline;" href="https://sellercentral.amazon.co.uk/gp/mws/registration/register.html?devAuth=1&ie=UTF8&signInPageDisplayed=1&" target="_blank" >https://sellercentral.amazon.co.uk/gp/mws/registration/register.html?devAuth=1&ie=UTF8&signInPageDisplayed=1&</a><br/>
          2. Log in using your Amazon seller account<br/>
          3. Select <span style="font-weight: bold;">"I want to use an application to access my Amazon seller account with MWS."</span><br/>
          4. In the <span style="font-weight: bold;">Application name</span> field, enter: <span style="font-weight: bold;">Trendle Analytics</span><br/>
          5. In the <span style="font-weight: bold;">Application's Developer Account Number</span> field, enter: <span style="font-weight: bold;">4318-6168-8979</span><br/>
          6. Click <span style="font-weight: bold;">Next</span>.<br/>
          7. Accept the Amazon MWS License Agreement and click <span style="font-weight: bold;">Next</span>.<br/>
          8. Enter your MWS Auth Token and Seller ID below and click <span style="font-weight: bold;">Add</span>.<br/>

            <div class="row">
              <div class="form-group siddiv">
                <div class=" col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <input type="text" class="form-control input-sm sid" placeholder="Seller ID" value="<?php echo $sid_token[2]['sid']; ?>" />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="form-group authtokendiv">
                <div class=" col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <input type="text" class="form-control input-sm authtoken" placeholder="Auth Token" value="<?php echo $sid_token[2]['token']; ?>" />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="pull-right">
                  <button class="btn btn-primary verify_sc" country="eu" channel="2" <?php echo $verified_state[2]; ?> >Add</button>
                  <button class="btn btn-danger button-rectangle remove_sc" country="eu" channel="2" <?php echo $remove_state[2]; ?> >Remove</button>
                </div>
              </div>
            </div>
          </div>
        </p>
      </div>
    </div>
  </div>
  <br>
  <div class="row">
  <div class="col-lg-6 col-md-6 col-sm-12 col-sm-12 login_amazon">
    <div class="panel panel-primary marketplace ">
      <div class="panel-heading">Login With Amazon For Advertising Module</div>
      <div class="panel-body col-md-12">
        <center>
          <br>
          <a href="#" id="LoginWithAmazon" class="btn {{ $amz_disabled }}">
            <img border="0" alt="Login with Amazon"
              src="https://images-na.ssl-images-amazon.com/images/G/01/lwa/btnLWA_gold_156x32.png"
              width="156" height="32" />
          </a>
          <br>
          <span class="text-success"> {{ $status }} <br></span>
          <br>
        </center>
      </div>
    </div>
  </div>
  </div>
<script type="text/javascript">

  document.getElementById('LoginWithAmazon').onclick = function() {
    options = { scope : 'cpc_advertising:campaign_management profile', response_type: 'code' };
    amazon.Login.authorize(options, '{{ url('/marketplace/auth_amz_account') }}');
    return false;
  };

  $(document).ready(function() {
    var flash = "{{ session('status') }}";
    var title = ""; // 'Failed!' or 'Success!'
    var flag = false;
    if(flash == "Connected to Amazon NA and EU regions!" || flash == 'Connected to Amazon NA region! You can login again to connect your EU region.' || flash == 'Connected to Amazon EU region! You can login again to connect your NA region.' ){
      flag = true;
      title = 'Success!';
    }
    if(flash == 'The email is not connected to any region!' || flash == "The credentials you entered could not be verified. Please make sure the log in details and correct, valid and active in your seller central account. Then try again. If the error persists, contact us and we'll take a look."){
      flag = true;
      title = 'Failed!';
    }
    if(flag){
      swal({
          title: title,
          text: flash,
          confirmButtonColor: '#4fb7fe'
      }).done();
      return false;
    }
  });

</script>
