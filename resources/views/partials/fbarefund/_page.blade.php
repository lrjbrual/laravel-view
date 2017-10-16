<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/chosen/css/chosen.css')}}"/>
<div class="col-md-12">
  @if ($warn_bill == true)
    <div class="alert alert-warning m-t-5 button-rectangle">
      <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
      Please enter a valid payment method in the <a href="{{ url('billing') }}">Billing section</a> before activating this feature. Note: This feature is not included in the Free Trial
    </div>
  @endif
  <!-- {{--
  @if ($warn_pay_method == true)
    <div class="alert alert-warning m-t-5 button-rectangle"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Please update your payment method in <a href="{{ url('billing') }}">billing section</a></div>
  @endif
  --}}
  @if ($warn_pref_currency == true)
    <div class="alert alert-warning m-t-5 button-rectangle"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Please update your preferred currency in <a href="{{ url('billing') }}">billing section</a></div>
  @endif -->
  @if (Session::has('success'))
    <div class="alert alert-success m-t-5 button-rectangle"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>{{ Session::get('success') }}</div>
  @endif
  @if (Session::has('error'))
    <div class="alert alert-danger m-t-5 button-rectangle"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>{{ Session::get('error') }}</div>
  @endif

  <div class="m-t-15">
    FBA Refunds is an optional feature that is billed on top of your monthly base subscription. For more information please view this video or visit our <a style="color:#FF8000" target="_blank" href="">Help pages</a>.
    <br>
    <br>
    To activate or de-activate this optional feature, please toggle this button: 
    
    <input type="checkbox" id="refunds-switch" class="js-switch" {{ $active_checker }} />
    <span class="radio_switchery_padding" id="refunds-switch-msg"></span>
    @if ($active_checker == 'checked')
    {{-- <input type="submit" id="refunds-setup" value="Update Filing Preference" class="btn btn-warning m-l-10" data-toggle="modal" data-target="#refunds-setup-modal"> --}}
    @endif

  </div>
  
</div>

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
                {{-- <p>For more information, please visit our help section: <a style="color:#FF8000" target="_blank" href="http://help.trendle.io/">help.trendle.io</a></p> --}}
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

<div class="col-md-12 m-t-10">
  <div class="instructions">
    <h4 id="header">Instructions<span class="pull-right"><i class="fa fa-window-minimize toggleInstruction" id="action"></i></span></h4>
    <div id="msg">
      @if ($diy_checker == 'checked')

      For details on how to file cases, please visit our <a style="color:#FF8000" target="_blank" href="">help pages</a>

      @else

        To enable Trendle Analytics to file your refund cases on your behalf, please create a new user in your Seller Central with limited rights. Here is a step-by-step guide:
          <br><br>
          <strong>Set-up Amazon User with Limited Permissions</strong><br>
          <ol>
            <!-- <li>Login to <a class="text-link" href="#">Seller Central</a></li> -->
            <li>Log in to Seller Central in <a target="_blank" class="text-link" href="https://sellercentral.amazon.co.uk/">Europe</a> and/or in <a target="_blank" class="text-link" href="https://sellercentral.amazon.com/">North America</a>. Note: If you sell in both regions then you will need to do this process in both regions</li>
            <li>From your Seller Central account select on <strong>Settings</strong> then <strong>User Permissions</strong></li>
            <li>In the box at the top fill in the email address <strong>{{ $sc_email }}</strong> and select <strong>Send invitation</strong>.</li>
            <li>Press <strong>continue</strong> (Do not send invitation twice)</li>
            <li>Let us know in the chat (bottom right of your screen) that you've sent us the invitation. If we're online, we'll complete the steps needed on our side within a few minutes. If not, we'll send you a message the next working day so that you can proceed with step 6.</li>
            <li><strong>Confirm</strong> the new user inside the Pending Users list.</li>
            <li>Select the <strong>View/Edit</strong> permission icon next to the following sections:</li>
            <ul>
              <li>Reports section: <strong>Fulfillment Reports</strong> AND <strong>Payments</strong> AND <strong>Business Reports</strong></li>
          <li>Settings section: <b>Manage your cases</b></li>
            </ul>
            Note: These settings do not give us access to any of your payment information nor bank information nor the ability to change anything to your account.
            <li>After activating these limited permissions to view/edit, select <strong>Continue</strong> at the bottom of the page.</li>
            <li>Make sure you have the feature activated and a valid payment method registered in your Billing settings in Trendle Analytics</li>
            <li>Done! Our team will begin to claim the money owed to you.</li>
          </ol>

          Need a little help? Simply <a class="text-link" href="/contact" target="_BLANK">Contact us</a> and we will be happy to walk you through the process.
      @endif
    </div>
  </div>
