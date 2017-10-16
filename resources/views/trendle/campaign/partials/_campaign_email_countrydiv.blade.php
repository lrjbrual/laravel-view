<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/chosen/css/chosen.css')}}"/>
<div class="row col-lg-3 m-t-10 countryTour">
    <select data-placeholder="Choose marketplace" style="width:220px;" class="mkp chzn-select" id="mkpt" multiple="multiple">
        <!-- <optgroup label="All"> -->
        <?php
            $currcountry = array();
            if(is_object($campaign_country)){
              foreach($campaign_country as $dd5){
                  $currcountry[]=$dd5->country_id;
              }
            }

            foreach($countrymkp as $cmkp){
                $s='';
                if(in_array($cmkp->id, $currcountry)){
                    $s='selected';
                }
                echo '<option value="'.$cmkp->id.'" '.$s.'>'.$cmkp->name.'</option>';
            }
        ?>
        <!-- </optgroup> -->
    </select>

      <a class="country-info popoverclick" data-toggle="tooltip" data-placement="right" title="Select the country(ies) for which the message(s)
        in this campaign will be sent to. For example, if this
        campaign is only for orders from the USA marketplace, then
        select only &#34;USA&#34;"><i class="fa fa-info-circle"></i></a>
</div>
<script type="text/javascript" src="{{asset('assets/vendors/chosen/js/chosen.jquery.js')}}"></script>
<script type="text/javascript">
$(document).ready(function () {
  $(".chzn-select").chosen({allow_single_deselect: true});
  $(".chzn-select-deselect").chosen();
});
</script>
