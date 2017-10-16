<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/datepicker/css/bootstrap-datepicker.min.css')}}" />
<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/chosen/css/chosen.css')}}"/>
<div class="row">
    <div class="col-md-12">
    <!-- Recommendation Start -->            
    <div class="col-md-12">
      <div id="adperformance_recommendation" class="col-md-12 recommendationcontainer m-t-10 m-b-20">
      <div class="headRuleContainer">
          <div class="row">
            <div class="col-md-12">
              <div class="col-lg-6 col-md-12">
                <div class="row">
                  <div class="col-lg-4 col-md-6">
                    Recommendation Name
                  </div>
                  <div class="col-lg-8 col-md-6">
                    <input id="recommendation_name" type="text" name="" class="form-control" value="">
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-md-12 m-t-5">
            <div class="row">
              <div class="col-lg-4 col-md-6">
                Country
              </div>
              <div class="col-lg-8 col-md-6">
                {!! Form::select('recommendation_country', $countriesRec, old('country_id'),  ['class' => 'form-control select-countryRec recMultiSelect','multiple','required' ,'id'=>'recommendation_country']) !!}
              </div>
            </div>
            <div class="row m-t-5">
              <div class="col-lg-4 col-md-6">
                Campaign Type
              </div>
              <div class="col-lg-8 col-md-6">
                  <select id="recommendation_camp_type" name="campaign_type" class="form-control select-camppTypeRec recMultiSelect" multiple>
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
                {!! Form::select('recommendation_campaign', $camp_name1, old('camp_name1'),  ['class' => 'form-control select-campaignRec tb-text-color-gray recMultiSelect','multiple', 'required' ,'id'=>'recommendation_campaign']) !!}
              </div>
            </div>
            <div class="row m-t-5">
              <div class="col-lg-4 col-md-6">
                Ad Group
              </div>
              <div class="col-lg-8 col-md-6">
                {!! Form::select('ad_group_name', $ad_group_name, old('ad_group_name'),  ['class' => 'form-control select-adGroupRec recMultiSelect', 'multiple', 'required' ,'id'=>'recommendation_ad_group']) !!}
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-md-12 m-t-5">
            <div class="row">
              <div class="col-lg-4 col-md-6">
                Time Period
              </div>
              <div class="col-lg-8 col-md-6">
                <select id="recommendation_period" name="recommendation_period" class="form-control select-time-period" required="">
                  <option value="">Select Your Time Period</option>
                  <option value="7">7 Days</option>
                  <option value="14">14 Days</option>
                  <option value="30" selected="">30 Days</option>
                  <option value="60">60 Days</option>
                  <option value="90">90 Days</option>
                </select>
              </div>
            </div>
            <div class="row m-t-5">
              <div class="col-lg-4 col-md-6">
                Recommendation Settings
              </div>
              <div class="col-lg-8 col-md-6">
                <select id="recommendation" name="recommendation" class="form-control select-recommendation" required="">
                  <option value="">Select Your Recommendation</option>
                  <option value="Increase Bid">Increase Bid</option>
                  <option value="Decrease Bid">Decrease Bid</option>
                  <option value="Negative Keyword">Negative Keyword</option>
                  <option value="Custom">Custom</option>
                </select>
              </div>
            </div>
            <div class="row m-t-5" id="custom_recommendation_div">
              <div class="col-lg-4 col-md-6">
                Custom Recommendation
              </div>
              <div class="col-lg-8 col-md-6">
                <input type="text" name="custom_recommendation" id="custom_recommendation" class="form-control">
              </div>
            </div>
          </div>
          <div class="col-md-12">
          <hr>
            <span style="color: #7AC482">Conditions</span>
          </div>
          <div class="col-md-12">
          <div class="table-responsive">
            <table cellspacing=0 cellpadding=0 class="table table-bordered table-striped table_res" id="tableRule" style="min-width: 400px;">
              <thead>
                <tr>
                    <th class="text-center">Operation</th>
                    <th class="text-center">KPI</th>
                    <th class="text-center">Equation</th>
                    <th class="text-center" colspan="2">Value</th>
                </tr>
              </thead>
              <tbody>
                <tr class="ruleRow" data="0">                              
                    <td>
                      <select id="recommendation_operation" name="recommendation_operation" class="form-control select_operation" required="">
                        <option value="default">Default</option>
                        <option value="and" disabled>AND</option>
                        <option value="or" disabled>OR</option>
                      </select>
                    </td>
                    <td>
                      <select id="recommendation_matrix" name="recommendation_matrix" class="form-control select_matrix" required="">
                        <option value="">Select Your KPI</option>
                        <option value="acos">ACoS</option>
                        <option value="impressions">Impressions</option>
                        <option value="clicks">Clicks</option>
                        <option value="ctr">CTR</option>
                        <option value="average_cpc">Average CPC</option>
                        <option value="revenue">Revenue</option>
                        <option value="bid">Bid</option>
                        <option value="orders">Orders</option>
                      </select>
                    </td>
                    <td>
                      <select id="recommendation_metric" name="recommendation_metric" class="form-control select_metric" required="">
                        <option value="">Select Your Equation</option>
                        <option value="≥">≥ (Greater Than or Equal To)</option>
                        <option value="≤">≤ (Less Than or Equal To))</option>
                        <option value="=">= (Equals)</option>
                      </select>
                    </td>
                    <td colspan="2">
                      <input id="recommendation_value" type="text" name="recommendation_value" class="form-control" value="" onblur="ValidateValue(this)">
                    </td>
                </tr>
              </tbody>
            </table>
            </div>
          </div>
        <div class="col-lg-6 col-md-12">
          <button class="btn btn-primary btn-sm m-r-10" id="addRuleBtn">Add condition</button>
          <button class="btn btn-success btn-sm" id="saveRuleBtn" style="border-radius:0px;">Save rule</button>
        </div>
        </div>
        <div class="col-md-12">
        <hr>
            <span style="color: #7AC482;">List of recommendation rules</span>
        </div>
        <!-- list of table  -->
        <div class="list_recommendation_rules"></div>
        <!-- end -->
      </div>
    </div>
    </div>
    <!-- Recommendation End -->

<script type="text/javascript">
  var bs = '{{ $bs }}';
</script>
<script type="text/javascript" src="{{asset('assets/vendors/chosen/js/chosen.jquery.js')}}"></script>
<script type="text/javascript" src="js/adsrecommendation.js"></script>