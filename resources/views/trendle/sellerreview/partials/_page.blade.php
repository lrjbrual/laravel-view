<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/datepicker/css/bootstrap-datepicker.min.css')}}" />
<script type="text/javascript">
var isfilterclicked = false;
var action = "";
var _token = $('meta[name="csrf-token"]').attr('content')

function viewAllComments(id) {
    var popup = document.getElementById("myPopup"+id);
    popup.classList.toggle("show");
}
function addComment(id) {
    var popup = document.getElementById("myPopupAdd"+id);
    popup.classList.toggle("show");
 	$('#comment_text'+id).focus();
 	$('#comment_text'+id).keydown(function(event){
 		var x = event.which || event.keyCode;
 		//alert(x);
 		if(x == 13){
 			var comment = $('#comment_text'+id).val();
			var token = document.getElementsByName('_token')[0].value;
 			var datas = "review_id="+id+"&_token="+token+"&comment="+comment;
 			$.ajax({
			  type: "POST",
			  url: 'AddReviewComment',
			  data: datas,
			  success: function(result){
			  	//document.getElementById("myPopup"+id).innerHTML += result;
			  }
			});
			if(isfilterclicked)
				change_datatable_body(1);
			else
				change_datatable_body(0);
			event.preventDefault();
 		}else if(x == 32){
 			var comment = $('#comment_text'+id).val();
 			$('#comment_text'+id).val(comment + " ");
 			event.preventDefault();
 		}
 	});
}
function change_datatable_body(flag){
	$('.loading-table').html("<b>Loading ... </b>");
	$('.loading_result').show();
	if(flag==1){
		var countries = "";
		$('input:checkbox').each(function () {
		       countries += (this.checked ? $(this).val()+"-" : "");
		  });
		var date_range = $('#date_range').val();
		var date_from = $('#date_from').val();
		var date_to = $('#date_to').val();
		var rating_from = $('#rating_from').val();
		var rating_to = $('#rating_to').val();
		var text_filter = $('#text_filter').val();
	}
	var type = $('#sku_asin_name').val();
	var token = document.getElementsByName('_token')[0].value;
	var datas = "display_type="+type+"&_token="+token+"&action="+action;
	if(flag==1){
		datas = "display_type="+type+"&_token="+token+"&countries="+countries+"&date_range="+date_range+"&date_from="+date_from+"&date_to="+date_to+"&rating_from="+rating_from+"&rating_to="+rating_to+"&text_filter="+text_filter;
	}
	$.ajax({
	  type: "POST",
	  url: 'SellerReviewsFilter',
	  data: datas,
	  success: function(result){
  	  	var response = jQuery.parseJSON(result);
  	  	$('#sellerreview_table').dataTable({
            "data": response,
            "bLengthChange": false,
  	        "bFilter": false,
  	        "destroy": true,
  	        "aoColumnDefs" : [
			{
			  'bSortable' : false,
			  'aTargets' : [ 2 ]
			}],
			createdRow: function( row, data, dataIndex ) {

                $(row).children(':nth-child(9)').find('.addComment').click(function(){
                	var id = $(this).attr('data-id')

                	swal({
						  title: 'Add comment to this product',
						  input: 'textarea',
						  showCancelButton: true,
						  confirmButtonText: 'Submit',
						  showLoaderOnConfirm: true,
						  preConfirm: function (comments) {
						    return new Promise(function (resolve, reject) {
						      datas = {
				 					_token: _token,
				 					review_id: id,
				 					comment: comments
				 				}
						      	
						      	if (comments != '') {
						      		$.ajax({
									    type: "POST",
									  	url: 'AddReviewComment',
									  	data: datas,
									  	success: function(result){
										  	if(isfilterclicked)
												change_datatable_body(1);
											else
												change_datatable_body(0);

									  		resolve()
									  	},
									  	error: function(){
									  		reject('Opps! something went wrong when saving.')
									  	}
									})
						      	}else{
						      		reject('Please write a comment.')
						      	}

						    })
						  },
						  allowOutsideClick: false
						}).then(function (email) {
						  swal({
						    type: 'success',
						    title: '',
						    text: 'Comment successfully save'
						  })
						}).catch(swal.noop)
                })

			}
        });
        var tableWrapper = $('#sellerreview_table_wrapper');
        tableWrapper.find('.dataTables_length select').select2();
        $('.loading-table').html("");
        $('.loading_result').hide();
        $('[data-toggle="tooltip"]').tooltip(); 

	  }
	});
}
function getReviewFilters(id){
	var token = document.getElementsByName('_token')[0].value;
	var datas = "id="+id+"&_token="+token;
	$.ajax({
	  type: "POST",
	  url: 'GetReviewFilter',
	  data: datas,
	  success: function(result){
	  	var response = jQuery.parseJSON(result);
	  	//alert(response[0].country_filter);
	  	if(id>0){
		  	$('#filter_name').val(response[0].filter_name);
		  	$('#rating_to').val(response[0].rating_to_filter);
		  	$('#rating_from').val(response[0].rating_from_filter);
		  	$('#text_filter').val(response[0].text_filter);
		  	$('#date_range').val(response[0].date_range_filter);
		  	var countries1 = response[0].country_filter;
		  	countries = countries1.split('-');
		  	$('input:checkbox').each(function () {
		  		if($.inArray($(this).val(), countries) >= 0) $(this).prop("checked", true);
			  });
		  	change_date_selector();
	  	}else{
	  		for(var x=0;x<response.length;x++)
	  		$('#filter_selector').append($('<option>', {
			    value: response[x].id,
			    text: response[x].filter_name
			}));
	  	}
	  }
	});
}
function change_date_selector(){
	var range = $('#date_range').val();
	if(range == 0){
		$('#date_to').datepicker('clearDates');
		$("#date_from").datepicker('clearDates');
	}else if(range == 7){
    $('#date_to').datepicker('setDate', new Date());
    $('#date_from').datepicker('setDate', "-6d");
	}else if(range == 14){
    $('#date_to').datepicker('setDate', new Date());
    $('#date_from').datepicker('setDate', "-13d");
	}else if(range == 30){
    $('#date_to').datepicker('setDate', new Date());
    $('#date_from').datepicker('setDate', "-29d");
	}else if(range == 60){
    $('#date_to').datepicker('setDate', new Date());
    $('#date_from').datepicker('setDate', "-59d");
	}else if(range == 180){
    $('#date_to').datepicker('setDate', new Date());
    $('#date_from').datepicker('setDate', "-179d");
	}else if(range == 365){
    $('#date_to').datepicker('setDate', new Date());
    $('#date_from').datepicker('setDate', "-364d");
	}
}
	$(document).ready(function(){
		getReviewFilters(0);
		change_datatable_body(0);
		$('#sku_asin_name').change(function(e){
			e.preventDefault();
			if(isfilterclicked) change_datatable_body(1);
			else change_datatable_body(0);
			var type = $('#sku_asin_name').val();
			if(type == 'sku'){
				$('#change-type').html('SKU');
				$('#change-type-th').html('SKU');
			}else if(type == 'asin'){
				$('#change-type').html('ASIN');
				$('#change-type-th').html('ASIN');
			}else if(type == 'product_name'){
				$('#change-type').html('Product Name');
				$('#change-type-th').html('Product Name');
			}else{
				$('#change-type').html('SKU');
				$('#change-type-th').html('SKU');
			}
		});
		$('#reset_filter').click(function(e){
			e.preventDefault();
			location.reload();
		});
		$('#apply_filter').click(function(e){
			isfilterclicked = true;
			e.preventDefault();
			change_datatable_body(1);
		});
		$('#save_filter').click(function(e){
			e.preventDefault();
			addNewFilters();
		});
		$('#closed-reviews').click(function(e){
			e.preventDefault();
			action = "archive";
			if(isfilterclicked) change_datatable_body(1);
			else change_datatable_body(0);
		});

		$('#later-reviews').click(function(e){
			e.preventDefault();
			action = "later";
			if(isfilterclicked) change_datatable_body(1);
			else change_datatable_body(0);
		});

		$('#inbox-reviews').click(function(e){
			e.preventDefault();
			action = "inbox";
			if(isfilterclicked) change_datatable_body(1);
			else change_datatable_body(0);
		});
		$('#date-action').change(function(e){
			e.preventDefault();
		});
		$('#date_range').change(function(e){
			e.preventDefault();
			change_date_selector();
		});
		$("#filter_selector").change(function(e){
			e.preventDefault();
			getReviewFilters($("#filter_selector").val());
		});
		$(".ratings").change(function(e) {
		    var $this = $(this);
		    var val = $this.val();
		    if (val > 5){
		        e.preventDefault();
		        $this.val(5);
		    }
		    else if (val < 1)
		    {
		        e.preventDefault();
		        $this.val(1);
		    }
		});
    $("#sr-filter-action").click(function(){
        $("#sr-filter").toggle();
        if($("#sr-filter-action").html() == "Show Filters"){
          $("#sr-filter-action").html("Hide Filters");
        } else {
          $("#sr-filter-action").html("Show Filters");
        }
    });
    $(function(){
        $("#inbox-reviews").click();
    });
	});
	function change_review_action(id,moveto){
		var token = document.getElementsByName('_token')[0].value;
		var action_date = $('#date-action-'+id).val();
		var datas = "action_date="+moveto+"&id="+id+"&_token="+token;
		var isError = false;
		var custom_date = $('#custom-date-action-'+id).val();
		$('#custom-date-action-'+id).removeAttr('type');
		$('#custom-date-action-'+id).attr('type', 'hidden');
		if(action_date == "") isError = true;
		if(action_date == 'custom'){
			if(custom_date == "" || custom_date == " " || custom_date == 'null'){
				isError = true;
				$('#custom-date-action-'+id).removeAttr('type');
				$('#custom-date-action-'+id).attr('type', 'text');
				$('#custom-date-action-'+id).datepicker({
				    format: 'yyyy-mm-dd',
				    autoclose: true
				}).on('change', function(){
					change_review_action(id);
				});
			}
			else datas += "&custom_date="+custom_date;
		}
		if(!isError){
		$('.loading-table').html("<b>Loading ... </b>");
		$('.loading_result').show();
			$.ajax({
			  type: "POST",
			  url: 'UpdateReviewsAction',
			  data: datas,
			  success: function(result){
			  	$('.loading-table').html("");
			  	$('.loading_result').hide();
			  	if(action == ""){
			  		if(isfilterclicked) change_datatable_body(1);
			  		else change_datatable_body(0);
			  	}else{
			  		change_datatable_body(0);
			  	}
			  }
			});
		}
	}
	function addNewFilters(){
		var isError=false;
		var title = $('#filter_name').val();
		if(title=="" || title ==" " || title=='null'){
			isError = true;
			$('#err_search_bar').html('<small><div class="alert alert-danger">Filter Name should not be empty!</div></small>');
		}
		var countries = "";
		$('input:checkbox').each(function () {
		       countries += (this.checked ? $(this).val()+"-" : "");
		  });
		var type = $('#sku_asin_name').val();
		var token = document.getElementsByName('_token')[0].value;
		var date_range = $('#date_range').val();
		var date_from = $('#date_from').val();
		var date_to = $('#date_to').val();
		var rating_from = $('#rating_from').val();
		var rating_to = $('#rating_to').val();
		var text_filter = $('#text_filter').val();
		datas = "display_type="+type+"&_token="+token+"&countries="+countries+"&date_range="+date_range+"&date_from="+date_from+"&date_to="+date_to+"&rating_from="+rating_from+"&rating_to="+rating_to+"&text_filter="+text_filter+"&title="+title;
		if(!isError){
			$.ajax({
			  type: "POST",
			  url: 'AddReviewFilter',
			  data: datas,
			  success: function(result){
			  	$('#err_search_bar').html('<small><div class="alert alert-success">Successfully added!</div></small>');
			  }
			});
		}
		setTimeout(function() {
		  //your code to be executed after 1 second
		  $('#err_search_bar').html("");
		}, 5000);
	}
