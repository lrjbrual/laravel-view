    <fieldset class="m-t-35">

        <div class="row">
            <div class="col-md-12 newCampaignContainer">

                <div class="form-group row">
                    <div class="col-lg-3 ">
                        <label for="" class="col-form-label">Campaign Name</label>
                    </div>
                    <div class="col-lg-9">
                        <div class="input-group">
                            <input type="text" id="newCampaign_campName" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-lg-3 ">
                        <label for="" class="col-form-label">Campaign Type</label>
                    </div>
                    <div class="col-lg-9">
                        
                        <label for="auto" class="custom-control custom-radio">
                            <input id="auto" name="camp_type" data-val="auto" type="radio" class="custom-control-input campaign_type" checked>
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">Create Automatic Campaign</span>
                        </label><br>
                        <label for="manual" class="custom-control custom-radio">
                            <input id="manual" name="camp_type" data-val="manual" type="radio" class="custom-control-input campaign_type">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">Create Manual Campaign</span>
                        </label>
                           
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-lg-3 ">
                        <label for="" class="col-form-label"></label>
                    </div>
                    <div class="col-lg-9">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" id="is_link" class="custom-control-input is_link">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description lblLink">Link to existing manual campaign</span>
                        </label><br>
                        <div class="existing_manual">
                            {!! Form::select('targetingtype', $camp_auto, old('id'),  ['class' => 'form-control multiple_select  existing_manual_select','required','multiple' ,'id'=>'']) !!}
                        </div>
                        <div class="existing_auto">
                            {!! Form::select('targetingtype', $camp_manual, old('id'),  ['class' => 'form-control multiple_select  existing_auto_select','required','multiple' ,'id'=>'']) !!}
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-lg-3 ">
                        <label for="" class="col-form-label">Country</label>
                    </div>
                    <div class="col-lg-9">
                        <div class="input-group">
                            {!! Form::select('campaign_country', $countriesRec, old('country_id'),  ['class' => 'form-control multiple_select countrySelect','required','multiple' ,'id'=>'newCampaign_country']) !!}
                        </div>
                    </div>
                </div>
                                     
                <div class="form-group row">
                    <div class="col-lg-3 ">
                        <label for="" class="col-form-label">SKUs</label>
                    </div>
                    <div class="col-lg-9">
                        <div class="input-group">
                        {{--     {!! Form::select('campaign_country', $countriesRec, old('country_id'),  ['class' => 'form-control multiple_select sku','required','multiple' ,'id'=>'newCampaign_sku']) !!} --}}    
                        <select class="form-control multiple_select sku" id="newCampaign_sku" multiple>
                                
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-lg-3 ">
                        <label for="gender3" class="col-form-label">Daily Budget</label>
                    </div>
                    <div class="col-lg-9">
                        <div class="input-group">
                            <input type="text" id="newCampaign_dailyBudget" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-lg-3 ">
                        <label for="gender3" class="col-form-label">Default Bid</label>
                    </div>
                    <div class="col-lg-9">
                        <div class="input-group">
                            <input type="text" id="newCampaign_defaultBudget" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="input-daterange">
                    <div class="form-group row">
                        <div class="col-lg-3 ">
                            <label for="" class="col-form-label">Start Date</label>
                        </div>
                        <div class="col-lg-9">
                            <div class="input-group">
                                <input type="text" id="start_date" class="form-control newCampaign_startDate m-r-5" placeholder="Date Start" readonly="true" style="text-align: left;border-radius: 0px;">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-lg-3 ">
                            <label for="" class="col-form-label">End Date</label>
                        </div>
                        <div class="col-lg-9">
                            <div class="input-group">
                                <input type="text" id="end_date" class="form-control newCampaign_startDate m-r-5" placeholder="End Start" readonly="true" style="text-align: left;border-radius: 0px;">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                </div>

                <span class="btn btn-primary no-radius pull-right" id="next_newCampaign_btn">Next <i class="fa fa-angle-double-right"></i></span>
            </div>

        </div>

    </fieldset>