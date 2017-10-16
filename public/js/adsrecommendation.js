var counterRow=1;
  var page_len = 25;
  $(document).ready(function(){
      if(bs == 'XS'){
        swal({
          title: ' ',
          text: "This feature is not available in your current subscription. Please upgrade your subscription in the Settings.",
          type: 'warning',
          showCancelButton: false,
          confirmButtonColor: '#DD6B55',
          allowOutsideClick: false,
          // cancelButtonColor: '#d33',
          confirmButtonText: 'Okay'
        }).then(function () {
          window.location.replace("adsperformance");
        });
      }

    hide_unhide_custom_rec_div();
      
      getDropDownData();
      
      $('#recommendationbtn').click(function(){
        $('#adperformance_recommendation').toggle('fast');
      }); 

      $('#addRuleBtn').click(function(){
          if (validateCellData()) {
              addRuleRow('');
          }else{
            swal('Input required','All fields are required.','error')
          }
      })

      $('#saveRuleBtn').click(function(){
          saveRule(this)
      })

      $(".filter_select, .recMultiSelect").chosen({allow_single_deselect: true}); 

      $('.chosen-container').css('width','100%')

      $('#reset_filter').click(function(){
          reset_filter();
          $('.filter_parameter').remove()
      })


    $('#recommendation').change(function(){
      hide_unhide_custom_rec_div();
    });

    $('#recommendation_campaign').change(function(){
      setAdgroupDropdown();
    });
    $("#recommendation_country, #recommendation_camp_type").change(function(){
      setCampaignDropdown();
    });

    $.each($('.make-switch-radio'), function () {
        $(this).bootstrapSwitch({
            onText: $(this).data('onText'),
            offText: $(this).data('offText'),
            onColor: $(this).data('onColor'),
            offColor: $(this).data('offColor'),
            size: $(this).data('size'),
            labelText: $(this).data('labelText')
        });
    });

  });

  function hide_unhide_custom_rec_div(id=''){
    var rec = $('#recommendation'+id).val();
      if(rec == 'Custom'){
        $('#custom_recommendation_div'+id).show();
      }else{
        $('#custom_recommendation_div'+id).hide();
      }
  }
    
  function validateCellData(){
    var cellData;
    var error = false;
    $(".ruleRow").find('td').each(function(){
        cellData = $(this).find('select').val();
        if(typeof cellData == 'undefined'){
          cellData = $(this).find('input').val(); 
        }
        if (cellData == "") {
            error = true;
        }
    });

    if (error) {
       return false
    }else{
       return true;
    }
  }

  function validateHeaderData(){
    var error = false;
    if($('#recommendation_name').val() == "") error = true;
    if(!$('#recommendation_campaign').val()) error = true;
    if(!$('#recommendation_country').val()) error = true;
    if($('#recommendation').val() == "") error = true;
    if($('#recommendation_period').val() == "") error = true;
    if(!$('#recommendation_ad_group').val()) error = true;
    if(!$('#recommendation_camp_type').val()) error = true;

    if (error) {
       return false
    }else{
       return true;
    }
  }



  function saveRule(element){
    var cust_rec = $('#custom_recommendation').val();
    var rec = $('#recommendation').val();
    if(rec == 'Custom' && (cust_rec == '' || cust_rec == null)){
      swal('Input required','Custom Recommendation is required.','error')
      $(element).html('Save Changes')
      $(element).attr("disabled", false);
    }else if (validateCellData()) {
          $(element).html('Saving... <i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span>');
          $(element).attr("disabled", true);
          if (validateHeaderData()) {
            var operation_arr = [];
            var matrix_arr = [];
            var metric_arr = [];
            var value_arr = [];
            var data;
            var counter=0;
            
            $('.headRuleContainer').find('select[name="recommendation_operation"]').each(function(){
                operation_arr[counter] = $(this).val();
                counter++
            });

            counter=0;
            $('.headRuleContainer').find('select[name="recommendation_matrix"]').each(function(){
                matrix_arr[counter] = $(this).val();
                counter++
            });

            counter=0;
            $('.headRuleContainer').find('select[name="recommendation_metric"]').each(function(){
                metric_arr[counter] = $(this).val();
                counter++
            });

            counter=0;
            $('.headRuleContainer').find('input[name="recommendation_value"]').each(function(){
                value_arr[counter] = $(this).val();
                counter++
            });

            if(rec == 'Custom'){
              rec = cust_rec;
            }

            data = {
                recommendationName: $('#recommendation_name').val(),
                campaignName: $('#recommendation_campaign').val().toString(),
                country: $('#recommendation_country').val().toString(),
                adGroupName: $('#recommendation_ad_group').val().toString(),
                CampType: $('#recommendation_camp_type').val().toString(),
                recommendation: rec,
                timePeriod: $('#recommendation_period').val(),
                status: ($("#recStatus").is(':checked') == true) ? 1 : 0,
                operation: operation_arr,
                matrix: matrix_arr,
                metric: metric_arr,
                value: value_arr
            }
            // console.log(data);
            $.ajax({url: "saveAdRule", type: 'POST', data: data, success: function(result){
                var response = jQuery.parseJSON(result);
                if (response[0].success) {
                  resetFieldsRecommendation()
                  // populateAddedRule(response);

                  swal({
                      text: "Recommendation rule successfully added",
                      type: 'success',
                      allowOutsideClick: false,
                    }).then(function () {
                        location.reload()
                    })

                  // swal(" ", "Recommendation rule successfully added", "success")
                  $(element).html('Save rule')
                  $(element).attr("disabled", false);
                  // initialize_table();
                  
                }else{
                  resetFieldsRecommendation()
                  swal(' ','Something went wrong when saving your request','error')
                  $(element).html('Save rule')
                  $(element).attr("disabled", false);
                }
              }
            });

      }else{
        swal('Input required','All fields are required.','error')
        $(element).html('Save rule')
        $(element).attr("disabled", false);
      }
    }else{
      swal('Input required','All fields are required.','error')
      $(element).html('Save rule')
      $(element).attr("disabled", false);
    }
  }

  function resetFieldsRecommendation(){
    $('#recommendation_name').val('')
  }

  function closeRow(element){
      $(element).parent().parent().remove();
  }
 
  function addRuleRow(idRow){
      
      var tableRule = $('#tableRule'+idRow);
      var operation = '<select id="recommendation_operation" name="recommendation_operation" class="form-control" required="">'+
                                      '<option value="and">AND</option>'+
                                      '<option value="or">OR</option>'+
                                    '</select>';

      var matrix = '<select id="recommendation_matrix" name="recommendation_matrix" class="form-control" required="">'+
                                      '<option value="">Select Your KPI</option>'+
                                      '<option value="acos">ACoS</option>'+
                                      '<option value="impressions">Impressions</option>'+
                                      '<option value="clicks">Clicks</option>'+
                                      '<option value="ctr">CTR</option>'+
                                      '<option value="average_cpc">Average CPC</option>'+
                                      '<option value="revenue">Revenue</option>'+
                                      '<option value="bid">Bid</option>'+
                                      '<option value="orders">Orders</option>'+
                                    '</select>';

      var metric =  '<select id="recommendation_metric" name="recommendation_metric" class="form-control" required="">'+
                                      '<option value="">Select Your Equation</option>'+
                                      '<option value="≥">≥ (Greater Than or Equal To)</option>'+
                                      '<option value="≤">≤ (Less Than or Equal To))</option>'+
                                      '<option value="=">= (Equals)</option>'+
                                    '</select>';                            

      var value = '<input id="recommendation_value" type="text" name="recommendation_value" class="form-control" value="" onblur="ValidateValue(this,12)">';

      tableRule.append('<tr class="ruleRow'+idRow+'" data="'+counterRow+'">'+
                          '<td>'+operation+'</td>'+
                          '<td>'+matrix+'</td>'+
                          '<td>'+metric+'</td>'+
                          '<td>'+value+'</td>'+
                          '<td width="2" style="vertical-align: middle;"><button onclick="closeRow(this)" class="btn btn-danger btn-sm" style="border-radius:0px;"><i class="fa fa-trash"></i></button></td>'+
                       '</tr>');
      counterRow++;

  }
  

    function deleteSavedRule(element,idRow){
      var data = {id: idRow}
      swal({
          title: ' ',
          text: "Are you sure you want to delete this rule?",
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#DD6B55',
          // cancelButtonColor: '#d33',
          confirmButtonText: '&nbsp; Okay &nbsp;'
        }).then(function () {
          $.ajax({url: "deleteRule", type: 'POST', data: data, success: function(result){
                  var response = jQuery.parseJSON(result);
                  if (response[0].success) {
                    $('.ruleListTitleMain'+idRow).remove();
                    $('.listRuleContainer'+idRow).remove();
                    swal(" ", "Rule successfully deleted", "success")
                  }else{
                    swal(' ','Something went wrong when deleting the rule','error')
                  }
                }
              });
        })
    }

    function saveChanges(element){
        var parentContainer = $(element).closest('.rowListContainer');
        var idRow = $(parentContainer).attr('data');
        if (validateHeaderDataList(idRow)) {
          if (validateCellDataList(idRow)) {
            $(element).html('Saving Changes... <i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span>');
            $(element).attr("disabled", true);
              var operation_arr = [];
              var matrix_arr = [];
              var metric_arr = [];
              var value_arr = [];
              var data;
              var counter=0;
              
              $('.listRuleContainer'+idRow).find('select[name="recommendation_operation"]').each(function(){
                  operation_arr[counter] = $(this).val();
                  counter++
              });

              counter=0;
              $('.listRuleContainer'+idRow).find('select[name="recommendation_matrix"]').each(function(){
                  matrix_arr[counter] = $(this).val();
                  counter++
              });

              counter=0;
              $('.listRuleContainer'+idRow).find('select[name="recommendation_metric"]').each(function(){
                  metric_arr[counter] = $(this).val();
                  counter++
              });

              counter=0;
              $('.listRuleContainer'+idRow).find('input[name="recommendation_value"]').each(function(){
                  value_arr[counter] = $(this).val();
                  counter++
              });
              var rec = $('#recommendation'+idRow).val();
              if($('#recommendation'+idRow).val() == 'Custom') rec = $('#custom_recommendation'+idRow).val();

              data = {
                  id: idRow,
                  recommendationName: $('#recommendation_name'+idRow).val(),
                  campaignName: $('#recommendation_campaign'+idRow).val().toString(),
                  country: $('#recommendation_country'+idRow).val().toString(),
                  adGroupName: $('#recommendation_ad_group'+idRow).val().toString(),
                  CampType: $('#recommendation_camp_type'+idRow).val().toString(),
                  recommendation: rec,
                  timePeriod: $('#recommendation_period'+idRow).val(),
                  status: ($("#recStatus"+idRow).is(':checked') == true) ? 1 : 0,
                  operation: operation_arr,
                  matrix: matrix_arr,
                  metric: metric_arr,
                  value: value_arr
              }

              $.ajax({url: "saveChanges", type: 'POST', data: data, success: function(result){
                  var response = jQuery.parseJSON(result);
                  if (response[0].success) {
                    swal(" ", "Recommendation rule successfully updated", "success")
                    $(element).html('Save Changes')
                    $(element).attr("disabled", false);
                    // initialize_table();
                  }else{
                    swal(' ','Something went wrong when saving your request','error')
                    $(element).html('Save Changes')
                    $(element).attr("disabled", false);
                  }
                }
              });
          }else{
            swal('Input required','All fields are required.','error')
            $(element).html('Save Changes')
            $(element).attr("disabled", false);
          }
        }else{
          swal('Input required','All fields are required.','error')
          $(element).html('Save Changes')
          $(element).attr("disabled", false);
        }
    }

    function validateCellDataList(idRow){
      var cellData;
      var error = false;
      $(".ruleRow"+idRow).find('td').each(function(){
          cellData = $(this).find('select').val();
          if(typeof cellData == 'undefined'){
            cellData = $(this).find('input').val(); 
          }
          if (cellData == "") {
              error = true;
          }
      });

      if (error) {
         return false
      }else{
         return true;
      }
  }

  function validateHeaderDataList(idRow){
    var error = false;
    if($('#recommendation_name'+idRow).val() == "") error = true;
    if(!$('#recommendation_campaign'+idRow).val()) error = true;
    if(!$('#recommendation_country'+idRow).val()) error = true;
    if($('#recommendation'+idRow).val() == "") error = true;
    if($('#recommendation_period'+idRow).val() == "") error = true;
    if(!$('#recommendation_ad_group'+idRow).val()) error = true;
    if(!$('#recommendation_camp_type'+idRow).val()) error = true;
    if($('#recommendation'+idRow).val() == 'Custom'){
      if($('#custom_recommendation'+idRow).val() == "" ) error = true;
    }
    if (error) {
       return false
    }else{
       return true;
    }
  }

  function ValidateValue(element){
    if (validateNumberPositiveNegative($(element).val())) {
        $(element).css('border', '1px solid #CECECE');
        return true;
    } else { 
        swal(" ", "Please input number only and up to 2 decimal places", "error")
        $(element).css('border', '1px solid red');
        $(element).val('');
        return false;
    }
  }

  function addConditionList(element){
      var parentContainer = $(element).closest('.rowListContainer');
      var idRow = $(parentContainer).attr('data');
      if (validateCellDataList(idRow)) {
          addRuleRow(idRow);
      }else{
        swal('Input required','All fields are required.','error')
      }
  }

    function populateRecommendationRules(){
      $.ajax({url: "showAdRule", type: 'GET' , success: function(result){
              var response = jQuery.parseJSON(result);
              var countrySelect = $("<select />").append($("#recommendation_country").clone()).html();
              var campaignameSelect = $("<select />").append($("#recommendation_campaign").clone()).html();
              if (response.length == 0) $('.list_recommendation_rules')
                    .html('<div class="col-md-12 text-center">'+
                              '<strong>No saved rules</strong>'+
                          '</div>');

              for(var index in response){
                  var countries = response[index].country.split(',');
                  var campaign_names = response[index].campaign_name.split(',');
                  var adgroup_names = response[index].adgrounp_name.split(',');
                  var camp_types = response[index].campaign_type.split(',');
                  var id = response[index].id
                  var output = '<div class="col-md-12 ruleListTitleMain'+id+'">'+
                                '<div class="col-md-12 ruleListTitle" data="'+id+'">'+
                                  response[index].recommendation_name+' <i class="fa fa-angle-right"></i>'+
                                  '<i class="fa fa-window-maximize pull-right m-t-5 maximizeBtn'+id+'" aria-hidden="true"></i>'+
                                '</div>'+
                              '</div>';

                  output += '<div class="col-md-12 rowListContainer m-b-15 listRuleContainer'+id+'" data="'+id+'">'+
                  '<div class="col-md-12 ruleTableRow">'+
                            '<div class="row">'+
                              '<div class="col-md-12">'+
                                '<div class="col-lg-6 col-md-12">'+
                                  '<div class="row">'+
                                    '<div class="col-lg-4 col-md-6">'+
                                      'Recommendation Name'+
                                    '</div>'+
                                    '<div class="col-lg-8 col-md-6">'+
                                      '<input id="recommendation_name'+id+'" type="text" name="" class="form-control" value="'+response[index].recommendation_name+'">'+
                                    '</div>'+
                                  '</div>'+
                                '</div>'+
                                '<div class="col-lg-6 col-md-12">'+
                                  '<div class="row">'+
                                    '<div class="col-lg-4 col-md-6">'+
                                      'Status'+
                                    '</div>'+
                                    '<div class="col-lg-8 col-md-6">'+
                                      '<input class="make-switch-radio" id="recStatus'+id+'" data-on-text="ON" data-off-text="OFF" data-on-color="success" data-off-color="danger" type="checkbox" data-size="small" '+((response[index].status) ? 'checked' : '')+'>'+

                                      '<strong class="text-orange hand dupliRule pull-right" onclick="dupliRule('+id+')" ><i class="fa fa-copy"></i> Duplicate rule</strong>'+
                                    '</div>'+
                                  '</div>'+
                                '</div>'+
                              '</div>'+
                            '</div>'+
                  '<div class="col-lg-6 col-md-12 m-t-5">'+
                            '<div class="row">'+
                              '<div class="col-lg-4 col-md-6">'+
                                'Country'+
                              '</div>'+
                              '<div class="col-lg-8 col-md-6">';
                      output += '<select class="form-control recMultiSelect2" required="required" id="recommendation_country'+id+'" multiple>';
                                  $('.select-countryRec option').each(function(){
                                    var val = $(this).val(); var text = $(this).text()
                                    var selected = '';
                                    for (var i = 0; i < countries.length; i++) {
                                        if (countries[i] == val.toLowerCase()) selected = 'selected';
                                    }
                                     output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                  });
                      output += '</select>'+
                              '</div>'+
                            '</div>'+
                            '<div class="row m-t-5">'+
                              '<div class="col-lg-4 col-md-6">'+
                                'Campaign Type'+
                              '</div>'+
                              '<div class="col-lg-8 col-md-6">'+
                                  '<select id="recommendation_camp_type'+id+'" name="campaign_type" class="form-control recMultiSelect2" multiple>';
                                    $('.select-camppTypeRec option').each(function(){
                                      var val = $(this).val(); var text = $(this).text()
                                      var selected = '';
                                      for (var i = 0; i < camp_types.length; i++) {
                                          if (camp_types[i].toLowerCase() == val.toLowerCase()) selected = 'selected';
                                      }
                                       output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                    });
                      output +=   '</select>'+
                              '</div>'+
                            '</div>'+
                            '<div class="row m-t-5">'+
                              '<div class="col-lg-4 col-md-6">'+
                                'Campaign Name'+
                              '</div>'+
                              '<div class="col-lg-8 col-md-6">';
                      output += '<select class="form-control recMultiSelect2" required="required" id="recommendation_campaign'+id+'" multiple>';
                                  var counter = 0;
                                  $('.select-campaignRec option').each(function(){
                                    var val = $(this).val(); var text = $(this).text()
                                    var selected = '';
                                    for (var i = 0; i < campaign_names.length; i++) {
                                      if (campaign_names[i] == val) selected = 'selected';
                                    }
                                     output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                     counter ++
                                  });
                      output += '</select>'+
                              '</div>'+
                            '</div>'+
                            '<div class="row m-t-5">'+
                              '<div class="col-lg-4 col-md-6">'+
                                'Ad Group'+
                              '</div>'+
                              '<div class="col-lg-8 col-md-6">'
                      output += '<select class="form-control recMultiSelect2" required="required" id="recommendation_ad_group'+id+'" multiple>';
                                  var counter = 0;
                                  $('.select-adGroupRec option').each(function(){
                                    var val = $(this).val(); var text = $(this).text()
                                    var selected = '';
                                    for (var i = 0; i < adgroup_names.length; i++) {
                                      if (adgroup_names[i] == val) selected = 'selected';
                                    }
                                     output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                     counter ++
                                  });
                      output += '</select>'+          
                              '</div>'+
                            '</div>'+
                          '</div>'+
                          '<div class="col-lg-6 col-md-12 m-t-5">'+
                            '<div class="row">'+
                              '<div class="col-lg-4 col-md-6">'+
                               'Time Period'+
                              '</div>'+
                              '<div class="col-lg-8 col-md-6">';
                        output += '<select id="recommendation_period'+id+'" class="form-control">';
                                  $('.select-time-period option').each(function(){
                                    var val = $(this).val(); var text = $(this).text()
                                    var selected = '';
                                    if (response[index].time_period == val) selected = 'selected';
                                     output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                  });
                        output +='</select>'+
                              '</div>'+
                            '</div>'+
                            '<div class="row m-t-5">'+
                              '<div class="col-lg-4 col-md-6">'+
                                'Recommendation'+
                              '</div>'+
                              '<div class="col-lg-8 col-md-6">';
                        output += '<select id="recommendation'+id+'" class="form-control" onchange="hide_unhide_custom_rec_div('+id.toString()+');">';
                                  var count_sel = 0;
                                  var rec = "";
                                  $('.select-recommendation option').each(function(){
                                    var val = $(this).val(); var text = $(this).text()
                                    var selected = '';
                                    if (response[index].recommendation == val){
                                      selected = 'selected';
                                      count_sel++;
                                    }
                                    if(val == 'Custom' && count_sel == 0){
                                      selected = 'selected';
                                      rec = response[index].recommendation;
                                    }

                                     output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                  });
                        output +='</select>'+
                              '</div>'+
                            '</div>'+
                            '<div class="row m-t-5" id="custom_recommendation_div'+id+'">'+
                              '<div class="col-lg-4 col-md-6">'+
                                'Custom'+
                              '</div>'+
                              '<div class="col-lg-8 col-md-6">';
                        output += '<input type="text" name="custom_recommendation'+id+'" id="custom_recommendation'+id+'" class="form-control" value="'+rec+'">';
                        output +='</div>'+
                            '</div>'+
                          '</div>'+
                          '<div class="col-md-12">'+
                          '<hr>'+
                            '<span style="color: #7AC482">Conditions</span>'+
                          '</div>'+
                          '<div class="col-md-12">'+
                          '<div class="table-responsive">';
                        output += '<table cellspacing=0 cellpadding=0 class="table table-bordered table_res" id="tableRule'+id+'" style="min-width:400px;">'+
                              '<thead>'+
                                '<tr>'+
                                    '<th class="text-center">Operation</th>'+
                                    '<th class="text-center">Matrix</th>'+
                                    '<th class="text-center">Metric</th>'+
                                    '<th class="text-center" colspan="2">Value</th>'+
                                '</tr>'+
                              '</thead>'+
                              '<tbody>';
                          for (var i = 0; i < response[index].condition.length; i++) {
                        output += '<tr class="ruleRow'+id+'">'+
                                    '<td>'+
                                      '<select id="recommendation_operation" name="recommendation_operation" class="form-control" required="">';
                                        $('.select_operation option').each(function(){
                                          var val = $(this).val(); var text = $(this).text()
                                          var selected = '';
                                          if (response[index].condition[i].operation == val) selected = 'selected';
                                           output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                        });
                          output += '</select>'+
                                    '</td>'+
                                    '<td>'+
                                      '<select id="recommendation_matrix" name="recommendation_matrix" class="form-control" required="">';
                                         $('.select_matrix option').each(function(){
                                          var val = $(this).val(); var text = $(this).text()
                                          var selected = '';
                                          if (response[index].condition[i].matrix == val) selected = 'selected';
                                           output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                        });
                          output += '</select>'+
                                    '</td>'+
                                    '<td>'+
                                      '<select id="recommendation_metric" name="recommendation_metric" class="form-control" required="">';
                                         $('.select_metric option').each(function(){
                                            var val = $(this).val(); var text = $(this).text()
                                            var selected = '';
                                            if (response[index].condition[i].metric == val) selected = 'selected';
                                             output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                          });
                          output +=  '</select>'+
                                    '</td>';
                                    if(i == 0){ output += '<td colspan="2">';}else{output += '<td>';}
                          output += '<input id="recommendation_value" type="text" name="recommendation_value" class="form-control" value="'+response[index].condition[i].value+'" onblur="ValidateValue(this,12)">'+
                                    '</td>';
                                    if (i != 0) {
                          output += '<td width="2" style="vertical-align: middle;"><button onclick="closeRow(this)" class="btn btn-danger btn-sm btnDeleteRow" style="border-radius:0px;"><i class="fa fa-trash"></i></button></td>';
                                    }
                          output += '</tr>';
                              } // end for condition table
                        output += '</tbody>'+
                            '</table>'+
                            '</div>';
                        output += '<div class="row">'+
                            '<div class="col-lg-6 col-md-12">'+
                              '<button class="btn btn-primary btn-sm m-r-10 m-t-5" onclick="enableSelectedRowList(this)">Edit rule</button>'+
                              '<button class="btn btn-primary btn-sm m-r-10 cancelEditBtn m-t-5" onclick="disableSelectedRowList(this)" style="border-radius:0px;display:none">Cancel Edit</button>'+
                              '<button class="btn btn-primary btn-sm m-r-10 addConditionBtn m-t-5" onclick="addConditionList(this)"  style="border-radius:0px;display:none;background:#4FB7FE">Add condition</button>'+
                              '<button class="btn btn-success btn-sm m-r-10 btnSaveChanes m-t-5" onclick="saveChanges(this)" style="border-radius:0px;display:none">Save changes</button>'+
                              '<button class="btn btn-danger btn-sm m-r-10 btnDeleteListRow m-t-5" onclick="deleteSavedRule(this,'+id+')" style="border-radius:0px;display:none">Delete</button>'+
                              '</div>'+
                            '</div>'+
                          '</div>'+
                          '</div>'+
                          '</div>';

              $('.list_recommendation_rules').append(output);
              hide_unhide_custom_rec_div(id.toString());

              
              }//end loop
              $(".recMultiSelect2").chosen({allow_single_deselect: true}); 
              $('.rowListContainer').hide();
              $('.countrySelect').html(countrySelect)
              $('.campaignameSelect').html(campaignameSelect);
              $('.rowListContainer').find('select, input, .btnDeleteRow').attr("disabled", true);
              $('.rowListContainer').find(".make-switch-radio").bootstrapSwitch('disabled',true);
              $('.recMultiSelect2').prop('disabled', true).trigger("chosen:updated");

              $.each($('.make-switch-radio'), function () {
                  $(this).bootstrapSwitch({
                      onText: $(this).data('onText'),
                      offText: $(this).data('offText'),
                      onColor: $(this).data('onColor'),
                      offColor: $(this).data('offColor'),
                      size: $(this).data('size'),
                      labelText: $(this).data('labelText')
                  });
              });

              // $('.recommendationcontainer').hide();
              activateToggle('');
          }//end ajax success
        });

    }

    function dupliRule(idRow) {

        swal({
            title: '',
            html: 'Are you sure you want to duplicate this rule?',
            showCancelButton: true,
            confirmButtonColor: '#DD6B55',
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel',
            confirmButtonClass: 'btn btn-success',
            cancelButtonClass: 'btn btn-danger',
            showLoaderOnConfirm: true,
            preConfirm: function () {
            return new Promise(function (resolve, reject) {
              $.ajax({
                url: 'duplicateRule',
                type: 'POST',
                data: {id: idRow},
                success: function(response){
                  if (response.message == 'success') {
                    resolve()
                  }
                }
              })
            })
          },
          allowOutsideClick: false
          }).then(function () {
            swal({
              text: "Rule successfully duplicated",
              type: 'success',
              allowOutsideClick: false,
            }).then(function () {
                location.reload()
            })

          }, function (dismiss) {
            // if (dismiss === 'cancel') {
            //   $('#refunds-switch-msg').html('Feature Deactivated')

            // }
          }).catch(swal.noop)

    }

    function populateAddedRule(response){
            var countrySelect = $("<select />").append($("#recommendation_country").clone()).html();
            var campaignameSelect = $("<select />").append($("#recommendation_campaign").clone()).html();
            if (response.length == 0) $('.list_recommendation_rules')
                  .html('<div class="col-md-12 text-center">'+
                            '<strong>No saved rules</strong>'+
                        '</div>');
            for(var index in response){
                var countries = response[index].country.split(',');
                var campaign_names = response[index].campaign_name.split(',');
                var adgroup_names = response[index].adgrounp_name.split(',');
                var camp_types = response[index].campaign_type.split(',');
                var id = response[index].id
                var output = '<div class="col-md-12 ruleListTitleMain'+id+'">'+
                              '<div class="col-md-12 ruleListTitle'+id+' ruleListTitleToggle" data="'+id+'">'+
                                response[index].recommendation_name+' <i class="fa fa-angle-right"></i>'+
                                '<i class="fa fa-window-maximize pull-right m-t-5 maximizeBtn'+id+'" aria-hidden="true"></i>'+
                              '</div>'+
                            '</div>';

                output += '<div class="col-md-12 rowListContainer m-b-15 listRuleContainer'+id+'" data="'+id+'">'+
                '<div class="col-md-12 ruleTableRow">'+
                          '<div class="row">'+
                            '<div class="col-md-12">'+
                              '<div class="col-lg-6 col-md-12">'+
                                '<div class="row">'+
                                  '<div class="col-lg-4 col-md-6">'+
                                    'Recommendation Name'+
                                  '</div>'+
                                  '<div class="col-lg-8 col-md-6">'+
                                    '<input id="recommendation_name'+id+'" type="text" name="" class="form-control" value="'+response[index].recommendation_name+'">'+
                                  '</div>'+
                                '</div>'+
                              '</div>'+
                            '</div>'+
                          '</div>'+
                '<div class="col-lg-6 col-md-12 m-t-5">'+
                          '<div class="row">'+
                            '<div class="col-lg-4 col-md-6">'+
                              'Country'+
                            '</div>'+
                            '<div class="col-lg-8 col-md-6">';
                    output += '<select class="form-control recMultiSelect2" required="required" id="recommendation_country'+id+'" multiple>';
                                $('.select-countryRec option').each(function(){
                                  var val = $(this).val(); var text = $(this).text()
                                  var selected = '';
                                    for (var i = 0; i < countries.length; i++) {
                                        if (countries[i] == val.toLowerCase()) selected = 'selected';
                                    }
                                   output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                });
                    output += '</select>'+
                            '</div>'+
                          '</div>'+
                          '<div class="row m-t-5">'+
                            '<div class="col-lg-4 col-md-6">'+
                              'Campaign Type'+
                            '</div>'+
                            '<div class="col-lg-8 col-md-6">'+
                                '<select id="recommendation_camp_type'+id+'" name="campaign_type" class="form-control recMultiSelect2" multiple>';
                                  $('.select-camppTypeRec option').each(function(){
                                    var val = $(this).val(); var text = $(this).text()
                                    var selected = '';
                                    for (var i = 0; i < camp_types.length; i++) {
                                        if (camp_types[i].toLowerCase() == val.toLowerCase()) selected = 'selected';
                                    }
                                     output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                  });
                    output +=   '</select>'+
                            '</div>'+
                          '</div>'+
                          '<div class="row m-t-5">'+
                            '<div class="col-lg-4 col-md-6">'+
                              'Campaign Name'+
                            '</div>'+
                            '<div class="col-lg-8 col-md-6">';
                    output += '<select class="form-control recMultiSelect2" required="required" id="recommendation_campaign'+id+'" multiple>';
                                $('.select-campaignRec option').each(function(){
                                  var val = $(this).val(); var text = $(this).text()
                                  var selected = '';
                                    for (var i = 0; i < campaign_names.length; i++) {
                                      if (campaign_names[i] == val) selected = 'selected';
                                    }
                                   output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                });
                    output += '</select>'+
                            '</div>'+
                          '</div>'+
                          '<div class="row m-t-5">'+
                              '<div class="col-lg-4 col-md-6">'+
                                'Ad Group'+
                              '</div>'+
                              '<div class="col-lg-8 col-md-6">'
                      output += '<select class="form-control recMultiSelect2" required="required" id="recommendation_ad_group'+id+'" multiple>';
                                  var counter = 0;
                                  $('.select-adGroupRec option').each(function(){
                                    var val = $(this).val(); var text = $(this).text()
                                    var selected = '';
                                    for (var i = 0; i < adgroup_names.length; i++) {
                                      if (adgroup_names[i] == val) selected = 'selected';
                                    }
                                     output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                     counter ++
                                  });
                      output += '</select>'+          
                              '</div>'+
                            '</div>'+
                        '</div>'+
                        '<div class="col-lg-6 col-md-12 m-t-5">'+
                          '<div class="row">'+
                            '<div class="col-lg-4 col-md-6">'+
                             'Time Period'+
                            '</div>'+
                            '<div class="col-lg-8 col-md-6">';
                      output += '<select id="recommendation_period'+id+'" class="form-control">';
                                $('.select-time-period option').each(function(){
                                  var val = $(this).val(); var text = $(this).text()
                                  var selected = '';
                                  if (response[index].time_period == val) selected = 'selected';
                                   output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                });
                      output +='</select>'+
                            '</div>'+
                          '</div>'+
                          '<div class="row m-t-5">'+
                            '<div class="col-lg-4 col-md-6">'+
                              'Recommendation'+
                            '</div>'+
                            '<div class="col-lg-8 col-md-6">';
                      output += '<select id="recommendation'+id+'" class="form-control" onchange="hide_unhide_custom_rec_div('+id.toString()+');">';
                                var count_sel = 0;
                                var rec = '';
                                $('.select-recommendation option').each(function(){
                                  var val = $(this).val(); var text = $(this).text()
                                  var selected = '';
                                  if (response[index].recommendation == val){
                                    selected = 'selected';
                                    count_sel++;
                                  }
                                  if(val == 'Custom' && count_sel == 0){
                                    selected = 'selected';
                                    rec = response[index].recommendation;
                                  }
                                   output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                });
                      output +='</select>'+
                            '</div>'+
                          '</div>'+
                          '<div class="row m-t-5" id="custom_recommendation_div'+id+'">'+
                              '<div class="col-lg-4 col-md-6">'+
                              'Custom'+
                              '</div>'+
                            '<div class="col-lg-8 col-md-6">';
                      output += '<input type="text" name="custom_recommendation'+id+'" id="custom_recommendation'+id+'" class="form-control" value="'+rec+'">';
                      output +='</div>'+
                            '</div>'+
                        '</div>'+
                        '<div class="col-md-12">'+
                        '<hr>'+
                          '<span style="color: #7AC482">Conditions</span>'+
                        '</div>'+
                        '<div class="col-md-12">'+
                        '<div class="table-responsive">';
                      output += '<table cellspacing=0 cellpadding=0 class="table table-bordered table_res" id="tableRule'+id+'" style="min-width:400px;">'+
                            '<thead>'+
                              '<tr>'+
                                  '<th class="text-center">Operation</th>'+
                                  '<th class="text-center">Matrix</th>'+
                                  '<th class="text-center">Metric</th>'+
                                  '<th class="text-center" colspan="2">Value</th>'+
                              '</tr>'+
                            '</thead>'+
                            '<tbody>';
                        for (var i = 0; i < response[index].condition.length; i++) {
                      output += '<tr class="ruleRow'+id+'">'+
                                  '<td>'+
                                    '<select id="recommendation_operation" name="recommendation_operation" class="form-control" required="">';
                                      $('.select_operation option').each(function(){
                                        var val = $(this).val(); var text = $(this).text()
                                        var selected = '';
                                        if (response[index].condition[i].operation == val) selected = 'selected';
                                         output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                      });
                        output += '</select>'+
                                  '</td>'+
                                  '<td>'+
                                    '<select id="recommendation_matrix" name="recommendation_matrix" class="form-control" required="">';
                                       $('.select_matrix option').each(function(){
                                        var val = $(this).val(); var text = $(this).text()
                                        var selected = '';
                                        if (response[index].condition[i].matrix == val) selected = 'selected';
                                         output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                      });
                        output += '</select>'+
                                  '</td>'+
                                  '<td>'+
                                    '<select id="recommendation_metric" name="recommendation_metric" class="form-control" required="">';
                                       $('.select_metric option').each(function(){
                                          var val = $(this).val(); var text = $(this).text()
                                          var selected = '';
                                          if (response[index].condition[i].metric == val) selected = 'selected';
                                           output += '<option value="'+val+'" '+selected+'>'+text+'</option>';
                                        });
                        output +=  '</select>'+
                                  '</td>';
                                  if(i == 0){ output += '<td colspan="2">';}else{output += '<td>';}
                        output += '<input id="recommendation_value" type="text" name="recommendation_value" class="form-control" value="'+response[index].condition[i].value+'" onblur="ValidateValue(this,12)">'+
                                  '</td>';
                                  if (i != 0) {
                        output += '<td width="2" style="vertical-align: middle;"><button onclick="closeRow(this)" class="btn btn-danger btn-sm btnDeleteRow" style="border-radius:0px;"><i class="fa fa-trash"></i></button></td>';
                                  }
                        output += '</tr>';
                            } // end for condition table
                      output += '</tbody>'+
                          '</table>'+
                          '</div>';
                      output += '<div class="row">'+
                          '<div class="col-lg-6 col-md-12">'+
                            '<button class="btn btn-primary btn-sm m-r-10 m-t-5" onclick="enableSelectedRowList(this)">Edit rule</button>'+
                            '<button class="btn btn-primary btn-sm m-r-10 cancelEditBtn m-t-5" onclick="disableSelectedRowList(this)" style="border-radius:0px;display:none">Cancel Edit</button>'+
                            '<button class="btn btn-primary btn-sm m-r-10 addConditionBtn m-t-5" onclick="addConditionList(this)"  style="border-radius:0px;display:none;background:#4FB7FE">Add condition</button>'+
                            '<button class="btn btn-success btn-sm m-r-10 btnSaveChanes m-t-5" onclick="saveChanges(this)" style="border-radius:0px;display:none">Save changes</button>'+
                            '<span class="loading-edit-button"></span>'+
                            '<button class="btn btn-danger btn-sm btnDeleteListRow m-t-5" onclick="deleteSavedRule(this,'+id+')" style="border-radius:0px;display:none">Delete</button>'+
                          '</div>'+
                          '</div>'+
                        '</div>'+
                        '</div>'+
                        '</div>';

            $('.list_recommendation_rules').prepend(output);
            $(".recMultiSelect2").chosen({allow_single_deselect: true}); 
            $('.rowListContainer').hide();
            $('.countrySelect').html(countrySelect)
            $('.campaignameSelect').html(campaignameSelect);
            $('.rowListContainer').find('select, input, .btnDeleteRow, .make-switch-radio').attr("disabled", true);
            $('.rowListContainer').find(".make-switch-radio").bootstrapSwitch('disabled',true);
            $('.recMultiSelect2').prop('disabled', true).trigger("chosen:updated");

            $.each($('.make-switch-radio'), function () {
                  $(this).bootstrapSwitch({
                      onText: $(this).data('onText'),
                      offText: $(this).data('offText'),
                      onColor: $(this).data('onColor'),
                      offColor: $(this).data('offColor'),
                      size: $(this).data('size'),
                      labelText: $(this).data('labelText')
                  });
              });

            activateToggle(id);
            hide_unhide_custom_rec_div(id.toString());
            }//end loop

            
    }

    function activateToggle(id){
       $('.ruleListTitle'+id).click(function(){
         var idRow = $(this).attr('data')
         $('.listRuleContainer'+idRow).toggle('fast', function(){
              if ( $('.listRuleContainer'+idRow).is(':visible') ) {
                $('.maximizeBtn'+idRow).removeClass('fa-window-maximize')
                $('.maximizeBtn'+idRow).addClass('fa-window-minimize')
             }else{
                $('.maximizeBtn'+idRow).removeClass('fa-window-minimize')
                $('.maximizeBtn'+idRow).addClass('fa-window-maximize')
             }
         })
      })
    }

    function enableSelectedRowList(element){
        var parentContainer = $(element).closest('.rowListContainer');
        $(parentContainer).find('.cancelEditBtn, .btnSaveChanes, .btnDeleteListRow, .addConditionBtn').show();
        $(parentContainer).find('select, input, .btnDeleteRow').attr("disabled", false);
        $(parentContainer).find(".make-switch-radio").bootstrapSwitch('disabled',false);
        $(parentContainer).find('.recMultiSelect2').prop('disabled', false).trigger("chosen:updated");
    }

    function disableSelectedRowList(element){
        var parentContainer = $(element).closest('.rowListContainer');
        $(parentContainer).find('.cancelEditBtn, .btnSaveChanes, .btnDeleteListRow, .addConditionBtn').hide();
        $(parentContainer).find('select, input, .btnDeleteRow').attr("disabled", true);
        $(parentContainer).find(".make-switch-radio").bootstrapSwitch('disabled',true);
        $(parentContainer).find('.recMultiSelect2').prop('disabled', true).trigger("chosen:updated");
    }

    function close_param(element){
        var filter_criteria = $(element).attr("data");
        switch (filter_criteria) {
          case 'date_range':
                $('#start_date').val('');
                $('#end_date').val('');
                $(element).parent().remove();
          break;
          case 'country':
                $('#filter_country').val('').trigger('chosen:updated');
                $(element).parent().remove();
          break;
          case 'camp_type':
                $('#filter_camp_type').val('').trigger('chosen:updated');
                $(element).parent().remove();
          break;
          case 'camp_name':
                $('#filter_camp_name').val('').trigger('chosen:updated');
                $(element).parent().remove();
          break;
          case 'ad_group':
                $('#filter_ad_group').val('').trigger('chosen:updated');
                $(element).parent().remove();
          break;
          case 'recommendation':
                $('#filter_recommendation').val('').trigger('chosen:updated');
                $(element).parent().remove();
          break;
          case 'keyword':
                $('#filter_keyword').val('');
                $(element).parent().remove();
          break;
          case 'time_range':
                $('#time_range').val('');
                $(element).parent().remove();
          break;
          case 'imp':
                $('#filter_imp_min').val('');
                $('#filter_imp_max').val('');
                $(element).parent().remove();
          break;
          case 'clicks':
                $('#filter_clicks_min').val('');
                $('#filter_clicks_max').val('');
                $(element).parent().remove();
          break;
          case 'ctr':
                $('#filter_ctr_min').val('');
                $('#filter_ctr_max').val('');
                $(element).parent().remove();
          break;
          case 'total_spend':
                $('#filter_total_spend_min').val('');
                $('#filter_total_spend_max').val('');
                $(element).parent().remove();
          break;
          case 'avg_cpc':
                $('#filter_avg_cpc_min').val('');
                $('#filter_avg_cpc_max').val('');
                $(element).parent().remove();
          break;
          case 'acos':
                $('#filter_acos_min').val('');
                $('#filter_acos_max').val('');
                $(element).parent().remove();
          break;
          case 'conv_rate':
                $('#filter_conv_rate_min').val('');
                $('#filter_conv_rate_max').val('');
                $(element).parent().remove();
          break;
          case 'revenue':
                $('#filter_revenue_min').val('');
                $('#filter_revenue_max').val('');
                $(element).parent().remove();
          break;
        }
    }

    function reset_filter(){
        $('#filter_name').val(''); 
        $('#start_date').val('');
        $('#end_date').val('');
        $('#filter_imp_min, #filter_imp_max').val('');
        $('#filter_clicks_min, #filter_clicks_max').val();
        $('#filter_ctr_min, #filter_ctr_max').val()+"-"+$('');
        $('#filter_total_spend_min, #filter_total_spend_max').val('');
        $('#filter_avg_cpc_min, #filter_avg_cpc_max').val('');
        $('#filter_acos_min, #filter_acos_max').val('');
        $('#filter_conv_rate_min, #filter_conv_rate_max').val('');
        $('#filter_revenue_min, #filter_revenue_max').val('');
        $('#filter_country').val('').trigger('chosen:updated');
        $('#filter_camp_type').val('').trigger('chosen:updated');
        $('#filter_camp_name').val('').trigger('chosen:updated');
        $('#filter_ad_group').val('').trigger('chosen:updated');
        $('#filter_recommendation').val('').trigger('chosen:updated');
        $('#filter_keyword').val('');
        $('#time_range').val('');
    }

    function time_range(element){
        var now = new Date();
        var time_range_val = $(element).val();
            var date = new Date();
        switch(time_range_val){
            case '14d':
              date.setDate( date.getDate() - 14 );
            break;
            case '30d':
              date.setDate( date.getDate() - 30 );
            break;
            case '60d':
              date.setDate( date.getDate() - 60 );
            break;
            case 'lifetime':
              var date = new Date(1970,1,1);
            break;
        }
        var day = (date.getDate() < 10) ? '0' + date.getDate() : date.getDate()
        var month = (date.getMonth() < 10) ? '0' + (date.getMonth()+1) : (date.getMonth()+1)
        $("#start_date").val(day + '/' + month + '/' + (date.getFullYear()));
    }

    function show_filter_parameter(){
        var actv_filter = "";
        var filter_date_start = $('#start_date').val();
        var filter_date_end = $('#end_date').val();
        var filter_imp = $('#filter_imp_min').val()+"-"+$('#filter_imp_max').val();
        var filter_clicks = $('#filter_clicks_min').val()+"-"+$('#filter_clicks_max').val();
        var filter_ctr = $('#filter_ctr_min').val()+"-"+$('#filter_ctr_max').val();
        var filter_total_spend = $('#filter_total_spend_min').val()+"-"+$('#filter_total_spend_max').val();
        var filter_avg_cpc = $('#filter_avg_cpc_min').val()+"-"+$('#filter_avg_cpc_max').val();
        var filter_acos = $('#filter_acos_min').val()+"-"+$('#filter_acos_max').val();
        var filter_conv_rate = $('#filter_conv_rate_min').val()+"-"+$('#filter_conv_rate_max').val();
        var filter_revenue = $('#filter_revenue_min').val()+"-"+$('#filter_revenue_max').val();
        var filter_country = $('#filter_country').val();
        var filter_camp_type = $('#filter_camp_type').val();
        var filter_camp_name = $('#filter_camp_name').val();
        var filter_ad_group = $('#filter_ad_group').val();
        var filter_recommendation = $('#filter_recommendation').val();
        var filter_keyword = $('#filter_keyword').val();
        var time_range = $('#time_range').val();

        if (filter_date_start != "" && filter_date_end != "") {
          actv_filter += '<div class="filter_parameter">Date Range: '+filter_date_start+' to '+filter_date_end+'<i onclick="close_param(this)" data="date_range" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_date_start != "" && filter_date_end == ""){
          actv_filter += '<div class="filter_parameter">Date Range: '+filter_date_start+'<i onclick="close_param(this)" data="date_range" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_date_start == "" && filter_date_end != ""){
          actv_filter += '<div class="filter_parameter">Date Range: '+filter_date_end+'<i onclick="close_param(this)"  data="date_range" sclass="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        if (time_range) {
          actv_filter += '<div class="filter_parameter">Time Range: '+time_range;
          actv_filter += '<i onclick="close_param(this)" data="time_range" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        if (filter_country) {
          actv_filter += '<div class="filter_parameter">Country: ';
          for (var i = 0; i < filter_country.length; i++) {
             country = filter_country[i].split('|');
             actv_filter += country[1]+', ';
          };

          actv_filter = actv_filter.substr(0,actv_filter.length-2);
          actv_filter += '<i onclick="close_param(this)" data="country" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        if (filter_camp_type) {
          actv_filter += '<div class="filter_parameter">Campaign Type: ';
          for (var i = 0; i < filter_camp_type.length; i++) {
              actv_filter += filter_camp_type[i]+', ';
          };

          actv_filter = actv_filter.substr(0,actv_filter.length-2);
          actv_filter += '<i onclick="close_param(this)" data="camp_type" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        if (filter_camp_name) {
          actv_filter += '<div class="filter_parameter">Campaign Name: ';
          for (var i = 0; i < filter_camp_name.length; i++) {
              actv_filter += filter_camp_name[i]+', ';
          };

          actv_filter = actv_filter.substr(0,actv_filter.length-2);
          actv_filter += '<i onclick="close_param(this)" data="camp_name" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        if (filter_ad_group) {
          actv_filter += '<div class="filter_parameter">Ad Group: ';
          for (var i = 0; i < filter_ad_group.length; i++) {
              actv_filter += filter_ad_group[i]+', ';
          };

          actv_filter = actv_filter.substr(0,actv_filter.length-2);
          actv_filter += '<i onclick="close_param(this)" data="ad_group" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        if (filter_recommendation) {
          actv_filter += '<div class="filter_parameter">Recommendation: ';
          for (var i = 0; i < filter_recommendation.length; i++) {
              actv_filter += filter_recommendation[i]+', ';
          };

          actv_filter = actv_filter.substr(0,actv_filter.length-2);
          actv_filter += '<i onclick="close_param(this)" data="recommendation" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        if (filter_keyword) {
          actv_filter += '<div class="filter_parameter">Keyword: '+filter_keyword;
          actv_filter += '<i onclick="close_param(this)" data="keyword" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }


        filter_imp = filter_imp.split("-")
        if (filter_imp[0].trim() != "" && filter_imp[1].trim() != "") {
            actv_filter += '<div class="filter_parameter">Imp: from '+filter_imp[0].trim()+' to '+filter_imp[1].trim()+'<i onclick="close_param(this)" data="imp" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_imp[0].trim() != "" && filter_imp[1].trim() == ""){
            actv_filter += '<div class="filter_parameter">Imp: from '+filter_imp[0].trim()+'<i onclick="close_param(this)" data="imp" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_imp[0].trim() == "" && filter_imp[1].trim() != ""){
            actv_filter += '<div class="filter_parameter">Imp: from 0 to '+filter_imp[1].trim()+'<i onclick="close_param(this)" data="imp" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        filter_clicks = filter_clicks.split("-")
        if (filter_clicks[0].trim() != "" && filter_clicks[1].trim() != "") {
            actv_filter += '<div class="filter_parameter">Clicks: from '+filter_clicks[0].trim()+' to '+filter_clicks[1].trim()+'<i onclick="close_param(this)" data="clicks" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_clicks[0].trim() != "" && filter_clicks[1].trim() == ""){
            actv_filter += '<div class="filter_parameter">Clicks: from '+filter_clicks[0].trim()+'<i onclick="close_param(this)" data="clicks" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_clicks[0].trim() == "" && filter_clicks[1].trim() != ""){
            actv_filter += '<div class="filter_parameter">Clicks: from 0 to '+filter_clicks[1].trim()+'<i onclick="close_param(this)" data="clicks" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        filter_ctr = filter_ctr.split("-")
        if (filter_ctr[0].trim() != "" && filter_ctr[1].trim() != "") {
            actv_filter += '<div class="filter_parameter">CTR: from '+filter_ctr[0].trim()+'% to '+filter_ctr[1].trim()+'%<i onclick="close_param(this)" data="ctr" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_ctr[0].trim() != "" && filter_ctr[1].trim() == ""){
            actv_filter += '<div class="filter_parameter">CTR: from '+filter_ctr[0].trim()+'%<i onclick="close_param(this)" data="ctr" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_ctr[0].trim() == "" && filter_ctr[1].trim() != ""){
            actv_filter += '<div class="filter_parameter">CTR: from 0 to '+filter_ctr[1].trim()+'%<i onclick="close_param(this)" data="ctr" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        filter_total_spend = filter_total_spend.split("-")
        if (filter_total_spend[0].trim() != "" && filter_total_spend[1].trim() != "") {
            actv_filter += '<div class="filter_parameter">Total Spend: from '+filter_total_spend[0].trim()+' to '+filter_total_spend[1].trim()+'<i onclick="close_param(this)" data="total_spend" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_total_spend[0].trim() != "" && filter_total_spend[1].trim() == ""){
            actv_filter += '<div class="filter_parameter">Total Spend: from '+filter_total_spend[0].trim()+'<i onclick="close_param(this)" data="total_spend" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_total_spend[0].trim() == "" && filter_total_spend[1].trim() != ""){
            actv_filter += '<div class="filter_parameter">Total Spend: from 0 to '+filter_total_spend[1].trim()+'<i onclick="close_param(this)" data="total_spend" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        filter_avg_cpc = filter_avg_cpc.split("-")
        if (filter_avg_cpc[0].trim() != "" && filter_avg_cpc[1].trim() != "") {
            actv_filter += '<div class="filter_parameter">Average CPC: from '+filter_avg_cpc[0].trim()+' to '+filter_avg_cpc[1].trim()+'<i onclick="close_param(this)" data="avg_cpc" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_avg_cpc[0].trim() != "" && filter_avg_cpc[1].trim() == ""){
            actv_filter += '<div class="filter_parameter">Average CPC: from '+filter_avg_cpc[0].trim()+'<i onclick="close_param(this)" data="avg_cpc" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_avg_cpc[0].trim() == "" && filter_avg_cpc[1].trim() != ""){
            actv_filter += '<div class="filter_parameter">Average CPC: from 0 to '+filter_avg_cpc[1].trim()+'<i onclick="close_param(this)" data="avg_cpc" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        filter_acos = filter_acos.split("-")
        if (filter_acos[0].trim() != "" && filter_acos[1].trim() != "") {
            actv_filter += '<div class="filter_parameter">ACoS: from '+filter_acos[0].trim()+'% to '+filter_acos[1].trim()+'%<i onclick="close_param(this)" data="acos" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_acos[0].trim() != "" && filter_acos[1].trim() == ""){
            actv_filter += '<div class="filter_parameter">ACoS: from '+filter_acos[0].trim()+'%<i onclick="close_param(this)" data="acos" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_acos[0].trim() == "" && filter_acos[1].trim() != ""){
            actv_filter += '<div class="filter_parameter">ACoS: from 0 to '+filter_acos[1].trim()+'%<i onclick="close_param(this)" data="acos" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        filter_conv_rate = filter_conv_rate.split("-")
        if (filter_conv_rate[0].trim() != "" && filter_conv_rate[1].trim() != "") {
            actv_filter += '<div class="filter_parameter">Conversion Rate: from '+filter_conv_rate[0].trim()+'% to '+filter_conv_rate[1].trim()+'%<i onclick="close_param(this)" data="conv_rate" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_conv_rate[0].trim() != "" && filter_conv_rate[1].trim() == ""){
            actv_filter += '<div class="filter_parameter">Conversion Rate: from '+filter_conv_rate[0].trim()+'%<i onclick="close_param(this)" data="conv_rate" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_conv_rate[0].trim() == "" && filter_conv_rate[1].trim() != ""){
            actv_filter += '<div class="filter_parameter">Conversion Rate: from 0 to '+filter_conv_rate[1].trim()+'%<i onclick="close_param(this)" data="conv_rate" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        filter_revenue = filter_revenue.split("-")
        if (filter_revenue[0].trim() != "" && filter_revenue[1].trim() != "") {
            actv_filter += '<div class="filter_parameter">Revenue: from '+filter_revenue[0].trim()+' to '+filter_revenue[1].trim()+'<i onclick="close_param(this)" data="revenue" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_revenue[0].trim() != "" && filter_revenue[1].trim() == ""){
            actv_filter += '<div class="filter_parameter">Revenue: from '+filter_revenue[0].trim()+'<i onclick="close_param(this)" data="revenue" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }else if(filter_revenue[0].trim() == "" && filter_revenue[1].trim() != ""){
            actv_filter += '<div class="filter_parameter">Revenue: from 0 to '+filter_revenue[1].trim()+'<i onclick="close_param(this)" data="revenue" class="fa fa-times close_filter_selected" aria-hidden="true"></i></div>';
        }

        $('.filter_param').html(actv_filter);
    }
    var campaign_list;
    var cct;
    var adgrouplist;
    var cid_adg;
    var country_list;
    var targetingtype = ["Automatic","Manual"];
    var display_campaign = [];
    var display_campaignid = [];
    var display_adgroup = [];
    function getDropDownData(){
      $.ajax({
        url: 'getDropDownData', 
        type: 'GET', 
        success: function(result){
          var res = jQuery.parseJSON(result);
          campaign_list = res.campaign_list;
          cct = res.cct;
          //adgrouplist = res.adgrouplist;
          cid_adg = res.cid_adg;
          country_list = res.country_list;
          setCampaignDropdown();

          populateRecommendationRules();

        } 
      })
    }

    function setCampaignDropdown(){
      display_campaignid=[];
      display_campaign = [];
      
      var countries = $("#recommendation_country").val();
      var ttype = $("#recommendation_camp_type").val();
      if(countries == null){
        countries = country_list;
      }else{
        for(var x=0; x< countries.length; x++){
          var c = countries[x].split('|');
          countries[x] = c[0].toLowerCase();
        }
      }
      if(ttype == null) ttype = targetingtype;
      for(var x=0; x < ttype.length; x++){
        if(ttype[x] == 'Automatic') ttype[x]='auto';
        if(ttype[x] == 'Manual') ttype[x]='manual';
      }
      //console.log(countries);
      //console.log(ttype);
      for(x in countries){
        for (i in ttype) {
          if(cct[countries[x]][ttype[i]]){
            cct_list = cct[countries[x]][ttype[i]];
            for(c in cct_list){
              var cid = cct_list[c];
              display_campaignid.push(cid);
              display_campaign.push(campaign_list[cid]);
            }
          }
        }
      }
      //console.log(display_campaignid);
      display_campaign = Array.from(new Set(display_campaign));
      display_campaign.sort();
      //console.log(display_campaign);
      $('#recommendation_campaign').html("");
      //$('#recommendation_campaign').append('<option value="select">Select All</option>');
      for(index in display_campaign){
        $('#recommendation_campaign').append('<option value="'+display_campaign[index]+'">'+display_campaign[index]+'</option>');
      }
      $("#recommendation_campaign").trigger("chosen:updated");
      setAdgroupDropdown(); 
    }
    function setAdgroupDropdown(){
      display_adgroup=[];
      var campaigns = $('#recommendation_campaign').val();
      if(campaigns == null) campaigns = display_campaign;
      for(x in campaigns){
        cid = campaigns[x];
        for(a in cid_adg[cid])
          display_adgroup.push(cid_adg[cid][a]);
      }

      display_adgroup = Array.from(new Set(display_adgroup));
      display_adgroup.sort();
      //console.log(display_adgroup);
      $('#recommendation_ad_group').html("");
      for(index in display_adgroup){
        $('#recommendation_ad_group').append('<option value="'+display_adgroup[index]+'">'+display_adgroup[index]+'</option>');
      }
      $("#recommendation_ad_group").trigger("chosen:updated");
    }