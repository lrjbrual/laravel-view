<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/chosen/css/chosen.css')}}"/>
<div class="modal fade in display_none" id="fbaModal" tabindex="-1" role="dialog" aria-hidden="false">
                    <div class="modal-dialog modal-lg modal-fba">
                        <div class="modal-content">
                            <div class="modal-header bg-default">
                            <strong>Do it yourself FBA table</strong>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="modal-body">
                                <!-- begin -->
                                <div class="row">
                                <div class="col-md-12">

                                <div class="col-lg-12 col-sm-12 col-md-12">
                                <h4 class="m-t-10" id="seller_tab">Sellers</h4>
                                <span class="loading-seller-list"></span>
                                  <div class="row">
                                      <div class="form-group col-md-6">
                                      <br>
                                        <span class="fba_admin_filter_text filter_txt_seller">Filter by:</span>
                                      </div>
                                  </div>
                                  <div class="row dontdisplay seller_filter">
                                    <div class="col-lg-2 col-md-12 col-md-2 m-b-15">
                                      <input type="text" id="filterSellerName" class="form-control" name="" placeholder="Seller Name">
                                    </div>
                                    <div class="col-lg-2 col-md-12 m-b-15">
                                      <select id="filterCountry" class="form-control">
                                        <option value="" selected>Select Country</option>
                                        <option value="us">United States</option>
                                        <option value="ca">Canada</option>
                                        <option value="uk">United Kingdom</option>
                                        <option value="fr">France</option>
                                        <option value="de">Germany</option>
                                        <option value="it">Italy</option>
                                        <option value="es">Spain</option>
                                      </select>
                                    </div>
                                    <div class="col-md-5">
                                      <button class="btn btn-xs btn-primary m-r-20" id="btnFilterSeller">Apply Filter</button>
                                      <button class="btn btn-xs btn-primary" id="btnShowAllSeller" onclick="showAll()">Show All</button>
                                    </div>
                                  </div>
                                  <div class="table-responsive" style="overflow-y: hidden;">
                                    <table id="seller_table_list" cellspacing="0" cellpadding="0" class="table table-striped table-bordered dataTable no-footer fba_refund_table_header" style="width:100%;">
                                      <thead>
                                            <th><p style="width: 80px">Country</th>
                                            <th>Total Amount Estimate Owed (outstanding)</th>
                                            <th>Total amount saved to date</th>
                                        </thead>
                                      <tbody>

                                      </tbody>
                                    </table>
                                  </div>
                                </div>

                                <br><br>
                                <!-- ORDER ID -->

                                <div class="col-lg-12 col-sm-12 col-md-12">
                                <hr>
                                <h4 class="m-t-10" id="oic_h4">Order ID Claims</h4>
                                <p class="m-t-15 seller_info_text" style="color:#FF5722"></p>
                                <span class="loading-oic-list"></span>
                                <div class="row">
                                      <div class="form-group col-md-6">
                                      <br>
                                        <span class="fba_admin_filter_text filter_txt_oic">Filter by:</span>
                                      </div>
                                  </div>
                                  <div class="row dontdisplay oic_filter">
                                    <div class="col-lg-2 col-md-12 m-b-15">
                                      <input type="text" class="form-control" name="" id="filterOicOrderId" placeholder="Order ID">
                                    </div>
                                    <div class="col-lg-2 col-md-12 m-b-15">
                                      <input type="text" class="form-control" name="" id="filterOicTicket" placeholder="Support ticket">
                                    </div>
                                    <div class="col-lg-2 col-md-12 m-b-15">
                                        <div>
                                            <select class="form-control" id="filterOicStatus">
                                              <option value="">Select Status</option>
                                              <option value="Open">Open</option>
                                              <optgroup label="Closed">
                                              <option value="All Ok">All Ok</option>
                                              <option value="Refund issued by seller">Refund issued by seller</option>
                                              <option value="Amz won't refund difference">Amz won't refund difference</option>                     
                                              </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                      <button class="btn btn-xs btn-primary m-r-20" id="btnFilterOic">Apply Filter</button>
                                      <button class="btn btn-xs btn-primary" id="btnShowAllOic" onclick="showAll('getSellerOIC')">Show All</button>
                                    </div>
                                  </div>
                                <div class="table-responsive" style="overflow-y: hidden;">
                                <table id="seller_oic" cellspacing="0" cellpadding="0" class="table table-striped table-bordered dataTable no-footer fba_refund_table_header" style="width:100%;">
                                  <thead>
                                    <th><p style="width: 100px"></p>Order ID</th>
                                    <th>Partial/Full claim</th>
                                    <th><p style="width: 100px"></p>Detailed Disposition</th>
                                    <th>Amount to Claim</th>
                                    <th>Support Ticket</th>
                                    <th>Reimb. ID(1)</th>
                                    <th>Reimb. ID(2)</th>
                                    <th>Reimb. ID(3)</th>
                                    <th>Total Amount Reimb.</th>
                                    <th>Diff.</th>
                                    <th><p style="width: 160px"></p>Status</th>
                                    <th>Comments</th>
                                  </thead>
                                  <tbody>

                                  </tbody>
                                </table>
                                </div>
                                </div>

                                <!-- FNSKU -->
                                <br>
                                <div class="col-lg-12 col-sm-12 col-md-12 m-b-20">
                                <hr>
                                <h4 class="m-t-10">FnSKU Claims</h4>
                                <p class="m-t-15 seller_info_text" style="color:#FF5722"></p>
                                <div class="row">
                                      <div class="form-group col-md-6">
                                      <br>
                                        <span class="fba_admin_filter_text filter_txt_sku">Filter by:</span>
                                      </div>
                                  </div>
                                  <div class="row dontdisplay sku_filter">
                                    <div class="col-lg-2 col-md-12 m-b-15">
                                      <input type="text" class="form-control" name="" id="filterSkuOrderId" placeholder="Order ID">
                                    </div>
                                    <div class="col-lg-2 col-md-12 m-b-15">
                                      <input type="text" class="form-control" name="" id="filterSkuTicket" placeholder="Support ticket">
                                    </div>
                                    <div class="col-lg-2 col-md-12 m-b-15">
                                      <div>
                                            <select class="form-control" id="filterSkuStatus">
                                              <option value="">Select Status</option>
                                              <option value="Open">Open</option>
                                              <optgroup label="Closed">
                                              <option>All Ok</option>
                                              <option>Refund issued by seller</option>
                                              <option>Amz won't refund difference</option>                     
                                              </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                      <button class="btn btn-xs btn-primary m-r-20" id="btnFilterSku">Apply Filter</button>
                                      <button class="btn btn-xs btn-primary" id="btnShowAllSku" onclick="showAll('getSellerFNSKU')">Show All</button>
                                    </div>
                                  </div>
                                  <div class="table-responsive" style="overflow-y: hidden;">
                                    <table id="seller_fnsku" cellspacing="0" cellpadding="0" class="table table-striped table-bordered dataTable no-footer fba_refund_table_header" style="width:100%;">
                                      <thead>
                                        <th><p style="width: 100px">FnSKU</th>
                                        <th>Qty to Claim</th>
                                        <th>Ave. Val.</th>
                                        <th>Total Owed</th>
                                        <th>Support Ticket</th>
                                        <th>Reimb. ID(1)</th>
                                        <th>Reimb. ID(2)</th>
                                        <th>Reimb. ID(3)</th>
                                        <th>Total Amount Reimb.</th>
                                        <th><p style="width: 100px"></p>Diff.</th>
                                        <th><p style="width: 160px">Status</th>
                                        <th>Comments</th>
                                      </thead>
                                      <tbody>

                                      </tbody>
                                    </table>
                                  </div>
                                </div>
                                </div>
                                </div>
                                <!-- end -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" data-dismiss="modal" class="btn btn-secondary no-radius">Close</button>
                            </div>
                        </div>
                    </div>
                </div>