</script>

@include('trendle.sellerreview.partials._upperdiv')
<div class="col-md-12">

  <span id="sr-filter-action">Show Filters</span>
	<div id="sr-filter" class="col-md-12 s_reviews_filter dontdisplay">
		<div id="err_search_bar"></div>

    <div class="col-md-2">
      <div class="">
        <label class="">Saved Filters</label>
        <select class="form-control input-sm" type="" name="" id="filter_selector">
          <option value='0'>New Filter</option>
        </select>
      </div>

      <div class="">
        <label class="">Filter Name</label>
        <input id="filter_name" class="form-control input-sm" type="" name="">
      </div>
    </div>


		<div class="col-md-4">
			<label class="col-md-12">Country</label>
			<div class="col-md-7">
			  <label><input class="" type="checkbox" value="uk"> United Kingdom</label><br>
			  <label><input class="" type="checkbox" value="de"> Germany</label><br>
			  <label><input class="" type="checkbox" value="it"> Italy</label><br>
			  <label><input class="" type="checkbox" value="ca"> Canada</label>
			</div>
      <div class="col-md-5">
        <label><input class="" type="checkbox" value="fr"> France</label><br>
        <label><input class="" type="checkbox" value="es"> Spain</label><br>
        <label><input class="" type="checkbox" value="us"> USA</label><br>
      </div>
		</div>

		<div class="col-md-3">
      <div class="input-group">
        <label>Date</label><br>
        <select id='date_range' class="form-control input-sm m-t-5" type="" name="">
          <option value='7'>Last 7 Days</option>
          <option value='14'>Last 14 Days</option>
          <option value="30">Last 30 Days</option>
          <option value="60">Last 60 Days</option>
          <option value="180">Last 180 Days</option>
          <option value="365">Last 365 Days</option>
          <option value="0" selected>Exact Days</option>
        </select>
      </div>

      <div class="input-group input-daterange m-t-5">
          <input type="text" id="date_from" class="form-control" style="background:#fff;" readonly>
          <span class="input-group-addon">to</span>
          <input type="text" id="date_to" class="form-control" style="background:#fff;" readonly>
      </div>
		</div>

    <div class="col-md-3">
      <label>Rating</label>
      <div class="input-group m-t-5">
          <input id="rating_from" class="form-control" type="number" value="1" min="1" max="5">
          <span class="input-group-addon">to</span>
          <input id="rating_to" class="form-control" type="number" value="5" min="1" max="5">
      </div>
			<label class="row col-md-12" id="change-type">SKU</label>
			<input id="text_filter" class="form-control input-sm"  type="" name="">
		</div>

		<div class="col-md-12">
      <button id="save_filter" class=" btn btn-default button-rectangle pull-right">Save Filter</button>
			<button id="reset_filter" class="btn btn-orange pull-right m-r-10">Reset Filter</button>
			<button id="apply_filter" class="btn btn-primary pull-right m-r-10">Apply Filter</button>
		</div>
	</div>
