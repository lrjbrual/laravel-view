<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/chosen/css/chosen.css')}}"/>
<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/datepicker/css/bootstrap-datepicker.min.css')}}" />

  <div class="col-md-12">
  <div class="alert alert-info m-t-20">
      <a><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      </a>
      We are aware of performance issues in the loading of the P&L table. We apologise about this and are working on resolving this issue. The table will always load but may take some time. We recommend you leave the P&L page to load and open a new tab to continue using the rest of the application.
  </div>
    <div class="row">
      <div class="col-lg-4 col-md-12 col-xs-12 m-t-25">
        <div class="input-group">
          <strong>Filter</strong>&nbsp;&nbsp; 
          <select data-placeholder="Select Filter" class="filter-select" style="width:14.5em;" multiple="multiple">
              <option value="profit">Profit</option>
              <option value="revenue">Revenue</option>
          </select>
        </div>
      </div>

      <div class="col-lg-8 col-md-12 col-xs-12 m-t-25">
        <div class="input-group input-daterange">
            <strong>Date Range</strong>&nbsp;&nbsp;
            <input type="text" id="date_from" class="m-b-20" style="background:#fff;font-size:12pt;" readonly>
            <span class="">&nbsp;to&nbsp;</span>
            <input type="text" id="date_to" class="m-b-20" style="background:#fff;font-size:12pt;" readonly>
            &nbsp;&nbsp;
            <button id="showbtn" class="btn btn-primary" type="button" style="position:relative;top:-2px;" name="show">Show</button>
        </div>
      </div>
    </div>
  </div>

<script type="text/javascript" src="{{asset('assets/vendors/chosen/js/chosen.jquery.js')}}"></script>
<script type="text/javascript">
$(document).ready(function () {
  $(".filter-select").chosen({allow_single_deselect: true});
  $(".filter-select-deselect").chosen();

  $('.input-daterange input').each(function() {
      $(this).datepicker({
          todayHighlight: true,
          autoclose: true,
          orientation: "auto",
          format: 'dd-mm-yyyy'
      }).on('changeDate', function(e) {
          if($(this).attr("id") == "date_to") {
            $("#date_from").datepicker('setEndDate', $("#date_to").val());
          } else if($(this).attr("id") == "date_from") {
            $("#date_to").datepicker('setStartDate', $("#date_from").val());
          }
      });
  });
});
</script>
