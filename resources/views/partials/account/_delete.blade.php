<header class="head">
    <div class="main-bar row">
        <div class="col-lg-6 col-sm-4">
            <h4 class="nav_top_align">
                <i class="fa fa-file-o"></i>
                Delete Account
            </h4>
        </div>
    </div>
</header>

<div class="col-md-12 m-t-25">
  <h3 class="text-orange">Delete Account</h3>
  <p>We're sorry to hear that you will leave us. Please do contact us
  before you delete your account as we would like to resolve any
  issue you may have experienced.<br>
  You can reach us here:&nbsp;&nbsp;<strong>{{ env('CONTACT_EMAIL1') }}</strong></p>
  <br>
  <p>If you wish to delete your account, please note that this is not
  reversible and all your data will be erased immediatelyand cannot be recovered.</p>

  <div class="row">
    <div class="col-md-5 col-sm-12 col-xs-12">
      {!! Form::text('reason', null,  ['class' => 'form-control', 'id' => 'reason', 'placeholder' => 'Reason for leaving', 'required']) !!}
    </div>
    <button id="deleteAccount" class="btn btn-raised btn-danger md-trigger adv_cust_mod_btn"
            data-toggle="modal" data-target="#modal-deleteAccount">Delete Account
    </button>
  </div>

  <div class="modal fade" id="modal-deleteAccount" role="dialog" aria-labelledby="modalLabeldanger">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h4 class="modal-title text-white" id="modalLabeldanger">Delete Account</h4>
            </div>
            <div class="modal-body">
                Are you sure you want to delete your account?
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" id="deleteAccountConfirmed">Yes</button>
                <button class="btn btn-default" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
  </div>

  <script src="/js/account.js"></script>
</div>
