<div class="modal fade in display_none" id="prodCostsGraphtModal" tabindex="-1" role="dialog" aria-hidden="false">
    <div class="modal-dialog modal-lg modal-adsprodcosts">
        <div class="modal-content">
            <div class="modal-header bg-default">
            <strong>Details</strong>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">

            <div class="graphloading dontdisplay">
              <div class="dataTables_processing2" style="display:block;z-index: 1051;top:50%;"> <b>Loading graph </b><i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span></div>
            </div>


            <div class="row">
              <div class="col-md-12">
                  <table>
                    <tr>
                      <td>
                        <div class="bidLegend">
                          <div class="bidLegend1"></div>
                        </div>
                      </td>
                      <td>&nbsp Bid 1 0.65</td>
                    </tr>
                    <tr>
                      <td>
                        <div class="bidLegend">
                          <div class="bidLegend2"></div>
                        </div>
                      </td>
                      <td>&nbsp Bid 2 0.9 <i><strong class="color-blue">Date Updated: </strong> <span class="date_updated color-blue"></span></i></td>
                    </tr>
                  </table>
              </div>
            </div>
            <div class="row m-t-15">
              <div class="col-md-4 m-b-20">
                <div class="col-md-12 graph_container">
                  <div class="ct-chart ct-perfect-fourth prodCostsGraph" id="impGraphProdCost"></div>
                </div>
              </div>

              <div class="col-md-4 m-b-20">
                <div class="col-md-12 graph_container">
                  <div class="ct-chart ct-perfect-fourth prodCostsGraph" id="impClickProdCost"></div>
                </div>
              </div>

              <div class="col-md-4 m-b-20">
                <div class="col-md-12 graph_container">
                  <div class="ct-chart ct-perfect-fourth prodCostsGraph" id="impAcosProdCost"></div>
                </div>
              </div>

            </div>
              
            </div>
        </div>
    </div>
</div>