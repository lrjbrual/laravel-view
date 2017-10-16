$(document).ready(function () {
    $(function(){
        // Revenue
        $(".OrdersDetailsRow").hide();
        $(".AdjustmentDetailsRow").hide();
        $(".RevenueOthersRow").hide();

        // Cost
        $(".CostRefundsDetailsRow").hide();
        $(".CostAdjustmentsDetailsRow").hide();
        $(".CostDiscountDetailsRow").hide();
        $(".CostOtherSellingDetailsRow").hide();
        $(".CostTaxDetailsRow").hide();
        $(".CostOtherDetailsRow").hide();

    });

    // Revenue
    $("#RevenueOrdersSection").click(function(){
        if ($(this).hasClass("row-details-open")) {
            $(this).addClass("row-details-close").removeClass("row-details-open");
            $(".OrdersDetailsRow").hide();
        } else {
            $(this).addClass("row-details-open").removeClass("row-details-close");
            $(".OrdersDetailsRow").show();
        }
    });

    $("#RevenueAdjustmentsSection").click(function(){
        if ($(this).hasClass("row-details-open")) {
            $(this).addClass("row-details-close").removeClass("row-details-open");
            $(".AdjustmentDetailsRow").hide();
        } else {
            $(this).addClass("row-details-open").removeClass("row-details-close");
            $(".AdjustmentDetailsRow").show();
        }
    });

    $("#RevenueOthersSection").click(function(){
        if ($(this).hasClass("row-details-open")) {
            $(this).addClass("row-details-close").removeClass("row-details-open");
            $(".RevenueOthersRow").hide();
        } else {
            $(this).addClass("row-details-open").removeClass("row-details-close");
            $(".RevenueOthersRow").show();
        }
    });

    // Cost
    $("#CostRefundsSection").click(function(){
        if ($(this).hasClass("row-details-open")) {
            $(this).addClass("row-details-close").removeClass("row-details-open");
            $(".CostRefundsDetailsRow").hide();
        } else {
            $(this).addClass("row-details-open").removeClass("row-details-close");
            $(".CostRefundsDetailsRow").show();
        }
    });

    $("#CostAdjustmentsSection").click(function(){
        if ($(this).hasClass("row-details-open")) {
            $(this).addClass("row-details-close").removeClass("row-details-open");
            $(".CostAdjustmentsDetailsRow").hide();
        } else {
            $(this).addClass("row-details-open").removeClass("row-details-close");
            $(".CostAdjustmentsDetailsRow").show();
        }
    });

    $("#CostDiscountSection").click(function(){
        if ($(this).hasClass("row-details-open")) {
            $(this).addClass("row-details-close").removeClass("row-details-open");
            $(".CostDiscountDetailsRow").hide();
        } else {
            $(this).addClass("row-details-open").removeClass("row-details-close");
            $(".CostDiscountDetailsRow").show();
        }
    });

    $("#CostOtherSellingSection").click(function(){
        if ($(this).hasClass("row-details-open")) {
            $(this).addClass("row-details-close").removeClass("row-details-open");
            $(".CostOtherSellingDetailsRow").hide();
        } else {
            $(this).addClass("row-details-open").removeClass("row-details-close");
            $(".CostOtherSellingDetailsRow").show();
        }
    });

    $("#CostTaxSection").click(function(){
        if ($(this).hasClass("row-details-open")) {
            $(this).addClass("row-details-close").removeClass("row-details-open");
            $(".CostTaxDetailsRow").hide();
        } else {
            $(this).addClass("row-details-open").removeClass("row-details-close");
            $(".CostTaxDetailsRow").show();
        }
    });

    $("#CostOtherSection").click(function(){
        if ($(this).hasClass("row-details-open")) {
            $(this).addClass("row-details-close").removeClass("row-details-open");
            $(".CostOtherDetailsRow").hide();
        } else {
            $(this).addClass("row-details-open").removeClass("row-details-close");
            $(".CostOtherDetailsRow").show();
        }
    });
});


function refreshcosttablejs(){
  // $(".detailsrow").hide();
  $(".costtable .row-details").click(function(){
      var rowlevel = $(this).parents('tr').attr('rowlevel');
      // console.log(rowlevel);

      if ($(this).hasClass("row-details-open")) {
          $(this).addClass("row-details-close").removeClass("row-details-open");

          $(this).parents('tr').nextUntil('[rowlevel='+rowlevel+']').each(function() {
            var thisrowlevel = $(this).attr('rowlevel');
            if(thisrowlevel>(parseInt(rowlevel))){
              if($(this).find('span.row-details').hasClass("row-details-open")) {
                $(this).find('span.row-details').addClass("row-details-close").removeClass("row-details-open");
              }
              $(this).hide();
            }
          });
      } else {
          $(this).addClass("row-details-open").removeClass("row-details-close");

          $(this).parents('tr').nextUntil('[rowlevel='+rowlevel+']').each(function() {
            var thisrowlevel = $(this).attr('rowlevel');
            if(thisrowlevel==(parseInt(rowlevel)+1)){
              $(this).show();
              $(this).addClass("details");
              $(this).addClass("border-bottom-1");
            }
          });
      }
  });
}

function formatNumber(n){

  var val = n.replace(',','');
  return val;
}

function calcgrossvalues(){

  var costtablesumarray = [];
  var revtablesumarray = [];
  var grossmarginvalue = [];

  //get total of costtable
  var dctr=0;
  $('.costabletotaltr').find('td').each(function(dataindex){
      if(dctr>=3){
        var thiscelltext = formatNumber(($(this).text()) ? $(this).text() : '0' );
            thiscelltext = ((parseFloat(thiscelltext)) ? parseFloat(thiscelltext) : 0);

        if(grossmarginvalue[dctr]===undefined){
          grossmarginvalue[dctr]=0;
          costtablesumarray[dctr]=0;
        }
        grossmarginvalue[dctr]+= thiscelltext;
        costtablesumarray[dctr] = thiscelltext;
      }
      dctr++;
  });

  //get total of revenue table
  dctr=0;
  $('.revtabletotal').find('td').each(function(dataindex){
      if(dctr>=3){
          var thiscelltext = formatNumber(($(this).text()) ? $(this).text() : '0' );
              thiscelltext = ((parseFloat(thiscelltext)) ? parseFloat(thiscelltext) : 0);
        if(grossmarginvalue[dctr]===undefined){
          grossmarginvalue[dctr]=0;
          revtablesumarray[dctr]=0;
        }
        grossmarginvalue[dctr]+= thiscelltext;
        revtablesumarray[dctr] = thiscelltext;
        // console.log(revtablesumarray[dctr]);
      }
      dctr++;
  });

  // console.log(revtablesumarray);

  dctr=0;
  $('.grossmarginvaluetr').find('td').each(function(dataindex){
      if(dctr>=3){
        $(this).html(numberForHuman( Math.round(grossmarginvalue[dctr] * 100) / 100));
      }
      dctr++;
  });

  dctr=0;
  $('.grossmarginpercenttr').find('td').each(function(dataindex){
      var grossmarginpercent = 0;
      if(dctr>=3){
        grossmarginpercent = (revtablesumarray[dctr]+costtablesumarray[dctr])/revtablesumarray[dctr];
        if (isNaN(grossmarginpercent)) {
          $(this).html(0 + '%' );
        }else{
          $(this).html(numberForHuman(Math.round(grossmarginpercent*10000)/100) + '%' );
        }
      }
      dctr++;
  });


}
