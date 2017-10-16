<ul class="nav nav-tabs" role="tablist" id="templatetablist">

	<li style="display:none;" ><a href="#tloading" aria-controls="home" role="tab" data-toggle="tab"></a></li>
	<?php
	$key=0;
		if(count($campaigntemplatedata)){
			$ctd_ctr=0;
			foreach($campaigntemplatedata as $ctd){
			$key++;
				$isClassActive='';
				if($ctd_ctr==0){
					$isClassActive='active';
				}
				if(isset($ctd->id)){
				?>
				<li class="nav-item actualtab" data-tab-index="{{ $key }}">
					<a class="nav-link {{ $isClassActive }}" href="#tab{{ $ctd->id }}" aria-controls="home" role="tab" data-toggle="tab">
						@if (isset($ctd))
							{{ $ctd->template_name }}
						@else
						  	New Message
						@endif
			 			&nbsp;<span class="fa fa-pencil editthis"></span>
					</a>
				</li>
				<?php
			}else{
				?>
					<li class="nav-item actualtab" data-tab-index="{{ $key + 1 }}">
						<a class="nav-link active" href="#tabn1" aria-controls="home" role="tab" data-toggle="tab">
							  	New Message
				 			&nbsp;<span class="fa fa-pencil editthis"></span>
						</a>
					</li>
					<?php
				}
				$ctd_ctr++;
			}

		}else{
			?>

			<li class="nav-item active actualtab" data-tab-index="{{ $key + 1 }}">
				<a class="nav-link active" href="#tab{{ $tab_index }}" aria-controls="home" role="tab" data-toggle="tab">
							New Message
					&nbsp;<span class="fa fa-pencil editthis"></span>
				</a>
			</li>

			<?php

		}
	?>





	<li class="nav-item"><a class="nav-link" id="newtab" ><span class="add-template fa fa-plus"></span></a></li>

</ul>

<div class="tab-content" id="templatetabcontentlist">


<div role="tabpanel" class="tab-pane in" id="tloading">
Please wait...
</div>

<?php

if(count($campaigntemplatedata)){


	$ctd_ctr=0;
	foreach($campaigntemplatedata as $ctd){
		$isClassActive='';
		if($ctd_ctr==0){
			$isClassActive='active';
		}

		$arr=array(
			'campaign_trigger'=>$campaign_trigger,
			'tab_index'=>$tab_index,
			'campaigntemplatedata'=>$ctd,
			'isClassActive'=>$isClassActive,
			'mode'=>$mode,
		);

		//blank
		// echo $ctd->id;
		echo view('trendle.campaign.partials._campaign_email_tab_content_section',$arr);
		$ctd_ctr++;
	}
}else{
	$arr=array(
		'campaign_trigger'=>$campaign_trigger,
		'tab_index'=>$tab_index,
		'campaigntemplatedata'=>$campaigntemplatedata,
		'isClassActive'=>'active',
		'mode'=>$mode,
	);
	//
	echo view('trendle.campaign.partials._campaign_email_tab_content_section',$arr);
}
?>
</div>
<script>
var uploader=[];
var tabcount=0;
var tabcountctr=0;
var isLast=false;
var hasUpload = 0;

function save_campaign(){


	var cid = $('#campaign_id').val();
	var campaignname = $('#campaignname').text().trim();
	var mkp = $('#mkpt').val();
	$.ajax({
	type: "POST",
	"url": "{{ url('/campaign/savecampaign') }}",
	data: {
		"_token":"{{ csrf_token() }}",
		"campaignname" : campaignname,
		"mkp" : mkp,
		"cid" : cid
	      // "campaigntype" : campaigntype
	},
	cache: false,
	success: function(r)
	{
		// console.log(r);

		save_all_templates(r);

	}
	});

}

function save_all_templates(campaign_id){
			var counter = $("#templatetablist li.actualtab").length + 2;
			tabcount=counter-2;
			tabcountctr=0;


			var actualtabcontent_count = $('.actualtabcontent').length;
			$('.actualtabcontent').each(function(index){
				var thisthis=this;

				setTimeout( function () {


					var thistab = $(thisthis).closest('.tab-pane');
					mode='savetemp';
					$(thistab).find('.savechanges').attr('disabled', true);
					$(thistab).find('.savetemp').attr('disabled', true);

					var new_att = [];
					var old_att = [];
					var tabindexname = $(thisthis).attr('tabindexname');

					if (index === actualtabcontent_count - 1) {
						isLast=true;
			    }

					savetemplate(thistab,new_att,old_att,mode,campaign_id,uploader[tabindexname],tabindexname,hasUpload,isLast);
			 	}, 100);

			});
}