</div>
<div class="col-md-12 m-t-25">
  <div class="s_reviews_view_buttons">
    <button class="btn s_reviews_view_btn" id="closed-reviews">Archive</button>
    {{-- <button class="btn s_reviews_view_btn" id="later-reviews">Remind me later</button> --}}
    <button class="btn s_reviews_view_btn" id="inbox-reviews">Inbox</button>
  </div>

  <div class="col-md-12 m-t-5">
    {{ Form::open(array('url' => 'SellerReviews')) }}
      {{ csrf_field() }}

      {{-- <label class="row col-md-1">Show</label>
      <div class="row col-md-2">
      <span class="loading-table col-md-2 m-t-5"></span>
      </div> --}}
    {{ Form::close() }}
  </div>

  <div class="col-md-12 m-t-5" id="sellerreview_div">
  <div class="dataTables_processing2 loading_result" style="top:50px;z-index: 1059"> <b>Loading result. . . <i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span></b></div>
      <table id="sellerreview_table" cellspacing="0" cellpadding="0" class="table table-striped table-bordered dataTable no-footer" style="width:100%;">
          <thead>
              <tr>
                <th class="sort">Date</th>
                  <th class="sort">Country</th>
                  <th class="sort" >
                  	<select name = "display_type" id="sku_asin_name" class="form-control" style="height: 15px">
			          <option value="sku" selected="true">SKU</option>
			          <option value="asin">ASIN</option>
			          <option value="product_name">Product Name</option>
			        </select>
                  </th>
                  <th class="sort">Review Comment</th>
                  <th class="sort">Review<br>Rating</th>
                  <th class="sort">Review Name</th>
                  <th class="sort">Order<br>Number</th>
                  <th class="sort">Archive</th>
                  <th class="sort">Comments</th>
              </tr>
          </thead>
          <tbody>

          </tbody>
      </table>
  </div>
</div>
<script type="text/javascript" src="{{asset('assets/vendors/datepicker/js/bootstrap-datepicker.min.js')}}"></script>
<script type="text/javascript">
$(document).ready(function(){
	

    $('.input-daterange input').each(function() {
        $(this).datepicker({
            todayHighlight: true,
            autoclose: true,
            orientation: "auto",
        }).on('changeDate', function(e) {
            if($(this).attr("id") == "date_to") {
            	$("#date_from").datepicker('setEndDate', $("#date_to").val());
            } else if($(this).attr("id") == "date_from") {
            	$("#date_to").datepicker('setStartDate', $("#date_from").val());
            }
        });
    });
    $("#rating_from").change(function(){
        $(this).attr('max', $("#rating_to").val());
        $("#rating_to").attr('min', $(this).val());
    });
    $("#rating_to").change(function(){
        $(this).attr('min', $("#rating_from").val());
        $("#rating_from").attr('max', $(this).val());
    });
    $(".s_reviews_view_btn").click(function(){
        $(".s_reviews_view_btn").removeClass('sr_active');
        $(this).addClass('sr_active');
    });
});
</script>