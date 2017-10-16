<!-- Content Header (Page header) -->
<header class="head">
    <div class="main-bar row">
        <div class="col-lg-6 col-sm-4">
            <h4 class="nav_top_align"><i class="fa fa-envelope-o"></i>
             {!! (Request::segment(2) == 'newcampaign') ? 'Campaign':'Edit Campaign' !!}
             </h4>
        </div>
    	@include('partials._helpiconWithTour')
    </div>
</header>

<h3 class="col-md-12 m-t-25">
    <span id="campaignname" class="text-orange">
      @if ((isset($campaigntemplatedata))&&($campaign_id!=0))
        {{ $campaigntemplatedata{0}->campaign_name }}
      @else
          New Campaign
      @endif
      <i class="fa fa-pencil editthis"></i></span>

      <input type="hidden" value="{{ $campaign_id }}" id="campaign_id">
      <input type="hidden" value="" id="exec_mode">
</h3>

<div class="col-md-12">
  <div class="container">
      <div class="row">
        @include('trendle.campaign.partials._campaign_email_countrydiv')
      </div>

      <div class="row m-t-10">
        @include('trendle.campaign.partials._campaign_email_tab_section')
      </div>
  </div>
</div>

<div class="modal fade" id="modal-deleteMessage" role="dialog" aria-labelledby="modalLabeldanger">
	<div class="modal-dialog" role="document">
			<div class="modal-content">
					<div class="modal-header bg-danger">
							<h4 class="modal-title text-white" id="modalLabeldanger">Delete Message</h4>
					</div>
					<div class="modal-body">
							Are you sure you want to delete this message?
					</div>
					<div class="modal-footer">
							<button class="btn btn-danger" id="deleteMessageConfirmed" tid="0">Yes</button>
							<button class="btn btn-default" data-dismiss="modal">No</button>
					</div>
			</div>
	</div>
</div>

<div class="modal fade" id="modal-cancelMessage" role="dialog" aria-labelledby="modalLabeldanger">
	<div class="modal-dialog" role="document">
			<div class="modal-content">
					<div class="modal-header bg-danger">
							<h4 class="modal-title text-white" id="modalLabeldanger">Delete Message</h4>
					</div>
					<div class="modal-body">
							Are you sure you want to delete this message?
					</div>
					<div class="modal-footer">
							<button class="btn btn-danger" id="cancelMessageConfirmed" tid="">Yes</button>
							<button class="btn btn-default" data-dismiss="modal">No</button>
					</div>
			</div>
	</div>
</div>

<script>
RefreshSomeEventListener();
function RefreshSomeEventListener() {


	$(".editthis").off();
	$('.editthis').click( function(e){
		e.preventDefault()
		var a = $(this).parent().text().trim();
		var thisparent = $(this).parent();
		$(this).parent().html('<input type="text" class="savethisonblur" value="'+a+'">').promise().done(function(){
			$('.savethisonblur').focus();
		});
		RefreshSomeEventListener();

	});

	$(".savethisonblur").off();

	$('.savethisonblur').blur( function(e){
		var thisval = $(this).val();
		$(this).parent().html(thisval + ' <i class="fa fa-pencil editthis"></i>');
		RefreshSomeEventListener();
	});




  $(".savetemp").off();
  $('.savetemp').click( function(e){

    $('#exec_mode').val('savetemp');
    var thistab = $(this).closest('.tab-pane');
    RefreshSomeEventListener();
    mode='preftemp';


      var new_att = [];
      var old_att = [];

      var tabindexname = $(thistab).attr('tabindexname');
      var hasUpload = 0;
      isLast=true;
      savetemplate(thistab,new_att,old_att,mode,undefined,uploader[tabindexname],tabindexname,hasUpload,isLast);

    // }

    // RefreshSomeEventListener();

  });


  $(".savechanges").off();
  $('.savechanges').click( function(e){
      $('#exec_mode').val('saveeml');
      save_campaign();
	});

  $('.ajax-file-upload-cancel').click( function(e){
		$(this).parent().remove();
	});
}
</script>
