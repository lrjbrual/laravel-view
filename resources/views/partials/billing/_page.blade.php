<div class="col-md-12 billingTour">
@if($payment_valid != 1)
  @include('partials.billing._modal_error')
@endif
  @include('partials.billing._contact')
  @include('partials.billing._card')
</div>

<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script type="text/javascript">
$( document ).ready(function() {

  $('#billingErrorModal').modal('show');

  $('#payment-form').submit(function(event) {
      validBillingAddress = checkBillingAddress();

      if(validBillingAddress == true){
        var $form = $(this);
        
        // Disable the submit button to prevent repeated clicks
        $form.find('#btn-submit-card').prop('disabled', true);
        $form.find('#btn-cancel-card').prop('disabled', true);

        Stripe.setPublishableKey('{{ ENV('STRIPE_KEY') }}');
        Stripe.card.createToken($form, stripeResponseHandler);

        // Prevent the form from submitting with the default action
        return false;
      } else {
        return false;
      }
  });

  $('.vatTb').blur(function(){
    if ($(this).val() != "") {
      var firstTwoDigit = $(this).val()
      firstTwoDigit = firstTwoDigit.substring(0,2)
      if (validateNumberOnly(firstTwoDigit)){
        sweetAlert("Invalid Input", "Please input number only", "error");
        $(this).val('');
        $(this).css('border', '1px solid red');
      }else{
        $(this).css('border', '1px solid #D9D9D9');
      }
    };
  })

  function checkBillingAddress(){
      var required = [];

      if($("#firstname").val() == ""){ required.push('firstname'); }
      if($("#lastname").val() == ""){ required.push('lastname'); }
      if($("#company").val() == ""){ required.push('company'); }
      if($("#address1").val() == ""){ required.push('address1'); }
      if($("#city").val() == ""){ required.push('city'); }
      if($("#postal_code").val() == ""){ required.push('postal_code'); }
      if($("#country_id").val() == ""){ required.push('country_id'); }

      if(required.length > 0){
          swal({
              title: "Billing Address",
              text: 'Incomplete billing address',
              confirmButtonColor: '#00ADB5'
          }).done();

          $.each(required, function(index, value) {
              $("input[name='" + value + "']").css('border', '1px solid red');
          });

          return false;
      } else {
          return true;
      }
  }

  function setValidity(id)
  {
    var _token = "{{ csrf_token() }}";
    var data = { _token:_token, id:id };
    $.ajax({url: 'setValidity', type: 'POST',data:data, success: function(result){
         } })
  }

  function notValid(id)
  {
    var _token = "{{ csrf_token() }}";
    var data = { _token:_token, id:id };
    $.ajax({url: 'notValid', type: 'POST',data:data, success: function(result){
         } })
  }

  function stripeResponseHandler(status, response, id) {
      var $form = $('#payment-form');
          id = $form.find('#seller_id').val();
      if (response.error) {
          // Show the errors on the form
          swal({
              title: "Card",
              text: response.error.message,
              confirmButtonColor: '#00ADB5'
          }).done();
          $form.find('#btn-submit-card').prop('disabled', false);
          $form.find('#btn-cancel-card').prop('disabled', false);
      } else {
          $form.find('#btn-submit-card').prop('disabled', true);
          $form.find('#btn-cancel-card').prop('disabled', true);
          // response contains id and card, which contains additional card details
          var token = response.id;
          // Insert the token into the form so it gets submitted to the server
          $form.append($('<input type="hidden" name="stripeToken" />').val(token));
          // and submit
          $form.get(0).submit();
      }

  };
});

</script>