</div>
<div class="row">
 <div class="col-md-12">
    @if( $countries )
      <div class="row container" id="country-cards-div">
      </div>
    @endif
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

@if ($diy_checker == 'checked')
  @include('partials.fbarefund._fba_diy_table')
@endif


<!-- Page level script -->
<script type="text/javascript">
  function getFBADetails(){
    var total_refund_raw = 0;
    var with_records = 0;

    var countries = '{{ $countries }}';
    var c_arr = countries.split('-');
    var currency = '{{ $preferred_currency }}';

    if(countries != ""){
    for (var i = 0; i < c_arr.length; i++){
      var country = c_arr[i];
      var div = document.getElementById('country-cards-div');
      div.innerHTML = div.innerHTML + '<div class="col-md-3 loading-card-'+country+'" style="margin-top:10px;"><div>';

      if(country){
          $('.loading-card-'+country).html("<h4>Loading card...</h4>");
      }

      var token = '{{ csrf_token() }}';
      var datas = "country="+country+"&_token="+token+"&currency="+currency;
      var new_card='';
      $.ajax({
        type: "POST",
        url: 'refund/getFBADetails',
        data: datas,
        success: function(result){

          response = JSON.parse(result);
          var cntry = response.country;
          total_refund_raw = total_refund_raw + parseFloat(response.total_refund_country);
          with_records = with_records + parseFloat(response.with_records);
          //for modal footer
          var container = document.getElementById("modal_footer_inside_form");
          var input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'amount_'+cntry;
          input.value= parseFloat(response.total_owed_to_collect);
          container.appendChild(input);

          new_card = '<div  class="refunds-marketplace">';
            new_card += '<div class="col-md-12 front refunds-card-normal">';
            new_card += '<h3 id="marketplace" class="text-center m-t-25">'+response.country_name+'</h3>';
            new_card += '<div id="outstanding" class="text-center">{{ $currency}}'+response.total_refund_country+'</div>';
            new_card += '<div id="lblamount" class="text-center">Total amount owed (outstanding)</div>';
            new_card += '<div id="view" class=" text-center">Click to View</div>';
            new_card += '<div id="spacer">&nbsp;</div>';
            new_card += '</div>';

            new_card += '<div class="col-md-12 back refunds-card-flip">';
            new_card += '<h4 id="marketplace" class="col-md-12 m-t-5">'+response.country_name+'</h4>';
            new_card += '<div class="col-md-12">';
            new_card += '<div class="row" id="line"></div>';
            new_card += '</div>';
            new_card += '<div class="col-md-8">Total amount owed<br>(outstanding)</div>';
            new_card += '<div class="col-md-4 text-right">{{ $currency }}'+response.total_refund_country+'</div>';

            new_card += '<div class="col-md-8">Fees To Date</div>';
            new_card += '<div class="col-md-4 text-right">{{ $currency }}'+response.total_refund_country_percent+'</div>';

            new_card += '<div class="col-md-8">Total amount recovered to date</div>';
            new_card += '<div class="col-md-4 text-right">{{ $currency }}'+response.total_reimbursed+'</div>';
            new_card += '</div>';
          new_card += '</div>';
          $('.loading-card-'+response.country).html("");
          
          $('.loading-card-'+response.country).html(new_card);

          $(".refunds-marketplace").flip({
              axis: 'x',
              trigger: 'click',
              forceHeight: false,
              forceWidth: false,
              autoSize: false
          });
        }
      });
    }
  }
    //loop for the footer
    var total_refund = total_refund_raw - parseFloat({{ $total_reimbursed }});
    var amount = total_refund*0.1;

    var container1 = document.getElementById("modal_footer_inside_form");
  }

