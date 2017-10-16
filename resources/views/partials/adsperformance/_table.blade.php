<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/datepicker/css/bootstrap-datepicker.min.css')}}" />
<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/chosen/css/chosen.css')}}"/>
<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/tipso/css/tipso.min.css')}}"/>
<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/chartist/css/chartist.min.css')}}" />
<link type="text/css" rel="stylesheet" href="{{asset('assets/css/pages/chartist.css')}}" />
<div class="col-xs-12 data_tables">
    <!-- BEGIN EXAMPLE1 TABLE PORTLET-->
    @include('partials.adsperformance._ads_bid_modal')

    <!-- TABLE FOR BAR CHART -->
    @include('partials.adsperformance._ads_prod_costs_modal_graph')
    <div class="card">
        <div class="card-header bg-white">
            <i class="fa fa-table"></i> Ads Performance
        </div>
        <div class="card-block p-t-25">
            <div class="row">
                    <div class="col-md-12">
                    <!-- Recommendation Start -->            
                
                    </div>
                    <!-- Recommendation End -->
                <div class="col-lg-8 col-md-12 col-xs-12" >                
                    <div class="col-md-9 m-t-10">
                        <span id="filterbtn" style="color: #7AC482;cursor: pointer;">Filter <i id="filter_caret" class="fa fa-caret-up"></i></span>
                        <span>Active Filters: </span>
                        <span id="update_filter_msg"></span>
                    </div>
                </div>
                
                <div class="col-md-12">
                  <div class="col-md-12">
                    <div class="filter_param"></div>
                  </div>
                </div>
            </div>
            <div class="col-md-12">
                <div id="adperformance_filter" class="col-md-12  filtercontainer m-t-10">
                
                  <div class="row">
                    <div class="col-md-12">
                      <div class="col-lg-6 col-md-12 input-daterange">
                        <div class="row">
                          <div class="col-lg-4 col-md-6 m-b-10">
                          <span style="color: #7AC482;">Date Range: </span>
                        </div>
                        <div class="col-lg-8 col-md-12" style="padding:0px;">
                           <div class="col-lg-6 col-md-6 m-b-20" style="margin-left: ;">
                            <div class="input-group">
                                <span class="input-group-addon" style="border-radius: 0px;">
                                  <i class="fa fa-calendar"></i>
                                </span>
                                <input type="text" id="start_date" class="form-control m-r-5" placeholder="Date Start" readonly="true" style="text-align: left;border-radius: 0px;">
                            </div>
                           </div>
                           <div class="col-lg-6 col-md-6 m-b-20" style="margin-left: ;">
                            <div class="input-group">
                                <span class="input-group-addon" style="border-radius: 0px;">
                                  <i class="fa fa-calendar"></i>
                                </span>
                                <input type="text" id="end_date" class="form-control m-l-5" placeholder="Date End" readonly="true" style="text-align: left;border-radius: 0px;">
                            </div>
                           </div>
                        </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 input-daterange">
                        <div class="row">
                          <div class="col-lg-4 col-md-6 m-b-10">
                          <span style="color: #7AC482;">Time Range: </span>
                        </div>
                         <div class="col-lg-6 col-md-6 m-b-20" style="margin-left: ;">
                          <select class="form-control" id="time_range" onchange="time_range(this)">
                            <option value="">Select time range</option>
                            <option value="14d">14 days</option>
                            <option value="30d">30 days</option>
                            <option value="60d">60 days</option>
                            <option value="lifetime">Lifetime</option>
                          </select>
                         </div>
                        </div>
                    </div>
                    </div>
                  </div>
                  <form action="" name="filterform">
                  <div class="col-lg-6 col-md-12">
                  <div class="row">
                      <div class="col-lg-4 col-md-6">
                            Filter Name
                      </div>
                      <div class="col-lg-8 col-md-6">
                            <input id="filter_name" type="text" name="filter_name" class="form-control" value="">
                      </div>
                    </div>
                    <div class="row m-t-5">
                      <div class="col-lg-4 col-md-6">
                            Country
                      </div>
                      <div class="col-lg-8 col-md-6">
                           {!! Form::select('country_id', $countries, old('country_id'),  ['class' => 'form-control select-country filter_select','multiple', 'required' ,'id'=>'filter_country']) !!}
                      </div>
                    </div>
                    <div class="row m-t-5">
                        <div class="col-lg-4 col-md-6">
                              Campaign Type
                        </div>
                        <div class="col-lg-8 col-md-6">
                              <select id="filter_camp_type" name="campaign_type" class="form-control tb-text-color-gray filter_select" multiple>
                                <option value="Automatic">Automatic</option>
                                <option value="Manual">Manual</option>
                              </select>
                        </div>
                    </div>
                    <div class="row m-t-5">
                        <div class="col-lg-4 col-md-6">
                              Campaign Name
                        </div>
                        <div class="col-lg-8 col-md-6">
                          <select id="filter_camp_name" class="form-control select-campaign tb-text-color-gray filter_select" multiple="" required="">
                            
                          </select>
                            <!-- {!! Form::select('camp_name', $camp_name, old('camp_name'),  ['class' => 'form-control select-campaign tb-text-color-gray filter_select', 'multiple', 'required' ,'id'=>'filter_camp_name']) !!} -->
                        </div>
                    </div>
                    <div class="row m-t-5">
                        <div class="col-lg-4 col-md-6">
                              Ad Group
                        </div>
                        <div class="col-lg-8 col-md-6">
                          <select id="filter_ad_group" class="form-control select-campaign tb-text-color-gray filter_select" multiple="" required="">
                            
                          </select>
                            <!-- {!! Form::select('ad_group_name', $ad_group_name, old('ad_group_name'),  ['class' => 'form-control select-campaign tb-text-color-gray filter_select', 'multiple', 'required' ,'id'=>'filter_ad_group']) !!} -->
                        </div>
                    </div>
                    <div class="row m-t-5">
                        <div class="col-lg-4 col-md-6">
                              Recommendation
                        </div>
                        <div class="col-lg-8 col-md-6">
                              <select id="filter_recommendation" name="" class="form-control tb-text-color-gray filter_select" multiple>
                                <option value="Increase Bid">Increase Bid</option>
                                <option value="Decrease Bid">Decrease Bid</option>
                                <option value="Negative Keyword">Negative Keyword</option>
                              </select>
                        </div>
                    </div>
                    <div class="row m-t-5">
                        <div class="col-lg-4 col-md-6">
                              Show only active campaigns/adgroups/keywords 
                        </div>
                        <div class="col-lg-8 col-md-6">
                              <input type="checkbox" class="form-control tb-text-color-gray js-switch" name="filter_show_enabled"  id="filter_show_enabled" checked="true" />
                        </div>
                    </div>
                    <!-- <div class="row m-t-5" style="margin-bottom: 5px">
                        <div class="col-lg-4 col-md-6">
                              Keyword
                        </div>
                        <div class="col-lg-8 col-md-6">
                              <input id="filter_keyword" type="text" name="filter_keyword" class="form-control" value="">
                        </div>
                    </div> -->
                  </div>
                  <div class="col-lg-6 col-md-12">
                    <div class="row">
                      <div class="col-lg-4 col-md-6">
                            Imp
                      </div>
                      <div class="col-lg-8 col-md-6">
                            <input id='filter_imp_min' type="text" name="imp_min" value="" class="form-control-copy w-46-p" placeholder="Min" onblur="ValidateMinMax(this,12)"> -
                            <input id='filter_imp_max' type="text" name="imp_max" value="" class="form-control-copy w-46-p" placeholder="Max" onblur="ValidateMinMax(this,12)">
                      </div>
                    </div>
                    <div class="row m-t-5">
                        <div class="col-lg-4 col-md-6">
                              Clicks
                        </div>
                        <div class="col-lg-8 col-md-6">
                              <input id='filter_clicks_min' type="text" name="clicks_min" value="" class="form-control-copy w-46-p" placeholder="Min" onblur="ValidateMinMax(this,12)"> -
                              <input id="filter_clicks_max" type="text" name="clicks_max" value="" class="form-control-copy w-46-p" placeholder="Max" onblur="ValidateMinMax(this,12)">
                        </div>
                    </div>
                    <div class="row m-t-5">
                        <div class="col-lg-4 col-md-6">
                              CTR
                        </div>
                        <div class="col-lg-8 col-md-6">
                              <input id='filter_ctr_min' type="text" name="ctr_min" value="" class="form-control-copy w-46-p"  placeholder="Min" onblur="ValidateMinMax(this,12)"> -
                              <input id="filter_ctr_max" type="text" name="ctr_max" value="" class="form-control-copy w-46-p"  placeholder="Max" onblur="ValidateMinMax(this,12)">
                        </div>
                    </div>
                    <div class="row m-t-5">
                        <div class="col-lg-4 col-md-6">
                              Total Spend
                        </div>
                        <div class="col-lg-8 col-md-6">
                              <input id="filter_total_spend_min" type="text" name="total_spend_min" value="" class="form-control-copy w-46-p"  placeholder="Min" onblur="ValidateMinMax(this,12,12)"> -
                              <input id="filter_total_spend_max" type="text" name="total_spend_max" value="" class="form-control-copy w-46-p"  placeholder="Max" onblur="ValidateMinMax(this,12,12)">
                        </div>
                    </div>
                    <div class="row m-t-5">
                        <div class="col-lg-4 col-md-6">
                              Average CPC
                        </div>
                        <div class="col-lg-8 col-md-6">
                              <input id="filter_avg_cpc_min" type="text" name="avg_cpc_min" value="" class="form-control-copy w-46-p"  placeholder="Min" onblur="ValidateMinMax(this,12)"> -
                              <input id="filter_avg_cpc_max" type="text" name="avg_cpc_max" value="" class="form-control-copy w-46-p"  placeholder="Max" onblur="ValidateMinMax(this,12)">
                        </div>
                    </div>
                    <div class="row m-t-5">
                        <div class="col-lg-4 col-md-6">
                              ACoS
                        </div>
                        <div class="col-lg-8 col-md-6">
                              <input id="filter_acos_min" type="text" name="acos_min" value="" class="form-control-copy w-46-p"  placeholder="Min" onblur="ValidateMinMax(this,12)"> -
                              <input id="filter_acos_max" type="text" name="acos_max" value="" class="form-control-copy w-46-p"  placeholder="Max" onblur="ValidateMinMax(this,12)">
                        </div>
                    </div>
                    <div class="row m-t-5">
                        <div class="col-lg-4 col-md-6">
                              Conversion Rate
                        </div>
                        <div class="col-lg-8 col-md-6">
                              <input id="filter_conv_rate_min" type="text" name="conversion_rate_min" value="" class="form-control-copy w-46-p"  placeholder="Min" onblur="ValidateMinMax(this,12)"> -
                              <input id="filter_conv_rate_max" type="text" name="conversion_rate_max" value="" class="form-control-copy w-46-p"  placeholder="Max" onblur="ValidateMinMax(this,12)">
                        </div>
                    </div>
                    <div class="row m-t-5">
                        <div class="col-lg-4 col-md-6">
                              Revenue
                        </div>
                        <div class="col-lg-8 col-md-6">
                              <input id="filter_revenue_min" type="text" name="revenue_min" value="" class="form-control-copy w-46-p"  placeholder="Min" onblur="ValidateMinMax(this,12)"> -
                              <input id="filter_revenue_max" type="text" name="revenue_max" value="" class="form-control-copy w-46-p"  placeholder="Max" onblur="ValidateMinMax(this,12)">
                        </div>
                    </div>
                  </div>
                  </form>
                   <div class="col-md-12">
                    <div class="row col-md-12">
                       <button id="quick_filter" class="btn btn-primary btn-sm m-t-20 m-r-10">Filter</button>
                       <button id="save_filter" class="btn btn-success btn-sm m-t-20 m-r-10" style="border-radius: 0px;">Save Filter</button>
                       <button id="reset_filter" class="btn btn-danger btn-sm m-t-20" style="border-radius: 0px;">Reset</button>
                    </div>
                  </div>
                  <div class="col-md-12 m-t-20">
                    <table id="filter_table" cellspacing=0 cellpadding=0 class="table table-striped table-bordered table_res">
                          <thead>
                          <tr>
                              <th width="90%">Filter Name</th>
                              <th colspan="3" class="text-center">Action</th>
                          </tr>
                          </thead>
                          <tbody id="filter_tbody">
                         
                          </tbody>
                      </table>
                  </div>
                </div>
                <div class="row">
                  <div id="adperformance_table" class="col-md-12 m-t-20">

                  <div class="loadingTableContainer dontdisplay">
                    <div class="dataTables_processing2" style="display:block;z-index: 998;top:50%;"> <b>Loading records </b><i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span></div>
                  </div>

                  <div class="row m-b-10">
                    <div class="col-md-12">
                        <div class="pull-right"><button class="btn btn-sm btn-danger no-radius" id="reviewChanges">Review Changes</button></div>
                    </div>
                  </div>
                    <!-- <div class="float-md-right text-xs-right m-t-5">
                        <div class="btn-group show-hide">
                            <a class="btn btn-primary" style="border-radius:0px;" href="#" data-toggle="dropdown">
                                Columns
                                <i class="fa fa-angle-down"></i>
                            </a>
                            <div id="adperformance_table_column_toggler" class="dropdown-menu dropdown-checkboxes dropdown_checkbox_margin_left float-xs-right" style="padding:10px;margin-left:-100px;overflow-y:scroll;height:15em;">
                                <label><input type="checkbox" checked data-column="0">Country</label>
                                <label><input type="checkbox" checked data-column="1">Campaign Type</label>
                                <label><input type="checkbox" checked data-column="2">Campaign Name</label>
                                <label><input type="checkbox" checked data-column="3">Ad Group</label>
                                <label><input type="checkbox" checked data-column="4">Keywords</label>
                                <label><input type="checkbox" checked data-column="5">searchterms</label>
                                <label><input type="checkbox" checked data-column="6">Match Type</label>
                                <label><input type="checkbox" checked data-column="7">Imp</label>
                                <label><input type="checkbox" checked data-column="8">Clicks</label>
                                <label><input type="checkbox" checked data-column="9">CTR</label>
                                <label><input type="checkbox" checked data-column="10">Revenue</label>
                                <label><input type="checkbox" checked data-column="11">Orders placed within 30 days of a click</label>
                                <label><input type="checkbox" checked data-column="12">CR</label>
                                <label><input type="checkbox" checked data-column="13">Total Spend</label>
                                <label><input type="checkbox" checked data-column="14">Average CPC</label>
                                <label><input type="checkbox" checked data-column="15">ACoS</label>
                                <label><input type="checkbox" checked data-column="16">Bid</label>
                                <label><input type="checkbox" checked data-column="17">Max Bid Recommendation</label>
                                <label><input type="checkbox" checked data-column="18">Recommendation</label>
                                <label><input type="checkbox" checked data-column="19">Comments</label>
                            </div>
                        </div>
                    </div> -->
                    
                    <table cellspacing=0 cellpadding=0 class="table table-striped" id="adsperformance_table" style="min-width: 1200px;">
                        <thead>
                        <tr>
                            <th class="th-nowrap" style="min-width:50px">
                              <span class="countryHeader" data-tipso-title="" data-tipso="Country">Cty</span>
                            </th>
                            {{-- <th class="th-nowrap">Type</th> --}}
                            <th class="th-nowrap" style="min-width:65px"></th>
                            <th class="th-nowrap" style="">Campaign Name</th>
                            {{-- <th class="th-nowrap">Ad Group</th> --}}
                            {{-- <th class="th-nowrap">Keywords</th> --}}
                            {{-- <th class="th-nowrap">Search Terms</th> --}}
                            
                            <th class="th-nowrap" style="min-width:50px">Imp</th>
                            <th class="th-nowrap" style="min-width:50px">Clicks</th>
                            <th class="th-nowrap" style="min-width:50px">CTR</th>
                            <th class="th-nowrap" style="min-width:50px"><span class="revHeader" data-tipso-title="" data-tipso="Revenue">Rev</span></th>
                            <th class="th-nowrap" style="min-width:50px"><span class="orderHeader" data-tipso-title="" data-tipso="Orders placed within 30 days of a click">Orders</span></th>
                            <th class="th-nowrap" style="min-width:50px">CR</th>
                            <th class="" style="min-width:50px;font-size: 11px">Total Spend</th>
                            <th class="" style="min-width:50px;font-size: 11px">Average CPC</th>
                            <th class="th-nowrap" style="min-width:50px">ACoS</th>
                            <th class="th-nowrap" style="min-width:50px">Bid</th>
                            <th class="" style="min-width:50px;font-size: 11px;"><span class="maxBidHeader" data-tipso-title="" data-tipso="Max Bid Recommendation">Max Bid</span></th>
                            <th class="th-nowrap">Recommendation</th>
                            {{-- <th class="th-nowrap">Comments</th> --}}
                        </tr>
                        </thead>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
