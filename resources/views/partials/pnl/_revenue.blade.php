<div class="col-md-12 m-t-5">
  <div class="card card-outline-primary">
    <div class="card-header bg-primary">
       <span class="card-title">Revenue</span>
       <span class="float-xs-right">
          <i class="fa fa-chevron-up"></i>
       </span>
    </div>
    <div class="card-block">
        <div class="table-responsive">
            <table cellspacing="0" cellpadding="0" class="revenuetable table table-bordered table_res dataTable no-footer">
                <div class="dataTables_processing2 pnl_table_rev_loading" style="display:block">
                    <b>Loading records. . .</b>
                </div>
                <thead>
                    <tr>
                        <th width="3%"><span class="row-details"></span></th>
                        <th width="10%">&nbsp;</th>
                        <th width="20%" colspan=2>&nbsp;</th>
                        @foreach ($mkp_c as $mkp_c_data)
                            <th width="10%">{{ $mkp_c_data->iso_3166_2 }}</th>
                        @endforeach
                        <th width="10%">Total</th>
                    </tr>
                </thead>
                <tr class="details" id="OrdersDetailsRow_principal">
                    <td width="3%">&nbsp;</td>
                    <td width="10%" colspan=2>Orders</td>
                    <td width="20%">Principal</td>
                    @foreach ($mkp_c as $mkp_c_data)
                        <td width="10%">&nbsp;</td>
                    @endforeach
                    <td width="10%">&nbsp;</td>
                </tr>
                <tr id="Adjustments">
                    <td width="3%"><span id="RevenueAdjustmentsSection" class="row-details row-details-close"></span></td>
                    <td width="10%">Adjustments</td>
                    <td width="20%" colspan=2><strong>Total</strong></td>
                    @foreach ($mkp_c as $mkp_c_data)
                        <td width="10%">&nbsp;</td>
                    @endforeach
                    <td width="10%">&nbsp;</td>
                </tr>
                <tr class="AdjustmentDetailsRow details" id="FBAInventoryReimbursement">
                    <td width="3%">&nbsp;</td>
                    <td width="10%" colspan=2>&nbsp;</td>
                    <td width="20%">FBAInventoryReimbursement</td>
                    @foreach ($mkp_c as $mkp_c_data)
                        <td width="10%">&nbsp;</td>
                    @endforeach
                    <td width="10%">&nbsp;</td>
                </tr>
                <tr class="AdjustmentDetailsRow details" id="PostageRefund">
                    <td width="3%">&nbsp;</td>
                    <td width="10%" colspan=2>&nbsp;</td>
                    <td width="20%">PostageRefund</td>
                    @foreach ($mkp_c as $mkp_c_data)
                        <td width="10%">&nbsp;</td>
                    @endforeach
                    <td width="10%">&nbsp;</td>
                </tr>
                <tr id="Others">
                    <td width="3%"><span id="RevenueOthersSection" class="row-details row-details-close"></span></td>
                    <td width="10%">Others</td>
                    <td width="20%" colspan=2><strong>Total</strong></td>
                    @foreach ($mkp_c as $mkp_c_data)
                        <td width="10%">&nbsp;</td>
                    @endforeach
                    <td width="20%">&nbsp;</td>
                </tr>
                <tr class="RevenueOthersRow details" id="Giftwrap">
                    <td width="3%">&nbsp;</td>
                    <td width="10%" colspan=2>&nbsp;</td>
                    <td width="20%">Giftwrap</td>
                    @foreach ($mkp_c as $mkp_c_data)
                        <td width="10%">&nbsp;</td>
                    @endforeach
                    <td width="10%">&nbsp;</td>
                </tr>
                <tr class="RevenueOthersRow details" id="ShippingCharge">
                    <td width="3%">&nbsp;</td>
                    <td width="10%" colspan=2>&nbsp;</td>
                    <td width="20%">ShippingCharge</td>
                    @foreach ($mkp_c as $mkp_c_data)
                        <td width="10%">&nbsp;</td>
                    @endforeach
                    <td width="10%">&nbsp;</td>
                </tr>
                <tr class="RevenueOthersRow details" id="ReturnShipping">
                    <td width="3%">&nbsp;</td>
                    <td width="10%" colspan=2>&nbsp;</td>
                    <td width="20%">ReturnShipping</td>
                    @foreach ($mkp_c as $mkp_c_data)
                        <td width="10%">&nbsp;</td>
                    @endforeach
                    <td width="10%">&nbsp;</td>
                </tr>
                <tr class="RevenueOthersRow details" id="FreeReplacementReturnShipping">
                    <td width="3%">&nbsp;</td>
                    <td width="10%" colspan=2>&nbsp;</td>
                    <td width="20%">FreeReplacementReturnShipping</td>
                    @foreach ($mkp_c as $mkp_c_data)
                        <td width="10%">&nbsp;</td>
                    @endforeach
                    <td width="10%">&nbsp;</td>
                </tr>
                <tr class="RevenueOthersRow details" id="LoanAdvance">
                    <td width="3%">&nbsp;</td>
                    <td width="10%" colspan=2>&nbsp;</td>
                    <td width="20%">LoanAdvance</td>
                    @foreach ($mkp_c as $mkp_c_data)
                        <td width="10%">&nbsp;</td>
                    @endforeach
                    <td width="10%">&nbsp;</td>
                </tr>
                <tr class="RevenueOthersRow details" id="ProviderCredit">
                    <td width="3%">&nbsp;</td>
                    <td width="10%" colspan=2>&nbsp;</td>
                    <td width="20%">ProviderCredit</td>
                    @foreach ($mkp_c as $mkp_c_data)
                        <td width="10%">&nbsp;</td>
                    @endforeach
                    <td width="10%">&nbsp;</td>
                </tr>

                <tr class="revtabletotal text-success" id="Total">
                    <td width="3%"><span class="row-details"></span></td>
                    <td width="10%"><strong>Total Revenue</strong></td>
                    <td width="20%" colspan=2>&nbsp;</td>
                    @foreach ($mkp_c as $mkp_c_data)
                        <td width="10%">&nbsp;</td>
                    @endforeach
                    <td width="10%">&nbsp;</td>
                </tr>
            </table>
       </div>
    </div>
  </div>