var oTable;
var oTable1;
var oTable2;
var sellerData;
var selected_element;
var diff_value_oic;
var diff_value_sku;
var fba_mode;
var showModal = false;

function getFBASellers(param){
    var objToken = { _token: '{{ csrf_token() }}' };
    var objParam = {};
    var data = {};
    var url  = 'getfbasellers1';
    if (param) {
      objParam = param;
      url = 'fbarefundsellerfilter1';
    };
    data = Object.assign({}, objToken, objParam);
    $('.loading-seller-list').html("<img src='{{asset('assets/img/loader.gif')}}' style=' width: 15px;height:15px;' alt='Processing data...'>Processing data...");
    $.ajax({
      type: "POST",
      url: url,
      data: data,
      success: function(result){
        var response = jQuery.parseJSON(result);
        oTable = $('#seller_table_list').dataTable({
            "data": response,
            "bLengthChange": false,
            "bFilter": false,
            "destroy": true,
            "bPaginate": true,
            "paging": true,
            "ordering": true,
            "bInfo" : false,
            "scrollX": true,
            "scrollY": false,
            "scrollCollapse": true,
        });

        if (selected_element) {
            $('#seller_table_list').find("#"+selected_element).addClass('row_selected') 
        }
        $('.loading-seller-list').html("");
      }
    });
}
function getSellerDetails(seller_id,country,param,funcTrigger){
    if(!funcTrigger){ //load after click result from seller table
      getSellerOIC(seller_id,country,param);
      getSellerFNSKU(seller_id,country,param);
    }else if(funcTrigger == 'getSellerOIC'){ //load after filtering oic table
      getSellerOIC(seller_id,country,param);
    }else if(funcTrigger == 'getSellerFNSKU'){ //load after filtering sku table
      getSellerFNSKU(seller_id,country,param);
    }
}
function getSellerOIC(seller_id,country,param){
    var objToken = { _token: '{{ csrf_token() }}' };
    var objParam = {};
    var data = {};
    var url = 'getSellerOIC1';
    if (param) {
        objParam = param;
        url = 'getSellerOICFilter1';
    }else{
        objParam = {seller_id: seller_id, country:country};
    }
    data = Object.assign({}, objToken, objParam);
    $('.loading-oic-list').html("<img src='{{asset('assets/img/loader.gif')}}' style=' width: 15px;height:15px;' alt='Processing data...'>Processing data...");
    $.ajax({
      type: "POST",
      url: url,
      data: data,
      success: function(result){
        var response = jQuery.parseJSON(result);
        oTable1 = $('#seller_oic').dataTable({
            "data": response,
            "bLengthChange": false,
            "bFilter": false,
            "destroy": true,
            "bPaginate": true,
            "paging": true,
            "ordering": true,
            "bInfo" : false,
            "scrollX": true,
            "scrollY": false,
            "scrollCollapse": true,
        });

        oTable1.$('td:nth-child(5),td:nth-child(12)').editable( 'update_adminOIC1', {
            "callback": function( sValue, y ) {
                sValue = JSON.parse(sValue);

                var aPos = oTable1.fnGetPosition( this );
                oTable1.fnUpdate( sValue.value, aPos[0], aPos[1] );
                if (aPos[1] == 4) {
                  oTable1.fnUpdate( sValue.rid1, aPos[0], 5 );
                  oTable1.fnUpdate( sValue.rid2, aPos[0], 6 );
                  oTable1.fnUpdate( sValue.rid3, aPos[0], 7 );
                  oTable1.fnUpdate( sValue.tar, aPos[0], 8 );
                  oTable1.fnUpdate( sValue.dif, aPos[0], 9 );
                }
            },
            "submitdata": function ( value, settings ) {
                newval = $(this).find('input').val();
                return {
                    "row_id": this.parentNode.getAttribute('id'),
                    "order_id": $(this).closest("tr").children().eq(0).html(),
                    "claim_amount": $(this).closest("tr").children().eq(3).html(),
                    "column": oTable1.fnGetPosition( this )[2],
                    "newval": newval
                };
            },
            "height": "100%",
            "width": "100%"
        } );

        $('.loading-oic-list').html("");
      }
    });
}

