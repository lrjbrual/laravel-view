<div class="modal fade in display_none" id="dashboardErrorModal" tabindex="-1" role="dialog" aria-hidden="false">
  <div class="modal-dialog modal-md">
      <div class="modal-content">
          <div class="modal-header bg-danger">
          <strong>Payment error</strong>
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
          </div>
          <div class="modal-body">
              <span class="text-center">
                @if($payment_valid == 0 && $has_subscription != 0)
                  To continue using the application, enter a valid payment method.
                @elseif($payment_valid == 0 && $has_subscription == 0)
                  To continue using the application, enter a valid payment method and base subscription.
                @elseif($payment_valid == -1)
                  We have tried to bill {{ $preferred_currency }} {{ $amount_payable }} but your card was declined. We will try again tomorrow. Please make sure your card is valid and has enough funds. Alternatively you can change your payment card. If your card still fails in {{ $dayCount}} day/s from now then your account will be suspended.
                @endif
              </span>
          </div>
          <div class="modal-footer">
              <button type="button" data-dismiss="modal" class="btn btn-secondary no-radius">Close</button>
          </div>
      </div>
  </div>
  </div>