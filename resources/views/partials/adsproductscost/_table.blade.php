	<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/chartist/css/chartist.min.css')}}" />
    <link type="text/css" rel="stylesheet" href="{{asset('assets/css/pages/chartist.css')}}" />
	<div class="row">
		@include('partials.adsproductscost._ads_prod_costs_modal_graph')
		<div class="col-md-12">
			<div class="col-md-12"></div>
			<div class="col-md-12">
				@if ($message = Session::get('success'))
					<div class="alert alert-success" role="alert">
						{{ Session::get('success') }}
					</div>
				@endif
				@if ($message = Session::get('error'))
					<div class="alert alert-danger" role="alert">
						{{ Session::get('error') }}
					</div>
				@endif
			</div>
			<div class="col-md-12 pull-left m-t-20">
				<form id="upload_form" action="{{ url('productscosts/importExcelAdsProduct') }}" class="form-horizontal" method="post" enctype="multipart/form-data">
					<a class="btn btn-info no-radius" target="_blank" href="{{ url('exportExcelAdsProduct') }}"><i class="fa fa-cloud-download"></i> Export table as CSV File</a>
					&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="file" name="import_file" id="import_file" style="display:none"/>
					{{ csrf_field() }}
					<button id="btn-upload-view" type="button" class="btn btn-primary" onclick="click_upload()">
						<i class="fa fa-cloud-upload"></i>
						 Import CSV or Excel File
					</button>
				</form>
			</div>
			<div class="col-md-12 m-t-20">
				<div class="containerAdsProdCostTable">
					
		              <table cellspacing=0 cellpadding=0 class="table table-striped table-bordered table_res" id="adsprodcost_table">
		                  <thead>
			                  <tr>
			                    <th><p style="width:70px;"></p>Country</th>
			                    <th><p style="width:100px;"></p>SKU</th>
			                    <th><p style="width:80px;"></p>ASIN</th>
			                    <th><p style="width:180px;"></p>Product Name</th>
			                    <th><p style="width:70px;"></p>Price on Amazon</th>
			                    <th><p style="width:100px;">Sale Price</th>
			                    <th><p style="width:100px;">Est. Amazon Fees</th>
			                    <th><p style="width:100px;">Unit Cost</th>
			                    <th><p style="width:100px;">Est. Profit</th>
			                    <th><p style="width:100px;">Est. Profit (%)</th>
			                    <th><p style="width:120px;">Target Profit Margin</th>
			                    <th><p style="width:170px;">ACoS Calculation Period</th>
			                    <th><p style="width:100px;">Target ACoS</th>
			                  </tr>
		                  </thead>
		                  <tbody>
		                  </tbody>
		              </table>
				</div>
			</div>
		</div>
	</div>
<script type="text/javascript" src="{{asset('assets/vendors/chartist/js/chartist.min.js')}}"></script>
<script type="text/javascript" src="js/adsprodcosts.js"></script>
<script type="text/javascript">
	function changeTimePeriod(id){
	  time_period = $("#time_period"+id).val();
	  var _token = "{{ csrf_token() }}";
	  var data = { _token:_token, id:id, time_period:time_period };
	  $.ajax({url: "updateConversionTimePeriod", type: 'POST', data: data, success: function(result){
	        
	    }
	    });
	}

	function click_upload(){
		document.getElementById("import_file").click();
	}

	$(document).ready(function(){
		$('#import_file').on('change', function(evt) {
			evt.preventDefault();
			//var file_data = $('#sortpicture').prop('files')[0];   
			var form_data = new FormData($("#upload_form")[0]);                  
			//form_data.append('file', file_data);
			localStorage.setItem("processing", 1);
        	swal('','Processing uploaded CSV data!','info');
        	$("#btn-upload-view").html('Uploading data <i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span>');
        	$('#btn-upload-view').attr('disabled',true)
			$.ajax({
	            url: '{{ url('productscosts/importExcelAdsProduct') }}', // point to server-side PHP script 
	            dataType: 'text',  // what to expect back from the PHP script, if anything
	            cache: false,
	            contentType: false,
	            processData: false,
	            data: form_data,                         
	            type: 'post',
	            success: function(response){
	            	var n = response.search("success");
	            	if(n < 0 ){
	            		swal('',response,'error');
	            		localStorage.setItem("processing", 0);
	            		$('#btn-upload-view').attr('disabled',false)
	            	}else{
	            		swal('',response,'success');
	            		localStorage.setItem("processing", 0);
	            		$('#btn-upload-view').attr('disabled',false)
	            	} 
	            		
	            	$("#btn-upload-view").html('<i class="fa fa-cloud-upload"></i> Import CSV or Excel File');
	                //alert(php_script_response); // display response from the PHP script, if any
	            },
	            error(xhr, status, response){
	            	localStorage.setItem("processing", 0);
	            	$("#btn-upload-view").html('<i class="fa fa-cloud-upload"></i> Import CSV or Excel File');
	            	$('#btn-upload-view').attr('disabled',false)
	            }
			 });
		});
	});

</script>