    <fieldset class="m-t-35">

        <div class="row">
            <div class="col-md-12">

                <div class="keywordRow">
                 
                </div>
                
                <span class="btn btn-primary no-radius m-r-10" id="back_newAdGroup_btn"><i class="fa fa-angle-double-left"></i> Back</span>

                <span class="btn btn-success no-radius pull-right saveNewCampaignBtn" id="saveCampaignBtn">Save Campaign</span>

            </div>
        </div>

    </fieldset>

        <div id="bulkKeywordModal" class="modal fade" role="dialog">
          <div class="modal-dialog modal-sm">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title keywordmodal_title"></h4>
              </div>
              <div class="modal-body">
              Add keywords by bulk<br><small><i>Select Match Type</i></small>
                <select class="form-control bulkMatchType" >
                    <option value="BROAD">BROAD</option>
                    <option value="PHRASE">PHRASE</option>
                    <option value="EXACT">EXACT</option>
                    <option value="NEGATIVE PHRASE">NEGATIVE PHRASE</option>
                    <option value="NEGATIVE EXACT">NEGATIVE EXACT</option>
                </select>
                
                <i><small>add keywords separated by break line or new line</small></i>
                <textarea class="form-control text_area_bulk_keyword" placeholder="Add keywords separated by break line or new line" style="resize: none;min-height:200px;"></textarea>

              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-success btn-sm no-radius addKeyWordBtn">Add</button>
                <button type="button" class="btn btn-warning btn-sm no-radius" data-dismiss="modal">Close</button>
              </div>
            </div>

          </div>
        </div>