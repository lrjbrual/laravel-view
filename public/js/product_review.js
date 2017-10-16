var obj_param = {

			_token: $('meta[name="csrf-token"]').attr('content'),
			tab: 'inbox',
			processing: false,
			product_unique: 'sku',

		}

$(document).ready(function(){

	$(".s_reviews_view_btn").click(function(){
        $(".s_reviews_view_btn").removeClass('sr_active')
        $(this).addClass('sr_active')
        var tab = $(this).attr('data-tab')
        changeTab(tab,obj_param)
    })

    init_table(obj_param)

    $('#sku_asin_name').change(function(){
    	obj_param.product_unique = $(this).val()

    	init_table(obj_param)
    })

    $('#product_sku_asin_name').change(function(){
    	obj_param.product_unique = $(this).val()
    	
    	init_table(obj_param)
    })

    $('.email_notif').click(function(){
		if ($(this).is(':checked')) {
			activateNotif(1)
		}else{
			activateNotif(0)
		}
	})

})


function activateNotif(action){
	$.ajax({
      	url: 'productreview/notifAction',
      	type: 'POST',
      	data: {notif: action},
      	success: function(response){
      		
      	}
    })
  }


function viewAllComments(id) {
    var popup = document.getElementById("myPopup"+id)
    popup.classList.toggle("show")
}

function changeTab(tab,obj_param){
	obj_param.tab = tab

	init_table(obj_param)
}

function init_table(obj_param)
{
	var data = 
	{
		_token: obj_param._token,
		tab: obj_param.tab,
		product_unique: obj_param.product_unique
	}
	obj_param.processing = true
	$('.loadingTableContainer').show()

	if (obj_param.tab != "products") 
	{
		$('#inboxArchiveContainer_table').show()
		$('#productsContainer_table').hide()

		if (obj_param.tab == 'inbox') {
			$('.rName').html('Reviewer Name')
		}else{
			$('.rName').html('Review Name')
		}

		var oTable = $('#productreview_table').DataTable( {
	        "processing": true,
	        "serverSide": true,
	        "oLanguage": {
	          sProcessing: '<div class="dataTables_processing2" style="display:block;z-index: 999;top:50%;"><b>Loading records </b><i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span></div>'
	        },
	        "deferRender": true,
	        "searching" : false,
	        "destroy" : true,
	        "ajax": {
	        	url: 'getProductReviewData',
				type: 'POST',
				data: data,
				// beforeSend()
				// {
				// 	obj_param.processing = true
				// 	$('.loadingTableContainer').show()
				// },
				error: function(xhr, status, response)
				{
					swal('Opps!','error occured during fetching of data','error')
					$('.loadingTableContainer').hide()
				},
	        }, 
	        createdRow: function( row, data, dataIndex ) 
	        {
				$(row).children(':nth-child(5)').hover(function(){
                    $('[data-toggle="tooltip"]').tooltip()
                })

                $(row).children(':nth-child(8)').hover(function(){
                    $('[data-toggle="tooltip"]').tooltip()
                })

                $(row).children(':nth-child(7)').hide()

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
				 					_token: obj_param._token,
				 					review_id: id,
				 					comment: comments
				 				}
						      	
						      	if (comments != '') {
						      		$.ajax({
									    type: "POST",
									  	url: 'AddProductReviewComment',
									  	data: datas,
									  	success: function(result){
										  	init_table(obj_param)
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
	    } );
	}
	else
	{
		$('#inboxArchiveContainer_table').hide()
		$('#productsContainer_table').show()

		var oTable = $('#product_table').DataTable( 
		{
	        "processing": true,
	        "serverSide": true,
	        "oLanguage": {
	          sProcessing: '<div class="dataTables_processing2" style="display:block;z-index: 999;top:50%;"><b>Loading records </b><i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span></div>'
	        },
	        "deferRender": true,
	        "searching" : false,
	        "destroy" : true,
	        "ajax": {
	        	url: 'getProductReviewData',
				type: 'POST',
				data: data,
				error: function(xhr, status, response)
				{
					swal('Opps!','error occured during fetching of data','error')
					$('.loadingTableContainer').hide()
				}
	        },
	        "aoColumnDefs" : [
			{
			  'bSortable' : false,
			  'aTargets' : [ 1 ]
			}],
		        createdRow: function( row, data, dataIndex ) {

				$(row).children(':nth-child(3)').hover(function(){
	                $('[data-toggle="tooltip"]').tooltip()
	            })

			}
		});
	
	}
	obj_param.processing = false
	$('.loadingTableContainer').hide()
	
}

function change_review_action(id,moveto){
	var data_obj = {
		id: id,
		moveto: moveto
	}

	var data = Object.assign({}, data_obj, obj_param)

	$.ajax({
		url: 'moveProducts',
		type: 'POST',
		data: data,
		beforeSend: function(){
			$('.loadingTableContainer').show()
		},
		success: function(result){
			$('.loadingTableContainer').hide()

			swal('','Sucessfully moved to '+moveto+'','success')

			init_table(obj_param)
		},
		error: function(xhr, status, response){
			swal('Opps!','error occured during fetching of data','error')
			$('.loadingTableContainer').hide()
		}
	})
}
