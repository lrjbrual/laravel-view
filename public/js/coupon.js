$(document).ready(function() {
    $("#verifycoupon").click(function(){
        verifyCoupon();
    });


    function verifyCoupon() {
        // disable button to avoid submitting while checking coupon
        $("#subscribe").prop('disabled', true);

        if ($('#coupon').val() == "") {
            swal({
                title: "Please provide a promo code.",
                confirmButtonColor: '#00ADB5'
            }).done();

            return false;
        }

        $.ajax({
            url: "/subscription/verify-coupon",
            type: "post",
            dataType: "json",
            data: {
                coupon: $('#coupon').val(),
            },
            success: function(response){
                if (response.status == 'failed') {
                    if (response.message.indexOf("No such coupon") != -1) {
                        swal({
                            title: $('#coupon').val() + "\nThis code is invalid",
                            confirmButtonColor: '#00ADB5'
                        }).done();
                    } else {
                        swal({
                            title: response.message,
                            confirmButtonColor: '#00ADB5'
                        }).done();
                    }

                    return false;
                } else {
                   
                    $("#coupon-id").html(response.coupon.voucher_code);
                    $("#coupon-id").attr("data-coupon-id", response.coupon.id);

                    if (response.coupon.amount_off) {
                      $("#coupon-discount").html(response.currency_symbol + response.coupon.amount_off / 100);
                      updateTotalByAmountOff(response.coupon.amount_off);
                    }

                    if (response.coupon.percent_off) {
                      $("#coupon-discount").html(response.coupon.percent_off+"%");
                      updateTotalByPercentOff(response.coupon.percent_off);
                    }

                    if (response.coupon.duration == "repeating") {
                        $("#coupon-duration").html(response.coupon.duration_in_months + " Month's");
                    } else {
                        $("#coupon-duration").html(response.coupon.duration);
                    }

                    swal({
                        title: 'Promo code added',
                        confirmButtonColor: '#00ADB5'
                    }).done();

                    savePromoCode(response.coupon);
                }

                $("#subscribe").prop('disabled', false);
            }
        });
    }

    function updateTotalByPercentOffByAmountOff(amount_off) {
        total = parseInt($("#total").html());
        discount = amount_off / 100;
        $("#total-sum-amount").html(total - discount);
    }

    function updateTotalByPercentOff(percent_off) {
        total = parseInt($("#total").html());
        $("#total-sum-amount").html(total - (total * (percent_off/100)));
    }

    function savePromoCode(coupon) {
        $.ajax({
            url: "/promocode",
            type: "post",
            data: {
                voucher_code: coupon.voucher_code,
            },
            success: function(response){
                if(response.status == 'success')
                {
                window.location = '/subscription';
            }
            }
        });
    }
});
