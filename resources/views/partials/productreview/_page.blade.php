<link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/tipso/css/tipso.min.css')}}"/>
<div class="col-md-12 m-t-25">
  <div class="col-md-12 m-b-20">
    Last Update: <i class="color-orange">{{ $lastUpdate }}</i>
  </div>
  

  <div class="col-md-12 m-t-5" id="">
    <div class="loadingTableContainer dontdisplay" style="height: 102%;">
      <div class="dataTables_processing2" style="display:block;z-index: 998;top:50%;"> <b>Loading records </b><i class="fa fa-refresh fa-spin fa-fw"></i><span class="sr-only">Loading...</span>
      </div>
    </div>

    <div class="row">
      <div class="s_reviews_view_buttons">
        <button class="btn s_reviews_view_btn sr_active" data-tab="inbox">Inbox</button>
        <button class="btn s_reviews_view_btn" data-tab="products">Products</button>
        <button class="btn s_reviews_view_btn" data-tab="archive">Archive</button>
      </div>
    </div>
      
      <div id="inboxArchiveContainer_table" style="padding-top: 10px">
        <table id="productreview_table" cellspacing="0" cellpadding="0" class="table table-striped table-bordered dataTable no-footer" style="width:100%;">
            <thead>
                <tr>
                  <th class="sort">Date</th>
                    <th class="sort">Country</th>
                    <th class="sort">
                      <select name="display_type" id="sku_asin_name" class="form-control" style="height: 15px">
                        <option value="asin">ASIN</option>
                        <option value="sku" selected="true">SKU</option>
                        <option value="product_name">Product Title</option>
                      </select>
                    </th>
                    <th class="sort">Review Comment</th>
                    <th class="sort" width="80">Review<br>Rating</th>
                    <th class="sort rName">Review Name</th>
                    <th class hidden="sort">Order<br>Number</th>
                    <th class="sort" width="50">Archive</th>
                    <th class="sort" width="">Comments</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
      </div>

      <div id="productsContainer_table" class="dontdisplay" style="padding-top: 10px">
        <table id="product_table" cellspacing="0" cellpadding="0" class="table table-striped table-bordered dataTable no-footer" style="width:100%;">
            <thead>
                <tr>
                    <th class="sort" width="80">Country</th>
                    <th class="sort" width="">
                    <select name="display_type" id="product_sku_asin_name" class="form-control" style="height: 15px">
                        <option value="asin">ASIN</option>
                        <option value="sku" selected="true">SKU</option>
                        <option value="product_name">Product Title</option>
                      </select>
                    </th>
                    <th class="sort">Avg Rating</th>
                    <th class="sort">Number of Ratings</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
      </div>

  </div>
</div>
<script type="text/javascript" src="{{asset('assets/vendors/tipso/js/tipso.min.js')}}"></script>
<script type="text/javascript" src="{{ url('js/product_review.js') }}"></script>
