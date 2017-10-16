@extends('layouts.admin')
@section('title', '| Cron Schedule')

@section('user')

<script type="text/javascript">
var oTable;
	function update_cronlogs_datatable(){
		$('.loading-table').html(" Initializing table ... ");
		var id = $('#selected_seller').val();
		var desc = $('#selected_description').val();
		var token = '{{ csrf_token() }}';
		var datas = "seller_id="+id+"&_token="+token+"&desc="+desc;
		$.ajax({
		  type: "POST",
		  url: 'getcronlogs',
		  data: datas,
		  success: function(result){
		  	var response = jQuery.parseJSON(result);
		  	oTable = $('#cron_logs_table').dataTable({
	            "data": response,
	            "bLengthChange": false,
			    "bFilter": false,
			    "destroy": true,
	        });

			$('.loading-table').html("");
		  }
		});
	}

	$(document).ready(function(){
		update_cronlogs_datatable();

		$('#selected_seller').change(function(event) {
			update_cronlogs_datatable();
		});


		$('#selected_description').change(function(event) {
			update_cronlogs_datatable();
		});

	});

</script>

@include('admin.cronsched.seller_cron_sched_modal')
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12 col-sm-12 cron-main-div" id="wrap-fluid">
      <div>
      	<label>Filters: </label>
  		<select id="selected_seller">
  			<option value='0' selected="selected">All Sellers</option>
  			<?php foreach ($seller as $value) { ?>
  				<option value="{{ $value->id }}">{{ ucwords($value->company) }}</option>
  			<?php } ?>
  		</select>
  		<select  id="selected_description">
  			<option value="0" selected="selected">All Description</option>
  			<?php foreach ($desc as $value) { ?>
  				<option value="{{ $value->description }}">{{ ucwords($value->description) }}</option>
  			<?php } ?>
  		</select>
      	<span class="loading-table"> </span>
      </div>
			<div class="table-responsive" style="overflow-y: hidden;">
				<table id="cron_logs_table" cellspacing="0" cellpadding="0" class="table table-striped table-bordered dataTable no-footer" style="width:100%;">
						<thead>
							<td>Description</th>
							<td>Date Sent</th>
							<td>Subject</th>
							<td>API Used</th>
							<td>Start Time</th>
							<td>End Time</th>
							<td>Records Fetched</th>
							<td>Elapsed Time</th>
						</thead>
						<tbody>
						</tbody>
					</table>
			</div>
      </div>
    </div> <!-- end of Banner image -->
  </div>
@endsection
