@extends('layouts.admin')
@section('title', '| Merchant Refund')

@section('user')
<script type="text/javascript">
var oTable;
var oTable1;
var oTable2;
var sellerData;
var selected_element;
var diff_value_oic;
var diff_value_sku;
var fba_mode;

function updateFulfillmentCountry(id,fulfillment_center_id){
    var token = '{{ csrf_token() }}';
    var c = $('#f_country_'+fulfillment_center_id).val();
    var datas = "_token="+token+"&id="+id+"&country_code="+c+"&fulfillment_center_id="+fulfillment_center_id;
    $.ajax({
      type: "POST",
      url: 'updateFulfillmentCenter',
      data: datas,
      success: function(result){
      }
    });
}
function getFulfillmentCenters(){
    $('.loading-table').html(" Initializing table ... ");
    var token = '{{ csrf_token() }}';
    var datas = "_token="+token;
    $.ajax({
      type: "POST",
      url: 'getFulfillmentCenters',
      data: datas,
      success: function(result){
        var response = jQuery.parseJSON(result);
        $('#fulfillment_center_table').dataTable({
            "data": response,
            "bLengthChange": false,
            "bFilter": false,
            "destroy": true,
            "bPaginate": false,
            "paging": false,
            "bInfo" : true,
            "scrollY": "450px",
            "scrollCollapse": true,
        });

        $('.loading-table').html("");
      }
    });
}
function getFBASellers(param){
    var objToken = { _token: '{{ csrf_token() }}' };
    var objParam = {};
    var data = {};
    var url  = 'getfbasellers';
    if (param) {
      objParam = param;
      url = 'fbarefundsellerfilter';
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
        });

        oTable.$('td:nth-child(4),td:nth-child(5),td:nth-child(6),td:nth-child(11)').editable( 'update_adminsellers', {
            "callback": function( sValue, y ) {
                var aPos = oTable.fnGetPosition( this );
                oTable.fnUpdate( sValue, aPos[0], aPos[1] );
            },
            "submitdata": function ( value, settings ) {
                newval = $(this).find('input').val();
                return {
                    "row_id": this.parentNode.getAttribute('id'),
                    "column": oTable.fnGetPosition( this )[2],
                    "newval": newval
                };
            },
            "height": "100%",
            "width": "100%"
        } );

        if (selected_element) {
            $('#seller_table_list').find("#"+selected_element).addClass('row_selected')
        }
        $('.loading-seller-list').html("");

        $('.fbaSellerContainer .dataTable').wrap('<div class="dataTables_scroll" />');

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
    var url = 'getSellerOIC';
    if (param) {
        objParam = param;
        url = 'getSellerOICFilter';
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
            createdRow: function( row, data, dataIndex ) {
                $(row).children(':nth-child(17)').find('.supportTicketCell').click(function(){
                    var elemClass = $(this);
                    var inputType = 'textbox'
                    var val = { 'val': '' }
                    var textToDisplay = 'Click to edit'
                    var nthChild = $(row).children(':nth-child(17)')
                    var url = 'update_adminOIC'
                    var column = 13
                    var from = 'order'
                    var data =  { 
                                    row_id: $(this).parent().parent().attr('id'),
                                    order_id: $(this).closest("tr").children().eq(0).html(),
                                    claim_amount: $(this).closest("tr").children().eq(15).html(),
                                    column: column,
                                }
                    generateInput(inputType,val,elemClass,textToDisplay,nthChild,url,data,column,from)
                    $(this).hide()
                })

                $(row).children(':nth-child(18)').find('.supportTicketCell2').click(function(){
                    var elemClass = $(this);
                    var inputType = 'textbox'
                    var val = { 'val': '' }
                    var textToDisplay = 'Click to edit'
                    var nthChild = $(row).children(':nth-child(18)')
                    var url = 'update_adminOIC'
                    var column = 14
                    var from = 'order'
                    var data =  { 
                                    row_id: $(this).parent().parent().attr('id'),
                                    order_id: $(this).closest("tr").children().eq(0).html(),
                                    claim_amount: $(this).closest("tr").children().eq(15).html(),
                                    column: column,
                                }
                    generateInput(inputType,val,elemClass,textToDisplay,nthChild,url,data,column,from)
                    $(this).hide()
                })

                $(row).children(':nth-child(25)').find('.commentCell').click(function(){
                    var elemClass = $(this);
                    var inputType = 'textbox'
                    var val = { 'val': '' }
                    var textToDisplay = 'Click to edit'
                    var nthChild = $(row).children(':nth-child(25)')
                    var url = 'update_adminOIC'
                    var column = 20
                    var from = 'order'
                    var data =  { 
                                    row_id: $(this).parent().parent().attr('id'),
                                    order_id: $(this).closest("tr").children().eq(0).html(),
                                    claim_amount: $(this).closest("tr").children().eq(15).html(),
                                    column: column,
                                }
                    generateInput(inputType,val,elemClass,textToDisplay,nthChild,url,data,column,from)
                    $(this).hide()
                })
            }
        });

        // if (fba_mode == 'DIY') {
        //   oTable1.fnSetColumnVis(1, false);
        //   oTable1.fnSetColumnVis(2, false);
        //   oTable1.fnSetColumnVis(3, false);
        //   oTable1.fnSetColumnVis(4, false);
        //   oTable1.fnSetColumnVis(5, false);
        //   oTable1.fnSetColumnVis(6, false);
        //   oTable1.fnSetColumnVis(7, false);
        //   oTable1.fnSetColumnVis(8, false);
        //   oTable1.fnSetColumnVis(9, false);
        // } else {          
        //   oTable1.fnSetColumnVis(1, true);
        //   oTable1.fnSetColumnVis(2, true);
        //   oTable1.fnSetColumnVis(3, true);
        //   oTable1.fnSetColumnVis(4, true);
        //   oTable1.fnSetColumnVis(5, true);
        //   oTable1.fnSetColumnVis(6, true);
        //   oTable1.fnSetColumnVis(7, true);
        //   oTable1.fnSetColumnVis(8, true);
        //   oTable1.fnSetColumnVis(9, true);
        // }

        $('.loading-oic-list').html("");

        $('.orderIdContainer .dataTable').wrap('<div class="dataTables_scroll" />');
      }
    });
}

