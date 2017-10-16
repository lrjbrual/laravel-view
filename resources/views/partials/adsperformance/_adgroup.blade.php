<div style="width: 100%;" class="adgrouplist{{ $campaignid }}">
	
</div>
<input type="hidden" class="cid{{ $campaignid }}" data-campaign="{{ $campaignid }}">

<script type="text/javascript">
    $(document).ready(function(){
        init_table()
    })

    function init_table(){
        var campId = '{{ $campaignid}}'
        var isFilter = '{{ $isFilter}}'

        var id = $('.cid'+campId).attr('data-campaign');
        var filter_date_start = $('#start_date').val();
        var filter_date_end = $('#end_date').val();
        var _token = "{{ csrf_token() }}";
        var data = { _token: _token, filter_date_start: filter_date_start, filter_date_end:filter_date_end,id: id };
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


          data = { _token: _token, filter_date_start: filter_date_start, filter_date_end:filter_date_end, filter_imp:filter_imp, filter_clicks:filter_clicks, filter_ctr:filter_ctr, filter_total_spend:filter_total_spend, filter_avg_cpc:filter_avg_cpc, filter_acos:filter_acos, filter_conv_rate:filter_conv_rate, filter_country:filter_country, filter_camp_type:filter_camp_type, filter_camp_name:filter_camp_name, filter_keyword:filter_keyword, filter_revenue:filter_revenue, filter_ad_group:filter_ad_group, id: id };
        }

        $.ajax({
            url: 'performance_adgroup',
            type: 'POST',
            data: data,
            success: function(result){
                response = jQuery.parseJSON(result);
                console.log(result)
                var output = '';
                 // 'icon' => $icon,
                 //    'ad_group_name' => $value->ad_group_name,
                 //    'imp' => $value->impressions,
                 //    'clicks' => $value->clicks,
                 //    'ctr' => $value->impressions == 0 || $value->clicks == 0 ? 0 : round(($value->clicks/$value->impressions)*100,2)."%",
                 //    'rev' => $value->attributedsales30d,
                 //    'orders' => $value->attributedconversions30dsamesku,
                 //    'cr' => round($value->cr,2)."%",
                 //    'total_spend' => round($value->total_spend, 2),
                 //    'average_cpc' => round($value->average_cpc, 2),
                 //    'acos' => $value->total_spend == 0 || $value->attributedsales30d == 0 ? 0 : round(($value->total_spend/$value->attributedsales30d)*100,2)."%",
                 //    'bid' => $value->bid,
                    // 'max_bid' => round($value->max_bid_recommendation,2),
                for(var i in response.data){
                	output += '<tr >'+
                				        '<td width="70"></td>'+
                				        '<td width="">'+response.data[i].icon+' '+response.data[i].ad_group_name+'</td>'+
                                '<td width="60">'+response.data[i].imp+'</td>'+
                                '<td width="60">'+response.data[i].clicks+'</td>'+
                                '<td width="60">'+response.data[i].ctr+'</td>'+
                                '<td width="60">'+response.data[i].rev+'</td>'+
                                '<td width="60">'+response.data[i].orders+'</td>'+
                                '<td width="60">'+response.data[i].cr+'</td>'+
                                '<td width="60">'+response.data[i].total_spend+'</td>'+
                                '<td width="60">'+response.data[i].average_cpc+'</td>'+
                                '<td width="60">'+response.data[i].acos+'</td>'+
                			 '</tr>';
                }
                $('.adgrouplist'+campId).html(output)
            }
        })


    }
</script>