
        
<div class="card">
    <div class="card-block">
    <div class="tab-content text-justify">
        <div>
            <table cellspacing=0 cellpadding=0 class="table table-bordered table-striped table_keyword table-res" id="campSku{{ $adgroupid }}">
            <thead>
                <tr style="background: #565656">
                    <th>Sku</th>
                    <th>State</th>
                </tr>
            </thead>
            </table>
        </div>
    </div>
</div>
</div>
<input type="hidden" class="adg{{ $adgroupid }}" data-campaign="{{ $adgroupid }}">

<script type="text/javascript">
    $(document).ready(function(){
        init_table_sku()
    })

    function init_table_sku(){
        var id = '{{ $adgroupid}}'
        $.ajax({
            url: 'getSkuByadGroup',
            type: 'POST',
            data: {id: $('.adg'+id).attr('data-campaign')},
            success: function(result){
                response = jQuery.parseJSON(result);
                $('#campSku'+id).DataTable({
                    "data": response,
                    "processing": true,
                    "bLengthChange": false,
                    "bFilter": false,
                    "destroy": true,
                    "bPaginate": false,
                    "paging": false,
                    "ordering": true,
                    "bInfo": false,
                    "language": {
                                  processing: '<b>Rendering result </b><i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span>'
                                },
                });

            }
        })


    }
</script>