$(document).ready(function() {
  modal_event = 'proceed';
  var objects;

  // Events
  $("#refunds-switch").change(function () {

      objects = {
        activate: $("#refunds-switch").is(':checked')
      }

      if($("#refunds-switch").is(':checked') == true){
         
          if (modal_event == 'proceed'){
                
                /*COMMENTED BY JASON FOR TEMPORARY REMOVING OF DIY OPTION UPON ACTIVATING*/
          //     getRefundSwitchMsg();
          //     $("#refunds-modal-activate").modal('show');

              activateFeature()

          } else {
              modal_event = 'proceed';
          }
      } else {
         
          if (modal_event == 'proceed'){
                 /*COMMENTED BY JASON FOR TEMPORARY REMOVING OF DIY OPTION UPON ACTIVATING*/
          //     getRefundSwitchMsg();
          //     $("#refunds-modal-deactivate").modal('show');

              deactivateFeature()

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

  $(".instructions #action").click(function(){
      $(".instructions #msg").toggle();
      $(".instructions #header").css('margin-bottom', '0px');

      if($(this).hasClass("fa-window-minimize")){
          $(this).removeClass("fa-window-minimize");
          $(this).addClass("fa-window-maximize");
          $(".instructions #action").css('top', '0px');
      } else {
          $(this).removeClass("fa-window-maximize");
          $(this).addClass("fa-window-minimize");
          $(".instructions #action").css('top', '-5px');
      }
  });

  // Functions
  /*ADDED BY JASON FOR TEMPORARY REMOVING OF DIY OPTION UPON ACTIVATING*/
  function activateFeature(){

      $('#refunds-switch-msg').html('Feature Activated')
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
            $('#refunds-switch-msg').html('Feature Deactivated')

            cancelSwitchState()

          }
        }).catch(swal.noop)

  }

  function deactivateFeature(){

      $('#refunds-switch-msg').html('Feature Deactivated')
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
            $('#refunds-switch-msg').html('Feature Activated')

            cancelSwitchState()

          }
        }).catch(swal.noop)

  }

  /*END*/

  function getRefundSwitchMsg(){
      if($("#refunds-switch").is(':checked') == true){
        msg = "Feature Activated";
      } else {
        msg = "Feature Deactivated";
      }

      $("#refunds-switch-msg").html(msg);
  }

  function cancelSwitchState(){
      modal_event = 'cancel';
      switchbutton = $("#refunds-switch");
      switchbutton.trigger('click');
      getRefundSwitchMsg()
  }

  $(".refunds-marketplace").flip({
      axis: 'x',
      trigger: 'click',
      forceHeight: false,
      forceWidth: false,
      autoSize: false
  });

  // On page load
  $(function(){
      getRefundSwitchMsg();
  });
});
