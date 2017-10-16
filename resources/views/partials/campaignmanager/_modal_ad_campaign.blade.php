<div id="modalAddCampaign" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <strong>New Campaign</strong>
      </div>
      <div class="modal-body">
       
      	<div class="row">
  			<div class="col-md-3">
  				<div class="tab">
				  <button class="tablinks" onclick="addCampaignProcess(event, 'newCampTab')" id="defaultOpen">New Campaign</button>
				  <button class="tablinks" onclick="addCampaignProcess(event, 'AdGroupTab')">Ad Groups</button>
				  <button class="tablinks lastButton" onclick="addCampaignProcess(event, 'KeywordsTab')">Keywords</button>
				</div>
  			</div>

			<div class="col-md-9 tab_content">
				<div id="newCampTab" class="tabcontent">
					@include('partials.campaignmanager._new_campaign');
				</div>

				<div id="AdGroupTab" class="tabcontent">
				  <h3>Paris</h3>
				  <p>Paris is the capital of France.</p> 
				</div>

				<div id="KeywordsTab" class="tabcontent">
				  <h3>Tokyo</h3>
				  <p>Tokyo is the capital of Japan.</p>
				</div>
			</div>
      	</div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
<script type="text/javascript">
	function addCampaignProcess(evt, cityName) {
	    var i, tabcontent, tablinks;
	    tabcontent = document.getElementsByClassName("tabcontent");
	    for (i = 0; i < tabcontent.length; i++) {
	        tabcontent[i].style.display = "none";
	    }
	    tablinks = document.getElementsByClassName("tablinks");
	    for (i = 0; i < tablinks.length; i++) {
	        tablinks[i].className = tablinks[i].className.replace(" active", "");
	    }
	    document.getElementById(cityName).style.display = "block";
	    evt.currentTarget.className += " active";
	}

	document.getElementById("defaultOpen").click();
</script>