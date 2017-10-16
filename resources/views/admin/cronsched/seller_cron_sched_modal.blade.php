<!-- Modal Cron-->
<div id="cron_modal" class="modal fade" role="dialog">
  <div class="modal-dialog  modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Cron List</h4>
      </div>
      <div class="modal-body">
        <span class="loading-table-cron"> </span>
        <table id="cron_table" class="table table-striped table-bordered">
          <thead>
            <td></td>
            <td>Cron ID</td>
            <td>Cron Name</td>
            <td>Cron Path</td>
            <td>Minutes</th>
            <td>Hours</th>
            <td>Day of Month</th>
            <td>Month</th>
            <td>Day of Week</th>
            <td>isActive</th>
          </thead>
          <tbody>
            
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button id="add_selected_seller_cron" type="button" class="btn btn-primary" data-dismiss="modal">Add Selected Cron to Seller</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<!-- Modal Error-->
<div id="cron_modal_error" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Error!</h4>
      </div>
      <div class="modal-body" id="modal-body-error">
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>