    <fieldset class="m-t-35">
                
        <div class="addgroup_container">
            <div class="row adgroup_row">
                <div class="col-md-12 m-b-10 ">
                    <button class="btn btn-sm btn-success no-radius pull-right addNewAdGroup"><i class="fa fa-plus"></i> New Ad Group</button>
                </div>
                <div class="col-md-12">
                    <div class="form-group row">
                        <div class="col-lg-3 ">
                            <label for="" class="col-form-label">Ad Group Name</label>
                        </div>
                        <div class="col-lg-9">
                            <div class="input-group">
                                <input type="text" id="" class="form-control ad_group_tb">
                            </div>
                        </div>
                    </div>
             
                    <div class="form-group row">
                        <div class="col-lg-3 ">
                            <label for="" class="col-form-label">Default Bid</label>
                        </div>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <input type="text" id="" class="form-control default_bid_tb">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row default_matchtype_container" >
                        <div class="col-lg-3 ">
                            <label for="" class="col-form-label">Default Match Type</label>
                        </div>
                        <div class="col-lg-4">
                            <select class="form-control default_matchtype">
                                <option value="Broad">Broad</option>
                                <option value="Phrase">Phrase</option>
                                <option value="Exact">Exact</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <span class="btn btn-primary no-radius pull-right" id="next_newAdGroup_btn">Next <i class="fa fa-angle-double-right"></i></span>
        <span class="btn btn-success no-radius pull-right dontdisplay saveNewCampaignBtn" id="saveNewCampaignBtn">Save Campaign</span>

        <span class="btn btn-primary no-radius m-r-10" id="back_newCampaign_btn"><i class="fa fa-angle-double-left"></i> Back</span>


    </fieldset>