function oicupdateStatus(element)
{
  var id = element.id;
  var value = element.value;

  $.ajax({
        url: "update_oicstatus1",
        type: "post",
        data: {
            "id": id,
            "value": value
        },
        success: function(response){
            var sellerName = $('#filterSellerName').val();
            var country = $('#filterCountry').val();
            param = { companyname: sellerName, country: country }
            getFBASellers(param);

            if (value != "Open") {
                diff_value_oic = $(element).parent().parent().parent().find('td:nth-child(10)').text();
                $(element).parent().parent().parent().find('td:nth-child(10)').html('0');
            }else{
                $(element).parent().parent().parent().find('td:nth-child(10)').html(diff_value_oic);
            }
        }
    });
}

function copyTextToClipboard(text) {
  var textArea = document.createElement("textarea");

  //
  // *** This styling is an extra step which is likely not required. ***
  //
  // Why is it here? To ensure:
  // 1. the element is able to have focus and selection.
  // 2. if element was to flash render it has minimal visual impact.
  // 3. less flakyness with selection and copying which **might** occur if
  //    the textarea element is not visible.
  //
  // The likelihood is the element won't even render, not even a flash,
  // so some of these are just precautions. However in IE the element
  // is visible whilst the popup box asking the user for permission for
  // the web page to copy to the clipboard.
  //

  // Place in top-left corner of screen regardless of scroll position.
  textArea.style.position = 'fixed';
  textArea.style.top = 0;
  textArea.style.left = 0;

  // Ensure it has a small width and height. Setting to 1px / 1em
  // doesn't work as this gives a negative w/h on some browsers.
  textArea.style.width = '2em';
  textArea.style.height = '2em';

  // We don't need padding, reducing the size if it does flash render.
  textArea.style.padding = 0;

  // Clean up any borders.
  textArea.style.border = 'none';
  textArea.style.outline = 'none';
  textArea.style.boxShadow = 'none';

  // Avoid flash of white box if rendered for any reason.
  textArea.style.background = 'transparent';


  textArea.value = text;

  document.body.appendChild(textArea);

  textArea.select();

  try {
    var successful = document.execCommand('copy');
    var msg = successful ? 'successful' : 'unsuccessful';
    console.log('Copying text command was ' + msg);
  } catch (err) {
    console.log('Oops, unable to copy');
  }

  document.body.removeChild(textArea);
}

