<div class="col-md-12">
  <div class="wrapper">
    <div class="row">
  <div class="col-md-12 m-t-10">
    <div class="instructions">
        <h4 id="header">Instructions<span class="pull-right"><i class="fa fa-window-minimize" id="action"></i></span></h4>
        <div id="msg">
            <p>Please add <strong>crm@trendle.io</strong> as an "alternative email address" sender in Seller Central. Go to your "Messages", then to "Alternate Address" or "Authorised Emails" (this depends on the User Interface shown to you in Seller Central), then add as an "Approved Sender". <br>Make sure to do this for all marketplaces where you are selling. If you do not do this then the emails will not reach your customers but you will still be charged. Your customers will not see the email as coming from this address. It will appear to them in the same way as if you sent them a message yourself from within seller central.
            </p>
        </div>
      </div>
   </div>
   <div class="col-lg-12 m-t-25 m-b-20">
        <a class="btn btn-primary" data-toggle="modal" data-target="#camapigntemplatemodal" data-controls-modal="your_div_id" data-backdrop="static" data-keyboard="false" href="#" id="addnewcampaignmodalbtn">Add Automatic Campaign</a>
      </div>
    </div>
    <div id="for-alert">
    </div>
    <div class="col-md-12 camgain_div">
      @foreach($campaigns as $campaign)
      <div id="campaign{{ $campaign->id }}">
      <div class="row">
          <span class="row col-md-2 campaign-name">{{ $campaign->campaign_name }}</span>
          <div class="col-md-6 campaign-actions">
              <a href="campaign/loadcampaign/{{ $campaign->id }}"><i class="rounded fa fa-pencil"></i></a>
              <a class="deleteCampaign" data-campaign-id="{{ $campaign->id }}" id="deleteCampaign" href="#"><i class="rounded fa fa-trash-o"></i></a>
              <input type="checkbox" id="campaign-switch" class="make-switch-radio" data-on-color="success" data-off-color="danger" data-campaign-id="{{ $campaign->id }}" {{ $campaign->is_active == 1 ? 'checked' : '' }} />
          </div>
      </div>
      <div class="row m-t-10">
          <span class="country_label">
            @foreach($campaign->campaign_country as $country)
                <span class="campaign_country">{{ $country->country->name }}</span>
            @endforeach
          </span>
          <table class="table table-condensed m-t-10 campaignlist">
            <thead>
              <tr>
                <th style="border-color:#00ADB5;">Subject</th>
                <th style="border-color:#00ADB5;">Trigger</th>
                <th style="border-color:#00ADB5;">After</th>
                <th style="border-color:#00ADB5;">Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($campaign->emails as $email)
              <tr>
                <td>{{ $email->template_name }}</td>
                <td>{{ $email->campaign_trigger != null ? $email->campaign_trigger->description : '' }}</td>
                <td>{{ $email->days_delay == 0 ? 'asap' : $email->days_delay . 'Days' }}</td>
                <td>{{ $email->is_active == 1 ? 'active' : 'inactive' }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
          <br>
      </div>
      </div>
      @endforeach
      </div>
  </div>
</div>
<div class="modal fade" id="camapigntemplatemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header text-center">
				<span style="font-size:18px">Choose your Template</span>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeaddnewcampaignmodalbtn">
          <span aria-hidden="true">&times;</span>
      </button>
			</div>
			<table cellspacing=0 cellpadding=0 class="table table-responsive table-colored table-condensed table-hover" id="templatelisttbl">
				<thead>
					<tr>
						<th>Template Name</th>
            <th>Date created</th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
</div>

<script>
$(document).ready(function(){
    $('#templatelisttbl').DataTable({

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
				"url": "campaign/campaigntemplatelist",
				'data': {"_token":"{{ csrf_token() }}"},
				'type': "POST"
			},
			"serverSide": true,
      "initComplete":function( settings, json ) {


				$('#templatelisttbl tbody tr').click( function(){
					var ct_id=$(this).attr('recid');

					location.href="campaign/newcampaign/" + ct_id;
				});
			},
      fnRowCallback: function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {

					$(nRow).attr('recid',aData.recid);
			},
      columns: [
            { data: 'templateNameColumn', name: 'templateNameColumn' },
            { data: 'dateCreatedColumn', name: 'dateCreatedColumn' }
        ]

    });

    $(".deleteCampaign").click(function(){
        $(this).blur();
        element = $(this);
        swal({
            title: 'Are you sure you want to delete this campaign?',
            type: 'error',
            showCancelButton: true,
            confirmButtonColor: '#EF6F6C',
            cancelButtonColor: '#ff9933',
            confirmButtonText: 'Delete'
        }).then(function () {
            var id = $(element).attr('data-campaign-id');
            // disable button to avoid double clicking
            $('.deleteCampaign' + id).prop('disabled', true);
            htmlStr = '<br />'
            htmlStr += '<div class="alert alert-success alert-dismissable">';
            htmlStr += '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
            htmlStr += 'Successfully deleted campaign #'+id+'.';
            htmlStr += '</div>';
            $.ajax({
                url: "/campaign/deleteCampaign",
                type: "post",
                data: {
                  'id': id,
                },
                success: function(response){
                  $("#campaign" + id).remove();
                  $("#for-alert").html(htmlStr);
                }
            });
        }, function (dismiss) {

        });
        return false;
    });

    //new Switchery(document.querySelector('#campaign-switch'), { size: 'small', color: '#ff5722', jackColor: '#fff' });

    $("#campaign-switch").on("switchChange.bootstrapSwitch", function () {
        campaignstatus = $(this);
        campaign_id = campaignstatus.attr('data-campaign-id');

        $.ajax({
            url: "/campaign/setStatus",
            type: "post",
            data: {
                id: campaign_id,
                status: campaignstatus.is(':checked') == true ? 1 : 0,
                _token: "{{ csrf_token() }}",
            },
            success: function(response){

            }
        });
    });
      // For instruction <windows></windows>
        $(".instructions #action").click(function(){
        $(".instructions #msg").toggle();
        $(".instructions #header").css('margin-bottom', '0px');

        if($(this).hasClass("fa-window-minimize")){
            $(this).removeClass("fa-window-minimize");
            $(this).addClass("fa-window-maximize");
            $(".instructions #action").css('top', '0px');
        } else {
            $(this).removeClass("fa-window-maximize");
            $(this).addClass("fa-window-minimize");
            $(".instructions #action").css('top', '-5px');
        }
      });
      //End of windows instructions
});
</script>
