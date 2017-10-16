<div role="tabpanel" class="row m-t-20 tab-pane in {{ $isClassActive }} actualtabcontent" id="tab{{ $campaigntemplatedata->id or $tab_index }}" tid="{{ $campaigntemplatedata->id or 0 }}" tabindexname="{{ $campaigntemplatedata->id or $tab_index }}">
<input type="hidden" class="loadmode" id="loadmode{{ $campaigntemplatedata->id or $tab_index }}" value="{{ $mode }}">
		<div class="col-lg-12 col-offset-1">
			<div class="col-lg-6 col-md-6 triggerdelaydiv">
				<h4>Send Message to Customer</h4>
				<div class="row col-lg-12">
					<div class="switch-field">

            <?php
              $checked_asap='';
              $checked_custom='';
              $checked_custom_val='';
            ?>
            @if (isset($campaigntemplatedata))
              <?php
                $days_delay='';
                if(isset($campaigntemplatedata->days_delay)){
                   $days_delay = $campaigntemplatedata->days_delay;
                }


               if($days_delay>0){
                 $checked_custom='checked';
                 $checked_custom_val=$days_delay;
               }else if($days_delay===0){
                 $checked_asap='checked';
               }
              ?>
            @endif


						<span class="messageDayTour">
            <input class="dontdisplay delay" type="radio" id="d0t{{ $campaigntemplatedata->id or $tab_index }}" name="delayt{{ $campaigntemplatedata->id or $tab_index }}" value="0" {{ $checked_asap }}/>
            <label for="d0t{{ $campaigntemplatedata->id or $tab_index }}" class="btn btn-secondary delaylabel" style="padding:10px;">asap</label>
            <input class="dontdisplay delay" type="radio" id="dxt{{ $campaigntemplatedata->id or $tab_index }}" name="delayt{{ $campaigntemplatedata->id or $tab_index }}" value="custom" {{ $checked_custom }}/>
            <label for="dxt{{ $campaigntemplatedata->id or $tab_index }}" class="btn btn-secondary delaylabel">
              <input id="customdayt" class="customday"  type="number" for="dxt{{ $campaigntemplatedata->id or $tab_index }}" value="{{ $checked_custom_val }}" max="100" min="0" > days
            </label>
            </span>
					</div>
				</div>
			</div>

			<div class="isactivediv pull-right">
			<span class="switchTour">
      @if ($mode == 'load')
        @if ($campaigntemplatedata->is_active == 1)
          <input type="checkbox" id="status{{ $campaigntemplatedata->id or $tab_index }} isactivet" name="isActive" class="make-switch-radio isactive" data-on-color="success" data-off-color="danger" checked />
        @else
          <input type="checkbox" id="status{{ $campaigntemplatedata->id or $tab_index }} isactivet" name="isActive" class="make-switch-radio isactive" data-on-color="success" data-off-color="danger" />
        @endif
      @else
        <input type="checkbox" id="status{{ $campaigntemplatedata->id or $tab_index }} isactivet" name="isActive" class="make-switch-radio" data-on-color="success" data-off-color="danger" checked />
      @endif
      </span>
			&nbsp;&nbsp;&nbsp;

      @if($mode =='load')
          <i class="rounded fa fa-trash-o delete-template"></i>
			@else
          <i class="rounded fa fa-trash-o cancel-template" id="btnCancel{{ $campaigntemplatedata->id or $tab_index }}" data-template-id="{{ $campaigntemplatedata->id or $tab_index }}"></i>
			@endif
			</div>
			<br/>
			<br/>

			<div class="col-lg-12">
				<h4>After Order Has Been</h4>
				<div class="row col-lg-12">

				<div class="switch-field">
        <span class="sendMessageTour">
					<?php
					foreach($campaign_trigger as $dd2){
						$dstatid=$dd2->id;
            $ct_checked='';
            if(isset($campaigntemplatedata->campaign_trigger_id)){

              if($campaigntemplatedata->campaign_trigger_id===$dstatid){
                $ct_checked='checked';
              }
            }

						$ii='';
						switch($dstatid){
							case 1;
								$ii='<i class="fa fa-check-circle-o"></i>';
							break;
							case 2;
								$ii='<i class="fa fa-truck"></i>';
							break;
							case 3;
								$ii='<i class="fa fa-dropbox"></i>';
							break;
						}
						?>
            <input class="dontdisplay event" type="radio" id="s{{ $dstatid }}t{{ $campaigntemplatedata->id or $tab_index }}" name="statt{{ $campaigntemplatedata->id or $tab_index }}" value="{{ $dstatid }}" {{ $ct_checked }}/>
						<label for="s{{ $dstatid }}t{{ $campaigntemplatedata->id or $tab_index }}" class="btn btn-secondary statlabel">{!! $ii !!} {{ $dd2->description }}</label>
            <?php
					}
					?>
        </span>
				</div>
				</div>
			</div>
			<br/>
			<br/>

      <div class="row">
  			<div class="col-md-12 emailTourBody">
         <div class="col-lg-12">
            <br />
            <h4>Subject</h4>
            <div class="panel" style="margin-bottom:0px;">
              <div class="input-group">
                  <input id="subjt{{ $campaigntemplatedata->id or $tab_index }}" type="text" class="pull-right form-control subj_inp" value="{{ $campaigntemplatedata->subject or '' }}">
                  <span class="input-group-btn autofillTour">
                    <button class="btn btn-orange subject-autofill-tags" type="button">
                    <span data-subject-id="subjt{{ $campaigntemplatedata->id or $tab_index }}" class="pull-right ">Auto Fill Tags</span>
                    </button>
                  </span>
              </div>
            </div>
          </div>



          <div class="col-md-12">
            <h4>Body</h4>
              <div id="bodyt{{ $campaigntemplatedata->id or $tab_index }}" class="col-lg-12 mailbody"></div>
          </div>
      </div>
      </div>
			<div class="col-lg-12">

				<div class="form-group attachment_label">
					<label  class=" control-label"  style="text-align:left;">Attachment: </label>
					<span class="btn btn-sm btn-default btn-file spanbtn">

						<div class="multi_attach fileuploadert{{ $campaigntemplatedata->id or $tab_index }}">Choose file</div>

					</span>


					<div class="col-lg-12 control-label attachment_div" style="text-align:left;" id="attachment_divt{{ $campaigntemplatedata->id or $tab_index }}">
          </div>

					<div class="col-lg-12 control-label " style="text-align:left;" id="preincludedattt{{ $campaigntemplatedata->id or $tab_index }}" >

            <?php
              // echo $mode;
              if(isset($campaigntemplatedata->att_data)){
                foreach($campaigntemplatedata->att_data as $dd4){
                  // print_r($dd4);
                  echo '<div loadmode="'.$mode.'" class="ajax-file-upload-statusbar preincludedatt" preinc-id="'.$dd4->id.'"><div class="ajax-file-upload-filename">'.$dd4->original_filename.'</div><div class="ajax-file-upload-cancel" style=""> <i class="fa fa-times" style="color:#337ab7"></i></div></div>';

                }

              }
            ?>
					</div>
				</div>
			</div>

			<br/>
			<br/>

			<div class="col-lg-12">

				<button class="btn btn-primary sendtest">Send Test</button>
				<div class="sendtestdiv" style="display: inline-block" >
					<input type="text" class="sampleemail">
					<button class="btn btn-primary gosendtest"  id="gosendtestt{{ $campaigntemplatedata->id or $tab_index }}">Send</button>
				</div>


				<button class="btn btn-orange pull-right savetemp">Save as Template</button>
				<button class="btn btn-orange pull-right savechanges_forshowbtn savechanges m-r-10">Save Changes</button>


			</div>
            <input type="hidden" id="customliststatus" />
            <input type="hidden" id="attachments" />
            <input type="hidden" id="actionperform" />
		</div>