function savetemplate(thistab,new_att,old_att,mode,new_cid,uploader_resource,tabindexname,hasUpload,isThisLast){
	var cid;
	if(new_cid==undefined){
		cid = $('#campaign_id').val();
	}else{
		cid = new_cid;
		$('#campaign_id').val(new_cid);
	}

	var tid =  $(thistab).attr('tid');
	var tab_id = $(thistab).attr('id');

	var templatename = $('a[href="#'+tab_id+'"]').text().trim();
	var delayval = $(thistab).find('.delay:checked').val();
		if(delayval=='custom'){
			delayvalid = $(thistab).find('.delay:checked').attr('id');

			delayval = $(thistab).find('input[for="'+delayvalid+'"]').val();

		}

	var isactive=0;
	if($(thistab).find('.isactive:checkbox:checked').length > 0){
		isactive=1;
	}

	var eventval = $(thistab).find('.event:checked').val();

	var subject = $(thistab).find('.subj_inp').val();
	var body = $(thistab).find('.mailbody').summernote('code');

	var to = $(thistab).find('.sampleemail').val();

	var loadmode = $(thistab).find('.loadmode').val();


	if(mode=='savetemp'){

	}else{
		if(mode=='preftemp'){
			growltitle="Saving template...";
		}else if(mode=='sendtest'){
			growltitle="Sending test email...";
		}

	}



	var growl_style = 'notice';
	var growl_msg = '';
	$.ajax({
	type: "POST",
	"url": "{{ url('campaign/savetemplate') }}",
	data: {
		"_token":"{{ csrf_token() }}",
		"key" : "1",
		"templatename" : templatename,
		"delayval" : delayval,
		"eventval" : eventval,
		"subject" : subject,
		"body" : body,
		"att" : new_att,
		"old_att" : old_att,
		"mode" : mode,
		"loadmode" : loadmode,
		"cid" : cid,
		"tid" : tid,
		"isactive" : isactive,
		"to" : to

	},
	cache: false,
	success: function(r)
	{
		console.log(r);

		tabcountctr++;
		var pass_tabcountctr=tabcountctr;
		var tempid=r;

		var old_att = [];
		var arr = {};
		$('#preincludedattt'+tabindexname).children('.preincludedatt').each(function(){
		 	arr = {};
			arr['id']=$(this).attr('preinc-id');
			arr['loadmode']=$(this).attr('loadmode');
			old_att.push(arr);
		});

		$.ajax({
		type: "POST",
		"url": "{{ url('campaign/manageattachment') }}",
		'data': {	  "mode":mode, "tid":tempid, "old_atts":old_att ,"_token":"{{ csrf_token() }}" },
		cache: false,
		success: function(r1)
		{
					if(mode=='savetemp'){
						$(thistab).attr('temp_tid_template','0');
						$(thistab).attr('temp_tid_email',tempid).promise().done(function(){
					});
					}else if(mode=='preftemp'){
						$(thistab).attr('temp_tid_email','0');
						$(thistab).attr('temp_tid_template',tempid).promise().done(function(){

						});
					}

					uploader_resource.startUpload();


	        if(mode=='savetemp'){
								if((hasUpload==0)){
									location.href= "{{ url('campaign/loadcampaign') }}" + '/' + cid;
								}
	        }else if(mode=='preftemp'){
	            growltitle="Template saved...";

							$(this).blur();
			        element = $(this);
			        swal({
			            title: 'Template saved',
			            type: 'success',
			        });
			        return false;

	        }else if(mode=='sendtest'){
	        }
		}
		});
	}
	});
}


$(document).ready(function() {

	var ontabprocess = true;

		$('#newtab').on('click', function(e){
			if (ontabprocess) {
			ontabprocess = false;
			tabIndexArray = [];
			slot = [1, 2, 3, 4, 5];
			availableSlots = [];

			$("li[data-tab-index]").each(function(){
					tabIndexArray.push(parseInt($(this).attr('data-tab-index')));
			});

			$.grep(slot, function(el) {
	        if ($.inArray(el, tabIndexArray) == -1) {
						availableSlots.push(el)
					};
			});

			givenSlot = Math.min.apply(Math, availableSlots);

			//console.log(givenSlot);

			var tab_index = givenSlot;
			if($(".actualtab").length < 4){
				var tabId = 'n'+tab_index;
				$(this).closest('li').before('<li class="nav-item actualtab unbind" data-tab-index="'+tab_index+'"><a class="nav-link" href="#tab'+tabId+'" aria-controls="home" role="tab" data-toggle="tab">New Message &nbsp;<span class="fa fa-pencil editthis"></span></a></li>');
				$('.unbind').hide();
				$.ajax({
				type: "POST",
				"url": "{{ url('campaign/newtabcontent') }}"  + '/' + tabId,
				'data': {	"_token":"{{ csrf_token() }}" , tab_index:tabId },
				cache: false,
				success: function(r)
				{
					$('.nav-link').removeClass('active');

					$('#templatetabcontentlist').append(r);
					$('.unbind').show();
					var curTab = $('#templatetablist .unbind');
					$(curTab).removeClass('unbind');

					$('#templatetablist li:nth-child(' + (tab_index+1) + ') a').click();

					ontabprocess = true;
				}
				}).promise().done(function(){
					$('#templatetablist li:nth-child(' + (tab_index+1) + ') a').click();
					RefreshSomeEventListener();
				});
				// RefreshSomeEventListener();
				if(tab_index==4){
					$(this).parent('li').hide();
				}else{
					$(this).parent('li').show();
				}
			}
			};
		});

});
</script>
