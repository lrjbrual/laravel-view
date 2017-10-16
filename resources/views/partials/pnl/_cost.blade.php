<div class="col-md-12 m-t-25">
  <div class="card card-outline-primary">
    <div class="card-header bg-primary">
       <span class="card-title">Costs</span>
       <span class="float-xs-right">
          <i class="fa fa-chevron-up"></i>
       </span>
    </div>
    <div class="card-block">
      <div class="table-responsive">
          <table cellspacing="0" cellpadding="0" class="costtable table table-bordered table_res dataTable no-footer">
              <thead>
                  <tr>
                      <th width="3%"><span class="row-details"></span></th>
                      <th>&nbsp;</th>
                      <th>&nbsp;</th>

                      @foreach ($mkp_c as $mkp_c_data)
                            <th width="10%">{{ $mkp_c_data->iso_3166_2 }}</th>
                      @endforeach
                      <th>Total</th>
                  </tr>
              </thead>

          </table>
      

{{--
      <div class="m-t-5">
          <table cellspacing="0" cellpadding="0" class="table table-bordered dataTable">
              <tr class="text-success">
                  <td><span class="row-details"></span></td>
                  <td><strong>Total Cost</strong></td>
                  <td>&nbsp;</td>
                  @foreach ($mkp_c as $mkp_c_data)
                        <td width="10%"></td>
                  @endforeach
                  <td></td>
              </tr>
          </table>
      </div> --}}

      </div>
    </div>
  </div>
</div>

<script>
var costtable;
$(document).ready(function(){
  $('#showbtn').click( function(){
    costtable.destroy();
    initialize_costtable();
  });
  initialize_costtable();

});


function initialize_costtable(){
  var date_from = $('#date_from').val();
  var date_to = $('#date_to').val();


  costtable = $('.costtable').DataTable({

    "bSort": false,
    "bPaginate" : false,
    "lengthChange": false,
    "searching": false,
    "dom": '<"top"flp>t<"bottom"ir><"clear">',
    "bInfo" : false,
    "bFilter": false,
    "processing": true,
    "bAutoWidth": false,
    "language": {
      "processing": '<b>Loading records... </b> '
    },
    "ajax":{
    	"url": "pnl/pnl_getpnlcosttable",
    	'data': {"_token":"{{ csrf_token() }}","date_from":date_from,"date_to":date_to},
    	'type': "POST"
    },
    "serverSide": true,
    "initComplete":function( settings, json ) {
      // $(".costtable thead").remove();
      console.log(settings);
      calcgrossvalues();
      refreshcosttablejs();
      calcgrossvalues();
      $('.pnl_table_gross_loading').hide();
    },
    fnRowCallback: function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
      // console.log();
      $(nRow).attr('rowlevel',aData.rowtype);
      if(aData.rowtype>1){

        $(nRow).hide();
      }else if(aData.rowtype==0){
        $(nRow).addClass('text-success');
        $(nRow).addClass('costabletotaltr');
      }
    }
    // columns: [
    //       { data: 'templateNameColumn', name: 'templateNameColumn' },
    //       { data: 'dateCreatedColumn', name: 'dateCreatedColumn' }
    //   ]

  });
}


</script>
