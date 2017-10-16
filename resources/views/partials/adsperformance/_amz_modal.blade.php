@include('trendle.marketplace.partials._amazondiv')
<div class="modal fade in display_none" id="amzModal" tabindex="-1" role="dialog" aria-hidden="false" data-keyboard="false" data-backdrop="static">
  <div class="modal-dialog modal-md">
      <div class="modal-content">
          <div class="modal-header bg-warning">
          <strong>Login with Amazon</strong>
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
          </div>
          <div class="modal-body">
              <p class="text-center">
                Before your can use the <strong>Advertising</strong> features, you need to log in with amazon.
              </p>

              <div class="text-center">
                <a href="#" id="LoginWithAmazon" class="btn">
                  <img border="0" alt="Login with Amazon"
                    src="https://images-na.ssl-images-amazon.com/images/G/01/lwa/btnLWA_gold_156x32.png"
                    width="156" height="32" />
                </a>
              </div>

          </div>
          <div class="modal-footer">
              <button type="button" data-dismiss="modal" class="btn btn-secondary no-radius">Close</button>
          </div>
      </div>
  </div>
</div>
<script type="text/javascript">
  
  $('#amzModal').modal('show')

  document.getElementById('LoginWithAmazon').onclick = function() {
    options = { scope : 'cpc_advertising:campaign_management profile', response_type: 'code' };
    amazon.Login.authorize(options, '{{ url('/marketplace/auth_amz_account') }}');
    return false; 
  };

  $(document).ready(function() {
    var flash = "{{ session('status') }}";
    if(flash == "Connected to Amazon!"){
      swal({
          title: 'Success!',
          text: flash,
          confirmButtonColor: '#4fb7fe'
      }).done();
      return false;
    }
  });
</script>