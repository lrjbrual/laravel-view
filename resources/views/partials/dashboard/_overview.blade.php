<div class="col-md-12">
@if($payment_valid != 1)
@include('partials.dashboard._modal_error')
@endif
</div>
<div class="col-md-4 col-sm-12 col-lg-4">
    <div class="card card-inverse m-t-35">
        <div class="card-header card-danger">FBA Refunds</div>
        <div class="card-block">
            <div class="row">
                <div class="col-md-6">
                  <h1 class="text-danger">
                    {{ $currency }}<span class="number_val" id="sales_count"></span>
                  </h1>
                  <h5>Total Amount Owed (est.)</h5>
                  <input type="hidden" id="total_owed" value="{{ $total_owed }}" />
                </div>
                <div class="col-md-6">
                  <h1 class="text-danger">
                    {{ $currency }}<span class="number_val" id="sales_count1"></span>
                  </h1>
                  <h5>Total reimbursed to date</h5>
                  <input type="hidden" id="total_reimburse" value="{{ $total_reimburse }}" />
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-md-4 col-sm-12 col-lg-4 ">
    <div class="card card-inverse m-t-35">
        <div class="card-header card-success">Automatic Email Campaigns</div>
        <div class="card-block">
            <div class="row">
                {{-- <div class="col-md-6 col-sm-12">
                  <h1 class="text-success">
                  <span class="number_val" id="emails_sent1"></span></h1>
                  <h5>Number of emails sent to date</h5>
                  <input type="hidden" id="number_sent" value="{{ $number_sent }}" />
                </div> --}}
                
                <div class="col-md-6 col-sm-12">
                  <h1 class="text-success">
                  <span class="number_val" id="emails_sent1"></span></h1>
                  <h5>Monthly Allowance Remaining</h5>
                  <input type="hidden" id="number_sent" value="{{ $monthly_remaining }}" />
                </div>

                <div class="col-md-6 col-sm-12">
                  <h1 class="text-success">
                  <span class="number_val" id="emails_sent3"></span>  
                  </h1>
                  <h5>Airbag Emails Remaining</h5>
                  <input type="hidden" id="credit" value="{{ $credit }}" />
                </div>

            </div>
        </div>
    </div>
</div>

<div class="col-md-4 col-sm-12 col-lg-4">
    <div class="card card-inverse m-t-35">
        <div class="card-header card-warning">New Reviews</div>
        <div class="card-block">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                  <h1 class="text-warning hidden"><span class="number_val" id="number_review1"></span></h1>
                  <h5>Seller</h5>
                  <input type="hidden" id="seller_review_count" value="{{ $seller_review_count }}" />
                </div>

                <div class="col-md-6 col-sm-12">
                  <h1 class="text-warning hidden"><span class="number_val" id="product_count"></span></h1>
                  <h5>Products</h5>
                  <input type="hidden" id="product_review_count" value="{{ $product_review_count }}" />
                </div>
            </div>
        </div>
    </div>
</div>
