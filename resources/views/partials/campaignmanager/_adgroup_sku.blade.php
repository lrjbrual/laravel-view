
        
        <div class="card">
        <div class="card-block">
        <div class="tab-content text-justify">
            <div>
                <table cellspacing=0 cellpadding=0 class="table table-bordered table-striped table_keyword table-res" id="campSku{{ $campaignid }}">
                <thead>
                    <tr style="background: #565656;">
                        <th>Name</th>
                        <th width="300">Default Bid</th>
                        <th width="200">State</th>
                    </tr>
                </thead>
                    
                </table>
            </div>
        </div>
    </div>

    </div>
    <input type="hidden" class="cid{{ $campaignid }}" data-campaign="{{ $campaignid }}">

<script type="text/javascript">
    $(document).ready(function(){
        init_table()
    })

    function init_table(){
        var table_id = '{{ $campaignid}}'
        $.ajax({
            url: 'getCampaignAdGroup',
            type: 'POST',
            data: {id: $('.cid'+table_id).attr('data-campaign')},
            success: function(result){
                response = jQuery.parseJSON(result);
                $('#campSku'+table_id).DataTable({
                    "data": response,
                    "processing": true,
                    "bLengthChange": false,
                    "bFilter": false,
                    "destroy": true,
                    "bPaginate": true,
                    "paging": true,
                    "ordering": true,
                    "bInfo": true,
                    "language": {
                                  processing: '<b>Rendering result </b><i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span>'
                    },
                    "createdRow": function( row, data, dataIndex ) {
                
                        $(row).children(':nth-child(1)').find('.toggleAdgroupDetails').click(function(){
                            $(this).toggleClass('row-details-open');
                            if($(this).hasClass('row-details-open')){
                                var index = $(this).parent().parent().index();
                                var id = $(this).parent().parent().attr('id');
                                if(typeof id == 'undefined') id = 0;
                                var html = '';

                                var loading = '<tr class="addSkuContainer_'+id+' addGroup_style"><td class="colSku_'+id+'" colspan="5">'+
                                                        '<div class="text-center loadingSku_'+id+'"><b>Loading result </b><i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span></div>'+
                                                '</td></tr>';

                                $('#campSku'+table_id+' > tbody > tr').eq(index).after(loading);
                                $.ajax({
                                    url: 'sendAdgroupId',
                                    type: "POST",
                                    data: { _token: _token, id:id},
                                    success: function(result){
                                        // var response = jQuery.parseJSON(result);
                                        // console.log(result)
                                        $('.loadingSku_'+id).remove();
                                        $('.colSku_'+id).append(result)
                                    }
                                })

                            }else{
                                var id = $(this).parent().parent().attr('id');
                                if(typeof id == 'undefined') id = 0;
                                $(this).parent().parent().parent().find('.addSkuContainer_'+id).hide()
                            }
                        })

                    }

                });


            }
        })


    }
</script>