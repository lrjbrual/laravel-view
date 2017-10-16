var total_record = "";
$(document).ready(function(){
	initialize_table()

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
	
});

function initialize_table(){

	//$.ajax({url: "getAdData", type: 'POST', data: data, success: function(result){
	$.fn.dataTable.ext.errMode = 'throw';
	var oTable2 = $('#adscampaignmanager_table').DataTable({
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
	      url: "getCampaigns",
	      type: "POST",
	      data: initialize_data_feed()
	    },
	    // "scrollX": true,
	    "createdRow": function( row, data, dataIndex ) {
	    	var tableinfo = oTable2.page.info();
                total_record = tableinfo.recordsTotal;
	    }
	});

	$('.dataTable').wrap('<div class="dataTables_scroll" />');

}

function initialize_data_feed(){
    var data = { total_number: total_record };
    return data;
}

