<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/datepicker/css/bootstrap-datepicker.min.css')}}" />
<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/chosen/css/chosen.css')}}"/>
<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/tipso/css/tipso.min.css')}}"/>
<div class="row">
	<div class="col-md-12">
		<div class="col-md-12 m-t-20">
			<button data-toggle="modal" data-target="#modalAddCampaign" data-show="false" class="btn btn-sm btn-primary no-radius pull-right newcamp"><i class="fa fa-plus"></i> New Campaign</button>
		</div>

		<div class="col-md-12">
			<div class="col-md-12 newParentCampaignContainer dontdisplay">
				<div class="col-md-8 m-t-20 m-b-20 push-md-2">
	  				<div class="tab">
					  <span class="tablinks button" id="newCampaginBtn">New Campaign</span>
					  <span class="tablinks button" id="adGroupTabBtn">Ad Groups</span>
					  <span class="tablinks button lastButton" id="keywordTabBtn">Keywords</span>
					</div>

					<div class="col-md-12 tab_content">
						<div id="newCampTab" class="tabcontent m-b-20">
						  @include('partials.campaignmanager._new_campaign')
						</div>

						<div id="AdGroupTab" class="tabcontent m-b-20">
						  @include('partials.campaignmanager._ad_group')
						</div>

						<div id="KeywordsTab" class="tabcontent m-b-20">
						  @include('partials.campaignmanager._keywords')
						</div>
					</div>

				</div>
			</div>
		</div>

		<div class="col-md-12 m-t-20">
			<div class="card">
                <div class="card-block">
                    <div class="col-md-12">
                    <div class="loadingTableContainer dontdisplay">
                    <div class="dataTables_processing2" style="display:block;z-index: 998;top:50%;"> <b>Loading records </b><i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span></div>
                  </div>
                    	<table cellspacing=0 cellpadding=0 class="table table-striped" id="adscampaignmanager_table" style="width: 100%">
		                    <thead>
			                    <tr>
			                        <th class="th-nowrap" style="min-width:60px">Country</th>
			                        <th class="th-nowrap" style="min-width:65px"></th>
			                        <th>Name</th>
			                        <th>Daily Budget</th>
			                        <th>Status</th>
			                    </tr>
			                </thead>
			            </table>
                    </div>
                </div>
            </div>
		</div>

	</div>
</div>
<script type="text/javascript">
	var bs = '{{ $bs }}';
</script>
<!-- <script type="text/javascript" src="js/adscampaginmanager.js"></script> -->
<script type="text/javascript" src="{{asset('assets/vendors/chosen/js/chosen.jquery.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/tipso/js/tipso.min.js')}}"></script>
<script type="text/javascript" src="{{ url('js/campaignmanager.js') }}"></script>