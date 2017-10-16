$(document).ready(function() {
    var table = $('#adsperformance_table');

    $('#filterbtn').click(function(){
        $('#adperformance_filter').toggle('fast');
    });

    $('#start_date').datepicker({
        todayHighlight: true,
        autoclose: true,
        format: 'dd/mm/yyyy',
        orientation: "auto",
    }).on('changeDate', function(e){
        ts = $(this).datepicker('getDate');
        month = (ts.getMonth()+1) >= 10 ? ts.getMonth()+1 : '0' + (ts.getMonth()+1);
        dateString = ts.getDate() + '/' + month + '/' + ts.getFullYear();

        $('#start_date_val').html(dateString);
    });

    $('#end_date').datepicker({
        todayHighlight: true,
        autoclose: true,
        format: 'dd/mm/yyyy',
        orientation: "auto",
    })

    var oTable = table.dataTable({
        "scrollX": true
    });

    var tableWrapper = $('#adperformance_table_column_toggler'); // datatable creates the table wrapper by adding with id {your_table_jd}_wrapper
    var tableColumnToggler = $('#adperformance_table_column_toggler');

    $('input[type="checkbox"]', tableColumnToggler).on("change",function() {
        /* Get the DataTables object again - this is not a recreation, just a get of the object */
        var iCol = parseInt($(this).attr("data-column"));
        var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
        oTable.fnSetColumnVis(iCol, (bVis ? false : true));
        return false;
    });
});

function ValidateMinMax(element,digit){
    pattern =/^[0-9]{1,10}$/;
    validatate = pattern.test(+$(element).val());
    if (!validatate) {
        $(element).css('border', '1px solid red');
        element.value = element.value.replace(/[^0-9]/g, '');
        if ($(element).val().length >= digit ) {
            element.value = element.value.substring(0, 11);
            $(element).css('border', '1px solid #D9D9D9');
        }
        sweetAlert("Invalid Input", "Please input number only and not greater than of 12 digits", "error");
    }else{
        $(element).css('border', '1px solid #D9D9D9');
    }
}
//# sourceMappingURL=frontend.js.map
