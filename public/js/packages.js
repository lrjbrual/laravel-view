$(document).ready(function() {
    var symbol = [];
    var country_ids = [];
    var countryID;
    var plans;
    var modal_event  = 'proceed';
    var objects;

    if ($("#currencies").val() == "gbp") {
      $(".packages").attr("data-country-id", "826");
      $(".packages").attr("data-symbol", "£");
      $(".pccs").html("£");
      $(".pcca1").html(5);
      $(".pcca2").html(10);
      $(".pcca3").html(25);
      $(".pcca4").html(40);
      $(".pcca5").html(60);
      var  attrValue, $this;
      $(".packages").each(function() {
          $this = $(this);
          if ($this.attr("data-id") == 1) {
            $this.attr("value", 500)
          }
          if ($this.attr("data-id") == 2) {
            $this.attr("value", 1000)
          }
          if ($this.attr("data-id") == 3) {
            $this.attr("value", 2500)
          }
          if ($this.attr("data-id") == 4) {
            $this.attr("value", 4000)
          }
          if ($this.attr("data-id") == 5) {
            $this.attr("value", 6000)
          }
      });

      $("#show-hide-paypal-button").show();
    } else if ($("#currencies").val() == "usd") {
      $(".packages").attr("data-country-id", "840");
      $(".packages").attr("data-symbol", "$");
      $(".pccs").html("$");
      $(".pcca1").html(7);
      $(".pcca2").html(13);
      $(".pcca3").html(32);
      $(".pcca4").html(50);
      $(".pcca5").html(75);
      var  attrValue, $this;
      $(".packages").each(function() {
          $this = $(this);
          if ($this.attr("data-id") == 1) {
            $this.attr("value", 700)
          }
          if ($this.attr("data-id") == 2) {
            $this.attr("value", 1300)
          }
          if ($this.attr("data-id") == 3) {
            $this.attr("value", 3200)
          }
          if ($this.attr("data-id") == 4) {
            $this.attr("value", 5000)
          }
          if ($this.attr("data-id") == 5) {
            $this.attr("value", 7500)
          }
      });

      $("#show-hide-paypal-button").hide();
    } else if ($("#currencies").val() == "eur") {
      $(".packages").attr("data-country-id", "724");
      $(".packages").attr("data-symbol", "€");
      $(".pccs").html("€");
      $(".pcca1").html(6);
      $(".pcca2").html(12);
      $(".pcca3").html(30);
      $(".pcca4").html(47);
      $(".pcca5").html(70);
      var  attrValue, $this;
      $(".packages").each(function() {
          $this = $(this);
          if ($this.attr("data-id") == 1) {
            $this.attr("value", 600)
          }
          if ($this.attr("data-id") == 2) {
            $this.attr("value", 1200)
          }
          if ($this.attr("data-id") == 3) {
            $this.attr("value", 3000)
          }
          if ($this.attr("data-id") == 4) {
            $this.attr("value", 4700)
          }
          if ($this.attr("data-id") == 5) {
            $this.attr("value", 7000)
          }
      });

      $("#show-hide-paypal-button").hide();
    }

    $("#currencies").change(function(){
        $("#total-sum-currency").html("");
        $("#total-sum-amount").html(0);
        $(".packages").attr('checked', false);

        if ($("#currencies").val() == "gbp") {

          $(".packages").attr("data-country-id", "826");
          $(".packages").attr("data-symbol", "£");
          $(".pccs").html("£");
          $(".pcca1").html(5);
          $(".pcca2").html(10);
          $(".pcca3").html(25);
          $(".pcca4").html(40);
          $(".pcca5").html(60);
          var  attrValue, $this;
          $(".packages").each(function() {
              $this = $(this);
              if ($this.attr("data-id") == 1) {
                $this.attr("value", 500)
              }
              if ($this.attr("data-id") == 2) {
                $this.attr("value", 1000)
              }
              if ($this.attr("data-id") == 3) {
                $this.attr("value", 2500)
              }
              if ($this.attr("data-id") == 4) {
                $this.attr("value", 4000)
              }
              if ($this.attr("data-id") == 5) {
                $this.attr("value", 6000)
              }
          });

          $("#show-hide-paypal-button").show();
        } else if ($("#currencies").val() == "usd") {
          $(".packages").attr("data-country-id", "840");
          $(".packages").attr("data-symbol", "$");
          $(".pccs").html("$");
          $(".pcca1").html(7);
          $(".pcca2").html(13);
          $(".pcca3").html(32);
          $(".pcca4").html(50);
          $(".pcca5").html(75);
          var  attrValue, $this;
          $(".packages").each(function() {
              $this = $(this);
              if ($this.attr("data-id") == 1) {
                $this.attr("value", 700)
              }
              if ($this.attr("data-id") == 2) {
                $this.attr("value", 1300)
              }
              if ($this.attr("data-id") == 3) {
                $this.attr("value", 3200)
              }
              if ($this.attr("data-id") == 4) {
                $this.attr("value", 5000)
              }
              if ($this.attr("data-id") == 5) {
                $this.attr("value", 7500)
              }
          });

          $("#show-hide-paypal-button").hide();
        } else if ($("#currencies").val() == "eur") {
          $(".packages").attr("data-country-id", "724");
          $(".packages").attr("data-symbol", "€");
          $(".pccs").html("€");
          $(".pcca1").html(6);
          $(".pcca2").html(12);
          $(".pcca3").html(30);
          $(".pcca4").html(47);
          $(".pcca5").html(70);
          var  attrValue, $this;
          $(".packages").each(function() {
              $this = $(this);
              if ($this.attr("data-id") == 1) {
                $this.attr("value", 600)
              }
              if ($this.attr("data-id") == 2) {
                $this.attr("value", 1200)
              }
              if ($this.attr("data-id") == 3) {
                $this.attr("value", 3000)
              }
              if ($this.attr("data-id") == 4) {
                $this.attr("value", 4700)
              }
              if ($this.attr("data-id") == 5) {
                $this.attr("value", 7000)
              }
          });

          $("#show-hide-paypal-button").hide();
        }
    });

    $("#subscribe").click(function(){
        if (checkPlan()) {
            sweetAlert("", "You are currently subscribed on this package.", "error");
            return false;
        }

        // disable button to avoid double clicking
        $("#subscribe").prop('disabled', true);

        $.ajax({
            url: "/subscription/subscribe",
            type: "post",
            data: {
                amount: $('#total').attr('data-stripe-amount'),
                country_id: countryID,
                plans: plans,
                coupon: $("#coupon-id").attr('data-coupon-id'),
            },
            success: function(response){
                window.location.reload();
            }
        });
    });

    $("#purchase").click(function(){
        checkCardBilling();


    });

    function checkCardBilling(){
        $.ajax({
            url: "/subscription/hasBillingCard",
            type: "post",
            success: function(response){
                if (response == 'false') {
                  swal({
                      title: 'You need to provide your card details to complete this transaction.',
                      confirmButtonColor: '#00ADB5'
                  }).then(function () {
                      window.location.href = '/billing';
                  }, function (dismiss) {

                  });
                  return false;
                } else {
                  purchase();
                }
            }
        });
    }

    function purchase(){
        if(plans[0] != ""){
          // disable button to avoid double clicking
          $("#purchase").prop('disabled', true);

          $.ajax({
              url: "/subscription/subscribe",
              type: "post",
              data: {
                  amount: $('#total').attr('data-stripe-amount'),
                  country_id: countryID,
                  plans: plans,
                  coupon: $("#coupon-id").attr('data-coupon-id'),
                  'token' : $('meta[name="csrf-token"]').attr('content'),
                  'planSize': $('#sizeEmail').attr('data-plan-size'),
                  // 'currency' : $("#currencies").val(),
              },
              success: function(response){
                  if(response == 'false')
                  {
                    swal({
                    title: 'Your card was been declined. Please try again or contact us.',
                    confirmButtonColor: '#00ADB5'
                    }).done();
                    return false;

                  }
                  else
                  {
                    window.location.reload();
                  } 
                }

          });
        } else {
          swal({
              title: 'Please select a package.',
              confirmButtonColor: '#00ADB5'
          }).done();
          return false;
        }
    }

    $(".btn-paypal").submit(function(){
        $("input[name='plans']").val(plans);

        if (checkPlan()) {
            sweetAlert("", "You are currently subscribed on this package.", "error");
            return false;
        }

        $("input[name='paypal_amount']").val($("#total").html());
        $("input[name='paypal_currency']").val($("#currencies").val());
        if (plans == "1") {
          $("input[name='paypal_product']").val("XS");
        } else if (plans == "2") {
          $("input[name='paypal_product']").val("S");
        } else if (plans == "3") {
          $("input[name='paypal_product']").val("M");
        } else if (plans == "4") {
          $("input[name='paypal_product']").val("L");
        } else if (plans == "5") {
          $("input[name='paypal_product']").val("XL");
        }
        amount = $("input[name='paypal_amount']").val();

        if(!amount || amount == 0){ alert("Invalid Amount"); return false; }
    });

    $(".packages").click(function() {
        setSymbol(this);
        setCountryId(this)
        checkCurrency(this);
        getPlans();
        updateTotal();
        updateTotalSum();
        setPlanSize();
    });

    // $("#subscription-refunds-switch").change(function(){
    //     if($(this).is(':checked') == true){
    //       msg = "Feature Activated";
    //     } else {
    //       msg = "Feature Deactivated";
    //     }

    //     $("#subscription-refunds-switch-msg").html(msg);
    // });

    // switch begin
    

    // Events
    $("#subscription-refunds-switch").change(function () {

        objects = {
          activate: $("#refunds-switch").is(':checked')
        }

        if($("#subscription-refunds-switch").is(':checked') == true){
            if (modal_event == 'proceed'){

                /*COMMENTED BY JASON FOR TEMPORARY REMOVING OF DIY OPTION UPON ACTIVATING*/
                /*getRefundSwitchMsg();
                $("#refunds-modal-activate").modal('show');*/

                activateFeature()

            } else {
                modal_event = 'proceed';
            }
        } else {

            if (modal_event == 'proceed'){

                deactivateFeature()

                /*COMMENTED BY JASON FOR TEMPORARY REMOVING OF DIY OPTION UPON ACTIVATING*/
                /*getRefundSwitchMsg();
                $("#refunds-modal-deactivate").modal('show');*/
            } else {
                modal_event = 'proceed';
            }
        }
    });

    $('#refunds-modal-activate').on('hidden.bs.modal', function (event) {
        cancelSwitchState();
    })

    $('#refunds-modal-deactivate').on('hidden.bs.modal', function () {
        cancelSwitchState();
    })

    // $(".instructions #action").click(function(){
    //     $(".instructions #msg").toggle();
    //     $(".instructions #header").css('margin-bottom', '0px');

    //     if($(this).hasClass("fa-window-minimize")){
    //         $(this).removeClass("fa-window-minimize");
    //         $(this).addClass("fa-window-maximize");
    //         $(".instructions #action").css('top', '0px');
    //     } else {
    //         $(this).removeClass("fa-window-maximize");
    //         $(this).addClass("fa-window-minimize");
    //         $(".instructions #action").css('top', '-5px');
    //     }
    // });

    // Functions

    /*ADDED BY JASON FOR TEMPORARY REMOVING OF DIY OPTION UPON ACTIVATING*/
    function activateFeature(){

      $('#subscription-refunds-switch-msg').html('Feature Activated')
      swal({
          title: '',
          html: "Please confirm you want to activate FBA Refunds and that you are aware of the associated pricing.<br><br>"+
                "Once activated, our team will begin filing cases within 1 business day.<br><br>"+
                "You may de-activate this feature again at any point.",
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Confirm',
          cancelButtonText: 'Cancel',
          confirmButtonClass: 'btn btn-success',
          cancelButtonClass: 'btn btn-danger',
          showLoaderOnConfirm: true,
          preConfirm: function () {
          return new Promise(function (resolve, reject) {
            $.ajax({
              url: 'refund/activate',
              type: 'POST',
              data: {fba_mode: 'MANAGE'},
              success: function(response){
                if (response.message == 'success') {
                  resolve()
                }else{

                  swal({
                      title: '',
                      html: 'Please enter your payment method to complete activation<br><small>You will be redirect to billing</small>',
                      timer: 5000,
                      type: 'info',
                      onOpen: function () {
                        swal.showLoading()
                      }
                    }).then(
                      function () {},
                      function (dismiss) {
                        if (dismiss === 'timer') {
                          window.location = window.origin+'/billing'
                        }
                      }
                    )

                }
              }
            })
          })
        },
        allowOutsideClick: false
        }).then(function () {
          swal('','FBA Refunds feature activated','success')
        }, function (dismiss) {
          if (dismiss === 'cancel') {
            $('#subscription-refunds-switch-msg').html('Feature Deactivated')

            cancelSwitchState()

          }
        }).catch(swal.noop)

    }

    function deactivateFeature(){

      $('#subscription-refunds-switch-msg').html('Feature Deactivated')
      swal({
          title: '',
          html: "Please confirm that you want to de-active FBA Refunds.<br><br>"+
                "Once confirmed, our team will complete the current open cases but will not raise any new cases.<br><br>"+
                "You can re-activate FBA Refunds at any point in the future.<br><br>"+
                "By clicking 'Confirm' you agree to the above terms.",
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Confirm',
          cancelButtonText: 'Cancel',
          confirmButtonClass: 'btn btn-success',
          cancelButtonClass: 'btn btn-danger',
          showLoaderOnConfirm: true,
          preConfirm: function () {
          return new Promise(function (resolve, reject) {
            $.ajax({
              url: 'refund/deactivate',
              type: 'POST',
              success: function(response){
                if (response.message == 'success') {
                  resolve()
                }else{
                  location.reload()
                }
              }
            })
          })
        },
        allowOutsideClick: false
        }).then(function () {
          swal('','FBA Refunds feature deactivated','success')
        }, function (dismiss) {
          if (dismiss === 'cancel') {
            $('#subscription-refunds-switch-msg').html('Feature Activated')

            cancelSwitchState()

          }
        }).catch(swal.noop)

    }
    /*END*/

    function setPlanSize(){
      var counter = 0;
      $('.emailPackage').each(function(){
          switch (counter){
            case 0:
                if($(this).is(':checked') == true){
                  $('#sizeEmail').attr('data-plan-size','S');
                }
            break;
            case 1:
                if($(this).is(':checked') == true){
                  $('#sizeEmail').attr('data-plan-size','M');
                }
            break;
            case 2:
                if($(this).is(':checked') == true){
                  $('#sizeEmail').attr('data-plan-size','L');
                }
            break;
          }
          counter ++;
      })
    }

    function getRefundSwitchMsg(){
        if($("#subscription-refunds-switch").is(':checked') == true){
          msg = "Feature Activated";
        } else {
          msg = "Feature Deactivated";
        }

        $("#subscription-refunds-switch-msg").html(msg);
    }

    function cancelSwitchState(){
        modal_event = 'cancel';
        switchbutton = $("#subscription-refunds-switch");
        switchbutton.trigger('click');
        getRefundSwitchMsg()
    }
    // end switch

    function setSymbol(id){
        symbol.push($(id).attr('data-symbol'));
        // $("#currency").html(symbol[symbol.length - 1]); commented by jason 08/26/17
    }

    function rollbackSymbol(){
        // $("#currency").html(symbol[symbol.length - 2]); commented by jason 08/26/17
        symbol.pop();
    }

    function setCountryId(id){
        country_ids.push($(id).attr('data-country-id'));
        countryID = country_ids[country_ids.length - 1];
    }

    function rollbackCountryId(){
        countryID = country_ids[country_ids.length - 2];
        country_ids.pop();
    }

    function checkCurrency(id){
        country_id = [];

        $(".packages").each(function() {
            if($(this).is(':checked'))
                country_id.push($(this).attr('data-country-id'));
        });

        first_country_id = country_id[0];
        // comment temporary
        // for(i = 1; i < country_id.length; i++) {
        //     if(country_id[i] != first_country_id) {
        //         sweetAlert("", "Please select plan with same currency.", "error");
        //         $(id).prop('checked', false);
        //         $(id).attr("data-ischeck", "no");
        //         rollbackSymbol();
        //         rollbackCountryId();
        //         return false;
        //     }
        // }
    }

    function getPlans() {
        plans = [];

        $(".packages").each(function() {
            if($(this).attr('data-ischeck') == 'yes')
                plans.push($(this).attr('data-id'));
        });
    }

    function checkPlan(){
        strplans = $("#current_plans").val();
        oldplans = strplans.split(",");
        newplans = plans;

        return arraysEqual(oldplans, newplans);
    }

    function arraysEqual(a, b) {
        if (a === b) return true;
        if (a == null || b == null) return false;
        if (a.length != b.length) return false;

        for (var i = 0; i < a.length; ++i) {
          if (a[i] !== b[i]) return false;
        }
        return true;
    }

    function updateTotal(){
        pricesum = 0;

        $(".packages").each(function() {
            if($(this).attr('data-ischeck') == 'yes')
                pricesum += parseFloat($(this).val());
        });

        $("#total").html(formatAmount(pricesum));
        $("#total").attr('data-stripe-amount', pricesum);
    }

    function updateTotalSum() {
        $("#total-sum-currency").html($(".pccs").html());
        $("#vat-currency").html($(".pccs").html());
        $("#total-sum-amount").html($("#total").attr('data-stripe-amount'));
    }

    function formatAmount(amount){
        total = amount; //removed / 100 by jason 07/26/17
        return total.toFixed(2).replace('.00', '');
    }

    function setCurrentSPlans() {
        strplans = $("#current_plans").val();
        plans = strplans.split(",");

        if(plans.length > 1){
          $.each(plans, function(index, value) {
              $("input[data-id=" + value + "]").prop('checked', true);
              $("input[data-id=" + value + "]").attr("data-ischeck", "yes");

              setSymbol("input[data-id=" + value + "]");
          });
        }
    }

    $(function(){
        // on page load
        setCurrentSPlans();
        updateTotal();
        updateTotalSum();
        getRefundSwitchMsg();

        if ($('.trialWarning').is(":visible")) {
           $('.subcCard').addClass('cardSubsheader')
           $('.cardSubsheader').removeClass('subcCard')
        }
    });
});
