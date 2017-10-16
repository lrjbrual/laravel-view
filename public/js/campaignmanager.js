	var total_record = "",
		_token = $('meta[name="csrf-token"]').attr('content'),
		ad_group_obj,
		counter = 0

	var adgroup_keyword_arr = [];
	$(document).ready(function(){
		initialize_table();

	    $('.campaign_type').click(function(){
	    	setCampType(this)
	    })

	    $('.is_link').click(function(){
	    	showExistingCampaign(this)
	    })

	    $(".multiple_select").chosen({allow_single_deselect: true}); 
	    $('.existing_manual ,.existing_auto').hide()

	    $('.addNewAdGroup').click(function(){
	    	add_newAdGroupRow();
	    })

	    $('.newcamp').click(function(){
	    	if(checkbsvalue() == true){
		    	if($(this).attr('data-show') == 'false'){
		    		$('.newParentCampaignContainer').slideDown(function(){
			    		$('.newcamp').html('<i class="fa fa-close"></i> Cancel');
			    		$('.newcamp').removeClass('btn-primary');
			    		$('.newcamp').addClass('btn-danger');
			    		$('.newcamp').attr('data-show', 'true')
			    	});
		    	}else{
		    		$('.newParentCampaignContainer').slideUp(function(){
			    		$('.newcamp').html('<i class="fa fa-plus"></i> New Campaign');
			    		$('.newcamp').removeClass('btn-danger');
			    		$('.newcamp').addClass('btn-primary');
			    		$('.newcamp').attr('data-show', 'false')
			    	});
		    	}
		    }
	    	
	    })

		$('.tabcontent').css('display','none');

		tabSwitch('newCampTab','newCampaginBtn');

		$('#next_newCampaign_btn').click(function(){
			if(validateNewCampaignData()){
				tabSwitch('AdGroupTab','adGroupTabBtn');

				$('.campaign_type').each(function(){
					if($(this).is(':checked')){
						if($(this).attr('data-val') == 'auto'){
							$('#next_newAdGroup_btn').hide();
							$('#saveNewCampaignBtn').show();
							$('.default_matchtype_container').hide();
						}else{
							$('#next_newAdGroup_btn').show();
							$('#saveNewCampaignBtn').hide();
							$('.default_matchtype_container').show();
						}
					}
				})

			}
		})

		$('#back_newCampaign_btn').click(function(){
			tabSwitch('newCampTab','newCampaginBtn');
		})

		$('#next_newAdGroup_btn').click(function(){
			if(validateAddGroupData()){
				temp_save_ad_group();
				populate_temp_saved_adgroup();
				tabSwitch('KeywordsTab','keywordTabBtn');
			}
		})

		$('#back_newAdGroup_btn').click(function(){
			tabSwitch('AdGroupTab','adGroupTabBtn');
		})

		$('.addRowKeyword').click(function(){
	    	add_rowKeyword($(this));
	    })

	    
		$('.text_area_bulk_keyword').keypress(function (e){
			var code = e.keyCode || e.which;	
			
			if(code == '13'){
				counter++;
			}else{
				counter = 0;
			}

			if(counter > 1){
				e.preventDefault();
				$('.text_area_bulk_keyword').focus();
			}
		});

		$('#newCampaign_dailyBudget').blur(function(){
			if(!validateNumberPositiveOnly($(this).val())){
				swal('','Please input number only and not less than 1 and up 2 decimal places','error')
				$(this).val('');
			}else{
				if($(this).val() < 1){
					swal('','Please input number only and not less than 1 and up 2 decimal places','error')
					$(this).val('');
				}
			}
		})

		$('#newCampaign_defaultBudget, .default_bid_tb').blur(function(){
			validate_default_bid($(this));
		})

		$('.addKeyWordBtn').click(function(){
			add_keyword_bulk($(this))
		})

		$('.saveNewCampaignBtn').click(function(event){
			saveCampaign(event);
		})

		$('.chosen-container').css('width','100%')


		$('.countrySelect').change(function(){
			
			get_country_sku($(this).val());
			
		})

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

	function saveCampaign(event){
// 		campaign fields
// name, campaignType, targetingType, state, dailyBudget and startDate

// adgroup fields
// campaignId, name, state and defaultBid

// keywords fields
// campaignId, adGroupId, keywordText, 
// matchType and state
		var camp_type;
		var link_campaign;
		var is_link = false;
		$('.campaign_type').each(function(){
			if($(this).is(':checked')){
				if($(this).attr('data-val') == 'auto'){
					camp_type = 'Automatic';
				}else{
					camp_type = 'Manual';
				}
			}
		})

		if ($('#is_link').is(':checked')){
			$('.campaign_type').each(function(){
				if($(this).is(':checked')){
					is_link = true;
					if($(this).attr('data-val') == 'auto'){
						link_campaign = $('.existing_manual_select').val().toString();

					}else{
						link_campaign = $('.existing_auto_select').val().toString();

					}
				}
			})
		}

		if(validateAddGroupData()){
			temp_save_ad_group();
		}else{
			event.preventDefault();
			return false
		}

		
		if(camp_type == 'Automatic'){
			adgroup_keyword_arr = [];
		}else{
			if(validateKeywordData()){
				setKeywordData();
			}else{
				event.preventDefault();
				return false;
			}
		}

		var data = { 
				// new campaign
				name: $('#newCampaign_campName').val(),
				campaignType: camp_type,
				is_link: is_link,
				link_campaign: link_campaign,
				country: $('#newCampaign_country').val().toString(),
				dailyBudget: $('#newCampaign_dailyBudget').val(),
				new_defaultBid: $('#newCampaign_defaultBudget').val(),
				sku: $('#newCampaign_sku').val().toString(),
				startDate: $('.newCampaign_startDate ').val(),
				// ad group
				ad_groupName: ad_group_obj.ad_group_name,
				defaultBid: ad_group_obj.default_bid,
				defaultMatchType: ad_group_obj.default_matchtype,
				// keyword
				keyword_datas: adgroup_keyword_arr,

			}

		$.ajax({
			url: 'postCampaignData',
			method: 'POST',
			data: data,
			beforeSend: function(){
				$('.saveNewCampaignBtn').attr('disabled',true)
			},
			success: function(result){
				$('.saveNewCampaignBtn').attr('disabled',false)
				swal('','Campaign successfully added','success')
			},
			error: function(){
				$('.saveNewCampaignBtn').attr('disabled',false)
				swal('','Something went wrong when saving data','error')
			}
		})
	}

	function get_country_sku(val){
		var elem = $('#newCampaign_sku');
		if(!val){
			elem.val('').trigger("chosen:updated");
			elem.prop('disabled', true).trigger("chosen:updated");
		}else{
			$.ajax({
				url: 'get_country_sku',
				type: 'POST',
				data : {country: val.toString()},
				beforeSend: function(){
					elem.val('').trigger("chosen:updated");
					elem.prop('disabled', true).trigger("chosen:updated");
				},
				success: function(result){
					var response = jQuery.parseJSON(result);
					elem.html("")
					for(index in response){
						elem.append('<option value="'+response[index]+'">'+response[index]+'</option>');
						elem.trigger('chosen:updated');
					}
					elem.prop('disabled', false).trigger("chosen:updated");
				}
			})
		}
	}

	function tabSwitch(element,btn){
		$('.tabcontent').css('display','none');
		$('.button').removeClass('active');
		$('#'+element).show();
		$('#'+btn).addClass('active');
	}

	function validate_default_bid(element){
		if(!validateNumberPositiveOnly($(element).val())){
			swal('','Please input number only and not less than 0.2 and up 2 decimal places','error')
			$(element).val('');
		}else{
			if($(element).val() < 0.2){
				swal('','Please input number only and not less than 0.2 and up 2 decimal places','error')
				$(element).val('');
			}
		}
	}

	function validateNewCampaignData(){

		var all_input = true;

		if ($('#newCampaign_campName').val().trim() == "" ) all_input = false;
		if (!$('#newCampaign_country').val()) all_input = false;
		if (!$('#newCampaign_sku').val()) all_input = false;
		if ($('#newCampaign_dailyBudget').val().trim() == "" ) all_input = false;
		if ($('.newCampaign_startDate').val().trim() == "" ) all_input = false;

		if ($('#is_link').is(':checked')){
			$('.campaign_type').each(function(){
				if($(this).is(':checked')){
					if($(this).attr('data-val') == 'auto'){
						if (!$('.existing_manual_select').val()) all_input = false;
					}else{
						if (!$('.existing_auto_select').val()) all_input = false;
					}
				}
			})
		}

		if(!all_input){
			swal('','All fields are required except for end date','error')
		}

		return all_input;
	}

	function validateKeywordData(){
		var all_input = true;
		$('.keyword_tb').each(function(){
			if($(this).val().trim() == ""){
				all_input = false;
			}
		})

		if(!all_input){
			swal('','All keywords fields are required','error')
		}
		return all_input;
	}

	function setKeywordData(){
		
		for (index in ad_group_obj.ad_group_name) {
			var keyword_arr = [];
		    var counter = 0;
		    $('.table_keywords_ad_campaign'+index+' tbody  tr').each(function() {

				keyword_arr[counter] = {
					default_bid: $(this).find('.keyword_default_bid').val(),
					match_type: $(this).find('.keyword_match_type').val(),
					keyword: $(this).find('.keyword_tb').val(),
				}
			
				counter ++;
		   	});

			adgroup_keyword_arr[index] = keyword_arr;

		}

	}

	function validateAddGroupData(){
		var inputs = $('.addgroup_container').find('input');
		var all_input = true;
		$(inputs).each(function(){
			if($(this).val() == ""){
				all_input = false;
			}
		})
		if(!all_input){
			swal('','All fields are required','error')
		}

		return all_input;
	}

	function temp_save_ad_group(){
		var ad_group_array = [];
		var bid_array = [];
		var default_mtype_array = [];
		var counter = 0;
		$('.adgroup_row').each(function(){
			$(this).find('.ad_group_tb').val();
			ad_group_array[counter] = $(this).find('.ad_group_tb').val();
			bid_array[counter] = $(this).find('.default_bid_tb').val();
			default_mtype_array[counter] = $(this).find('.default_matchtype').val();
			counter ++;
		})
		ad_group_obj = { 
				"ad_group_name": ad_group_array,
				"default_bid": bid_array,
				"default_matchtype": default_mtype_array
			}

		// console.log(ad_group_obj)
	}

	
	function adCampaignProcess(evt, cityName) {
	    var i, tabcontent, tablinks;
	    tabcontent = document.getElementsByClassName("tabcontent");
	    for (i = 0; i < tabcontent.length; i++) {
	        tabcontent[i].style.display = "none";
	    }
	    tablinks = document.getElementsByClassName("tablinks");
	    for (i = 0; i < tablinks.length; i++) {
	        tablinks[i].className = tablinks[i].className.replace(" active", "");
	    }
	    document.getElementById(cityName).style.display = "block";
	    evt.currentTarget.className += " active";
	}

	function setCampType(element){
		if($(element).is(':checked')) {
    	 	var camp_type = $(element).attr('data-val');
    	 	if(camp_type == 'auto'){
    	 		$('.lblLink').html('Link to existing manual campaign');

    	 	}else{
    	 		$('.lblLink').html('Link to existing automatic campaign');
    	 	}

    	 	if($('.is_link').is(':checked')) {
	    	 	if(camp_type == 'auto'){
	    	 		$('.existing_manual').show();
	    	 		$('.existing_auto').hide();
	    	 	}else{
	    	 		$('.existing_auto').show();
	    	 		$('.existing_manual').hide();
	    	 	}
	    	}
    	}
	}

	function showExistingCampaign(element){
		if($(element).is(':checked')) {
    	 	var camp_type = $('.campaign_type').attr('data-val');
    	 	$('.campaign_type').each(function(){

    	 		if($(this).is(':checked')) {
		    	 	var camp_type = $(this).attr('data-val');
		    	 	if(camp_type == 'auto'){
		    	 		$('.existing_manual').show();
		    	 		$('.existing_auto').hide();
		    	 	}else{
		    	 		$('.existing_auto').show();
		    	 		$('.existing_manual').hide();
		    	 	}
		    	}
    	 	})
    	 	
    	}else{
    		$('.existing_manual, .existing_auto').hide();
    	}
	}

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
		      url: "getCampaignData",
		      type: "POST",
		      data: initialize_data_feed()
		    },
		    "aoColumnDefs" : [
            {
              'bSortable' : false,
              'aTargets' : [ 1 ]
            }],
		    // "scrollX": true,
		    "createdRow": function( row, data, dataIndex ) {

		    	$(row).children(':nth-child(1)').hover(function(e){
                    $('.countryFlag').tipso({
                      position          : 'left',
                      background        : '#e8e8e8',
                      color             : '#787878',
                      size              : 'small'
                    })

                    $('.countryFlag.tipso_style').css({'border-bottom' : 'none', 'margin-bottom' : '-10px'})
                })

                $(row).children(':nth-child(2)').hover(function(e){
                    $('.campTypeIcon').tipso({
                      position          : 'left',
                      background        : '#e8e8e8',
                      color             : '#787878',
                      size              : 'small'
                    })

                    $('.campTypeIcon.tipso_style').css('border-bottom','none')
                })
		    	
		    	$(row).children(':nth-child(2)').find('.toggleCampaignDetails').click(function(){
                    var elem = $(this);
		    		var index = elem.parent().parent().index();
                    var id = elem.parent().parent().attr('id');

                    elem.toggleClass('row-details-open');

                    if(elem.hasClass('row-details-open')){

                    	if(elem.attr('data-withdata') == 1){
	                        $('.rowAdgroup'+id).show();
	                        $('.parentRow'+id).show();
	                        return false;
	                     }


                    	if(typeof id == 'undefined') id = 0;
	                    var output = '';

	                    output += '<tr class="rowAdgroup'+id+'" style="background:#e8e8e8 !important">'+
                                      '<td></td>'+
                                      '<td style="border-left:2px solid #FF5722"></td>'+
                                      '<td colspan="" width="" >'+
                                          '<span class="text-orange"><i class="fa fa-cubes" style="margin-right:10px;"></i><b> Ad Group</b></span>'+
                                      '</td>'+
                                      '<td colspan="" width="" style="border-left:1px dotted #FF5722">'+
                                          '<span class="text-orange"><b>Default Bid</b></span>'+
                                      '</td>'+
                                      '<td colspan="" width="" style="border-left:1px dotted #FF5722">'+
                                          '<span class="text-orange"><b>State</b></span>'+
                                      '</td>'+
                                  '</tr>';

	                    $.ajax({
	                    	url: 'getCampaignAdGroup',
	                    	type: "POST",
	                    	data: { _token: _token, id:id},
	                    	beforeSend: function(){
	                    		$('.loadingTableContainer').show();
	                    	},
	                    	success: function(result){

	                    		elem.attr('data-withdata','1');

	                    		for(var i in result.data){
			                    	  output += '<tr class="rowAdgroup'+id+'" data-id="'+result.data[i].adgroup_id+'">'+
				                    				'<td></td>'+
				                    				'<td style="border-left:2px solid #FF5722;padding-left:20px">'+
				                    					result.data[i].icon+
			                                        '</td>'+
			                                        '<td><span class="color-blue">'+result.data[i].name+'</span></td>'+
			                                        '<td style="border-left:1px dotted #FF5722">'+result.data[i].default_bid+'</td>'+
			                                        '<td style="border-left:1px dotted #FF5722">'+result.data[i].state+'</td>'+
		                                         '</tr>';

	                    		}
	                    		
	                    		$('#adscampaignmanager_table > tbody > tr').eq(index).after(output);
	                    		$('.loadingTableContainer').hide();

	                    		/*EDIT AGROUP NAME*/
	                    		$('.adgroup_name').mousemove(function(){
	                            	var id = $(this).attr('data-adgroup-id')
                    				$('.adgroup_pencilIcon'+id).fadeIn()
                    			})

                    			$('.adgroup_name').mouseout(function(){
                    				var id = $(this).attr('data-adgroup-id')
                    				$('.adgroup_pencilIcon'+id).fadeOut()
                    			})

                    			$('.adgroup_name').click(function(){
				                	var elem = $(this),
				                		html = '<input type="text" class="adgroup_name_tb for_editing_col" value="'+elem.text()+'">',
				                		id = elem.parent().parent().attr('data-id')

				                	elem.css('display','none')
									elem.parent().append(html)
									inputElem = $('.adgroup_name_tb')

									inputElem.select().focus()

									inputElem.focusout(function() {
									  $(this).remove()
									  elem.show();
									})

									$(inputElem).keypress(function (e) {
									  
									  if (e.which == 13) {

									      if ($(inputElem).val() == '') {
									        return false;
									      }

									      if ($(inputElem).val().trim() == "") {
									          swal(" ", "Please input campaign name", "error");
									          return false;
									      }

									      var data = {
									        _token: _token,
									        id: id,
									        new_name: inputElem.val(),
									        forQuery: 'adgroup_name'
									      }

									      $.ajax({
									          url: 'updateAdgroupValue',
									          type: 'POST',
									          data: data,
									          beforeSend: function(jqXHR, settings){
									              $('.loadingTableContainer').show();
											      if ( checkbsvalue() == false) {
											          $('.loadingTableContainer').hide();
											          jqXHR.abort(e);
											      }
									          },
									          success: function(result){
									          	if(result.status == 'success'){
										            elem.html(result.new_name+' <span class="adgroup_pencilIcon'+id+'" style="display:none"><i class="fa fa-pencil"></i></span>')
										            inputElem.focusout()
									            }else{
									            	swal('Oops!',result.message,'error')
									            }

									              $('.loadingTableContainer').hide();
									          },
									          error: function(xhr, status, response){
									              inputElem.remove()
									              elem.show()
									              swal('Oops!','An error occured during saving of data','error')
									              $('.loadingTableContainer').hide();
									          }
									      })
									      
									  }

									});

			                })

                    		/*END EDIT ADGOURP NAME*/


                    		/*EDIT DEFAULT BID*/
	                    		$('.adgroup_defaultbid').mousemove(function(){
	                            	var id = $(this).attr('data-adgroup-id')
                    				$('.adgroup_defaultbid_pencilIcon'+id).fadeIn()
                    			})

                    			$('.adgroup_defaultbid').mouseout(function(){
                    				var id = $(this).attr('data-adgroup-id')
                    				$('.adgroup_defaultbid_pencilIcon'+id).fadeOut()
                    			})

                    			$('.adgroup_defaultbid').click(function(){
				                	var elem = $(this),
				                		html = '<input type="text" class="adgroup_defaultbid_tb for_editing_col" value="'+elem.text()+'">',
				                		id = elem.parent().parent().attr('data-id')

				                	elem.css('display','none')
									elem.parent().append(html)
									inputElem = $('.adgroup_defaultbid_tb')

									inputElem.select().focus()

									inputElem.focusout(function() {
									  $(this).remove()
									  elem.show();
									})

									$(inputElem).keypress(function (e) {
									  
									  if (e.which == 13) {

									  	if(!validateNumberPositiveOnly($(inputElem).val())){
											swal('','Please input number only and not less than 0.2 and up 2 decimal places','error')
											$(inputElem).val('');
											return false
										}else{
											if($(inputElem).val() < 0.2){
												swal('','Please input number only and not less than 0.2 and up 2 decimal places','error')
												$(inputElem).val('');
												return false
											}
										}

									      var data = {
									        _token: _token,
									        id: id,
									        new_name: inputElem.val(),
									        forQuery: 'adgroup_name'
									      }

									      $.ajax({
									          url: 'updateAdgroupValue',
									          type: 'POST',
									          data: data,
									          beforeSend: function(jqXHR, settings){
									              $('.loadingTableContainer').show();
											      if ( checkbsvalue() == false) {
											          $('.loadingTableContainer').hide();
											          jqXHR.abort(e);
											      }
									          },
									          success: function(result){
									              elem.html(result.new_name+' <span class="adgroup_defaultbid_pencilIcon'+id+'" style="display:none"><i class="fa fa-pencil"></i></span>')
									              inputElem.focusout()
									              $('.loadingTableContainer').hide();
									          },
									          error: function(xhr, status, response){
									              inputElem.remove()
									              elem.show()
									              swal('Oops!','An error occured during saving of data','error')
									              $('.loadingTableContainer').hide();
									          }
									      })
									      
									  }

									});

			                })
                    		/*END EDIT DEFAULT BID*/

                    		/*EDIT ADGROUP STATUS*/
			                $('.adgroup_status').mousemove(function(){
                            	var id = $(this).attr('data-adgroup-id')
                				$('.adgroup_status_pencilIcon'+id).fadeIn()
                			})

                			$('.adgroup_status').mouseout(function(){
                				var id = $(this).attr('data-adgroup-id')
                				$('.adgroup_status_pencilIcon'+id).fadeOut()
                			})

			                $('.adgroup_status').click(function(){
			                	var elem = $(this),
			                		selected = 'selected',
			                		html = '<select class="adgroup_status_input" style="width:100%">'+
			                				'<option '+( (elem.text() == 'paused') ? selected : '' )+'>pause</option>'+
			                				'<option '+( (elem.text() == 'archived') ? selected : '' )+'>archived</option>'+
			                				'<option '+( (elem.text() == 'enabled') ? selected : '' )+'>enabled</option>'+
			                				'</select>',
			                		id = elem.parent().parent().attr('data-id')

			                	elem.css('display','none')
								elem.parent().append(html)
								inputElem = $('.adgroup_status_input')

								inputElem.focus()

								inputElem.focusout(function() {
								  $(this).remove()
								  elem.show();
								})

								$(inputElem).change(function (e) {

										var data = {
											_token: _token,
											id: id,
											new_name: inputElem.val(),
											forQuery: 'state'
										}

										$.ajax({
										  	url: 'updateAdgroupValue',
										  	type: 'POST',
										  	data: data,
										  	beforeSend: function(xhr, opts){
										      	$('.loadingTableContainer').show();

											      if ( checkbsvalue() == false) {
											          $('.loadingTableContainer').hide();
											          xhr.abort(e);
											      }
										  	},
										  	success: function(result){
										      	elem.html(result.new_name+' <span class="adgroup_status_pencilIcon'+id+'" style="display:none"><i class="fa fa-pencil"></i></span>')
										      	inputElem.focusout()
										      	$('.loadingTableContainer').hide();
										  	},
										  	error: function(xhr, status, response){
										      inputElem.remove()
										      elem.show()
										      swal('Oops!','An error occured during saving of data','error')
										      $('.loadingTableContainer').hide();
										  	}
										})

								});

			                })
                			/*END EDIT ADGROUP STATUS*/


	                    	}
	                    })

                    }else{
                    	var id = elem.parent().parent().attr('id');
                    	if(typeof id == 'undefined') id = 0;
                    	$('.rowAdgroup'+id).hide();
                    	$('.parentRow'+id).hide();
                    	elem.parent().parent().parent().find('.addGroupContainer_'+id).hide()
                    }
                })


		    	/*EDIT CAMPAIGN NAME*/
		    	$(row).children(':nth-child(3)').find('.camp_name').mousemove(function(){
		    		var elem = $(this),
		    			id = elem.parent().parent().attr('id')

		    		$('.camp_name_pencilIcon'+id).fadeIn()
		    	})

		    	$(row).children(':nth-child(3)').find('.camp_name').mouseout(function(){
		    		var elem = $(this),
		    			id = elem.parent().parent().attr('id')

		    		$('.camp_name_pencilIcon'+id).fadeOut()
		    	})

                $(row).children(':nth-child(3)').find('.camp_name').click(function(){
                	var elem = $(this),
                		html = '<input type="text" class="camp_name_tb for_editing_col" value="'+elem.text()+'">',
                		id = elem.parent().parent().attr('id')

                	elem.css('display','none')
					elem.parent().append(html)
					inputElem = $('.camp_name_tb')

					inputElem.select().focus()

					inputElem.focusout(function() {
					  $(this).remove()
					  elem.show();
					})

					$(inputElem).keypress(function (e) {

					      if ($(inputElem).val().trim() == "") {
					          swal(" ", "Please input campaign name", "error");
					          return false;
					      }

					      var data = {
					        _token: _token,
					        id: id,
					        new_name: inputElem.val(),
					        forQuery: 'camp_name'
					      }

					      $.ajax({
					          url: 'updateCampaignValue',
					          type: 'POST',
					          data: data,
					          beforeSend: function(jqXHR, settings){
					              $('.loadingTableContainer').show();
							      if ( checkbsvalue() == false) {
							          $('.loadingTableContainer').hide();
							          jqXHR.abort(e);
							      }
					          },
					          success: function(result){
					          	if(result.status == 'success'){
						            elem.html(result.new_name+' <span class="camp_name_pencilIcon'+id+'" style="display:none"><i class="fa fa-pencil"></i></span>')
						            inputElem.focusout()
					            }else{
					            	swal('Oops!',result.message,'error')
					            }
								$('.loadingTableContainer').hide();
					          },
					          error: function(xhr, status, response){
					              inputElem.remove()
					              elem.show()
					              swal('Oops!','An error occured during saving of data','error')
					              $('.loadingTableContainer').hide();
					          }
					      })
					      
					  

					});

                })
                /*END EDIT CAMPAIGN NAME*/

                /*EDIT DAILY BID*/
                $(row).children(':nth-child(4)').find('.camp_dailybid').mousemove(function(){
		    		var elem = $(this),
		    			id = elem.parent().parent().attr('id')

		    		$('.camp_dailybid_pencilIcon'+id).fadeIn()
		    	})

		    	$(row).children(':nth-child(4)').find('.camp_dailybid').mouseout(function(){
		    		var elem = $(this),
		    			id = elem.parent().parent().attr('id')

		    		$('.camp_dailybid_pencilIcon'+id).fadeOut()
		    	})

                $(row).children(':nth-child(4)').find('.camp_dailybid').click(function(){
                	var elem = $(this),
                		html = '<input type="text" class="dailybid_tb for_editing_col" value="'+elem.text()+'">',
                		id = elem.parent().parent().attr('id')

                	elem.css('display','none')
					elem.parent().append(html)
					inputElem = $('.dailybid_tb')

					inputElem.select().focus()

					inputElem.focusout(function() {
					  $(this).remove()
					  elem.show()
					})

					$(inputElem).keypress(function (e) {
					  
					  if (e.which == 13) {

					      	if(!validateNumberPositiveOnly($(inputElem).val())){
								swal('','Please input number only and not less than 1 and up 2 decimal places','error')
								$(this).val('');
								return false;
							}else{
								if($(this).val() < 1){
									swal('','Please input number only and not less than 1 and up 2 decimal places','error')
									$(this).val('');
									return false;
								}
							}

					      var data = {
					        _token: _token,
					        id: id,
					        new_name: inputElem.val(),
					        forQuery: 'dailybid'
					      }

					      $.ajax({
					          url: 'updateCampaignValue',
					          type: 'POST',
					          data: data,
					          beforeSend: function(jqXHR, settings){
					              $('.loadingTableContainer').show();
							      if ( checkbsvalue() == false) {
							          $('.loadingTableContainer').hide();
							          jqXHR.abort(e);
							      }
					          },
					          success: function(result){
					              elem.html(result.new_name+' <span class="camp_dailybid_pencilIcon'+id+'" style="display:none"><i class="fa fa-pencil"></i></span>')
					              inputElem.focusout()
					              $('.loadingTableContainer').hide();
					          },
					          error: function(xhr, status, response){
					              inputElem.remove()
					              elem.show()
					              swal('Oops!','An error occured during saving of data','error')
					              $('.loadingTableContainer').hide();
					          }
					      })
					      
					  }

					});

                })
                /*END EDIT DAILY BID*/

                /*EDIT STATUS*/
                $(row).children(':nth-child(5)').find('.camp_status').mousemove(function(){
		    		var elem = $(this),
		    			id = elem.parent().parent().attr('id')

		    		$('.camp_status_pencilIcon'+id).fadeIn()
		    	})

		    	$(row).children(':nth-child(5)').find('.camp_status').mouseout(function(){
		    		var elem = $(this),
		    			id = elem.parent().parent().attr('id')

		    		$('.camp_status_pencilIcon'+id).fadeOut()
		    	})

                $(row).children(':nth-child(5)').find('.camp_status').click(function(){
                	var elem = $(this),
                		selected = 'selected',
                		html = '<select class="status_input" style="width:100%">'+
                				'<option '+( (elem.text() == 'paused') ? selected : '' )+'>pause</option>'+
                				'<option '+( (elem.text() == 'archived') ? selected : '' )+'>archived</option>'+
                				'<option '+( (elem.text() == 'enabled') ? selected : '' )+'>enabled</option>'+
                				'</select>',
                		id = elem.parent().parent().attr('id')
               

                	elem.css('display','none')
					elem.parent().append(html)
					inputElem = $('.status_input')

					inputElem.focus()

					inputElem.focusout(function() {
					  $(this).remove()
					  elem.show();
					})

					$(inputElem).change(function (e) {

							var data = {
								_token: _token,
								id: id,
								new_name: inputElem.val(),
								forQuery: 'status'
							}

							$.ajax({
							  	url: 'updateCampaignValue',
							  	type: 'POST',
							  	data: data,
							  	beforeSend: function(xhr, opts){
							      	$('.loadingTableContainer').show();
								  	  if ( checkbsvalue() == false) {
								  	  	$('.loadingTableContainer').hide();
								          xhr.abort(e);
								      }
							  	},
							  	success: function(result){
							      	elem.html(result.new_name+' <span class="camp_status_pencilIcon'+id+'" style="display:none"><i class="fa fa-pencil"></i></span>')
							      	inputElem.focusout()
							      	$('.loadingTableContainer').hide();
							  	},
							  	error: function(xhr, status, response){
							      inputElem.remove()
							      elem.show()
							      swal('Oops!','An error occured during saving of data','error')
							      $('.loadingTableContainer').hide();
							  	}
							})

					});

                })
                /*END EDIT STATUS*/


		    	var tableinfo = oTable2.page.info();
	                total_record = tableinfo.recordsTotal;

		    }
		});

		$('.dataTable').wrap('<div class="dataTables_scroll" />');

	}

	function toggleKeyword(elem,parent_id){
		$(elem).toggleClass('row-details-open');
	    var id = $(elem).parent().parent().attr('data-id');
	    var index = $(elem).parent().parent().index();

	    if($(elem).hasClass('row-details-open')){
            if(typeof id == 'undefined') id = 0;

            if($(elem).attr('data-withdata') == 1){
	            $('.skuRow'+id).show();
	            return false;
	        }

            var output = '';
            
            $.ajax({
                url: 'getSkuByadGroup',
                type: "POST",
                data: { _token: _token, id:id},
                beforeSend: function(){
                	$('.loadingTableContainer').show();
                },
                success: function(result){
                    // var response = jQuery.parseJSON(result);
                    $(elem).attr('data-withdata','1');

                    output += '<tr class="skuRow'+id+' parentRow'+parent_id+'" style="background:#e8e8e8 !important">'+
                                      '<td></td>'+
                                      '<td style="border-left:2px solid #FF5722"></td>'+
                                      '<td colspan="2" width="" >'+
                                          '<span class="text-orange m-l-20"><i class="fa fa-barcode" style="margin-right:10px;"></i><b> SKU</b></span>'+
                                      '</td>'+
                                      '<td colspan="" width="" style="border-left:1px dotted #FF5722">'+
                                          '<span class="text-orange m-l-20"><b>State</b></span>'+
                                      '</td>'+
                                  '</tr>';

                    for(var i in result.data){
                	  	output 	+= '<tr class="skuRow'+id+' parentRow'+parent_id+'" data-id="'+result.data[i].adgroup_id+'">'+
	                    				'<td></td>'+
	                    				'<td style="border-left:2px solid #FF5722"></td>'+
	                                    '<td colspan="2"><span class="color-blue m-l-20">'+result.data[i].sku+'</span></td>'+
	                                    '<td style="border-left:1px dotted #FF5722"><span class="m-l-20">'+result.data[i].state+'</span></td>'+
	                                '</tr>';
            		}

                    $('#adscampaignmanager_table > tbody > tr').eq(index).after(output);
                    $('.loadingTableContainer').hide();
                }
            })

        }else{
            
            $('.skuRow'+id).hide();

        }

	}

	function initialize_data_feed(){
	    var data = { _token: _token, total_number: total_record };
	    return data;
	}
	
	function add_newAdGroupRow(){

		var camp_type = 'manual',
			display = ''
		$('.campaign_type').each(function(){
			if($(this).is(':checked')){
				if($(this).attr('data-val') == 'auto'){
					camp_type = 'auto'
					display = 'dontdisplay'
				}
			}
		})
		// console.log(camp_type)
		var html = '<div class="row adgroup_row">'+
				'<hr>'+
                '<div class="col-md-12 m-b-10 ">'+
                    '<button class="btn btn-sm btn-danger no-radius pull-right removeAdGroupRow"><i class="fa fa-trash"></i> Remove</button>'+
                '</div>'+
                '<div class="col-md-12">'+
                    '<div class="form-group row">'+
                        '<div class="col-lg-3 ">'+
                            '<label for="" class="col-form-label">Ad Group Name</label>'+
                        '</div>'+
                        '<div class="col-lg-9">'+
                            '<div class="input-group">'+
                                '<input type="text" id="" class="form-control ad_group_tb">'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div class="form-group row">'+
                        '<div class="col-lg-3 ">'+
                            '<label for="" class="col-form-label">Default Bid</label>'+
                        '</div>'+
                        '<div class="col-lg-4">'+
                            '<div class="input-group">'+
                                '<input type="text" id="" class="form-control default_bid_tb">'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div class="form-group row default_matchtype_container '+display+'" >'+
                        '<div class="col-lg-3 ">'+
                            '<label for="" class="col-form-label">Default Match Type</label>'+
                        '</div>'+
                        '<div class="col-lg-4">'+
                            '<select class="form-control default_matchtype">'+
                                '<option value="Broad">Broad</option>'+
                                '<option value="Phrase">Phrase</option>'+
                                '<option value="Exact">Exact</option>'+
                            '</select>'+
                        '</div>'+
                    '</div>'+
                '</div>'+
            '</div>';

        $('.addgroup_container').append(html);

        $('.default_bid_tb').blur(function(){
			validate_default_bid($(this));
		})

        $('.removeAdGroupRow').click(function(){
        	remove_addGroupRow($(this));
        })
	}

	function remove_addGroupRow(element){
		$(element).parent().parent().remove();
	}

	function add_rowKeyword(index){
		var html = 	'<tr>'+
                        '<td class="text-center">'+
                            '<input type="text" class="form-control keyword_default_bid">'+
                        '</td>'+
                        '<td class="text-center">'+
                                '<select class="form-control keyword_match_type">'+
                                    '<option value="BROAD">BROAD</option>'+
                                    '<option value="PHRASE">PHRASE</option>'+
                                    '<option value="EXACT">EXACT</option>'+
                                    '<option value="NEGATIVE">NEGATIVE PHRASE</option>'+
                                    '<option value="NEGATIVE EXACT">NEGATIVE EXACT</option>'+
                                '</select>'+
                            '</td>'+
                        '<td class="text-center">'+
                            '<input type="text" class="form-control keyword_tb">'+
                        '</td>'+
                        '<td class="text-center">'+
                            '<span class="btn btn-sm btn-danger no-radius removeKeywordRow"><i class="fa fa-trash"></i></span>'+
                        '</td>'+
                    '</tr>';
        $('.table_keywords_ad_campaign'+index+' tbody').append(html);

        $('.keyword_default_bid').blur(function(){
			validate_default_bid($(this));
		})

        $('.removeKeywordRow').click(function(){
        	$(this).parent().parent().remove();
        })
	}

	function populate_temp_saved_adgroup(){
		$('.keywordRowContainer').remove();
		var selected = "selected"
		for (index in ad_group_obj.ad_group_name) {
		    var html = '<div class="keywordRowContainer">'+
		    			'<div class="col-md-8 m-b-10">'+
                        '<div class="row"><span>'+
                           'Ad Group Name: <span class="adgroupName'+index+'">'+ad_group_obj.ad_group_name[index]+'</span></div>'+
                        '</span>'+
	                    '</div>'+

	                    '<div class=col-md-4">'+
	                    	'<span class="btn btn-sm btn-success no-radius pull-right m-l-10 add_BulkKeyword" data-id="'+index+'" data-default-bid="'+ad_group_obj.default_bid[index]+'"><i class="fa fa-cart-plus"></i> Add Bulk</span>'+

                    		'<span class="btn btn-sm btn-success no-radius pull-right addRowKeyword'+index+'" data-id="'+index+'"><i class="fa fa-plus"></i> Add New</span>'+
                    	'</div>'+

	                    '<table class="table table-striped table_keyword table_keywords_ad_campaign'+index+'">'+
	                        '<thead>'+
	                            '<tr>'+
	                                '<th rowspan="2" class="text-center" width="100">Default Bid</th>'+
	                                '<th rowspan="2" class="text-center" width="200">Match Type</th>'+
	                                '<th colspan="2" class="text-center">Keyword</th>'+
	                            '</tr>'+
	                        '</thead>'+
	                        '<tbody>'+
	                            '<tr>'+
	                                '<td class="text-center">'+
	                                    '<input type="text" class="form-control keyword_default_bid" value="'+ad_group_obj.default_bid[index]+'">'+
	                                '</td>'+
	                                '<td class="text-center">'+
	                                    '<select class="form-control keyword_match_type">'+
	                                        '<option '+( (ad_group_obj.default_matchtype[index] == 'Broad') ? selected : '' )+'>BROAD</option>'+
	                                        '<option '+( (ad_group_obj.default_matchtype[index] == 'Phrase') ? selected : '' )+'>PHRASE</option>'+
	                                        '<option '+( (ad_group_obj.default_matchtype[index] == 'Exact') ? selected : '' )+'>EXACT</option>'+
	                                        '<option>NEGATIVE PHRASE</option>'+
	                                        '<option>NEGATIVE EXACT</option>'+
	                                    '</select>'+
	                                '</td>'+
	                                '<td colspan="2" class="text-center">'+
	                                    '<input type="text" class="form-control keyword_tb">'+
	                                '</td>'+
	                            '</tr>'+
	                        '</tbody>'+
	                    '</table>'+
	                    '<hr style="border-bottom:2px solid #d6d6d6">'+
	                    '</div>';
            $('.keywordRow').append(html);

            $('.addRowKeyword'+index).click(function(){
		    	add_rowKeyword($(this).attr('data-id'));
		    })

		    $('.keyword_default_bid').blur(function(){
				validate_default_bid($(this));
			})

            $('.add_BulkKeyword').click(function(){
            	var id_row = $(this).attr('data-id')
            	var default_bid = $(this).attr('data-default-bid')
            	$('.keywordmodal_title').html($('.adgroupName'+id_row).html())
            	$('.addKeyWordBtn').attr('data-id',id_row)
            	$('.addKeyWordBtn').attr('data-default-bid',default_bid)
            	$('.text_area_bulk_keyword').val('');
		    	$('#bulkKeywordModal').modal('show');
            })
		}
	}


	function add_keyword_bulk(elem){
		var index = $(elem).attr('data-id')
		var default_bid = $(elem).attr('data-default-bid')
		var keywords_string = $('.text_area_bulk_keyword').val();
		var match_type = $('.bulkMatchType').val();
		keywords_arr = keywords_string.split("\n");

		jQuery.each(keywords_arr, function(key,value) {
			if(value){
				var html = 	'<tr>'+
                        '<td class="text-center">'+
                            '<input type="text" class="form-control keyword_default_bid" value="'+default_bid+'">'+
                        '</td>'+
                        '<td class="text-center">'+
                                '<select class="form-control keyword_match_type">'+
                                    '<option value="BROAD" '+((match_type == 'BROAD') ? 'selected' : '' )+'>BROAD</option>'+
                                    '<option value="PHRASE" '+((match_type == 'PHRASE') ? 'selected' : '')+'>PHRASE</option>'+
                                    '<option value="EXACT" '+((match_type == 'EXACT') ? 'selected' : '')+'>EXACT</option>'+
                                    '<option value="NEGATIVE" '+((match_type == 'NEGATIVE PHRASE') ? 'selected' : '')+'>NEGATIVE PHRASE</option>'+
                                    '<option value="NEGATIVE EXACT" '+((match_type == 'NEGATIVE EXACT') ? 'selected' : '')+'>NEGATIVE EXACT</option>'+
                                '</select>'+
                            '</td>'+
                        '<td class="text-center">'+
                            '<input type="text" class="form-control keyword_tb" value="'+value+'">'+
                        '</td>'+
                        '<td class="text-center">'+
                            '<span class="btn btn-sm btn-danger no-radius removeKeywordRow"><i class="fa fa-trash"></i></span>'+
                        '</td>'+
                    '</tr>';
        		$('.table_keywords_ad_campaign'+index+' tbody').append(html);

        		$('.removeKeywordRow').click(function(){
		        	$(this).parent().parent().remove();
		        })

		        $('.keyword_default_bid').blur(function(){
					validate_default_bid($(this));
				})

			}
		});
	
	}

	function checkbsvalue(){
		if(bs == 'XS'){
			swal({
	          title: ' ',
	          text: "This feature is not available in your current subscription. Please upgrade your subscription in the Settings.",
	          type: 'warning',
	          showCancelButton: false,
	          confirmButtonColor: '#DD6B55',
	          // cancelButtonColor: '#d33',
	          confirmButtonText: 'Okay'
	        }).then(function () {
	          //return false;
	        })
	        return false;
		}else{
			return true;
		}
	}