<br>
<script type="text/javascript">
  var bs = '{{ $bs }}';
</script>
<script type="text/javascript" src="{{asset('assets/vendors/chosen/js/chosen.jquery.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/tipso/js/tipso.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/chartist/js/chartist.min.js')}}"></script>
<script type="text/javascript" src="js/adsperformance-smartfilter.js"></script>
<script type="text/javascript">
  var counterRow=1;
  var page_len = 25;
  var total_record = "";
  var isFilter = 0;
  var object_filter;
  var object_bid_change;
  $(document).ready(function(){
      init_loading();
      populateRecommendationRules();
      initialize_filter_list();
      getCardsData();
      countChanges();
      initialize_table();
      $("#amz_submit_bid").click(function(){
        if(bs == 'XS'){
          swal({
            title: ' ',
            text: "This feature is not available in your current subscription. Please upgrade your subscription in the Settings.",
            type: 'warning',
            showCancelButton: false,
            confirmButtonColor: '#DD6B55',
            // cancelButtonColor: '#d33',
            confirmButtonText: 'Okay'
          }).then(function () {
            //return false;
          })
          $('#bidModal').modal('hide');
        }else{
          swal(" ", "Submitting bid changes. Please wait...", "info");
          var _token = "{{ csrf_token() }}";
          var data = { _token: _token };
          $.ajax({url: "submitBidToAmazon", type: 'POST', data: data, success: function(result){
              var res = jQuery.parseJSON (result);
              swal(" ", res.message, res.status);
              countChanges();
              init_loading();
              getCardsData();
              initialize_table();
              $('#bidModal').modal('hide');
          }});
        }
      });
      $("#quick_filter").click(function(){
          isFilter = 1;
          total_record = "";
          init_loading();
          getCardsData();
          show_filter_parameter();
          initialize_table();
      });
      $("#save_filter").click(function(){
          if ($('#filter_name').val().trim() != '') {
              add_filter();
          }else{
            swal(
              'Input required',
              'Please enter filter name.',
              'error'
            )
          }
      });
      
      $('#recommendationbtn').click(function(){
        $('#adperformance_recommendation').toggle('fast');
      }); 

      $('#addRuleBtn').click(function(){
          if (validateCellData()) {
              addRuleRow('');
          }else{
            swal('Input required','All fields are required.','error')
          }
      })

      $('#saveRuleBtn').click(function(){
          saveRule(this)
      })

      $('.input-daterange input').each(function() {
          $(this).datepicker({
              todayHighlight: true,
              autoclose: true,
              orientation: "auto",
              format: 'dd/mm/yyyy'
          }).on('changeDate', function(e) {
              if($(this).attr("id") == "end_date") {
                $("#start_date").datepicker('setEndDate', $("#end_date").val());
              } else if($(this).attr("id") == "start_date") {
                $("#end_date").datepicker('setStartDate', $("#start_date").val());
              }
          });
      });

      $(".filter_select, .recMultiSelect").chosen({allow_single_deselect: true}); 
      $('.filtercontainer').hide();
      $('.chosen-container').css('width','100%')

      $('.filter_parameter').click(function(){
          // alert($(this).attr('data'));
          // alert();
      })

      $('#reset_filter').click(function(){
          reset_filter();
          $('.filter_parameter').remove();
          isFilter = 0;
          total_record = "";
          init_loading();
          getCardsData();
          initialize_table();
      })


      $('#reviewChanges').click(function(){
          $('#bidModal').modal('show')
          init_bidChanges();
      })

      $('#filterbtn').click(function(){
        $('#adperformance_filter').toggle('fast');
      });

      $('#start_date').datepicker({
          todayHighlight: true,
          autoclose: true,
          format: 'dd/mm/yyyy',
          orientation: "auto",
      }).on('changeDate', function(e){
          ts = $(this).datepicker('getDate');
          month = (ts.getMonth()+1) >= 10 ? ts.getMonth()+1 : '0' + (ts.getMonth()+1);
          dateString = ts.getDate() + '/' + month + '/' + ts.getFullYear();

          $('#start_date_val').html(dateString);
      });

      $('#end_date').datepicker({
          todayHighlight: true,
          autoclose: true,
          format: 'dd/mm/yyyy',
          orientation: "auto",
      })

      $(".orderHeader, .countryHeader, .revHeader, .maxBidHeader").tipso({ background: '#7f7f7f'});

      $('#prodCostsGraphtModal').on('shown.bs.modal', function (event) {
         init_graph();
      });

  });

  function countChanges(){
    $.ajax({url: 'countChanges', type: 'GET', success: function(result){
      if(result > 0){
        $('#reviewChanges').text('Review Changes ( '+result+' )')
      }else{
        $('#reviewChanges').text('Review Changes')
      }
    } })
  }

  function init_bidChanges(){
    var oTable;
    $.ajax({
          url: 'getAdsBid',type: 'GET',success: function(result){
              var response = jQuery.parseJSON(result);
              oTable =  $('#ads_bid_table').dataTable({
                "data": response,
                "bLengthChange": false,
                "bFilter": false,
                "destroy": true,
                "bPaginate": false,
                "paging": false,
                "ordering": true,
                createdRow: function( row, data, dataIndex ) {
           
                $(row).children(':nth-child(5)').editable('updateAdsBid', {
                    onsubmit: function(settings, td) {
                      var input = $(td).find('input');
                      var original = input.val();
                      if (validateNumber(original)) {
                          return true;
                      } else {
                          swal(" ", "Please input number only", "error")
                          input.css('border', '1px solid red');
                          return false;
                      }
                    },
                    callback: function( sValue, y ){
                      countChanges();
                    },
                    submitdata: function ( value, settings ) {
                      hiddenBid = $(row).children(':nth-child(2)')
                      fromVal = $(row).children(':nth-child(4)').text()
                      newval = $(this).find('input').val();
                      $(hiddenBid).find('input').val(newval)
                      return {
                                  "row_id": this.parentNode.getAttribute('id'),
                                  "column": oTable.fnGetPosition( this )[2],
                                  "bid_from": fromVal,
                                  "bid_to": newval
                              };
                      },
                      "height": "100%",
                      "width": "100%"
                });

                $(row).children(':nth-child(7)').find('.matchType').click(function(){
                    var matchTypeElement = $(this);
                    var matchTypeFrom = $(row).children(':nth-child(6)').text();
                    $(this).hide()
                    $(row).children(':nth-child(7)')
                          .append('<select class="selectMatchType">'+
                            '<option value="">Select match type</option>'+
                            '<option value="NEGATIVE EXACT">NEGATIVE EXACT</option>'+
                            '<option value="NEGATIVE PHRASE">NEGATIVE PHRASE</option></select>')
                    $('.selectMatchType').focus()
                    $('.selectMatchType').change(function(){
                      matchTypeFrom = ( matchTypeFrom == 'Click to edit' ) ? '' : matchTypeFrom;
                      if($(this).val() != ""){
                        data = { 
                              id: $(matchTypeElement).parent().parent().attr('id'),
                              matchTypeFrom: matchTypeFrom,
                              matchTypeTo: $(this).val()
                              }
                        $.ajax({url: 'updateMatchType', type: 'POST',data: data, success: function(result){
                            $(matchTypeElement).text(result);
                            countChanges();
                          } 
                        })
                      }
                    })
                    $('.selectMatchType').focusout(function() {
                      $(this).hide()
                      $(matchTypeElement).show();
                    })
                })
            }
              });

              $('.deleteBidBtn').click(function(){
                element = $(this);
                swal({
                    title: ' ',
                    text: "Are you sure you want to delete this bid?",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#DD6B55',
                    // cancelButtonColor: '#d33',
                    confirmButtonText: 'Okay'
                  }).then(function(){
                    id = $(element).parent().parent().parent().attr('id');
                    data = {id:id};
                    $.ajax({ url:'deleteAdsBid', type:'POST', data:data, success: function(){
                       $(element).parent().parent().parent().remove();
                        countChanges();
                        swal(
                          ' ',
                          'Successfully deleted.',
                          'success'
                        )
                    }
                  })
                  })
              })
          }

    });
}
  function validateCellData(){
    var cellData;
    var error = false;
    $(".ruleRow").find('td').each(function(){
        cellData = $(this).find('select').val();
        if(typeof cellData == 'undefined'){
          cellData = $(this).find('input').val(); 
        }
        if (cellData == "") {
            error = true;
        }
    });

    if (error) {
       return false
    }else{
       return true;
    }
  }

  function validateHeaderData(){
    var error = false;
    if($('#recommendation_name').val() == "") error = true;
    if(!$('#recommendation_campaign').val()) error = true;
    if(!$('#recommendation_country').val()) error = true;
    if($('#recommendation').val() == "") error = true;
    if($('#recommendation_period').val() == "") error = true;

    if (error) {
       return false
    }else{
       return true;
    }
  }

  function saveRule(element){
    if (validateCellData()) {
          $(element).html('Saving... <i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span>');
          $(element).attr("disabled", true);
          if (validateHeaderData()) {
            var operation_arr = [];
            var matrix_arr = [];
            var metric_arr = [];
            var value_arr = [];
            var data;
            var counter=0;
            
            $('.headRuleContainer').find('select[name="recommendation_operation"]').each(function(){
                operation_arr[counter] = $(this).val();
                counter++
            });

            counter=0;
            $('.headRuleContainer').find('select[name="recommendation_matrix"]').each(function(){
                matrix_arr[counter] = $(this).val();
                counter++
            });

            counter=0;
            $('.headRuleContainer').find('select[name="recommendation_metric"]').each(function(){
                metric_arr[counter] = $(this).val();
                counter++
            });

            counter=0;
            $('.headRuleContainer').find('input[name="recommendation_value"]').each(function(){
                value_arr[counter] = $(this).val();
                counter++
            });

            data = {
                recommendationName: $('#recommendation_name').val(),
                campaignName: $('#recommendation_campaign').val().toString(),
                country: $('#recommendation_country').val().toString(),
                recommendation: $('#recommendation').val(),
                timePeriod: $('#recommendation_period').val(),
                operation: operation_arr,
                matrix: matrix_arr,
                metric: metric_arr,
                value: value_arr
            }

            $.ajax({url: "saveAdRule", type: 'POST', data: data, success: function(result){
                var response = jQuery.parseJSON(result);
                if (response[0].success) {
                  resetFieldsRecommendation()
                  popupateAddedRule(response);
                  swal(" ", "Recommendation rule successfully added", "success")
                  $(element).html('Save Changes')
                  $(element).attr("disabled", false);
                  initialize_table();
                  
                }else{
                  resetFieldsRecommendation()
                  swal(' ','Something went wrong when saving your request','error')
                  $(element).html('Save Changes')
                  $(element).attr("disabled", false);
                }
              }
            });

      }else{
        swal('Input required','All fields are required.','error')
        $(element).html('Save Changes')
        $(element).attr("disabled", false);
      }
    }else{
      swal('Input required','All fields are required.','error')
      $(element).html('Save Changes')
      $(element).attr("disabled", false);
    }
  }

  function resetFieldsRecommendation(){
    $('#recommendation_name').val('')
  }

  function closeRow(element){
      $(element).parent().parent().remove();
  }


  function addRuleRow(idRow){
      
      var tableRule = $('#tableRule'+idRow);
      var operation = '<select id="recommendation_operation" name="recommendation_operation" class="form-control" required="">'+
                                      '<option value="and">AND</option>'+
                                      '<option value="or">OR</option>'+
                                    '</select>';

      var matrix = '<select id="recommendation_matrix" name="recommendation_matrix" class="form-control" required="">'+
                                      '<option>Select Your Matrix</option>'+
                                      '<option value="acos">ACoS</option>'+
                                      '<option value="impressions">Impressions</option>'+
                                      '<option value="clicks">Clicks</option>'+
                                      '<option value="ctr">CTR</option>'+
                                      '<option value="average_cpc">Average CPC</option>'+
                                      '<option value="revenue">Revenue</option>'+
                                      '<option value="bid">Bid</option>'+
                                    '</select>';

      var metric =  '<select id="recommendation_metric" name="recommendation_metric" class="form-control" required="">'+
                                      '<option>Select Your Metric</option>'+
                                      '<option value="≥">≥</option>'+
                                      '<option value="≤">≤</option>'+
                                      '<option value="=">=</option>'+
                                    '</select>';                            

      var value = '<input id="recommendation_value" type="text" name="recommendation_value" class="form-control" value="" onblur="ValidateMinMax(this,12)">';

      tableRule.append('<tr class="ruleRow'+idRow+'" data="'+counterRow+'">'+
                          '<td>'+operation+'</td>'+
                          '<td>'+matrix+'</td>'+
                          '<td>'+metric+'</td>'+
                          '<td>'+value+'</td>'+
                          '<td width="2" style="vertical-align: middle;"><button onclick="closeRow(this)" class="btn btn-danger btn-sm" style="border-radius:0px;"><i class="fa fa-trash"></i></button></td>'+
                       '</tr>');
      counterRow++;

  }

  function initialize_data_feed(){
    var filter_date_start = $('#start_date').val();
    var filter_date_end = $('#end_date').val();
    var filter_show_enabled = $('#filter_show_enabled').prop('checked') ? 1 : 0;
    var _token = "{{ csrf_token() }}";
    var data = { _token: _token, filter_date_start: filter_date_start, filter_date_end:filter_date_end, total_number: total_record, filter_show_enabled:filter_show_enabled };
    if(isFilter == 1){
      var filter_imp = $('#filter_imp_min').val()+"-"+$('#filter_imp_max').val();
      var filter_clicks = $('#filter_clicks_min').val()+"-"+$('#filter_clicks_max').val();
      var filter_ctr = $('#filter_ctr_min').val()+"-"+$('#filter_ctr_max').val();
      var filter_total_spend = $('#filter_total_spend_min').val()+"-"+$('#filter_total_spend_max').val();
      var filter_avg_cpc = $('#filter_avg_cpc_min').val()+"-"+$('#filter_avg_cpc_max').val();
      var filter_acos = $('#filter_acos_min').val()+"-"+$('#filter_acos_max').val();
      var filter_conv_rate = $('#filter_conv_rate_min').val()+"-"+$('#filter_conv_rate_max').val();
      var filter_revenue = $('#filter_revenue_min').val()+"-"+$('#filter_revenue_max').val();
      var filter_country = ($('#filter_country').val()) ? $('#filter_country').val().toString() : '';
      var filter_camp_type = ($('#filter_camp_type').val()) ? $('#filter_camp_type').val().toString() : '';
      var filter_camp_name = ($('#filter_camp_name').val()) ? $('#filter_camp_name').val().toString() : '';
      var filter_ad_group = ($('#filter_ad_group').val()) ? $('#filter_ad_group').val().toString() : '';
      var filter_recommendation = ($('#filter_recommendation').val()) ? $('#filter_recommendation').val().toString() : '';
      var filter_keyword = $('#filter_keyword').val();
      data = { _token: _token, filter_date_start: filter_date_start, filter_date_end:filter_date_end, filter_imp:filter_imp, filter_clicks:filter_clicks, filter_ctr:filter_ctr, filter_total_spend:filter_total_spend, filter_avg_cpc:filter_avg_cpc, filter_acos:filter_acos, filter_conv_rate:filter_conv_rate, filter_country:filter_country, filter_camp_type:filter_camp_type, filter_camp_name:filter_camp_name, filter_keyword:filter_keyword, filter_revenue:filter_revenue, filter_ad_group:filter_ad_group,filter_recommendation:filter_recommendation, total_number: total_record, filter_show_enabled:filter_show_enabled };
    }
    return data;
  }
  function initialize_table(){

    //$.ajax({url: "getAdData", type: 'POST', data: data, success: function(result){
      $.fn.dataTable.ext.errMode = 'throw';
        var oTable2 = $('#adsperformance_table').DataTable({
            "processing": true,
            "serverSide": true,
            "lengthMenu": [[25, 50, 100, 250], [25, 50, 100, 250]],
            "oLanguage": {
              sProcessing: '<div class="dataTables_processing2" style="display:block;z-index: 999;top:50%;"><b>Loading records </b><i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span></div>'
            },
            "deferRender": true,
            "searching" : false,
            "destroy" : true,
            "ajax": {
              url: "getAdData",
              type: "POST",
              data: initialize_data_feed()
            },
            "aoColumnDefs" : [
            {
              'bSortable' : false,
              'aTargets' : [ 1,3,4,5,6,7,8,9,10,11,12,13, 14 ]
              //'aTargets' : [ 2,,4,5,6,7,8,9,10,11,12,13,14,15 ]
            }],
            // "scrollX": true,
            "createdRow": function( row, data, dataIndex ) {
           
                $(row).children(':nth-child(20)').editable( 'updateAdComment', {
                "callback": function( sValue, y ) {
                    
                },
                "submitdata": function ( value, settings ) {
                    newval = $(this).find('input').val();
                    return {
                                "row_id": this.parentNode.getAttribute('id'),
                                "comment": newval
                            };
                        },
                        "height": "100%",
                        "width": "100%"
                    } );

                // $(row).children(':nth-child(14)').editable('updateAdsBid', {
                //     onsubmit: function(settings, td) {
                //       var input = $(td).find('input');
                //       var original = input.val();
                //       if (validateNumber(original)) {
                //           return true;
                //       } else {
                //           swal(" ", "Please input number only", "error")
                //           input.css('border', '1px solid red');
                //           return false;
                //       }
                //     },
                //     callback: function( sValue, y ){
                //       countChanges();
                //     },
                //     submitdata: function ( value, settings ) {
                //       hiddenBid = $(row).children(':nth-child(2)')
                //       fromVal = $(hiddenBid).find('input').val()
                //       newval = $(this).find('input').val();
                //       // $(hiddenBid).find('input').val(newval)
                //       return {
                //                   "row_id": this.parentNode.getAttribute('id'),
                //                   "bid_from": fromVal,
                //                   "bid_to": newval
                //               };
                //       },
                //       "height": "100%",
                //       "width": "100%"
                // });

                $(row).children(':nth-child(7)').find('.matchType').click(function(){
                    var _token = "{{ csrf_token() }}";
                    var matchTypeElement = $(this);
                    var matchTypeFrom = $(row).children(':nth-child(7)').find('.matchTypeOrig').val();
                    $(this).hide()
                    $(row).children(':nth-child(7)')
                          .append('<select class="selectMatchType">'+
                            '<option value="">Select match type</option>'+
                            '<option value="NEGATIVE EXACT">NEGATIVE EXACT</option>'+
                            '<option value="NEGATIVE PHRASE">NEGATIVE PHRASE</option></select>')
                    $('.selectMatchType').focus()
                    $('.selectMatchType').change(function(){
                      matchTypeFrom = ( matchTypeFrom == 'Click to edit' ) ? '' : matchTypeFrom;
                      if($(this).val() != ""){
                        data = {
                              _token: _token,
                              id: $(matchTypeElement).parent().parent().attr('id'),
                              matchTypeFrom: matchTypeFrom,
                              matchTypeTo: $(this).val()
                              }
                        $.ajax({url: 'updateMatchType', type: 'POST',data: data, success: function(result){
                            $(matchTypeElement).text(result);
                            countChanges();
                          } 
                        })
                      }
                    })
                    $('.selectMatchType').focusout(function() {
                      $(this).hide()
                      $(matchTypeElement).show();
                    })
                })
                var tableinfo = oTable2.page.info();
                total_record = tableinfo.recordsTotal;

                $(row).children(':nth-child(1)').hover(function(e){
                    $('.countryFlag').tipso({
                      position          : 'left',
                      background        : '#e8e8e8',
                      color             : '#787878',
                      size              : 'small'
                    })

                    $('.countryFlag.tipso_style').css({'border-bottom' : 'none', 'margin-bottom' : '-10px'})
                })

                $(row).children(':nth-child(2)').hover(function(e){
                    $('.campTypeIcon').tipso({
                      position          : 'left',
                      background        : '#e8e8e8',
                      color             : '#787878',
                      size              : 'small'
                    })

                    $('.campTypeIcon.tipso_style').css('border-bottom','none')
                })

                $(row).children(':nth-child(1)').find('.warningChangesPopUp').mousemove(function(e){
                    var divid = 'bidUpdatePopUp'
                    var div = $('.bidUpdatePopUp');
                    var left  = e.pageX +20  + "px";
                    var top  = e.pageY + "px";

                    $(div).css('left',left)
                    $(div).css('top',top)

                    $("."+divid).fadeIn();
                    return false;
                })

                $(row).children(':nth-child(2)').find('.toggleCampaignDetails').click(function(){
                    var id = $(this).parent().parent().attr('id');
                    var elem = $(this);
                    $(this).toggleClass('row-details-open');
                    
                    if($(this).hasClass('row-details-open')){
                      
                      if($(this).attr('data-withdata') == 1){
                        $('.rowAdgroup'+id).show();
                        $('.parentRow'+id).show();
                        return false;
                      }

                      var index = $(this).parent().parent().index();
                      
                      if(typeof id == 'undefined') id = 0;
                      var html = '';

                      var filter_date_start = $('#start_date').val();
                      var filter_date_end = $('#end_date').val();
                      var filter_show_enabled = $('#filter_show_enabled').prop('checked') ? 1 : 0;
                      var _token = "{{ csrf_token() }}";
                      object_filter = { _token: _token, filter_date_start: filter_date_start, filter_date_end:filter_date_end,id: id, filter_show_enabled:filter_show_enabled };
                      if(isFilter == 1){
                        var filter_imp = $('#filter_imp_min').val()+"-"+$('#filter_imp_max').val();
                        var filter_clicks = $('#filter_clicks_min').val()+"-"+$('#filter_clicks_max').val();
                        var filter_ctr = $('#filter_ctr_min').val()+"-"+$('#filter_ctr_max').val();
                        var filter_total_spend = $('#filter_total_spend_min').val()+"-"+$('#filter_total_spend_max').val();
                        var filter_avg_cpc = $('#filter_avg_cpc_min').val()+"-"+$('#filter_avg_cpc_max').val();
                        var filter_acos = $('#filter_acos_min').val()+"-"+$('#filter_acos_max').val();
                        var filter_conv_rate = $('#filter_conv_rate_min').val()+"-"+$('#filter_conv_rate_max').val();
                        var filter_revenue = $('#filter_revenue_min').val()+"-"+$('#filter_revenue_max').val();
                        var filter_country = ($('#filter_country').val()) ? $('#filter_country').val().toString() : '';
                        var filter_camp_type = ($('#filter_camp_type').val()) ? $('#filter_camp_type').val().toString() : '';
                        var filter_camp_name = ($('#filter_camp_name').val()) ? $('#filter_camp_name').val().toString() : '';
                        var filter_ad_group = ($('#filter_ad_group').val()) ? $('#filter_ad_group').val().toString() : '';
                        var filter_keyword = $('#filter_keyword').val();
                        var filter_recommendation = ($('#filter_recommendation').val()) ? $('#filter_recommendation').val().toString() : '';


                        object_filter = { _token: _token, filter_date_start: filter_date_start, filter_date_end:filter_date_end, filter_imp:filter_imp, filter_clicks:filter_clicks, filter_ctr:filter_ctr, filter_total_spend:filter_total_spend, filter_avg_cpc:filter_avg_cpc, filter_acos:filter_acos, filter_conv_rate:filter_conv_rate, filter_country:filter_country, filter_camp_type:filter_camp_type, filter_camp_name:filter_camp_name, filter_keyword:filter_keyword, filter_revenue:filter_revenue, filter_ad_group:filter_ad_group, id: id, filter_show_enabled:filter_show_enabled, filter_recommendation:filter_recommendation };
                      }

                      // var loading = '<tr class="addGroupContainer_'+id+' addGroup_style"><td class="colAddGroup_'+id+'" colspan="14">'+
                      //             '<div class="text-center loadingAddGroup_'+id+'"><b>Loading result </b><i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span></div>'+
                      //         '</td></tr>';

                      // $('#adsperformance_table > tbody > tr').eq(index).after(loading);
                      // alert(isFilter)


                      $.ajax({
                        url: 'performance_adgroup',
                        type: "POST",
                        data: object_filter,
                        beforeSend: function(){
                          $('.loadingTableContainer').show();
                        },
                        success: function(result){
                          elem.attr('data-withdata','1');
                          var response = jQuery.parseJSON(result);
                          // console.log(result)
                          $('.loadingAddGroup_'+id).remove();
                          var output = '';
                          output += '<tr class="rowAdgroup'+id+'">'+
                                      '<td></td>'+
                                      '<td style="border-left:2px solid #FF5722"></td>'+
                                      '<td colspan="13" width="" >'+
                                          '<span class="text-orange"><i class="fa fa-cubes" style="margin-right:10px;"></i> Ad Group</span>'+
                                      '</td>'+
                                  '</tr>';

                          for(var i in response.data){
                            output += '<tr class="rowAdgroup'+id+'" data-bid="'+response.data[i].defaultbid+'" data-id="'+response.data[i].id+'">'+
                                          '<td width="50" class="text-center">'+response.data[i].warningIcon+'</td>'+
                                          '<td width="15" style="border-left:2px solid #FF5722;padding-left:20px">'+
                                                response.data[i].icon+
                                          '</td>'+
                                          '<td width="" style="padding-left:20px">'+
                                                '<span class="color-blue">'+
                                                  response.data[i].rowtitle+
                                                '</span>'+
                                          '</td>'+
                                          '<td width="60">'+response.data[i].imp+'</td>'+
                                          '<td width="60">'+response.data[i].clicks+'</td>'+
                                          '<td width="60">'+response.data[i].ctr+'</td>'+
                                          '<td width="60">'+response.data[i].rev+'</td>'+
                                          '<td width="60">'+response.data[i].orders+'</td>'+
                                          '<td width="60">'+response.data[i].cr+'</td>'+
                                          '<td width="60">'+response.data[i].total_spend+'</td>'+
                                          '<td width="60">'+response.data[i].average_cpc+'</td>'+
                                          '<td width="60">'+response.data[i].acos+'</td>'+
                                          '<td width="60">'+response.data[i].bid+'</td>'+
                                          '<td width="60">'+response.data[i].max_bid+'</td>'+
                                          '<td width="60">'+response.data[i].recommendation+'</td>'+
                                 '</tr>';
                          }
                          $('#adsperformance_table > tbody > tr').eq(index).after(output);
                          $('.loadingTableContainer').hide();

                          $('.warningChangesPopUp').mousemove(function(e){
                              object_bid_change = {
                                id: $(this).attr('data-id')
                              }
                              var divid = 'bidUpdatePopUp'
                              var div = $('.bidUpdatePopUp');
                              var left  = e.pageX +20  + "px";
                              var top  = e.pageY + "px";

                              $(div).css('left',left)
                              $(div).css('top',top)

                              $("."+divid).fadeIn();
                              return false;
                          })
                          
                        },
                        error: function(xhr, status, response){
                          swal('Oops!','An error occured during fetching of data','error');
                        }
                      })

                    }else{
                      var id = $(this).parent().parent().attr('id');
                      if(typeof id == 'undefined') id = 0;
                      $('.rowAdgroup'+id).hide();
                      $('.parentRow'+id).hide();
                      $(this).parent().parent().parent().find('.addGroupContainer_'+id).hide()
                    }
                })

                // $('.warningChangesPopUp').mousemove(function(e){
                    
                // })

                $('.closeBidUpdatePopUp').click(function(){
                    $('.bidUpdatePopUp').fadeOut('',function(){
                        $(this).hide();
                    })
                })

            }
        });
      
      $('.dataTable').wrap('<div class="dataTables_scroll" />');

      var tableColumnToggler = $('#adperformance_table_column_toggler');

      $('input[type="checkbox"]', tableColumnToggler).on("change",function() {
          var column = oTable2.column( $(this).attr('data-column') );
          column.visible( ! column.visible() );
          return false;
      });

  }

  function toggleKeyword(elem,query_for){
    $(elem).toggleClass('row-details-open');
    var id = $(elem).parent().parent().attr('data-id');
    var data_default_bid = $(elem).parent().parent().attr('data-bid');
    var index = $(elem).parent().parent().index();

    if($(elem).hasClass('row-details-open')){
        if($(elem).attr('data-withdata') == 1){
          $('.rowKeyword'+id).show();
          return false
        }

        var forQuery = { forQuery: query_for, overRideId: id, data_default_bid: data_default_bid }
        $.ajax({
            url: 'performance_adgroup',
            type: 'POST',
            data: Object.assign({}, object_filter, forQuery),
            beforeSend: function(){
              $('.loadingTableContainer').show();
            },
            success: function(result){
                $(elem).attr('data-withdata','1')
                var response = jQuery.parseJSON(result);
                // console.log(response)
                var output = '';
                var icon = response.forQuery == "keyword" ? "fa-key" : "fa-search";
                var margin = response.forQuery == "keyword" ? "40px" : "80px";
                var subTableTitle = response.forQuery == "keyword" ? "Keyword" : "Customer Search Term"
                var withToggle = response.forQuery == "keyword" ? '<span class="row-details row-details-close toggleKeywordDetails m-r-10" onclick="toggleKeyword(this,\'search_term\')" data-withdata="0"></span>' : "";

                output += '<tr class="rowKeyword'+id+' parentRow'+response.parentid+'">'+
                            '<td></td>'+
                            '<td style="border-left:2px solid #FF5722;"></td>'+
                            '<td colspan="13" width="">'+
                                '<span class="text-orange"><i class="fa '+icon+'" style="margin-right:10px;"></i> '+subTableTitle+'</span>'+
                            '</td>'+
                            '<td></td>'+
                        '</tr>';
              if(response.data.length > 0){
                for(var i in response.data){
                  output += '<tr class="rowKeyword'+id+' parentRow'+response.data[i].parentid+'" data-id="'+response.data[i].id+'">'+
                                '<td width="50" class="text-center">'+response.data[i].warningIcon+'</td>'+
                                '<td width="65" style="border-left:2px solid #FF5722;padding-left:40px">'+
                                  withToggle+
                                  '<span data-tipso-title="" data-tipso="Match Type: '+response.data[i].match_type.toUpperCase()+' " class="matchTypeTipso match_'+response.data[i].match_type.toLowerCase()+' m-r-10">'+response.data[i].match_type.toUpperCase().substring(0,1)+'</span>'+
                                '</td>'+
                                '<td width="" style="">'+
                                '<span class="color-blue">'+
                                        response.data[i].rowtitle+
                                      '</span>'+
                                '</td>'+
                                '<td width="60">'+response.data[i].imp+'</td>'+
                                '<td width="60">'+response.data[i].clicks+'</td>'+
                                '<td width="60">'+response.data[i].ctr+'</td>'+
                                '<td width="60">'+response.data[i].rev+'</td>'+
                                '<td width="60">'+response.data[i].orders+'</td>'+
                                '<td width="60">'+response.data[i].cr+'</td>'+
                                '<td width="60">'+response.data[i].total_spend+'</td>'+
                                '<td width="60">'+response.data[i].average_cpc+'</td>'+
                                '<td width="60">'+response.data[i].acos+'</td>'+
                                '<td width="60">'+response.data[i].bid+'</td>'+
                                '<td width="60">'+response.data[i].max_bid+'</td>'+
                                '<td width="60">'+response.data[i].recommendation+'</td>'+
                       '</tr>';
                }
              }else{
                  output += '<tr class="rowKeyword'+id+' parentRow'+response.parentid+'">'+
                            '<td></td>'+
                            '<td style="border-left:2px solid #FF5722;padding-left:40px"></td>'+
                            '<td colspan="11" width="">'+
                                '<span class="color-blue">No Data Available</span>'+
                            '</td>'+
                            '<td></td>'+
                        '</tr>';
                }
                $('#adsperformance_table > tbody > tr').eq(index).after(output);

                $('.warningChangesPopUp').mousemove(function(e){
                    object_bid_change = {
                      id: $(this).attr('data-id')
                    }
                    var divid = 'bidUpdatePopUp'
                    var div = $('.bidUpdatePopUp');
                    var left  = e.pageX +20  + "px";
                    var top  = e.pageY + "px";

                    $(div).css('left',left)
                    $(div).css('top',top)

                    $("."+divid).fadeIn();
                    return false;
                })

                $('.matchTypeTipso').tipso({
                  position          : 'left',
                  background        : '#e8e8e8',
                  color             : '#787878',
                  size              : 'small'
                })

                $('.loadingTableContainer').hide();
               
            },
            error: function(xhr, status, response){
                swal('Oops!','An error occured during fetching of data','error');
            }
        })

    }else{
      if(typeof id == 'undefined') id = 0;
      $('.rowKeyword'+id).hide();
    }
  }

  function updateAdsBidV2(elem){

      var html = '<input type="number" class="inputTb" style="width:100%" value="'+$(elem).text()+'">',
          inputElem,
      id = $(elem).attr('data-row_id')
      keywordid = $(elem).attr('data-key-id')
      adgroupid = $(elem).attr('data-adg-id')

      $(elem).css('display','none')
      $(elem).parent().append(html)
      $('.inputTb').focus()
      inputElem = $('.inputTb')

      $('.inputTb').focusout(function() {
          $(this).remove()
          $(elem).show();
      })

      $(inputElem).keypress(function (e) {
          
          if (e.which == 13) {

              if ($(inputElem).val() == '') {
                return false;
              }

              if ($(inputElem).val() <= 0) {
                  swal(" ", "Please input not less than 0", "error");
                  return false;
              }

              var data = {
                _token: "{{ csrf_token() }}",
                row_id: id,
                adgroupid: adgroupid,
                keywordid: keywordid,
                bid_from: $(elem).text(),
                bid_to: $(inputElem).val()
              }

              $.ajax({
                  url: 'updateAdsBid',
                  type: 'POST',
                  data: data,
                  beforeSend: function(){
                      $('.loadingTableContainer').show();
                  },
                  success: function(result){
                      countChanges();
                      $(elem).text(result)
                      $('.loadingTableContainer').hide();
                  },
                  error: function(xhr, status, response){
                      $(this).remove()
                      $(elem).show()
                      swal('Oops!','An error occured during saving of data','error')
                      $('.loadingTableContainer').hide();
                  }
              })
              
          }

      });

      

      
  }

  function validateNumber(val){
    pattern = /^-?[0-9]\d*(\.\d+)?$/;
    validatate = pattern.test(val);
    if (!validatate) {
        return false;
    }else{
        return true;
    }
  }

  function initialize_filter_list(){
    $("#update_filter_msg").html("Updating Filter List...");
    var _token = "{{ csrf_token() }}";
    var data = { _token: _token };

    $.ajax({url: "getAdFilters", type: 'POST', data: data, success: function(result){
      var response = jQuery.parseJSON(result);
      $("#filter_table tbody").html("");
      for(var i = 0; i<response.length; i++){
        var id = response[i]['id'];
        var new_tr = '<tr>';
        new_tr += '<td>'+response[i]['filter_name']+'</td>';
        new_tr += '<td class="text-center" title="use"><button onclick="use_filter('+id+')" class="btn btn-success btn-sm"><i class="fa fa-check-square-o"></i></button></td>';
        new_tr += '<td class="text-center" title="edit"><button onclick="update_filter('+id+')" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></button></td>';
        new_tr += '<td class="text-center" title="trash"><button onclick="delete_filter('+id+')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></button></i></td>';
        new_tr += '</tr>';
        $("#filter_table tbody").append(new_tr);
      }
        // change the filter section to new
        $("#update_filter_msg").html("");
    }
    });
  }
  function add_filter(){
    $("#update_filter_msg").html("Saving new filter...");
    var _token = "{{ csrf_token() }}";
    var filter_name = $("#filter_name").val();
    var filter_date_start = $('#start_date').val();
    var filter_date_end = $('#end_date').val();
    var filter_imp = $('#filter_imp_min').val()+"-"+$('#filter_imp_max').val();
    var filter_clicks = $('#filter_clicks_min').val()+"-"+$('#filter_clicks_max').val();
    var filter_ctr = $('#filter_ctr_min').val()+"-"+$('#filter_ctr_max').val();
    var filter_total_spend = $('#filter_total_spend_min').val()+"-"+$('#filter_total_spend_max').val();
    var filter_avg_cpc = $('#filter_avg_cpc_min').val()+"-"+$('#filter_avg_cpc_max').val();
    var filter_acos = $('#filter_acos_min').val()+"-"+$('#filter_acos_max').val();
    var filter_conv_rate = $('#filter_conv_rate_min').val()+"-"+$('#filter_conv_rate_max').val();
    var filter_revenue = $('#filter_revenue_min').val()+"-"+$('#filter_revenue_max').val();
    var filter_country = ($('#filter_country').val()) ? $('#filter_country').val().toString() : '';
    var filter_camp_type = ($('#filter_camp_type').val()) ? $('#filter_camp_type').val().toString() : '';
    var filter_camp_name = ($('#filter_camp_name').val()) ? $('#filter_camp_name').val().toString() : '';
    var filter_ad_group = ($('#filter_ad_group').val()) ? $('#filter_ad_group').val().toString() : '';
    var filter_recommendation = ($('#filter_recommendation').val()) ? $('#filter_recommendation').val().toString() : '';
    var filter_keyword = $('#filter_keyword').val();
    var data = { _token: _token, filter_date_start: filter_date_start, filter_date_end:filter_date_end, filter_imp:filter_imp, filter_clicks:filter_clicks, filter_ctr:filter_ctr, filter_total_spend:filter_total_spend, filter_avg_cpc:filter_avg_cpc, filter_acos:filter_acos, filter_conv_rate:filter_conv_rate, filter_country:filter_country, filter_camp_type:filter_camp_type, filter_camp_name:filter_camp_name, filter_keyword:filter_keyword, filter_name:filter_name, filter_revenue:filter_revenue, filter_ad_group:filter_ad_group,filter_recommendation:filter_recommendation };
    $.ajax({url: "addAdFilter", type: 'POST', data: data, success: function(result){
        swal(" ", "Filter successfully added.", "success")
        initialize_filter_list();
        $("#update_filter_msg").html("");
    }
    });
  }

    function update_filter(id){
      $("#update_filter_msg").html("Updating filter...");
      var _token = "{{ csrf_token() }}";
      var filter_name = $('#filter_name').val();
      var filter_date_start = $('#start_date').val();
      var filter_date_end = $('#end_date').val();
      var filter_imp = $('#filter_imp_min').val()+"-"+$('#filter_imp_max').val();
      var filter_clicks = $('#filter_clicks_min').val()+"-"+$('#filter_clicks_max').val();
      var filter_ctr = $('#filter_ctr_min').val()+"-"+$('#filter_ctr_max').val();
      var filter_total_spend = $('#filter_total_spend_min').val()+"-"+$('#filter_total_spend_max').val();
      var filter_avg_cpc = $('#filter_avg_cpc_min').val()+"-"+$('#filter_avg_cpc_max').val();
      var filter_acos = $('#filter_acos_min').val()+"-"+$('#filter_acos_max').val();
      var filter_conv_rate = $('#filter_conv_rate_min').val()+"-"+$('#filter_conv_rate_max').val();
      var filter_revenue = $('#filter_revenue_min').val()+"-"+$('#filter_revenue_max').val();
      var filter_country = ($('#filter_country').val()) ? $('#filter_country').val().toString() : '';
      var filter_camp_type = ($('#filter_camp_type').val()) ? $('#filter_camp_type').val().toString() : '';
      var filter_camp_name = ($('#filter_camp_name').val()) ? $('#filter_camp_name').val().toString() : '';
      var filter_ad_group = ($('#filter_ad_group').val()) ? $('#filter_ad_group').val().toString() : '';
      var filter_recommendation = ($('#filter_recommendation').val()) ? $('#filter_recommendation').val().toString() : '';
      var filter_keyword = $('#filter_keyword').val();
      var data = { _token: _token, filter_date_start: filter_date_start, filter_date_end:filter_date_end, filter_imp:filter_imp, filter_clicks:filter_clicks, filter_ctr:filter_ctr, filter_total_spend:filter_total_spend, filter_avg_cpc:filter_avg_cpc, filter_acos:filter_acos, filter_conv_rate:filter_conv_rate, filter_country:filter_country, filter_camp_type:filter_camp_type, filter_camp_name:filter_camp_name, filter_keyword:filter_keyword, filter_revenue:filter_revenue, filter_name:filter_name, filter_ad_group:filter_ad_group ,id:id,filter_recommendation:filter_recommendation };
      $.ajax({url: "updateAdFilter", type: 'POST', data: data, success: function(result){
          $("#update_filter_msg").html("");
          swal(" ", "Filter successfully updated.", "success")
          initialize_filter_list();
      }
      });
    }

    function use_filter(id){
      var _token = "{{ csrf_token() }}";
      var data = { _token:_token, id:id };
      $.ajax({url: "getAdFilterData", type: 'POST', data: data, success: function(result){
        var response = jQuery.parseJSON(result);
        var imp = response['filter_imp'].split("-");
        if(imp[0].trim() != "") $('#filter_imp_min').val(imp[0]);
        if(imp[1].trim() != "") $('#filter_imp_max').val(imp[1]);
        var clicks = response['filter_clicks'].split("-");
        if(clicks[0].trim() != "") $('#filter_clicks_min').val(clicks[0]);
        if(clicks[1].trim() != "") $('#filter_clicks_max').val(clicks[1]);
        var ctr = response['filter_ctr'].split("-");
        if(ctr[0].trim() != "") $('#filter_ctr_min').val(ctr[0]);
        if(ctr[1].trim() != "") $('#filter_ctr_max').val(ctr[1]);
        var total_spend = response['filter_total_spend'].split("-");
        if(total_spend[0].trim() != "") $('#filter_total_spend_min').val(total_spend[0]);
        if(total_spend[1].trim() != "") $('#filter_total_spend_max').val(total_spend[1]);
        var avg_cpc = response['filter_avg_cpc'].split("-");
        if(avg_cpc[0].trim() != "") $('#filter_avg_cpc_min').val(avg_cpc[0]);
        if(avg_cpc[1].trim() != "") $('#filter_avg_cpc_max').val(avg_cpc[1]);
        var acos = response['filter_acos'].split("-");
        if(acos[0].trim() != "") $('#filter_acos_min').val(acos[0]);
        if(acos[1].trim() != "") $('#filter_acos_max').val(acos[1]);
        var conv_rate = response['filter_conv_rate'].split("-");
        if(conv_rate[0].trim() != "") $('#filter_conv_rate_min').val(conv_rate[0]);
        if(conv_rate[1].trim() != "") $('#filter_conv_rate_max').val(conv_rate[1]);
        var revenue = response['filter_revenue'].split("-");
        if(revenue[0].trim() != "") $('#filter_revenue_min').val(revenue[0]);
        if(revenue[1].trim() != "") $('#filter_revenue_max').val(revenue[1]);
        // if(response['filter_country'].trim() != "") $('#filter_country').val(response['filter_country']);
        if(response['filter_country']){
          county_arr = response['filter_country'].split(',')
          $('#filter_country').val(county_arr).trigger('chosen:updated');
        }
        // if(response['filter_camp_type'].trim() != "") $('#filter_camp_type').val(response['filter_camp_type']);
        if(response['filter_camp_type']){
          camp_type_arr = response['filter_camp_type'].split(',')
          $('#filter_camp_type').val(camp_type_arr).trigger('chosen:updated');
        }
        // if(response['filter_camp_name'].trim() != "") $('#filter_camp_name').val(response['filter_camp_name']);
        if(response['filter_camp_name']){
          camp_name_arr = response['filter_camp_name'].split(',')
          $('#filter_camp_name').val(camp_name_arr).trigger('chosen:updated');
        }
        // if(response['filter_ad_group'].trim() != "") $('#filter_ad_group').val(response['filter_ad_group']);
        if(response['filter_ad_group']){
          ad_group_arr = response['filter_ad_group'].split(',')
          $('#filter_ad_group').val(ad_group_arr).trigger('chosen:updated');
        }
        if(response['filter_recommendation']){
          recommendation_arr = response['filter_recommendation'].split(',')
          $('#filter_recommendation').val(recommendation_arr).trigger('chosen:updated');
        }
        if(response['filter_keyword'].trim() != "") $('#filter_keyword').val(response['filter_keyword']);
        $('#start_date').val(response['filter_date_start']);
        $('#end_date').val(response['filter_date_end']); 
        $('#filter_name').val(response['filter_name']); 
      }
      });
    }

    function delete_filter(id){
      $("#update_filter_msg").val('Deleting filter...');
      var _token = "{{ csrf_token() }}";
      var data = { _token:_token, id:id };
      $.ajax({url: "deleteAdFilterData", type: 'POST', data: data, success: function(result){
        $("#update_filter_msg").val('');
        initialize_filter_list();   
      }
      });
    }


    function deleteSavedRule(element,idRow){
      var data = {id: idRow}
      swal({
          title: ' ',
          text: "Are you sure you wan't to delete this rule?",
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#DD6B55',
          // cancelButtonColor: '#d33',
          confirmButtonText: 'Okay'
        }).then(function () {
          $.ajax({url: "deleteRule", type: 'POST', data: data, success: function(result){
                  var response = jQuery.parseJSON(result);
                  if (response[0].success) {
                    $('.ruleListTitleMain'+idRow).remove();
                    $('.listRuleContainer'+idRow).remove();
                    swal(" ", "Rule uccessfully deleted", "success")
                    initialize_table();
                  }else{
                    swal(' ','Something went wrong when deleting the rule','error')
                  }
                }
              });
        })
    }

    function saveChanges(element){
        var parentContainer = $(element).closest('.rowListContainer');
        var idRow = $(parentContainer).attr('data');
        if (validateHeaderDataList(idRow)) {
          if (validateCellDataList(idRow)) {
            $(element).html('Saving Changes... <i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span>');
            $(element).attr("disabled", true);
              var operation_arr = [];
              var matrix_arr = [];
              var metric_arr = [];
              var value_arr = [];
              var data;
              var counter=0;
              
              $('.listRuleContainer'+idRow).find('select[name="recommendation_operation"]').each(function(){
                  operation_arr[counter] = $(this).val();
                  counter++
              });

              counter=0;
              $('.listRuleContainer'+idRow).find('select[name="recommendation_matrix"]').each(function(){
                  matrix_arr[counter] = $(this).val();
                  counter++
              });

              counter=0;
              $('.listRuleContainer'+idRow).find('select[name="recommendation_metric"]').each(function(){
                  metric_arr[counter] = $(this).val();
                  counter++
              });

              counter=0;
              $('.listRuleContainer'+idRow).find('input[name="recommendation_value"]').each(function(){
                  value_arr[counter] = $(this).val();
                  counter++
              });

              data = {
                  id: idRow,
                  recommendationName: $('#recommendation_name'+idRow).val(),
                  campaignName: $('#recommendation_campaign'+idRow).val().toString(),
                  country: $('#recommendation_country'+idRow).val().toString(),
                  recommendation: $('#recommendation'+idRow).val(),
                  timePeriod: $('#recommendation_period'+idRow).val(),
                  operation: operation_arr,
                  matrix: matrix_arr,
                  metric: metric_arr,
                  value: value_arr
              }

              $.ajax({url: "saveChanges", type: 'POST', data: data, success: function(result){
                  var response = jQuery.parseJSON(result);
                  if (response[0].success) {
                    swal(" ", "Recommendation rule successfully updated", "success")
                    $(element).html('Save Changes')
                    $(element).attr("disabled", false);
                    initialize_table();
                  }else{
                    swal(' ','Something went wrong when saving your request','error')
                    $(element).html('Save Changes')
                    $(element).attr("disabled", false);
                  }
                }
              });
          }else{
            swal('Input required','All fields are required.','error')
            $(element).html('Save Changes')
            $(element).attr("disabled", false);
          }
        }else{
          swal('Input required','All fields are required.','error')
          $(element).html('Save Changes')
          $(element).attr("disabled", false);
        }
    }

    function validateCellDataList(idRow){
      var cellData;
      var error = false;
      $(".ruleRow"+idRow).find('td').each(function(){
          cellData = $(this).find('select').val();
          if(typeof cellData == 'undefined'){
            cellData = $(this).find('input').val(); 
          }
          if (cellData == "") {
              error = true;
          }
      });

      if (error) {
         return false
      }else{
         return true;
      }
  }

  function validateHeaderDataList(idRow){
    var error = false;
    if($('#recommendation_name'+idRow).val() == "") error = true;
    if(!$('#recommendation_campaign'+idRow).val()) error = true;
    if(!$('#recommendation_country'+idRow).val()) error = true;
    if($('#recommendation'+idRow).val() == "") error = true;
    if($('#recommendation_period'+idRow).val() == "") error = true;

    if (error) {
       return false
    }else{
       return true;
    }
  }

  function addConditionList(element){
      var parentContainer = $(element).closest('.rowListContainer');
      var idRow = $(parentContainer).attr('data');
      if (validateCellDataList(idRow)) {
          addRuleRow(idRow);
      }else{
        swal('Input required','All fields are required.','error')
      }
  }

    function populateRecommendationRules(){
      $.ajax({url: "showAdRule", type: 'GET' , success: function(result){
              var response = jQuery.parseJSON(result);
              var countrySelect = $("<select />").append($("#recommendation_country").clone()).html();
              var campaignameSelect = $("<select />").append($("#recommendation_campaign").clone()).html();
              if (response.length == 0) $('.list_recommendation_rules')
                    .html('<div class="col-md-12 text-center">'+
                              '<strong>No saved rules</strong>'+
                          '</div>');

              for(var index in response){
                  var countries = response[index].country.split(',');
                  var campaign_names = response[index].campaign_name.split(',');
                  var id = response[index].id
                  var output = '<div class="col-md-12 ruleListTitleMain'+id+'">'+
                                '<div class="col-md-12 ruleListTitle" data="'+id+'">'+
                                  response[index].recommendation_name+' <i class="fa fa-angle-right"></i>'+
                                  '<i class="fa fa-window-maximize pull-right m-t-5 maximizeBtn'+id+'" aria-hidden="true"></i>'+
                                '</div>'+
                              '</div>';

                  output += '<div class="col-md-12 rowListContainer m-b-15 listRuleContainer'+id+'" data="'+id+'">'+
                  '<div class="col-md-12 ruleTableRow">'+
                            '<div class="row">'+
                              '<div class="col-md-12">'+
                                '<div class="col-lg-6 col-md-12">'+
                                  '<div class="row">'+
                                    '<div class="col-lg-4 col-md-6">'+
                                      'Recommendation Name'+
                                    '</div>'+
                                    '<div class="col-lg-8 col-md-6">'+
                                      '<input id="recommendation_name'+id+'" type="text" name="" class="form-control" value="'+response[index].recommendation_name+'">'+
                                    '</div>'+
                                  '</div>'+
                                '</div>'+
                              '</div>'+
                            '</div>'+
                  '<div class="col-lg-6 col-md-12 m-t-5">'+
                            '<div class="row">'+
                              '<div class="col-lg-4 col-md-6">'+
                                'Country'+
                              '</div>'+
                              '<div class="col-lg-8 col-md-6">';
                      output += '<select class="form-control recMultiSelect2" required="required" id="recommendation_country'+id+'" multiple>';
                                  $('.select-countryRec option').each(function(){
                                    var val = $(this).val(); var text = $(this).text()
                                    var selected = '';
                                    for (var i = 0; i < countries.length; i++) {
                                        if (countries[i] == val.toLowerCase()) selected = 'selected';
                                    }
                                     output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                  });
                      output += '</select>'+
                              '</div>'+
                            '</div>'+
                            '<div class="row m-t-5">'+
                              '<div class="col-lg-4 col-md-6">'+
                                'Campaign Name'+
                              '</div>'+
                              '<div class="col-lg-8 col-md-6">';
                      output += '<select class="form-control recMultiSelect2" required="required" id="recommendation_campaign'+id+'" multiple>';
                                  var counter = 0;
                                  $('.select-campaignRec option').each(function(){
                                    var val = $(this).val(); var text = $(this).text()
                                    var selected = '';
                                    for (var i = 0; i < campaign_names.length; i++) {
                                      if (campaign_names[i] == val) selected = 'selected';
                                    }
                                     output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                     counter ++
                                  });
                      output += '</select>'+
                              '</div>'+
                            '</div>'+
                          '</div>'+
                          '<div class="col-lg-6 col-md-12 m-t-5">'+
                            '<div class="row">'+
                              '<div class="col-lg-4 col-md-6">'+
                               'Time Period'+
                              '</div>'+
                              '<div class="col-lg-8 col-md-6">';
                        output += '<select id="recommendation_period'+id+'" class="form-control">';
                                  $('.select-time-period option').each(function(){
                                    var val = $(this).val(); var text = $(this).text()
                                    var selected = '';
                                    if (response[index].time_period == val) selected = 'selected';
                                     output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                  });
                        output +='</select>'+
                              '</div>'+
                            '</div>'+
                            '<div class="row m-t-5">'+
                              '<div class="col-lg-4 col-md-6">'+
                                'Recommendation'+
                              '</div>'+
                              '<div class="col-lg-8 col-md-6">';
                        output += '<select id="recommendation'+id+'" class="form-control">';
                                  $('.select-recommendation option').each(function(){
                                    var val = $(this).val(); var text = $(this).text()
                                    var selected = '';
                                    if (response[index].recommendation == val) selected = 'selected';
                                     output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                  });
                        output +='</select>'+
                              '</div>'+
                            '</div>'+
                          '</div>'+
                          '<div class="col-md-12">'+
                          '<hr>'+
                            '<span style="color: #7AC482">Conditions</span>'+
                          '</div>'+
                          '<div class="col-md-12">'+
                          '<div class="table-responsive">';
                        output += '<table cellspacing=0 cellpadding=0 class="table table-bordered table_res" id="tableRule'+id+'" style="min-width:400px;">'+
                              '<thead>'+
                                '<tr>'+
                                    '<th class="text-center">Operation</th>'+
                                    '<th class="text-center">Matrix</th>'+
                                    '<th class="text-center">Metric</th>'+
                                    '<th class="text-center" colspan="2">Value</th>'+
                                '</tr>'+
                              '</thead>'+
                              '<tbody>';
                          for (var i = 0; i < response[index].condition.length; i++) {
                        output += '<tr class="ruleRow'+id+'">'+
                                    '<td>'+
                                      '<select id="recommendation_operation" name="recommendation_operation" class="form-control" required="">';
                                        $('.select_operation option').each(function(){
                                          var val = $(this).val(); var text = $(this).text()
                                          var selected = '';
                                          if (response[index].condition[i].operation == val) selected = 'selected';
                                           output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                        });
                          output += '</select>'+
                                    '</td>'+
                                    '<td>'+
                                      '<select id="recommendation_matrix" name="recommendation_matrix" class="form-control" required="">';
                                         $('.select_matrix option').each(function(){
                                          var val = $(this).val(); var text = $(this).text()
                                          var selected = '';
                                          if (response[index].condition[i].matrix == val) selected = 'selected';
                                           output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                        });
                          output += '</select>'+
                                    '</td>'+
                                    '<td>'+
                                      '<select id="recommendation_metric" name="recommendation_metric" class="form-control" required="">';
                                         $('.select_metric option').each(function(){
                                            var val = $(this).val(); var text = $(this).text()
                                            var selected = '';
                                            if (response[index].condition[i].metric == val) selected = 'selected';
                                             output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                          });
                          output +=  '</select>'+
                                    '</td>';
                                    if(i == 0){ output += '<td colspan="2">';}else{output += '<td>';}
                          output += '<input id="recommendation_value" type="text" name="recommendation_value" class="form-control" value="'+response[index].condition[i].value+'" onblur="ValidateMinMax(this,12)">'+
                                    '</td>';
                                    if (i != 0) {
                          output += '<td width="2" style="vertical-align: middle;"><button onclick="closeRow(this)" class="btn btn-danger btn-sm btnDeleteRow" style="border-radius:0px;"><i class="fa fa-trash"></i></button></td>';
                                    }
                          output += '</tr>';
                              } // end for condition table
                        output += '</tbody>'+
                            '</table>'+
                            '</div>';
                        output += '<div class="row">'+
                            '<div class="col-lg-6 col-md-12">'+
                              '<button class="btn btn-primary btn-sm m-r-10 m-t-5" onclick="enableSelectedRowList(this)">Edit rule</button>'+
                              '<button class="btn btn-primary btn-sm m-r-10 cancelEditBtn m-t-5" onclick="disableSelectedRowList(this)" style="border-radius:0px;display:none">Cancel Edit</button>'+
                              '<button class="btn btn-primary btn-sm m-r-10 addConditionBtn m-t-5" onclick="addConditionList(this)"  style="border-radius:0px;display:none;background:#4FB7FE">Add condition</button>'+
                              '<button class="btn btn-success btn-sm m-r-10 btnSaveChanes m-t-5" onclick="saveChanges(this)" style="border-radius:0px;display:none">Save changes</button>'+
                              '<button class="btn btn-danger btn-sm btnDeleteListRow m-t-5" onclick="deleteSavedRule(this,'+id+')" style="border-radius:0px;display:none">Delete</button>'+
                            '</div>'+
                            '</div>'+
                          '</div>'+
                          '</div>'+
                          '</div>';

              $('.list_recommendation_rules').append(output);

              }//end loop
              $(".recMultiSelect2").chosen({allow_single_deselect: true}); 
              $('.rowListContainer').hide();
              $('.countrySelect').html(countrySelect)
              $('.campaignameSelect').html(campaignameSelect);
              $('.rowListContainer').find('select, input, .btnDeleteRow').attr("disabled", true);
              $('.recMultiSelect2').prop('disabled', true).trigger("chosen:updated");
              $('.recommendationcontainer').hide();
              activateToggle('');
          }//end ajax success
        });

    }

    function popupateAddedRule(response){
            var countrySelect = $("<select />").append($("#recommendation_country").clone()).html();
            var campaignameSelect = $("<select />").append($("#recommendation_campaign").clone()).html();
            if (response.length == 0) $('.list_recommendation_rules')
                  .html('<div class="col-md-12 text-center">'+
                            '<strong>No saved rules</strong>'+
                        '</div>');
            for(var index in response){
                var countries = response[index].country.split(',');
                var campaign_names = response[index].campaign_name.split(',');
                var id = response[index].id
                var output = '<div class="col-md-12 ruleListTitleMain'+id+'">'+
                              '<div class="col-md-12 ruleListTitle'+id+' ruleListTitleToggle" data="'+id+'">'+
                                response[index].recommendation_name+' <i class="fa fa-angle-right"></i>'+
                                '<i class="fa fa-window-maximize pull-right m-t-5 maximizeBtn'+id+'" aria-hidden="true"></i>'+
                              '</div>'+
                            '</div>';

                output += '<div class="col-md-12 rowListContainer m-b-15 listRuleContainer'+id+'" data="'+id+'">'+
                '<div class="col-md-12 ruleTableRow">'+
                          '<div class="row">'+
                            '<div class="col-md-12">'+
                              '<div class="col-lg-6 col-md-12">'+
                                '<div class="row">'+
                                  '<div class="col-lg-4 col-md-6">'+
                                    'Recommendation Name'+
                                  '</div>'+
                                  '<div class="col-lg-8 col-md-6">'+
                                    '<input id="recommendation_name'+id+'" type="text" name="" class="form-control" value="'+response[index].recommendation_name+'">'+
                                  '</div>'+
                                '</div>'+
                              '</div>'+
                            '</div>'+
                          '</div>'+
                '<div class="col-lg-6 col-md-12 m-t-5">'+
                          '<div class="row">'+
                            '<div class="col-lg-4 col-md-6">'+
                              'Country'+
                            '</div>'+
                            '<div class="col-lg-8 col-md-6">';
                    output += '<select class="form-control recMultiSelect2" required="required" id="recommendation_country'+id+'" multiple>';
                                $('.select-countryRec option').each(function(){
                                  var val = $(this).val(); var text = $(this).text()
                                  var selected = '';
                                    for (var i = 0; i < countries.length; i++) {
                                        if (countries[i] == val.toLowerCase()) selected = 'selected';
                                    }
                                   output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                });
                    output += '</select>'+
                            '</div>'+
                          '</div>'+
                          '<div class="row m-t-5">'+
                            '<div class="col-lg-4 col-md-6">'+
                              'Campaign Name'+
                            '</div>'+
                            '<div class="col-lg-8 col-md-6">';
                    output += '<select class="form-control recMultiSelect2" required="required" id="recommendation_campaign'+id+'" multiple>';
                                $('.select-campaignRec option').each(function(){
                                  var val = $(this).val(); var text = $(this).text()
                                  var selected = '';
                                    for (var i = 0; i < campaign_names.length; i++) {
                                      if (campaign_names[i] == val) selected = 'selected';
                                    }
                                   output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                });
                    output += '</select>'+
                            '</div>'+
                          '</div>'+
                        '</div>'+
                        '<div class="col-lg-6 col-md-12 m-t-5">'+
                          '<div class="row">'+
                            '<div class="col-lg-4 col-md-6">'+
                             'Time Period'+
                            '</div>'+
                            '<div class="col-lg-8 col-md-6">';
                      output += '<select id="recommendation_period'+id+'" class="form-control">';
                                $('.select-time-period option').each(function(){
                                  var val = $(this).val(); var text = $(this).text()
                                  var selected = '';
                                  if (response[index].time_period == val) selected = 'selected';
                                   output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                });
                      output +='</select>'+
                            '</div>'+
                          '</div>'+
                          '<div class="row m-t-5">'+
                            '<div class="col-lg-4 col-md-6">'+
                              'Recommendation'+
                            '</div>'+
                            '<div class="col-lg-8 col-md-6">';
                      output += '<select id="recommendation'+id+'" class="form-control">';
                                $('.select-recommendation option').each(function(){
                                  var val = $(this).val(); var text = $(this).text()
                                  var selected = '';
                                  if (response[index].recommendation == val) selected = 'selected';
                                   output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                });
                      output +='</select>'+
                            '</div>'+
                          '</div>'+
                        '</div>'+
                        '<div class="col-md-12">'+
                        '<hr>'+
                          '<span style="color: #7AC482">Conditions</span>'+
                        '</div>'+
                        '<div class="col-md-12">'+
                        '<div class="table-responsive">';
                      output += '<table cellspacing=0 cellpadding=0 class="table table-bordered table_res" id="tableRule'+id+'" style="min-width:400px;">'+
                            '<thead>'+
                              '<tr>'+
                                  '<th class="text-center">Operation</th>'+
                                  '<th class="text-center">Matrix</th>'+
                                  '<th class="text-center">Metric</th>'+
                                  '<th class="text-center" colspan="2">Value</th>'+
                              '</tr>'+
                            '</thead>'+
                            '<tbody>';
                        for (var i = 0; i < response[index].condition.length; i++) {
                      output += '<tr class="ruleRow'+id+'">'+
                                  '<td>'+
                                    '<select id="recommendation_operation" name="recommendation_operation" class="form-control" required="">';
                                      $('.select_operation option').each(function(){
                                        var val = $(this).val(); var text = $(this).text()
                                        var selected = '';
                                        if (response[index].condition[i].operation == val) selected = 'selected';
                                         output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                      });
                        output += '</select>'+
                                  '</td>'+
                                  '<td>'+
                                    '<select id="recommendation_matrix" name="recommendation_matrix" class="form-control" required="">';
                                       $('.select_matrix option').each(function(){
                                        var val = $(this).val(); var text = $(this).text()
                                        var selected = '';
                                        if (response[index].condition[i].matrix == val) selected = 'selected';
                                         output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                      });
                        output += '</select>'+
                                  '</td>'+
                                  '<td>'+
                                    '<select id="recommendation_metric" name="recommendation_metric" class="form-control" required="">';
                                       $('.select_metric option').each(function(){
                                          var val = $(this).val(); var text = $(this).text()
                                          var selected = '';
                                          if (response[index].condition[i].metric == val) selected = 'selected';
                                           output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                        });
                        output +=  '</select>'+
                                  '</td>';
                                  if(i == 0){ output += '<td colspan="2">';}else{output += '<td>';}
                        output += '<input id="recommendation_value" type="text" name="recommendation_value" class="form-control" value="'+response[index].condition[i].value+'" onblur="ValidateMinMax(this,12)">'+
                                  '</td>';
                                  if (i != 0) {
                        output += '<td width="2" style="vertical-align: middle;"><button onclick="closeRow(this)" class="btn btn-danger btn-sm btnDeleteRow" style="border-radius:0px;"><i class="fa fa-trash"></i></button></td>';
                                  }
                        output += '</tr>';
                            } // end for condition table
                      output += '</tbody>'+
                          '</table>'+
                          '</div>';
                      output += '<div class="row">'+
                          '<div class="col-lg-6 col-md-12">'+
                            '<button class="btn btn-primary btn-sm m-r-10 m-t-5" onclick="enableSelectedRowList(this)">Edit rule</button>'+
                            '<button class="btn btn-primary btn-sm m-r-10 cancelEditBtn m-t-5" onclick="disableSelectedRowList(this)" style="border-radius:0px;display:none">Cancel Edit</button>'+
                            '<button class="btn btn-primary btn-sm m-r-10 addConditionBtn m-t-5" onclick="addConditionList(this)"  style="border-radius:0px;display:none;background:#4FB7FE">Add condition</button>'+
                            '<button class="btn btn-success btn-sm m-r-10 btnSaveChanes m-t-5" onclick="saveChanges(this)" style="border-radius:0px;display:none">Save changes</button>'+
                            '<span class="loading-edit-button"></span>'+
                            '<button class="btn btn-danger btn-sm btnDeleteListRow m-t-5" onclick="deleteSavedRule(this,'+id+')" style="border-radius:0px;display:none">Delete</button>'+
                          '</div>'+
                          '</div>'+
                        '</div>'+
                        '</div>'+
                        '</div>';

            $('.list_recommendation_rules').prepend(output);
            $(".recMultiSelect2").chosen({allow_single_deselect: true}); 
            $('.rowListContainer').hide();
            $('.countrySelect').html(countrySelect)
            $('.campaignameSelect').html(campaignameSelect);
            $('.rowListContainer').find('select, input, .btnDeleteRow').attr("disabled", true);
            $('.recMultiSelect2').prop('disabled', true).trigger("chosen:updated");
            activateToggle(id);

            }//end loop

            
    }

    function activateToggle(id){
       $('.ruleListTitle'+id).click(function(){
         var idRow = $(this).attr('data')
         $('.listRuleContainer'+idRow).toggle('fast', function(){
              if ( $('.listRuleContainer'+idRow).is(':visible') ) {
                $('.maximizeBtn'+idRow).removeClass('fa-window-maximize')
                $('.maximizeBtn'+idRow).addClass('fa-window-minimize')
             }else{
                $('.maximizeBtn'+idRow).removeClass('fa-window-minimize')
                $('.maximizeBtn'+idRow).addClass('fa-window-maximize')
             }
         })
      })
    }

    function enableSelectedRowList(element){
        var parentContainer = $(element).closest('.rowListContainer');
        $(parentContainer).find('.cancelEditBtn, .btnSaveChanes, .btnDeleteListRow, .addConditionBtn').show();
        $(parentContainer).find('select, input, .btnDeleteRow').attr("disabled", false);
        $(parentContainer).find('.recMultiSelect2').prop('disabled', false).trigger("chosen:updated");
    }

    function disableSelectedRowList(element){
        var parentContainer = $(element).closest('.rowListContainer');
        $(parentContainer).find('.cancelEditBtn, .btnSaveChanes, .btnDeleteListRow, .addConditionBtn').hide();
        $(parentContainer).find('select, input, .btnDeleteRow').attr("disabled", true);
        $(parentContainer).find('.recMultiSelect2').prop('disabled', true).trigger("chosen:updated");
    }


    function init_loading(){

      var flip_elements = '#revenue_widget, #ads_spend_widget, #acos_widget, #cpc_widget, #impressions_widget, #ctr_widget, #clicks_widget, #cr_widget';

      var graph_elements = '#visitsspark-chart1, #visitsspark-chart2, #visitsspark-chart3, #visitsspark-chart4, #visitsspark-chart5, #visitsspark-chart6, #visitsspark-chart7, #visitsspark-chart8';

      $(flip_elements).flip({
          axis: 'x',
          trigger: 'click'
      });

      $(graph_elements).sparkline([0], {
                type: 'line',
                width: '100%',
                height: '48',
                lineColor: '#4fb7fe',
                fillColor: '#e7f5ff',
                tooltipSuffix: 'Revenue'
            });
    }

    function close_param(element){
        var filter_criteria = $(element).attr("data");
        switch (filter_criteria) {
          case 'date_range':
                $('#start_date').val('');
                $('#end_date').val('');
                $(element).parent().remove();
          break;
          case 'country':
                $('#filter_country').val('').trigger('chosen:updated');
                $(element).parent().remove();
          break;
          case 'camp_type':
                $('#filter_camp_type').val('').trigger('chosen:updated');
                $(element).parent().remove();
          break;
          case 'camp_name':
                $('#filter_camp_name').val('').trigger('chosen:updated');
                $(element).parent().remove();
          break;
          case 'ad_group':
                $('#filter_ad_group').val('').trigger('chosen:updated');
                $(element).parent().remove();
          break;
          case 'recommendation':
                $('#filter_recommendation').val('').trigger('chosen:updated');
                $(element).parent().remove();
          break;
          case 'keyword':
                $('#filter_keyword').val('');
                $(element).parent().remove();
          break;
          case 'time_range':
                $('#time_range').val('');
                $(element).parent().remove();
          break;
          case 'imp':
                $('#filter_imp_min').val('');
                $('#filter_imp_max').val('');
                $(element).parent().remove();
          break;
          case 'clicks':
                $('#filter_clicks_min').val('');
                $('#filter_clicks_max').val('');
                $(element).parent().remove();
          break;
          case 'ctr':
                $('#filter_ctr_min').val('');
                $('#filter_ctr_max').val('');
                $(element).parent().remove();
          break;
          case 'total_spend':
                $('#filter_total_spend_min').val('');
                $('#filter_total_spend_max').val('');
                $(element).parent().remove();
          break;
          case 'avg_cpc':
                $('#filter_avg_cpc_min').val('');
                $('#filter_avg_cpc_max').val('');
                $(element).parent().remove();
          break;
          case 'acos':
                $('#filter_acos_min').val('');
                $('#filter_acos_max').val('');
                $(element).parent().remove();
          break;
          case 'conv_rate':
                $('#filter_conv_rate_min').val('');
                $('#filter_conv_rate_max').val('');
                $(element).parent().remove();
          break;
          case 'revenue':
                $('#filter_revenue_min').val('');
                $('#filter_revenue_max').val('');
                $(element).parent().remove();
          break;
        }
    }

    function reset_filter(){
        $('#filter_name').val(''); 
        $('#start_date').val('');
        $('#end_date').val('');
        $('#filter_imp_min, #filter_imp_max').val('');
        $('#filter_clicks_min, #filter_clicks_max').val();
        $('#filter_ctr_min, #filter_ctr_max').val()+"-"+$('');
        $('#filter_total_spend_min, #filter_total_spend_max').val('');
        $('#filter_avg_cpc_min, #filter_avg_cpc_max').val('');
        $('#filter_acos_min, #filter_acos_max').val('');
        $('#filter_conv_rate_min, #filter_conv_rate_max').val('');
        $('#filter_revenue_min, #filter_revenue_max').val('');
        $('#filter_country').val('').trigger('chosen:updated');
        $('#filter_camp_type').val('').trigger('chosen:updated');
        $('#filter_camp_name').val('').trigger('chosen:updated');
        $('#filter_ad_group').val('').trigger('chosen:updated');
        $('#filter_recommendation').val('').trigger('chosen:updated');
        $('#filter_keyword').val('');
        $('#time_range').val('');
    }

    function time_range(element){
        var now = new Date();
        var time_range_val = $(element).val();
            var date = new Date();
        switch(time_range_val){
            case '14d':
              date.setDate( date.getDate() - 14 );
            break;
            case '30d':
              date.setDate( date.getDate() - 30 );
            break;
            case '60d':
              date.setDate( date.getDate() - 60 );
            break;
            case 'lifetime':
              var date = new Date(1970,1,1);
            break;
        }
        var day = (date.getDate() < 10) ? '0' + date.getDate() : date.getDate()
        var month = (date.getMonth() < 10) ? '0' + (date.getMonth()+1) : (date.getMonth()+1)
        $("#start_date").val(day + '/' + month + '/' + (date.getFullYear()));
    }

    function show_filter_parameter(){
        var actv_filter = "";
        var filter_date_start = $('#start_date').val();
        var filter_date_end = $('#end_date').val();
        var filter_imp = $('#filter_imp_min').val()+"-"+$('#filter_imp_max').val();
        var filter_clicks = $('#filter_clicks_min').val()+"-"+$('#filter_clicks_max').val();
        var filter_ctr = $('#filter_ctr_min').val()+"-"+$('#filter_ctr_max').val();
        var filter_total_spend = $('#filter_total_spend_min').val()+"-"+$('#filter_total_spend_max').val();
        var filter_avg_cpc = $('#filter_avg_cpc_min').val()+"-"+$('#filter_avg_cpc_max').val();
        var filter_acos = $('#filter_acos_min').val()+"-"+$('#filter_acos_max').val();
        var filter_conv_rate = $('#filter_conv_rate_min').val()+"-"+$('#filter_conv_rate_max').val();
        var filter_revenue = $('#filter_revenue_min').val()+"-"+$('#filter_revenue_max').val();
        var filter_country = $('#filter_country').val();
        var filter_camp_type = $('#filter_camp_type').val();
        var filter_camp_name = $('#filter_camp_name').val();
        var filter_ad_group = $('#filter_ad_group').val();
        var filter_recommendation = $('#filter_recommendation').val();
        var filter_keyword = $('#filter_keyword').val();
        var time_range = $('#time_range').val();

        if (filter_date_start != "" && filter_date_end != "") {
          actv_filter += '<div class="filter_parameter">Date Range: '+filter_date_start+' to '+filter_date_end+'<i onclick="close_param(this)" data="date_range" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_date_start != "" && filter_date_end == ""){
          actv_filter += '<div class="filter_parameter">Date Range: '+filter_date_start+'<i onclick="close_param(this)" data="date_range" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_date_start == "" && filter_date_end != ""){
          actv_filter += '<div class="filter_parameter">Date Range: '+filter_date_end+'<i onclick="close_param(this)"  data="date_range" sclass="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        if (time_range) {
          actv_filter += '<div class="filter_parameter">Time Range: '+time_range;
          actv_filter += '<i onclick="close_param(this)" data="time_range" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        if (filter_country) {
          actv_filter += '<div class="filter_parameter">Country: ';
          for (var i = 0; i < filter_country.length; i++) {
             country = filter_country[i].split('|');
             actv_filter += country[1]+', ';
          };

          actv_filter = actv_filter.substr(0,actv_filter.length-2);
          actv_filter += '<i onclick="close_param(this)" data="country" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        if (filter_camp_type) {
          actv_filter += '<div class="filter_parameter">Campaign Type: ';
          for (var i = 0; i < filter_camp_type.length; i++) {
              actv_filter += filter_camp_type[i]+', ';
          };

          actv_filter = actv_filter.substr(0,actv_filter.length-2);
          actv_filter += '<i onclick="close_param(this)" data="camp_type" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        if (filter_camp_name) {
          actv_filter += '<div class="filter_parameter">Campaign Name: ';
          for (var i = 0; i < filter_camp_name.length; i++) {
              actv_filter += filter_camp_name[i]+', ';
          };

          actv_filter = actv_filter.substr(0,actv_filter.length-2);
          actv_filter += '<i onclick="close_param(this)" data="camp_name" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        if (filter_ad_group) {
          actv_filter += '<div class="filter_parameter">Ad Group: ';
          for (var i = 0; i < filter_ad_group.length; i++) {
              actv_filter += filter_ad_group[i]+', ';
          };

          actv_filter = actv_filter.substr(0,actv_filter.length-2);
          actv_filter += '<i onclick="close_param(this)" data="ad_group" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        if (filter_recommendation) {
          actv_filter += '<div class="filter_parameter">Recommendation: ';
          for (var i = 0; i < filter_recommendation.length; i++) {
              actv_filter += filter_recommendation[i]+', ';
          };

          actv_filter = actv_filter.substr(0,actv_filter.length-2);
          actv_filter += '<i onclick="close_param(this)" data="recommendation" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        if (filter_keyword) {
          actv_filter += '<div class="filter_parameter">Keyword: '+filter_keyword;
          actv_filter += '<i onclick="close_param(this)" data="keyword" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }


        filter_imp = filter_imp.split("-")
        if (filter_imp[0].trim() != "" && filter_imp[1].trim() != "") {
            actv_filter += '<div class="filter_parameter">Imp: from '+filter_imp[0].trim()+' to '+filter_imp[1].trim()+'<i onclick="close_param(this)" data="imp" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_imp[0].trim() != "" && filter_imp[1].trim() == ""){
            actv_filter += '<div class="filter_parameter">Imp: from '+filter_imp[0].trim()+'<i onclick="close_param(this)" data="imp" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_imp[0].trim() == "" && filter_imp[1].trim() != ""){
            actv_filter += '<div class="filter_parameter">Imp: from 0 to '+filter_imp[1].trim()+'<i onclick="close_param(this)" data="imp" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        filter_clicks = filter_clicks.split("-")
        if (filter_clicks[0].trim() != "" && filter_clicks[1].trim() != "") {
            actv_filter += '<div class="filter_parameter">Clicks: from '+filter_clicks[0].trim()+' to '+filter_clicks[1].trim()+'<i onclick="close_param(this)" data="clicks" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_clicks[0].trim() != "" && filter_clicks[1].trim() == ""){
            actv_filter += '<div class="filter_parameter">Clicks: from '+filter_clicks[0].trim()+'<i onclick="close_param(this)" data="clicks" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_clicks[0].trim() == "" && filter_clicks[1].trim() != ""){
            actv_filter += '<div class="filter_parameter">Clicks: from 0 to '+filter_clicks[1].trim()+'<i onclick="close_param(this)" data="clicks" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        filter_ctr = filter_ctr.split("-")
        if (filter_ctr[0].trim() != "" && filter_ctr[1].trim() != "") {
            actv_filter += '<div class="filter_parameter">CTR: from '+filter_ctr[0].trim()+'% to '+filter_ctr[1].trim()+'%<i onclick="close_param(this)" data="ctr" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_ctr[0].trim() != "" && filter_ctr[1].trim() == ""){
            actv_filter += '<div class="filter_parameter">CTR: from '+filter_ctr[0].trim()+'%<i onclick="close_param(this)" data="ctr" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_ctr[0].trim() == "" && filter_ctr[1].trim() != ""){
            actv_filter += '<div class="filter_parameter">CTR: from 0 to '+filter_ctr[1].trim()+'%<i onclick="close_param(this)" data="ctr" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        filter_total_spend = filter_total_spend.split("-")
        if (filter_total_spend[0].trim() != "" && filter_total_spend[1].trim() != "") {
            actv_filter += '<div class="filter_parameter">Total Spend: from '+filter_total_spend[0].trim()+' to '+filter_total_spend[1].trim()+'<i onclick="close_param(this)" data="total_spend" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_total_spend[0].trim() != "" && filter_total_spend[1].trim() == ""){
            actv_filter += '<div class="filter_parameter">Total Spend: from '+filter_total_spend[0].trim()+'<i onclick="close_param(this)" data="total_spend" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_total_spend[0].trim() == "" && filter_total_spend[1].trim() != ""){
            actv_filter += '<div class="filter_parameter">Total Spend: from 0 to '+filter_total_spend[1].trim()+'<i onclick="close_param(this)" data="total_spend" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        filter_avg_cpc = filter_avg_cpc.split("-")
        if (filter_avg_cpc[0].trim() != "" && filter_avg_cpc[1].trim() != "") {
            actv_filter += '<div class="filter_parameter">Average CPC: from '+filter_avg_cpc[0].trim()+' to '+filter_avg_cpc[1].trim()+'<i onclick="close_param(this)" data="avg_cpc" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_avg_cpc[0].trim() != "" && filter_avg_cpc[1].trim() == ""){
            actv_filter += '<div class="filter_parameter">Average CPC: from '+filter_avg_cpc[0].trim()+'<i onclick="close_param(this)" data="avg_cpc" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_avg_cpc[0].trim() == "" && filter_avg_cpc[1].trim() != ""){
            actv_filter += '<div class="filter_parameter">Average CPC: from 0 to '+filter_avg_cpc[1].trim()+'<i onclick="close_param(this)" data="avg_cpc" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        filter_acos = filter_acos.split("-")
        if (filter_acos[0].trim() != "" && filter_acos[1].trim() != "") {
            actv_filter += '<div class="filter_parameter">ACoS: from '+filter_acos[0].trim()+'% to '+filter_acos[1].trim()+'%<i onclick="close_param(this)" data="acos" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_acos[0].trim() != "" && filter_acos[1].trim() == ""){
            actv_filter += '<div class="filter_parameter">ACoS: from '+filter_acos[0].trim()+'%<i onclick="close_param(this)" data="acos" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_acos[0].trim() == "" && filter_acos[1].trim() != ""){
            actv_filter += '<div class="filter_parameter">ACoS: from 0 to '+filter_acos[1].trim()+'%<i onclick="close_param(this)" data="acos" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        filter_conv_rate = filter_conv_rate.split("-")
        if (filter_conv_rate[0].trim() != "" && filter_conv_rate[1].trim() != "") {
            actv_filter += '<div class="filter_parameter">Conversion Rate: from '+filter_conv_rate[0].trim()+'% to '+filter_conv_rate[1].trim()+'%<i onclick="close_param(this)" data="conv_rate" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_conv_rate[0].trim() != "" && filter_conv_rate[1].trim() == ""){
            actv_filter += '<div class="filter_parameter">Conversion Rate: from '+filter_conv_rate[0].trim()+'%<i onclick="close_param(this)" data="conv_rate" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_conv_rate[0].trim() == "" && filter_conv_rate[1].trim() != ""){
            actv_filter += '<div class="filter_parameter">Conversion Rate: from 0 to '+filter_conv_rate[1].trim()+'%<i onclick="close_param(this)" data="conv_rate" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        filter_revenue = filter_revenue.split("-")
        if (filter_revenue[0].trim() != "" && filter_revenue[1].trim() != "") {
            actv_filter += '<div class="filter_parameter">Revenue: from '+filter_revenue[0].trim()+' to '+filter_revenue[1].trim()+'<i onclick="close_param(this)" data="revenue" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_revenue[0].trim() != "" && filter_revenue[1].trim() == ""){
            actv_filter += '<div class="filter_parameter">Revenue: from '+filter_revenue[0].trim()+'<i onclick="close_param(this)" data="revenue" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_revenue[0].trim() == "" && filter_revenue[1].trim() != ""){
            actv_filter += '<div class="filter_parameter">Revenue: from 0 to '+filter_revenue[1].trim()+'<i onclick="close_param(this)" data="revenue" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        $('.filter_param').html(actv_filter);
    }

  function init_graph(){

      $.ajax({
        url: 'getChangeBid',
        type: 'POST',
        data: object_bid_change,
        beforeSend: function(){
          $('.graphloading').show()
        },
        success: function(response){
            $('.graphloading').hide()
            $('.date_updated').html(response.date)
            set_bid_graph(response);
        },
        error(xhr, status, response){
            $('.graphloading').hide()
        }
      })
  }
  
</script>