function oicupdateStatus(element)
{
  var id = element.id;
  var value = element.value;

  $.ajax({
        url: "update_oicstatus",
        type: "post",
        data: {
            "id": id,
            "value": value
        },
        success: function(response){
            var sellerName = $('#filterSellerName').val();
            var country = $('#filterCountry').val();
            var clipBtn = $(element).parent().parent().parent().find('td:nth-child(1)');
                clipBtn = $(clipBtn).find('#clipboard0-'+id);
            param = { companyname: sellerName, country: country }
            getFBASellers(param);

            if (value != "Open") {
                diff_value_oic = $(element).parent().parent().parent().find('td:nth-child(22)').text();
                $(element).parent().parent().parent().find('td:nth-child(22)').html('0');
                $(clipBtn).val('closed');
            }else{
                $(element).parent().parent().parent().find('td:nth-child(22)').html(diff_value_oic);
                $(clipBtn).val('Open');
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
    if (clip13.indexOf("AMZ fault & Product Not Returned") != -1) {
      var msg1 = 'Dear Seller Support,\n\nI have noticed an issue with a recent order which I would like you to investigate as it qualifies for a reimbursement which I have not yet received.\n\n';
      var msg2 = 'Here are the details:\nOrder ID: '+order_id+'\nFees due to be reimbursed: '+clip11+'\nQuantity Unsellable: '+clip9+'\nItem value: '+clip15+'\nTotal Amount Due: '+clip11+'\nReason: '+clip8+'\nDisposition: '+clip3+'\n\n';
      var msg3 = 'The amount due shown above is the sum of Amazon fees for this order and of the value of the item. As it is Amazon\'s responsibility to ensure timely and accurate delivery of my products, I should not be penalised for Amazon\'s error. In addition, following Amazon\'s mistake, my product has not been returned to inventory which means I have been financially penalised for you error and have lost the opportnity to sell this product to another customer. Had Amazon delivered the item correctly and timely my product we would not be in this situation and I would be able to sell it on to another customer. Therefore I would like to be reimbursed both for the fees incurred during shipment and refund to the customer as well as for the value of the item.\n\nTherefore, could you please provide me with a Reimbursement ID and reimbursement amount as stated above?\n\nMany thanks.\n\nKind Regards';
      var cc = msg1+msg2+msg3;
      copyTextToClipboard(cc);
    } else if (clip13.indexOf("AMZ fault & Returned Unsellable") != -1) {
      var msg1 = 'Dear Seller Support,\n\nI have noticed an issue with a recent order which I would like you to investigate as it qualifies for a reimbursement which I have not yet received.\n\n';
      var msg2 = 'Here are the details:\nOrder ID: '+order_id+'\nFees due to be reimbursed: '+clip11+'\nQuantity Unsellable: '+clip9+'\nItem value: '+clip15+'\nTotal Amount Due: '+clip11+'\nReason: '+clip8+'\nDisposition: '+clip3+'\n\n';
      var msg3 = 'The amount due shown above is the sum of Amazon fees for this order and of the value of the item. As it is Amazon\'s responsibility to ensure timely and accurate delivery of my products, I should not be penalised for Amazon\'s error. In addition, had Amazon delivered the item correctly and timely my product would not be in an Unsellable condition and I would be able to sell it on to another customer. However, my product was damaged whilst in your care and therefore I would like to be reimbursed both for the fees incurred during shipment and return as well as for the value of the item.\n\nTherefore, could you please provide me with a Reimbursement ID and reimbursement amount as stated above?\n\nMany thanks.\n\nKind Regards';
      var cc = msg1+msg2+msg3;
      copyTextToClipboard(cc);
    } else if (clip13.indexOf("AMZ fault") != -1) {
      var msg1 = 'Dear Seller Support,\n\nI have noticed an issue with a recent order which I would like you to investigate as it qualifies for a reimbursement which I have not yet received.\n\n';
      var msg2 = 'Here are the details:\nOrder ID: '+order_id+'\nAmount Due: '+clip11+'\nReason: '+clip8+'\nDisposition: '+clip3+'\n\n';
      var msg3 = 'The amount due shown above is the sum of Amazon fees for this order. As it is Amazon\'s responsibility to ensure timely and accurate delivery of my products, I should not be penalised for Amazon\'s error.\n\nTherefore, could you please provide me with a Reimbursement ID and reimbursement amount as stated above?\n\nMany thanks.\n\nKind Regards';
      var cc = msg1+msg2+msg3;
      copyTextToClipboard(cc);
    } else {
      if (clip3 == 'Larger Refund than Price Paid') {
        var msg1 = 'Dear Seller Support,\n\nI\'ve notice that a customer was issued a refund greater than what they intially paid and I\'m being charged for it.\n\nCould you please review and issue me with an adjustment reimbursement?\n\n';
        var msg2 = 'Order ID: '+order_id+'\n\n';
        var msg3 = 'Many thanks.\n\nRegards';
        var cc = msg1+msg2+msg3;
        copyTextToClipboard(cc);
      } else {
        if (type == 'Full') {
          var msg1 = 'Dear Seller Support,\n\nI have found entitled reimbursements on my account that I would like to be investigated:\n\nPlease find below details:\n\n';
          var msg2 = clip1+'\n'+clip2+'\nReason: '+clip3+'\n\n';
          var msg3 = 'If I am correct, could you please provide me with a Reimbursement ID and reimbursement amount?\n\nThank you.\n\nRegards';
          var cc = msg1+msg2+msg3;
          copyTextToClipboard(cc);
        } else {
          var msg1 = 'Dear Seller Support,\n\nI\'ve noticed that you issued reimbursements to my account. Upon checking these, I believe that I am entitled to further reimbursement. By taking into account the last few months of sales I can determine from my settlement report that the amount I should be compensated for should be higher.\n\nPlease find below the detailed list:\n\n';
          var msg2 = clip1+'\n'+clip4+'\n'+clip5+'\n'+'Estimated amount owed: '+clip15+'\n'+clip7+'\n\n';
          var msg3 = 'Our estimate is an average of the \'total column\' of the settlement report based on either the last 3 months of sales or 2000 orders (whichever came first), if there have been no sales of this product in the last 3 months, I\'ve taken the last 18months of data as the reference. This, therefore, takes into account the average selling price and average Amazon fees.\n\nCould you please issue new reimbursement IDs for the amount of '+clip15+' to complement the missing amounts?\n\nThank you.\n\nRegards';
          var cc = msg1+msg2+msg3;
          copyTextToClipboard(cc);
        }
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
      var msg3 = 'To verify this you will need to look at the adjustments made since the last reimbursement was issued for this FnSKU for Lost inventory. You will see that since then there have been more cases of Lost Items as of Found Items.\nIt\'s important that you look at it this way, because if you look at the 18months period this will be confusing as you will be ignoring data older than 18months that were previously used to issue the previous reimbursements for lost items.\n\nIf I am correct, could you please provide me with a Reimbursement ID and reimbursement amount?\n\nThank you.\n\nRegards';
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
        url: "update_fnskustatus",
        type: "post",
        data: {
            "id": id,
            "value": value
        },
        success: function(response){
            var sellerName = $('#filterSellerName').val();
            var country = $('#filterCountry').val();
            var clipBtn = $(element).parent().parent().parent().find('td:nth-child(1)');
                clipBtn = $(clipBtn).find('#clipboard0-'+id);

            param = { companyname: sellerName, country: country }
            getFBASellers(param);

            if (value != "Open") {
                diff_value_sku = $(element).parent().parent().parent().find('td:nth-child(25)').text();
                $(element).parent().parent().parent().find('td:nth-child(25)').html('0');
                $(clipBtn).val('closed');
            }else{
                $(element).parent().parent().parent().find('td:nth-child(25)').html(diff_value_sku);
                $(clipBtn).val('Open');
            }
        }
    });
}




function getSellerFNSKU(seller_id,country,param){
    var objToken = { _token: '{{ csrf_token() }}' };
    var objParam = {};
    var data = {};
    var url = 'getSellerFNSKU';
    if (param) {
        objParam = param;
        url = 'getSellerFNSKUFilter';
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
            createdRow: function( row, data, dataIndex ) {
                $(row).children(':nth-child(22)').find('.supportTicketCell').click(function(){
                    var elemClass = $(this);
                    var inputType = 'textbox'
                    var val = { 'val': '' }
                    var textToDisplay = 'Click to edit'
                    var nthChild = $(row).children(':nth-child(22)')
                    var url = 'update_adminFNSKU'
                    var column = 19
                    var from = 'fnsku'
                    var data =  { 
                                    row_id: $(this).parent().parent().attr('id'),
                                    order_id: $(this).closest("tr").children().eq(0).html(),
                                    claim_amount: $(this).closest("tr").children().eq(14).html(),
                                    column: column,
                                }
                    generateInput(inputType,val,elemClass,textToDisplay,nthChild,url,data,column,from)
                    $(this).hide()
                })

                $(row).children(':nth-child(23)').find('.supportTicketCell2').click(function(){
                    var elemClass = $(this);
                    var inputType = 'textbox'
                    var val = { 'val': '' }
                    var textToDisplay = 'Click to edit'
                    var nthChild = $(row).children(':nth-child(23)')
                    var url = 'update_adminFNSKU'
                    var column = 21
                    var from = 'fnsku'
                    var data =  { 
                                    row_id: $(this).parent().parent().attr('id'),
                                    order_id: $(this).closest("tr").children().eq(0).html(),
                                    claim_amount: $(this).closest("tr").children().eq(14).html(),
                                    column: column,
                                }
                    generateInput(inputType,val,elemClass,textToDisplay,nthChild,url,data,column,from)
                    $(this).hide()
                })

                $(row).children(':nth-child(30)').find('.commentCell').click(function(){
                    var elemClass = $(this);
                    var inputType = 'textbox'
                    var val = { 'val': '' }
                    var textToDisplay = 'Click to edit'
                    var nthChild = $(row).children(':nth-child(30)')
                    var url = 'update_adminFNSKU'
                    var column = 26
                    var from = 'fnsku'
                    var data =  { 
                                    row_id: $(this).parent().parent().attr('id'),
                                    order_id: $(this).closest("tr").children().eq(0).html(),
                                    claim_amount: $(this).closest("tr").children().eq(14).html(),
                                    column: column,
                                }
                    generateInput(inputType,val,elemClass,textToDisplay,nthChild,url,data,column,from)
                    $(this).hide()
                })
            }
        });

        // if (fba_mode == 'DIY') {
        //   oTable2.fnSetColumnVis(1, false);
        //   oTable2.fnSetColumnVis(2, false);
        //   oTable2.fnSetColumnVis(3, false);
        //   oTable2.fnSetColumnVis(4, false);
        //   oTable2.fnSetColumnVis(5, false);
        //   oTable2.fnSetColumnVis(6, false);
        //   oTable2.fnSetColumnVis(7, false);
        //   oTable2.fnSetColumnVis(8, false);
        //   oTable2.fnSetColumnVis(9, false);
        //   oTable2.fnSetColumnVis(10, false);
        //   oTable2.fnSetColumnVis(11, false);
        //   oTable2.fnSetColumnVis(12, false);
        //   oTable2.fnSetColumnVis(13, false);
        //   oTable2.fnSetColumnVis(15, false);
        //   oTable2.fnSetColumnVis(16, false);
        // } else {          
        //   oTable2.fnSetColumnVis(1, true);
        //   oTable2.fnSetColumnVis(2, true);
        //   oTable2.fnSetColumnVis(3, true);
        //   oTable2.fnSetColumnVis(4, true);
        //   oTable2.fnSetColumnVis(5, true);
        //   oTable2.fnSetColumnVis(6, true);
        //   oTable2.fnSetColumnVis(7, true);
        //   oTable2.fnSetColumnVis(8, true);
        //   oTable2.fnSetColumnVis(9, true);
        //   oTable2.fnSetColumnVis(10, true);
        //   oTable2.fnSetColumnVis(11, true);
        //   oTable2.fnSetColumnVis(12, true);
        //   oTable2.fnSetColumnVis(13, true);
        //   oTable2.fnSetColumnVis(15, true);
        //   oTable2.fnSetColumnVis(16, true);
        // }

        // oTable2.$('td:nth-child(20),td:nth-child(27)').editable( 'update_adminFNSKU', {
        //     "callback": function( sValue, y ) {
        //         sValue = JSON.parse(sValue);

        //         var aPos = oTable2.fnGetPosition( this );
        //         oTable2.fnUpdate( sValue.value, aPos[0], aPos[1] );
        //         if (aPos[1] == 19) {
        //           oTable2.fnUpdate( sValue.rid1, aPos[0], 20 );
        //           oTable2.fnUpdate( sValue.rid2, aPos[0], 21 );
        //           oTable2.fnUpdate( sValue.rid3, aPos[0], 22 );
        //           oTable2.fnUpdate( sValue.tar, aPos[0], 23 );
        //           oTable2.fnUpdate( sValue.dif, aPos[0], 24 );
        //         }
        //     },
        //     "submitdata": function ( value, settings ) {
        //         newval = $(this).find('input').val();
        //         return {
        //             "row_id": this.parentNode.getAttribute('id'),
        //             "fnsku": $(this).closest("tr").children().eq(0).html(),
        //             "total_owed": $(this).closest("tr").children().eq(18).html(),
        //             "column": oTable2.fnGetPosition( this )[2],
        //             "newval": newval
        //         };
        //     },
        //     "height": "100%",
        //     "width": "100%"
        // } );

        $('.loading-fnsku-list').html("");

        $('.fnSkuContainer .dataTable').wrap('<div class="dataTables_scroll" />');
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
        $('.seller_info_text').html('Seller: '+oData[0]+'<br>Country: '+oData[2]);

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
  <!-- Content Header (Page header) -->
  <header class="head">
      <div class="main-bar row">
          <div class="col-lg-6 col-sm-6">
              <h4 class="nav_top_align">
                  <img class="refunds-header-icon" src="{{ url('/images/icons/refunds-icon-small.png') }}">
                  FBA Refunds
              </h4>
          </div>
          <div class="col-lg-6 col-sm-6">
            <span class="pull-right"><button onclick="getFulfillmentCenters();" type="button" data-toggle="modal" data-target="#fulfillment_modal" class="btn btn-info">Fulfillment Center ID</button></span>
          </div>
      </div>
  </header>
<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/chosen/css/chosen.css')}}"/>
@include('admin.fbarefunds.fulfillment_modal')
<div class="col-lg-12 col-sm-12 col-md-12">
<h4 class="m-t-10" id="seller_tab">Sellers</h4>
<span class="loading-seller-list"></span>
  <div class="row">
      <div class="form-group col-md-6">
      <br>
        <span class="fba_admin_filter_text filter_txt_seller">Filter by:</span>
      </div>
  </div>
  <div class="row dontdisplay seller_filter">
    <div class="col-md-2 m-b-15">
      <input type="text" id="filterSellerName" class="form-control" name="" placeholder="Seller Name">
    </div>
    <div class="col-md-2 m-b-15">
      <select id="filterCountry" class="form-control">
        <option value="" selected>Select Country</option>
        <option value="us">United States</option>
        <option value="ca">Canada</option>
        <option value="uk">United Kingdom</option>
        <option value="fr">France</option>
        <option value="de">Germany</option>
        <option value="it">Italy</option>
        <option value="es">Spain</option>
      </select>
    </div>
    <div class="col-md-3">
      <button class="btn btn-xs btn-primary m-r-20" id="btnFilterSeller">Apply Filter</button>
      <button class="btn btn-xs btn-primary" id="btnShowAllSeller" onclick="showAll()">Show All</button>
    </div>
  </div>
  <div class="table-responsive fbaSellerContainer" style="overflow-y: hidden;">
    <table id="seller_table_list" cellspacing="0" cellpadding="0" class="table table-striped table-bordered dataTable no-footer fba_refund_table_header" style="width:100%;">
      <thead>
            <th><p style="width: 80px"></p>Seller Name</th>
            <th><p style="width: 200px"></p>Seller Email</th>
            <th><p style="width: 80px">Country</th>
            <th><p style="width: 200px"></p>Seller Central Log in email</th>
            <th><p style="width: 200px"></p>Seller Central log in password</th>
            <th>Seller Support Open Cases</th>
            <th>Total Amount Estimate Owed (outstanding)</th>
            <th>Total amount saved to date</th>
            <th>Total amount collected from seller</th>
            <th>Total amount outstanding to collect</th>
            <th><p style="width: 100px">Status</th>
            <th><p style="width: 100px">Valid payment method added?</th>
            <th><p style="width: 100px"></p>Mode</th>
            <th><p style="width: 100px"></p>Details</th>
        </thead>
      <tbody>

      </tbody>
    </table>
  </div>
</div>

<br><br>
<!-- ORDER ID -->

<div class="col-lg-12 col-sm-12 col-md-12 orderIdContainer">
<hr>
<h4 class="m-t-10" id="oic_h4">Order ID Claims</h4>
<p class="m-t-15 seller_info_text" style="color:#FF5722"></p>
<span class="loading-oic-list"></span>
<div class="row">
      <div class="form-group col-md-6">
      <br>
        <span class="fba_admin_filter_text filter_txt_oic">Filter by:</span>
      </div>
  </div>
  <div class="row dontdisplay oic_filter">
    <div class="col-md-2 m-b-15">
      <input type="text" class="form-control" name="" id="filterOicOrderId" placeholder="Order ID">
    </div>
    <div class="col-md-2 m-b-15">
      <input type="text" class="form-control" name="" id="filterOicTicket" placeholder="Support ticket">
    </div>
    <div class="col-md-2 m-b-15">
        <div>
            <select class="form-control" id="filterOicStatus">
              <option value="">Select Status</option>
              <option value="Open">Open</option>
              <optgroup label="Closed">
              <option value="All Ok">All Ok</option>
              <option value="Refund issued by seller">Refund issued by seller</option>
              <option value="Amz won't refund difference">Amz won't refund difference</option>
              </optgroup>
            </select>
        </div>
    </div>
    <div class="col-md-3">
      <button class="btn btn-xs btn-primary m-r-20" id="btnFilterOic">Apply Filter</button>
      <button class="btn btn-xs btn-primary" id="btnShowAllOic" onclick="showAll('getSellerOIC')">Show All</button>
    </div>
  </div>
<!-- <div class="table-responsive" style="overflow-y: hidden;"> -->
<table id="seller_oic" cellspacing="0" cellpadding="0" class="table table-striped table-bordered dataTable no-footer fba_refund_table_header" style="width:100%;">
  <thead>
    <!-- <th>Seller Name</th> -->
    <th><p style="width: 100px"></p>Order ID</th>
    <!-- <th>Country</th> -->
    <th>Qty Ordered</th>
    <th>Qty Refunded</th>
    <th>Qty Adjusted</th>
    <th>Total Ordered</th>
    <th>Total Refunded</th>
    <th>Total Adjusted</th>
    <th>Qty Returned</th>
    <th>Date of Return</th>
    <th>Over 45 days?</th>
    <th>Partial/Full claim</th>
    <th><p style="width: 50px"></p>Claim Name</th>
    <th><p style="width: 50px"></p>Detailed Disposition</th>
    <th>FMV 3mths</th>
    <th>FMV</th>
    <th>Amount to Claim</th>
    <th>Support Ticket</th>
    <th>Support Ticket2</th>
    <th>Reimb. ID(1)</th>
    <th>Reimb. ID(2)</th>
    <th>Reimb. ID(3)</th>
    <th>Total Amount Reimb.</th>
    <th>Diff.</th>
    <th><p style="width: 160px"></p>Status</th>
    <th>Comments</th>
  </thead>
  <tbody>

  </tbody>
</table>
<!-- </div> -->
</div>

<!-- FNSKU -->
<br>
<div class="col-lg-12 col-sm-12 col-md-12 fnSkuContainer">
<hr>
<h4 class="m-t-10">FnSKU Claims</h4>
<p class="m-t-15 seller_info_text" style="color:#FF5722"></p>
<div class="row">
      <div class="form-group col-md-6">
      <br>
        <span class="fba_admin_filter_text filter_txt_sku">Filter by:</span>
      </div>
  </div>
  <div class="row dontdisplay sku_filter">
    <div class="col-md-2 m-b-15">
      <input type="text" class="form-control" name="" id="filterSkuOrderId" placeholder="FnSKU">
    </div>
    <div class="col-md-2 m-b-15">
      <input type="text" class="form-control" name="" id="filterSkuTicket" placeholder="Support ticket">
    </div>
    <div class="col-md-2 m-b-15">
      <div>
            <select class="form-control" id="filterSkuStatus">
              <option value="">Select Status</option>
              <option value="Open">Open</option>
              <optgroup label="Closed">
              <option>All Ok</option>
              <option>Refund issued by seller</option>
              <option>Amz won't refund difference</option>
              </optgroup>
            </select>
        </div>
    </div>
    <div class="col-md-3">
      <button class="btn btn-xs btn-primary m-r-20" id="btnFilterSku">Apply Filter</button>
      <button class="btn btn-xs btn-primary" id="btnShowAllSku" onclick="showAll('getSellerFNSKU')">Show All</button>
    </div>
  </div>
<span class="loading-fnsku-list"></span>

  <!-- <div class="table-responsive" style="overflow-y: hidden;"> -->
    <table id="seller_fnsku" cellspacing="0" cellpadding="0" class="table table-striped table-bordered dataTable no-footer fba_refund_table_header" style="width:100%;">
      <thead>
        <!-- <th>Seller Name</th> -->
        <th><p style="width: 100px">FnSKU</th>
        <!-- <th>Country</th> -->
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>D</th>
        <th>E</th>
        <th>F</th>
        <th>M</th>
        <th>N</th>
        <th>O</th>
        <th>P</th>
        <th>Q</th>
        <th>Sum</th>
        <th>Reimb. Units</th>
        <th>Qty to Claim</th>
        <th><p style="width: 100px"></p>FnSKU</th>
        <th>Units</th>
        <th>Ave. Val.</th>
        <th>FMV 3mths</th>
        <th>FMV</th>
        <th>Total Owed</th>
        <th>Support Ticket</th>
        <th>Support Ticket2</th>
        <th>Reimb. ID(1)</th>
        <th>Reimb. ID(2)</th>
        <th>Reimb. ID(3)</th>
        <th>Total Amount Reimb.</th>
        <th><p style="width: 100px"></p>Diff.</th>
        <th><p style="width: 160px">Status</th>
        <th>Comments</th>
      </thead>
      <tbody>

      </tbody>
    </table>
  <!-- </div> -->
</div>
<script type="text/javascript" src="{{asset('assets/vendors/chosen/js/chosen.jquery.js')}}"></script>
@endsection
