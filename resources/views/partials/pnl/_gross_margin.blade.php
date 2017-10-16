<div class="col-md-12 m-t-25">
  <div class="card card-outline-primary">
    <div class="card-header bg-primary">
       <span class="card-title">Gross Margin</span>
       <span class="float-xs-right">
          <i class="fa fa-chevron-up"></i>
       </span>
    </div>
    <div class="card-block">
      <div class="table-responsive">
          <table cellspacing="0" cellpadding="0" class="table table-bordered table_res dataTable no-footer">
              <div class="dataTables_processing2 pnl_table_gross_loading" style="display:block">
                    <b>Loading records. . .</b>
              </div>
              <thead>
                  <tr>
                      <th width="3%"><span class="row-details"></span></th>
                      <th>&nbsp;</th>
                      <th>&nbsp;</th>
                      @foreach ($mkp_c as $mkp_c_data)
                            <th width=>{{ $mkp_c_data->iso_3166_2 }}</th>
                      @endforeach
                      <th>Total</th>
                  </tr>

                  <tr class="grossmarginvaluetr">
                      <td><span class="row-details"></span></td>
                      <td><strong>Gross Margin($)</strong></td>
                      <td>&nbsp;</td>
                      @foreach ($mkp_c as $mkp_c_data)
                            <td width=></td>
                      @endforeach
                      <td></td>
                  </tr>

                  <tr class="grossmarginpercenttr">
                      <td><span class="row-details"></span></td>
                      <td><strong>Gross Margin(%)</strong></td>
                      <td>&nbsp;</td>
                      @foreach ($mkp_c as $mkp_c_data)
                            <td width=></td>
                      @endforeach
                      <td></td>

                  </tr>
              </thead>

          </table>
      </div>

    </div>
  </div>
</div>