$(document).ready(function() {
    var table = $('#adsperformance_table');

    $('#filterbtn').click(function(){
        $('#adperformance_filter').toggle();

        if($('#adperformance_table').hasClass('col-md-12')){
            $('#adperformance_table').removeClass('col-md-12');
            $('#adperformance_table').addClass('col-md-8');

            $('#filter_caret').removeClass('fa-caret-up');
            $('#filter_caret').addClass('fa-caret-down');
        } else {
            $('#adperformance_table').removeClass('col-md-8');
            $('#adperformance_table').addClass('col-md-12');

            $('#filter_caret').removeClass('fa-caret-down');
            $('#filter_caret').addClass('fa-caret-up');
        }
    });

    $('#start_date').datepicker({
        todayHighlight: true,
        autoclose: true,
        orientation: "auto",
    }).on('changeDate', function(e){
        ts = $(this).datepicker('getDate');
        dateString = ts.getMonth() + '/' + ts.getDate() + '/' + ts.getFullYear();

        $('#start_date_val').html(dateString);
    });

    $('#end_date').datepicker({
        todayHighlight: true,
        autoclose: true,
        orientation: "auto",
    }).on('changeDate', function(e){
        ts = $(this).datepicker('getDate');
        dateString = ts.getMonth() + '/' + ts.getDate() + '/' + ts.getFullYear();

        $('#end_date_val').html(dateString);
    });

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