</div>
<script type="text/javascript">
    var country_array = [];

    $(document).ready(function(){
      var onclick = false;
      set_table_header_info(onclick);
      init_country_array();

      $('#showbtn').click( function(){
        onclick = true;
        $('.pnl_graph_loading').show();
        $('.pnl_table_rev_loading').show();
        $('.pnl_table_gross_loading').show();
        set_table_header_info(onclick);
        initialize_revenuegraph();
        initialize_revenuetable();
      });
      initialize_revenuegraph();
      initialize_revenuetable();
    });

    function set_table_header_info(action){
        var date_from = $('#date_from').val();
        var date_to = $('#date_to').val();
        var monthNames = ["January", "February", "March", "April", "May", "June","July", "August", "September", "October", "November", "December"];

        if(date_to == "" || date_to == " "){
            var new_date = new Date();
            var dtm="";
            if((new_date.getMonth() + 1) < 10) dtm = "0"+(new_date.getMonth()+1)
            else dtm = (new_date.getMonth()+1);
            date_to = new_date.getDate()+"-"+dtm+"-"+new_date.getFullYear();
            date_to2 = new_date.getDate()+" "+monthNames[new_date.getMonth()]+" "+new_date.getFullYear();
        }
        if(date_from == "" || date_from == " "){
            var df_a = date_to.split("-")
            var df = new Date(df_a[2], (df_a[1]-1), df_a[0]);
            df.setDate(df.getDate() - 30);
            var dtm="";
            if((df.getMonth() + 1) < 10) dtm = "0"+(df.getMonth()+1)
            else dtm = (df.getMonth()+1);
            date_from = df.getDate()+"-"+dtm+"-"+df.getFullYear();
            date_from2 = df.getDate()+" "+monthNames[df.getMonth()]+" "+df.getFullYear();
        }
        if (action) {
            var df_a = date_from.split("-")
            var dt_a = date_to.split("-")
            var df = new Date(df_a[2], (df_a[1]-1), df_a[0]);
            var dt = new Date(dt_a[2], (dt_a[1]-1), dt_a[0]);
            
            date_from2 = df.getDate()+" "+monthNames[df.getMonth()]+" "+df.getFullYear();
            date_to2 = dt.getDate()+" "+monthNames[dt.getMonth()]+" "+dt.getFullYear();
        }
        var df_a = date_to.split("-")
        var to = new Date(df_a[2], (df_a[1]-1), df_a[0]);

        df_a = date_from.split("-")
        var from = new Date(df_a[2], (df_a[1]-1), df_a[0]);

        var daysOfYear = [];
        var dp = 0;
        for (var d = from; d <= to; d.setDate(d.getDate() + 1)) {
            dp++;
        }
        $("#td_time_range").html("Time Period: &nbsp;&nbsp;<b>"+date_from2+" &nbsp;&nbsp;to&nbsp;&nbsp; "+date_to2+"</b>");
        $("#td_time_period").html("Time period (e.g. last "+dp+"days)");
    }


    function init_country_array(){
        var i=0;
        @foreach ($mkp_c as $mkp_c_data)
            country_array[i] = "{{ $mkp_c_data->iso_3166_2 }}";
            i++;
        @endforeach
        country_array[i] = "Total";
    }

    function initialize_revenuetable(){
      var date_from = $('#date_from').val();
      var date_to = $('#date_to').val();
      var _token = "{{ csrf_token() }}";
      var data = { date_from: date_from, date_to: date_to, _token: _token }
      $.ajax({url: "pnlRevTable",type: 'POST',data: data, success: function(result){
        $('.pnl_table_rev_loading').hide();
        if(result != "false"){
            var parsed = $.parseJSON(result);
            for(var i = 0; i<country_array.length; i++){
                $("tr#OrdersDetailsRow_principal :nth-child("+(i+4)+")").html(numberForHuman(parsed['table'][country_array[i]].Principal));
                $("tr#Adjustments :nth-child("+(i+4)+")").html(numberForHuman(parsed['table'][country_array[i]].Adjustments));
                $("tr#FBAInventoryReimbursement :nth-child("+(i+4)+")").html(numberForHuman(parsed['table'][country_array[i]].FBAInventoryReimbursement));
                $("tr#PostageRefund :nth-child("+(i+4)+")").html(numberForHuman(parsed['table'][country_array[i]].PostageRefund));
                $("tr#Others :nth-child("+(i+4)+")").html(numberForHuman(parsed['table'][country_array[i]].Others));
                $("tr#Giftwrap :nth-child("+(i+4)+")").html(numberForHuman(parsed['table'][country_array[i]].GiftWrap));
                $("tr#ShippingCharge :nth-child("+(i+4)+")").html(numberForHuman(parsed['table'][country_array[i]].ShippingCharge));
                $("tr#ReturnShipping :nth-child("+(i+4)+")").html(numberForHuman(parsed['table'][country_array[i]].ReturnShipping));
                $("tr#FreeReplacementReturnShipping :nth-child("+(i+4)+")").html((parsed['table'][country_array[i]].FreeReplacementReturnShipping));
                $("tr#LoanAdvance :nth-child("+(i+4)+")").html(numberForHuman(parsed['table'][country_array[i]].LoanAdvance));
                $("tr#ProviderCredit :nth-child("+(i+4)+")").html(numberForHuman(parsed['table'][country_array[i]].ProviderCredit));
                $("tr#Total :nth-child("+(i+4)+")").html(numberForHuman(parsed['table'][country_array[i]].Total));
            }
            }
            calcgrossvalues();
        }
      });
    }

    function numberForHuman(n) {
        var parts = n.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    }

    function initialize_revenuegraph(){
        var date_from = $('#date_from').val();
        var date_to = $('#date_to').val();
        var _token = "{{ csrf_token() }}";
        var data = { date_from: date_from, date_to: date_to, _token: _token }

        $.ajax({url: "pnlRevGraph", type: 'POST', data: data, success: function(result){
            $('.pnl_graph_loading').hide();
            var parsed = $.parseJSON(result);
            init_graph(parsed['graphprofit'], parsed['graphrevenue']);
        }
        });
    }
</script>