function oicClip(element)
{
  var id = element.id;
  var order_id = id.substring(5);
  var status = $('#clipboard0-'+order_id).val();
  var type = $('#clipboard-'+order_id).val();
  var clip1 = $('#clipboard1-'+order_id).val();
  var clip2 = $('#clipboard2-'+order_id).val();
  var clip3 = $('#clipboard3-'+order_id).val();
  var clip4 = $('#clipboard4-'+order_id).val();
  var clip5 = $('#clipboard5-'+order_id).val();
  var clip6 = $('#clipboard6-'+order_id).val();
  var clip7 = $('#clipboard7-'+order_id).val();
  var clip8 = $('#clipboard8-'+order_id).val();
  var clip9 = $('#clipboard9-'+order_id).val();
  var clip10 = $('#clipboard10-'+order_id).val();
  var clip11 = $('#clipboard11-'+order_id).val();
  var clip12 = $('#clipboard12-'+order_id).val();
  var clip13 = $('#clipboard13-'+order_id).val();
  var clip14 = $('#clipboard14-'+order_id).val();
  var clip15 = $('#clipboard15-'+order_id).val();

  var currency = '';
  if (clip10 == 'us') {
    currency = 'USD';
  } else if (clip10 == 'ca') {
    currency = 'CAD';
  } else if (clip10 == 'uk') {
    currency = 'GBP';
  } else if (clip10 == 'fr' || clip10 == 'it' || clip10 == 'de' || clip10 == 'es') {
    currency = 'GBP';
  }

  if (status == 'Open' || !status || status == '') {
    if (clip8 == 'MISSED_ESTIMATED_DELIVERY' && clip9 == 0) {
      var msg1 = 'Hi,\n\nI\'ve noticed that several orders were refunded because Amazon delivered the items too late.\n\nYet, I am still being charged commission and FBA fees even though the mistake is completely Amazon\'s fault.\n\nI therefore would like to request that you issue a reimbursement for the amounts charged as fulfilment is your responsibility.\n\n';
      var msg2 = 'Order ID: '+order_id+'\nAmount I am owed due to Amazon\'s delayed delivery: ('+currency+') '+clip11+'\nThis amount is the amount I made from the initial sale minus the amount that was taken from my account for the refund: '+clip12+'\n\n';
      var msg3 = 'Please could you issue a Reimbursement ID to reflect this.\n\nThank you.\n\nRegards';
      var cc = msg1+msg2+msg3;
      copyTextToClipboard(cc);
    } else if (clip8 == 'MISSED_ESTIMATED_DELIVERY' && clip9 > 0) {
      var msg1 = 'Hi,\n\nI\'ve noticed that several orders were refunded because Amazon delivered the items too late.\n\nYet, I am still being charged commission and FBA fees even though the mistake is completely Amazon\'s fault.\n\nI therefore would like to request that you issue a reimbursement for the amounts charged as fulfilment is your responsibility.\n\n';
      var msg2 = 'Order ID: '+order_id+'\nAmount I am owed due to Amazon\'s delayed delivery: ('+currency+') '+clip11+'\nThis amount is the amount I made from the initial sale minus the amount that was taken from my account for the refund: '+clip12+'\n\n';
      var msg3 = 'Please could you issue a Reimbursement ID to reflect this.\n\nIn addition, a (or some) product(s) were returned in an unsellable condition. As this whole return reason is your responsibility, I would expect that you compensate me for these unsellable products regardless of the unsellable status as the customer may not have returned the products had the delivery been achieved on time.\n\nFnSKU(s): '+clip13+'\nQuantity Unsellable: '+clip14+'\nTotal value: '+clip15+'\n\nPlease could you issue a second unique Reimbursement ID to reflect this.\n\nThank you.\n\nRegards';
      var cc = msg1+msg2+msg3;
      copyTextToClipboard(cc);
    } else {
      if (type == 'Full') {
        var msg1 = 'Dear Seller Support,\n\nI have found entitled reimbursements on my account that I would like to be investigated:\n\nPlease find below details:\n\n';
        var msg2 = clip1+'\n'+clip2+'\n'+clip3+'\n\n';
        var msg3 = 'If I am correct, could you please provide me with a Reimbursement ID and reimbursement amount?\n\nThank you.\n\nRegards';
        var cc = msg1+msg2+msg3;
        copyTextToClipboard(cc);
      } else {
        var msg1 = 'Dear Seller Support,\n\nI\'ve noticed that you issued reimbursements to my account. Upon checking these, I believe that I am entitled to further reimbursement. By taking into account the last few months of sales I can determine from my settlement report that the amount I should be compensated for should be higher.\n\nPlease find below the detailed list:\n\n';
        var msg2 = clip1+'\n'+clip4+'\n'+clip5+'\n'+'Estimated amount owed: '+clip6+'\n'+clip7+'\n\n';
        var msg3 = 'Our estimate is an average of the \'total column\' of the settlement report based on either the last 3 months of sales or 2000 orders (whichever came first), if there have been no sales of this product in the last 3 months, I\'ve taken the last 18months of data as the reference. This, therefore, takes into account the average selling price and average Amazon fees.\n\nCould you please issue new reimbursement IDs for the amount of '+clip6+' to complement the missing amounts?\n\nThank you.\n\nRegards';
        var cc = msg1+msg2+msg3;
        copyTextToClipboard(cc);
      }
    }
    swal('','Copied to clipboard.','success');
  } else {
    swal('','This Order ID claim is closed.','error');
  }
}

