
$(document).ready(function(){
    $('#templatelisttbl').DataTable({

      "bSort": false,
			"bPaginate" : false,
			"lengthChange": false,
			"searching": false,
			"dom": '<"top"flp>t<"bottom"ir><"clear">',
			"bInfo" : false,
			"bFilter": false,
			"processing": true,
			"bAutoWidth": false,
			"language": {
				"processing": '<b>Loading records... </b> '
			},
      "ajax":{
				"url": "campaign/campaigntemplatelist",
				'data': {"_token":"{{ csrf_token() }}"},
				'type': "POST"
			},
			"serverSide": true,
      "initComplete":function( settings, json ) {


				$('#templatelisttbl tbody tr').click( function(){
					var ct_id=$(this).attr('recid');

					location.href="Campaign/NewCampaign/" + ct_id;
				});


			},
      fnRowCallback: function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {

					$(nRow).attr('recid',aData.recid);
			},
      columns: [
            { data: 'templateNameColumn', name: 'templateNameColumn' },
            { data: 'dateCreatedColumn', name: 'dateCreatedColumn' }
        ]

    });
});
