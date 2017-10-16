$(document).ready(function(){
	init_table();
	$('#prodCostsGraphtModal').on('shown.bs.modal', function (event) {
		 init_graph();
	});

  if (localStorage.getItem("processing") == 1) {
    $("#btn-upload-view").html('Uploading data <i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span>');
    
    $('#btn-upload-view').attr('disabled',true)

    swal('','Processing uploaded CSV data!','info');
  }

})

function init_graph(){

	css1 = { 
		height: '0',
	 	width: '4px',
	 	border: '5px solid #0fb0c0' }

	css2 = { 
		height: '0',
	 	width: '4px',
	 	border: '5px solid #FF9933' }

	$('.bidLegend').find('.bidLegend1').css(css1)
	$('.bidLegend').find('.bidLegend2').css(css2)
	
	var data_imp = {
		        labels: ['IMP'],
		        series: [
		            [50],
		            [300]
		        ]
    };
    var option_imp = {
        seriesBarDistance: 20,
        axisX: {
            offset: 60
        },
        axisY: {
            offset: 80,
            labelInterpolationFnc: function(value) {
                return value + ''
            },
            scaleMinSpace: 30
        }
    };
    var chart6= new Chartist.Bar('#impGraphProdCost', data_imp, option_imp);
    new Chartist.Bar('#impGraphProdCost', data_imp, option_imp);
    //end imp

    //click
	var data_click = {
		        labels: ['Click'],
		        series: [
		            [2],
		            [50]
		        ]
    };
    var option_click = {
        seriesBarDistance: 20,
        axisX: {
            offset: 60
        },
        axisY: {
            offset: 80,
            labelInterpolationFnc: function(value) {
                return value + ''
            },
            scaleMinSpace: 30
        }
    };
    var chart6= new Chartist.Bar('#impClickProdCost', data_click, option_click);
    new Chartist.Bar('#impClickProdCost', data_click, option_click);
    //end click

    //acos
	var data_acos = {
		        labels: ['Acos'],
		        series: [
		            [10],
		            [22]
		        ]
    };
    var option_acos = {
        seriesBarDistance: 20,
        axisX: {
            offset: 60
        },
        axisY: {
            offset: 80,
            labelInterpolationFnc: function(value) {
                return value + '%'
            },
            scaleMinSpace: 30
        }
    };
    var chart6= new Chartist.Bar('#impAcosProdCost', data_acos, option_acos);
    new Chartist.Bar('#impAcosProdCost', data_acos, option_acos);
    //end acos
}


function init_table(){
	var oTable
    $.ajax({url: 'getAdProdCostData', type: 'GET', success:function(result){
        var response = jQuery.parseJSON(result);
        oTable = $('#adsprodcost_table').dataTable({
                "data": response,
                "bLengthChange": false,
                "bFilter": false,
                "destroy": true,
                "bPaginate": true,
                "paging": true,
                "ordering": true,
                "bInfo" : false,
                "scrollX": true,
                "scrollY": false,
                "scrollCollapse": true,
                createdRow: function( row, data, dataIndex ) {
           
                    $(row).children(':nth-child(8)').editable('updateUnitCost', {
                            onsubmit: function(settings, td) {
                              var input = $(td).find('input');
                              var original = input.val();

                              if (validateNumberPositiveOnly(original)) {
                                  return true;
                              } else { 
                                swal(" ", "Please input number only, not less than 0 and up 2 decimal places", "error")
                                input.css('border', '1px solid red');
                                return false;
                              }
                            },
                            callback: function( sValue, y ){
                              var sp = sValue.split("|");
                              $(row).children(':nth-child(8)').text(sp[0]);
                              $(row).children(':nth-child(9)').text(sp[1]);
                              $(row).children(':nth-child(10)').text(sp[2]);
                            },
                            submitdata: function ( value, settings ) {
                              unitCost = $(row).children(':nth-child(8)').find('input').val();
                              return {
                                          "row_id": this.parentNode.getAttribute('id'),
                                          "column": oTable.fnGetPosition( this )[2],
                                          "unitCost": unitCost
                                      };
                              },
                              "height": "100%",
                              "width": "100%"
                    });

                    $(row).children(':nth-child(11)').editable('updateMinimumMargin', {
                            onsubmit: function(settings, td) {
                              var input = $(td).find('input');
                              var original = input.val();
                              original = original.replace('%','');
                              if (validateNumberPositiveNegative(original)) {
                                  return true;
                              } else { 
                                  swal(" ", "Please input number only and up to 2 decimal places", "error")
                                  input.css('border', '1px solid red');
                                  return false;
                              }
                            },
                            callback: function( sValue, y ){
                              $(row).children(':nth-child(11)').text(sValue+'%')
                            },
                            submitdata: function ( value, settings ) {
                              minimumProfitMargin = $(row).children(':nth-child(11)').find('input').val();
                              //minimumProfitMargin = minimumProfitMargin.replace('%','');
                              return {
                                          "row_id": this.parentNode.getAttribute('id'),
                                          "column": oTable.fnGetPosition( this )[2],
                                          "minimumProfitMargin": minimumProfitMargin
                                      };
                              },
                              "height": "100%",
                              "width": "100%"
                    });

            }
        });

        

        $('.warningChangesPopUp').mousemove(function(e){
            var divid = 'bidUpdatePopUp'
            var div = $('.bidUpdatePopUp');
            var left  = e.pageX +20  + "px";
            var top  = e.pageY + "px";

            $(div).css('left',left)
            $(div).css('top',top)

            $("."+divid).fadeIn();
            return false;
        })

        $('.closeBidUpdatePopUp').click(function(){
            $('.bidUpdatePopUp').fadeOut('',function(){
                $(this).hide();
            })
        })

    }})
	
}