function skuClip(element)
{
  var id = $(element).attr('data-clip-id');
  console.log(id);
  var status = $('#clipboard0-'+id).val();
  var is_third = $('#clipboard-'+id).val();
  var clip1 = $('#clipboard1-'+id).val();
  var clip2 = $('#clipboard2-'+id).val();
  var clip3 = $('#clipboard3-'+id).val();
  var clip4 = $('#clipboard4-'+id).val();
  var clip5 = $('#clipboard5-'+id).val();

  if (status == 'Open' || !status || status == '') {
    if (is_third == '1') {
      var msg1 = 'Dear Seller Support,\n\nI have found that some inventory items have been misplaced or damaged in you warehouses. Could you please investigate?\n\nPlease find below details:\n\n';
      var msg2 = clip1+'\n'+clip2+'\n'+clip3+'\n'+clip4+'\n'+clip5+'\n\n';
      var msg3 = 'If I am correct, could you please provide me with a Reimbursement ID and reimbursement amount?\n\nPlease note that this has happened since the last Reimbursement was issue for Lost or Damaged inventory, and therefore any surplus you might see in your tool is not considering the fact that the surplus exists because you are not including inventory adjustments older than 18months and the previous adjustment was to adjust a Lost or Damaged Inventory which is now more than 18months old.\n\nThank you.\n\nRegards';
      var cc = msg1+msg2+msg3;
      copyTextToClipboard(cc);
    } else {
      var msg1 = 'Dear Seller Support,\n\nI have found that some inventory items have been misplaced or damaged in you warehouses. Could you please investigate?\n\nPlease find below details:\n\n';
      var msg2 = clip1+'\n'+clip2+'\n'+clip3+'\n'+clip4+'\n'+clip5+'\n\n';
      var msg3 = 'If I am correct, could you please provide me with a Reimbursement ID and reimbursement amount?\n\nThank you.\n\nRegards';
      var cc = msg1+msg2+msg3;
      copyTextToClipboard(cc);
    }
    swal('','Copied to clipboard.','success');
  } else {
    swal('','This FnSKU Claim is closed.','error');
  }
}

function fnskuUpdateStatus(element)
{
  var id = element.id;
  var value = element.value;

  $.ajax({
        url: "update_fnskustatus1",
        type: "post",
        data: {
            "id": id,
            "value": value
        },
        success: function(response){
            var sellerName = $('#filterSellerName').val();
            var country = $('#filterCountry').val();
            param = { companyname: sellerName, country: country }
            getFBASellers(param);

            if (value != "Open") {
                diff_value_sku = $(element).parent().parent().parent().find('td:nth-child(10)').text();
                $(element).parent().parent().parent().find('td:nth-child(10)').html('0');
            }else{
                $(element).parent().parent().parent().find('td:nth-child(10)').html(diff_value_sku);
            }
        }
    });
}




function getSellerFNSKU(seller_id,country,param){
    var objToken = { _token: '{{ csrf_token() }}' };
    var objParam = {};
    var data = {};
    var url = 'getSellerFNSKU1';
    if (param) {
        objParam = param;
        url = 'getSellerFNSKUFilter1';
    }else{
        objParam = {seller_id: seller_id, country:country};
    }
    data = Object.assign({}, objToken, objParam);
    $('.loading-fnsku-list').html("<img src='{{asset('assets/img/loader.gif')}}' style=' width: 15px;height:15px;' alt='Processing data...'>Processing data...");
    $.ajax({
      type: "POST",
      url: url,
      data: data,
      success: function(result){
        var response = jQuery.parseJSON(result);
        oTable2 = $('#seller_fnsku').dataTable({
            "data": response,
            "bLengthChange": false,
            "bFilter": false,
            "destroy": true,
            "bPaginate": true,
            "paging": true,
            "ordering": true,
            "bInfo" : false,
            "scrollX": true,
            "scrollY": false,
            "scrollCollapse": true,
        });

        oTable2.$('td:nth-child(5),td:nth-child(12)').editable( 'update_adminFNSKU1', {
            "callback": function( sValue, y ) {
                sValue = JSON.parse(sValue);

                var aPos = oTable2.fnGetPosition( this );
                oTable2.fnUpdate( sValue.value, aPos[0], aPos[1] );
                if (aPos[1] == 4) {
                  oTable2.fnUpdate( sValue.rid1, aPos[0], 5 );
                  oTable2.fnUpdate( sValue.rid2, aPos[0], 6 );
                  oTable2.fnUpdate( sValue.rid3, aPos[0], 7 );
                  oTable2.fnUpdate( sValue.tar, aPos[0], 8 );
                  oTable2.fnUpdate( sValue.dif, aPos[0], 9 );
                }
            },
            "submitdata": function ( value, settings ) {
                newval = $(this).find('input').val();
                return {
                    "row_id": this.parentNode.getAttribute('id'),
                    "fnsku": $(this).closest("tr").children().eq(0).html(),
                    "total_owed": $(this).closest("tr").children().eq(3).html(),
                    "column": oTable2.fnGetPosition( this )[2],
                    "newval": newval
                };
            },
            "height": "100%",
            "width": "100%"
        } );

        $('.loading-fnsku-list').html("");
      }
    });
}

