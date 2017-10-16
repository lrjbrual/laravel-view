<!-- Modal Cron-->
<div id="fulfillment_modal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Fulfillment Centers</h4>
      </div>
      <div class="modal-body">
        <span class="loading-table"> </span>
        <table id="fulfillment_center_table" class="table table-striped table-bordered">
          <thead>
            <td>ID</td>
            <td>Fulfillment Center ID</td>
            <td>Country Code</td>
          </thead>
          <tbody>
            
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button id="add_selected_seller_cron" type="button" class="btn btn-primary" data-dismiss="modal">Run Cron</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>