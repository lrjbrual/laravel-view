@extends('layouts.admin')
@section('title', '| Cron Schedule')

@section('user')

<script type="text/javascript">
var oTable;
	function update_seller_cron_datatable(sellerid){
		if(sellerid == 0 ) $('.loading-table').html(" Initializing table ... ");
		else $('.loading-table').html(" Initializing table for Seller ID "+sellerid+"... ");
		var id = sellerid;
		var token = '{{ csrf_token() }}';
		var datas = "seller_id="+id+"&_token="+token;
		$.ajax({
		  type: "POST",
		  url: 'getsellercron',
		  data: datas,
		  success: function(result){
		  	var response = jQuery.parseJSON(result);
		  	oTable = $('#seller_cron_table').dataTable({
	            "data": response,
	            "bLengthChange": false,
			    "bFilter": false,
			    "destroy": true,
			    "bPaginate": false,
			    "paging": false,
			    "rowId": 'rowId'
	        });

	        oTable.$('td').click( function(event){
	        	if(oTable.fnGetPosition( this )[2] > 1){
		        	if(oTable.fnGetPosition( this )[2] == 2) $('.loading-table').html(" Minutes 0 - 59 ");
		        	if(oTable.fnGetPosition( this )[2] == 3) $('.loading-table').html(" Hours 0 - 23 ");
		        	if(oTable.fnGetPosition( this )[2] == 4) $('.loading-table').html(" Day of Month 1 - 31 ");
		        	if(oTable.fnGetPosition( this )[2] == 5) $('.loading-table').html(" Month 1 - 12 ");
		        	if(oTable.fnGetPosition( this )[2] == 6) $('.loading-table').html(" Day of Week 0 - 7 ");
		        	if(oTable.fnGetPosition( this )[2] == 7) $('.loading-table').html(" isActive 0 and 1 ");
					oTable.$('td').editable( 'updateSellerCron', {
				        "callback": function( sValue, y ) {
				            var aPos = oTable.fnGetPosition( this );
				            oTable.fnUpdate( sValue, aPos[0], aPos[1] );
				            $('.loading-table').html("");
				        },
				        "submitdata": function ( value, settings ) {
				        	$('.loading-table').html("Processing ...");
				            return {
				            	"row_id"	: this.parentNode.getAttribute('id'),
				                "column"	: oTable.fnGetPosition( this )[2],
				            	"_token"	: token,
				            	"seller_id"	: id,
				            };
				        }
				    });
				}

	        });

			$('.loading-table').html("");
		  }
		});
	}

	$(document).ready(function(){
		update_seller_cron_datatable(0);

		$('#seller_cron_table').click(function(event) {
			$(oTable.fnSettings().aoData).each(function (){
				$(this.nTr).removeClass('row_selected');
			});
			$(event.target.parentNode).addClass('row_selected');
		});

		$('#seller_table').on('click', 'tr', function(e){
			 var id = $(this).attr('data-id');
			 $('#hidden_seller_id').val(id);
			 $("#seller_table tbody tr").removeClass('row_selected');
         	 $(this).addClass('row_selected');
   			 update_seller_cron_datatable(id);
		});

		$('#add_seller_cron').on('click', function(e){
			var id = $('#hidden_seller_id').val();
			var token = '{{ csrf_token() }}';
			var datas = "seller_id="+id+"&_token="+token;

			if(id > 0){
				$('.loading-table-cron').html(" Initializing table ... ");
				$('#cron_modal').modal('show');
				$.ajax({
				  type: "POST",
				  url: 'getNotSelectedCrons',
				  data: datas,
				  success: function(result){
				  	var response = jQuery.parseJSON(result);
				  	$('#cron_table').dataTable({
			            "data": response,
			            "bLengthChange": false,
					    "bFilter": false,
					    "destroy": true,
					    "bPaginate": false,
					    "paging": false,
					    "ordering": false,
			        });

					$('.loading-table-cron').html("");
				  }
				});
			}else{
				$('#modal-body-error').html('Please Select a Seller!');
				$('#cron_modal_error').modal('show');
			}
		});

		$('#add_selected_seller_cron').on('click', function(e){
			var cron_ids = "";
			var id = $('#hidden_seller_id').val();
			var cron_id_prop = "";
			var token = '{{ csrf_token() }}';
			$('input:checkbox').each(function () {
				var pid =  (this.checked ? $(this).val() : "");
				if(pid!=""){
					cron_id_prop += $('#inp_minutes_'+pid).val() + "-";
					cron_id_prop += $('#inp_hours_'+pid).val() + "-";
					cron_id_prop += $('#inp_dom_'+pid).val() + "-";
					cron_id_prop += $('#inp_month_'+pid).val() + "-";
					cron_id_prop += $('#inp_dow_'+pid).val() + "-";
					cron_id_prop += $('#inp_isactive_'+pid).val() + "+";
				}
			   cron_ids += (this.checked ? $(this).val()+"-" : "");
			   $(this).prop("checked", false);
			});
			var datas = "seller_id="+id+"&_token="+token+"&cron_ids="+cron_ids+"&cron_id_prop="+cron_id_prop;
			$.ajax({
			  type: "POST",
			  url: 'addCronToSeller',
			  data: datas,
			  success: function(result){
			  	update_seller_cron_datatable(id);
			  }
			});
		});

	});

</script>

@include('admin.cronsched.seller_cron_sched_modal')
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12 col-sm-12 cron-main-div" id="wrap-fluid">
        <div class="row">
	        <div class="col-md-3 col-sm-12">
	        	<input type="hidden" id="hidden_seller_id" value="0">
	        	<label style="margin-top:2px;">Seller List</label><br />
						<p>Current Server Time:&nbsp;<strong><a id="clock"></a></strong></p>
	        	<table id="seller_table" cellspacing="0" cellpadding="0" class="table table-striped table-bordered dataTable no-footer" style="width:100%;">
	        	<thead>
		        	<td>ID</td>
		        	<td>Company Name</td>
		        	<td>Seller Name</td>
	        	</thead>
	        	<tbody>
	        	<?php
	        		foreach ($seller as $key => $value) {
	        	?>
	        		<tr data-id="{{ $value->id }}">
	        			<td> {{ $value->id }} </td>
	        			<td> {{ $value->company }} </td>
	        			<td> {{ ucwords($value->firstname)." ".ucwords($value->lastname) }} </td>
	        		</tr>
	        	<?php
	        		}
	        	?>
	        	</tbody>
	        </table>
	        </div>
	        <div class="col-md-9 col-sm-12">
	        	<label>Seller Cron</label> <span class="loading-table"> </span>
	        	<span class="cronsched-float-right">
	        		<button class="btn btn-primary btn-small" id='add_seller_cron'>Add Seller Crons</button>
	        	</span>
						<div class="table-responsive" style="overflow-y: hidden;">
							<table id="seller_cron_table" cellspacing="0" cellpadding="0" class="table table-striped table-bordered dataTable no-footer" style="width:100%;">
									<thead>
										<td>Cron Name</th>
										<td>Cron Path</th>
										<td>Minutes</th>
										<td>Hours</th>
										<td>Day of Month</th>
										<td>Month</th>
										<td>Day of Week</th>
										<td>isActive</th>
									</thead>
									<tbody>
									</tbody>
								</table>
						</div>
	        </div>
        </div>
      </div>
    </div> <!-- end of Banner image -->
  </div>
@endsection