function showAll(funcTrigger){
  if(!funcTrigger){
    getFBASellers(0,'')
    getSellerDetails(false,false,false,'getSellerOIC');
    getSellerDetails(false,false,false,'getSellerFNSKU');
    $('.seller_info_text').html('');
    selected_element = '';
    sellerData = false;
  }else if(funcTrigger == 'getSellerOIC'){
    if (sellerData) {
      getSellerOIC(false,false,sellerData);
    }else{
      sweetAlert("No data to be filter", "Please select data from seller table first.", "error");
    }
  }else if(funcTrigger == 'getSellerFNSKU'){
    if (sellerData) {
      getSellerFNSKU(false,false,sellerData);
    }else{
      sweetAlert("No data to be filter", "Please select data from seller table first.", "error");
    }
  }
}

$(document).ready(function(){
    getFBADetails();
    getFBASellers();
    getSellerDetails(0,'');

    $('#seller_table_list').on('click', 'tr', function(){

        $("#seller_table_list tbody tr").removeClass('row_selected');
        $(this).addClass('row_selected');

        var oData = oTable.fnGetData(this);
        var id_seller = oData.id_seller;
        var country = oData.country;
        fba_mode = oData.fba_mode;
        // $(window).scrollTop($('#seller_tab').offset().top);
        getSellerDetails(id_seller,country);
        sellerData = { seller_id:id_seller, country:country };
        $('.seller_info_text').html('Country: '+oData[0]);

        selected_element = $(this).attr('id');
    });

    $('#seller_oic').on('click', 'tr', function(){
        $("#seller_oic tbody tr").removeClass('row_selected');
        $(this).addClass('row_selected');
    });

    $('#seller_fnsku').on('click', 'tr', function(){
        $("#seller_fnsku tbody tr").removeClass('row_selected');
        $(this).addClass('row_selected');
    });

    $(".filter-select").chosen({allow_single_deselect: true});

    $('.filter_txt_seller').click(function(){
        $('.seller_filter').toggle('fast');
    });

    $('.filter_txt_oic').click(function(){
        $('.oic_filter').toggle('fast');
    });

    $('.filter_txt_sku').click(function(){
        $('.sku_filter').toggle('fast');
    });

    $('#btnFilterSeller').click(function(){
      var sellerName = $('#filterSellerName').val();
      var country = $('#filterCountry').val();
      param = { companyname: sellerName, country: country }
      getFBASellers(param);
    });

    $('#btnFilterOic').click(function(){
      if (sellerData) {
        var orderId = $('#filterOicOrderId').val();
        var ticket = $('#filterOicTicket').val();
        var status = $('#filterOicStatus').val();
        param = { 
          orderid: orderId,
          support_ticket: ticket,
          status: status }
        param = Object.assign({}, sellerData, param);
        getSellerDetails(false,false,param,'getSellerOIC');
      }else{
        sweetAlert("No data to be filter", "Please select data from seller table first.", "error");
      }
      
    });

    $('#btnFilterSku').click(function(){
      if (sellerData) {
        var fnsku = $('#filterSkuOrderId').val();
        var ticket = $('#filterSkuTicket').val();
        var status = $('#filterSkuStatus').val();
        param = { 
          fnsku: fnsku,
          support_ticket: ticket,
          status: status }
        param = Object.assign({}, sellerData, param);
        getSellerDetails(false,false,param,'getSellerFNSKU');
      }else{
        sweetAlert("No data to be filter", "Please select data from seller table first.", "error");
      }
      
    });

});
</script>
<script src="{{ url('js/refunds.js') }}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/chosen/js/chosen.jquery.js')}}"></script>