</div>


<div class="modal fade" id="modal-subject-tags" role="dialog" aria-labelledby="modalLabeldefault">
	<div class="modal-dialog" role="document">
			<div class="modal-content">
					<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">×</span>
							</button>
							<h4 class="modal-title modal-subject-tags-title" id="modalLabeldefault">Insert Auto Fill Tag</h4>
					</div>
					<div class="modal-body" id="modal-subject-tags-body"></div>
			</div>
	</div>
</div>

<script>
$(document).ready(function() {
  var radioState;
	$('.delay,.event').click(function(){
		if (radioState === this) {
        this.checked = false;
        radioState = null;
    } else {
        radioState = this;
    }
	});


  uploader['{{ $campaigntemplatedata->id or $tab_index }}']='';
  $('.sendtestdiv').hide();
	$('.sendtest').click( function(e){

		$(this).siblings('.sendtestdiv').show();
	});

  $( ".customday" ).keyup(function(e) {
		var max = 100;
		var min = 0;
		var thisval = $(this).val();
		if (thisval > max)
		{
			$(this).val(max);
		}else if ((thisval < min))
		{
			$(this).val(min);
		}


		if((e.keyCode==189)||(e.keyCode==109)) {
			$(this).val(min);
		}else if((e.keyCode==101)||(e.keyCode==69)) {
			$(this).val(min);
		}
    });


    $(".delete-template").unbind().click(function(){
				var tid = $(this).closest('.tab-pane').attr('tid');
				$("#modal-deleteMessage").modal("show");
				$("#deleteMessageConfirmed").attr("tid",tid);
    });

		$("#deleteMessageConfirmed").click(function(){
				var tid = $(this).attr('tid');
				var cid = $('#campaign_id').val();

				$.ajax({
						url: "{{ url('campaign/removecampaignemail') }}",
						type: "POST",
						data: {
								"tid": tid,
								"cid": cid,
								"_token":"{{ csrf_token() }}"
						},
						success: function(r){
								location.reload();
						}
				});
		});

		$(".cancel-template").unbind().click(function(){
				var tid = $(this).attr("data-template-id");

				$("#modal-cancelMessage").modal("show");
				$("#cancelMessageConfirmed").attr("tid",tid);
		});

		$("#cancelMessageConfirmed").click(function(){
				var tid = $(this).attr("tid");
				var tabId = "#tab" + tid;
				var prevTab = $("a[href='#tab" + tid + "']").parents("li").prev("li").children("a");

				$("a[href='#tab" + tid + "']").parents('li').remove('li');
				$(tabId).remove();

				$("#modal-cancelMessage").modal("hide");
				prevTab.tab('show');

				if ($(".actualtab").length < 5) {
						$('#newtab').parent('li').show();
				}
		});

      var mode='';
    	var filectr=0;
    	uploader['{{ $campaigntemplatedata->id or $tab_index }}'] = $(".fileuploadert{{ $campaigntemplatedata->id or $tab_index }}").uploadFile({
        "url": "{{ url('campaign/doupload') }}",
        formData:{"_token":"{{ csrf_token() }}"},
    		fileName:"myfile"
    		,maxFileSize:'25000000'
    		// ,autoSubmit:true
    		,showFileSize:false
    		,autoSubmit:false
    		,showError:false
    		,allowDuplicates:false
    		,dragDrop:false
    		,multiple:true
    		,showProgress:true
    		,showFileCounter:false
    		,showAbort:false
    		,statusBarWidth:'auto'
    		,cancelStr:' <i class="fa fa-times" style="color:#337ab7"></i>'
    		,uploadStr:'<div class="btn btn-primary btn-file" style="border-radius: 0.25rem;"><i class="fa fa-paperclip"></i>  <span class="hidden-xs">Browse …</span></div>'
    		,showQueueDiv: "attachment_divt{{ $campaigntemplatedata->id or $tab_index }}"
        ,dynamicFormData: function()
        {
            //var data ="XYZ=1&ABCD=2";
            var thistab = $('#tab{{ $campaigntemplatedata->id or $tab_index }}');
            var temp_tid_email = $(thistab).attr('temp_tid_email');
            var temp_tid_template = $(thistab).attr('temp_tid_template');
            var exec_mode = $('#exec_mode').val();
            var dynamicFormUploadData = {"temp_tid_email":temp_tid_email,"temp_tid_template":temp_tid_template,'exec_mode':exec_mode};
            return dynamicFormUploadData;
        }
    		,onSelect:function(files)
    		{

          $('.ajax-file-upload-cancel').click( function(e){
        		$(this).parent().remove();
        	});
          hasUpload++;

    			return true; //to allow file submission.
    		}
    		,onCancel: function(files,pd)
    		{
          hasUpload--;
    		}
    		,onError: function(files,status,errMsg,pd)
    		{

    		},
    		onSubmit:function(files)
    		{
    		},
    		onSuccess:function(files,data,xhr,pd)
    		{
    		}
    		,afterUploadAll:function(obj)
    		{
          hasUpload = 0;

    			var attachment_ids = obj.getResponses();
          var thistab = $('#tab{{ $campaigntemplatedata->id or $tab_index }}');
          var exec_mode = $('#exec_mode').val();
    			att_id = [];
    			att_name = [];

          if(attachment_ids.length==1){
            objson = JSON.parse(attachment_ids);
            att_mode = objson.mode;
          }else{
            objson = JSON.parse(attachment_ids[0]);
            att_mode = objson.mode;
          }

    			for (var i = 0; i < attachment_ids.length; i++){
    				att = JSON.parse(attachment_ids[i]);
    				att_id.push(att[0].id);
    				att_name.push(att[0].oldname);

            htmlc='<div loadmode="'+att_mode+'" class="ajax-file-upload-statusbar preincludedatt" preinc-id="' +att[0].id+ '"><div class="ajax-file-upload-filename">'+att[0].oldname+'</div><div class="ajax-file-upload-cancel" style=""> <i class="fa fa-times" style="color:#337ab7"></i></div></div>';

  					$('#preincludedattt{{ $campaigntemplatedata->id or $tab_index }}').append(htmlc);
    			}
          $('.ajax-file-upload-cancel').click( function(e){
               $(this).parent().remove();
          });

          if((tabcountctr==tabcount)&&(exec_mode=='saveeml')){
            var cid = $('#campaign_id').val();
            location.href= "{{ url('campaign/loadcampaign') }}" + '/' + cid;
          }else if(exec_mode=='sendtest'){

            var tabindexname = $(thistab).attr('tabindexname');

            var old_att = [];
        		var arr = {};
        		$('#preincludedattt'+tabindexname).children('.preincludedatt').each(function(){
        		 	arr = {};
        			arr['id']=$(this).attr('preinc-id');
        			arr['loadmode']=$(this).attr('loadmode');
        			old_att.push(arr);
        		});

            var to = $(thistab).find('.sampleemail').val();
            var subject = $(thistab).find('.subj_inp').val();
            var body = $(thistab).find('.mailbody').summernote('code');
            // console.log(old_att);
            $.ajax({
            type: "POST",
            "url": "{{ url('campaign/sendtest') }}",
            'data': {	"to":to, "subject":subject, "body":body, "old_atts":old_att ,"_token":"{{ csrf_token() }}" },
            cache: false,
            success: function(r2)
            {
              console.log(r2);

            }
            });

          }


  				uploader['{{ $campaigntemplatedata->id or $tab_index }}'].reset();



    		}
    		,onLoad:function(obj){

          $('.sendtest').click( function(e){

        		$(this).siblings('.sendtestdiv').show();
        	});

          $('#gosendtestt{{ $campaigntemplatedata->id or $tab_index }}').click( function(e){
            $('#exec_mode').val('sendtest');

            var thisthis =this;

            mode='sendtest';
            var thistab = $(thisthis).closest('.tab-pane');
            var tabindexname = $(thistab).attr('tabindexname');


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
            'data': {	  "mode":mode, "tid":'0', "old_atts":old_att ,"_token":"{{ csrf_token() }}" },
            cache: false,
            success: function(r1)
            {

              var u = uploader['{{ $campaigntemplatedata->id or $tab_index }}'];
              if(hasUpload>0){
                u.startUpload();
              }else{

                var tabindexname = $(thistab).attr('tabindexname');

                var old_att = [];
            		var arr = {};
            		$('#preincludedattt'+tabindexname).children('.preincludedatt').each(function(){
            		 	arr = {};
            			arr['id']=$(this).attr('preinc-id');
            			arr['loadmode']=$(this).attr('loadmode');
            			old_att.push(arr);
            		});

                var to = $(thistab).find('.sampleemail').val();
                var subject = $(thistab).find('.subj_inp').val();
              	var body = $(thistab).find('.mailbody').summernote('code');
                // console.log(to);
                $.ajax({
                type: "POST",
                "url": "{{ url('campaign/sendtest') }}",
                'data': {	"to":to, "subject":subject, "body":body, "old_atts":old_att ,"_token":"{{ csrf_token() }}" },
                cache: false,
                success: function(r)
                {
                  console.log(r);

                }
            		});

              }
            }
        		});
            $(this).parent('.sendtestdiv').hide();

          });
    		}


    	});

  var TagsButton = function (context) {
      var ui = $.summernote.ui;

      var button = ui.button({
        contents: 'Auto Fill Tags',
        click: function () {
					$("#modal-subject-tags").modal("show");
					$("#modal-subject-tags-body").html("<div id='subject-tags' data-body-id='bodyt{{ $campaigntemplatedata->id or $tab_index }}'></div>");
					$("#subject-tags").load('{{ URL::to('/') }}/campaign/emailbodytags');
        }
      });

      return button.render();
  }

  var sn = $('.mailbody').summernote({
		dialogsInBody: true,
		height: 300,                 // set editor height
		minHeight: 300,             // set minimum height of editor
		maxHeight: 300,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'hr']],
            ['view', ['fullscreen', 'codeview']],
            ['help', ['help']],
            ['tags', ['tags']]
        ],
        buttons: {
             tags: TagsButton
        },
		callbacks: {

		}
	});

  <?php
  $eb='';
  $eboutput='';
  if(isset($campaigntemplatedata->email_body)){
    $eb = $campaigntemplatedata->email_body;
  }
	if(isset($campaigntemplatedata)){
		$eboutput=str_replace('\'', '\\\'', $eb );
		$eboutput = str_replace(array("\n","\r"), '', $eboutput);
	}
  ?>
  $('#bodyt{{ $campaigntemplatedata->id or $tab_index}}').summernote('code','<?php echo $eboutput ?>');

  $(".subject-autofill-tags").unbind().click(function(){
			$("#modal-subject-tags").modal("show");
			$("#modal-subject-tags-body").html("<div id='subject-tags' data-template-subject-id='" + $(this).attr('data-subject-id') + "'></div>");
			$("#subject-tags").load('{{ URL::to('/') }}/campaign/emailtags');
  });



	$(function(){
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
});
</script>
