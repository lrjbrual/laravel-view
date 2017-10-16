var total_record = "",
	_token = $('meta[name="csrf-token"]').attr('content')

$(document).ready(function(){
	initialize_table()
})


function initialize_table(){

	//$.ajax({url: "getAdData", type: 'POST', data: data, success: function(result){
	$.fn.dataTable.ext.errMode = 'throw';
	var oTable2 = $('#statistics_table').DataTable({
	    "processing": true,
	    "serverSide": true,
	    "lengthMenu": [[25, 50, 100, 250], [25, 50, 100, 250]],
	    "bLengthChange": false,
	    "language": {
	      processing: '<b>Loading result </b><i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span>'
	    },
	    "deferRender": true,
	    "searching" : false,
	    "destroy" : true,
	    "ajax": {
	      url: "getStatistics",
	      type: "POST",
	      data: initialize_data_feed()
	    },
	    // "aoColumnDefs" : [
     //    {
     //      'bSortable' : false,
     //      'aTargets' : [ 1 ]
     //    }],
	    // "scrollX": true,
	    "createdRow": function( row, data, dataIndex ) {



	    	var tableinfo = oTable2.page.info();
                total_record = tableinfo.recordsTotal;

	    }
	});

	$('.dataTable').wrap('<div class="dataTables_scroll" />');

}


function initialize_data_feed(){
    var data = { _token: _token, total_number: total_record };
    return data;
}