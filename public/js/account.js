$(document).ready(function() {
  $("#deleteAccount").click(function(){
    if ($("#reason").val() == "") {
      $("#reason").focus();
    }
  });

  $("#deleteAccountConfirmed").click(function(){
    $.ajax({
        url: "delete-confirmed",
        type: "post",
        data: {
            "reason": $("#reason").val()
        },
        success: function(response){
            $('#logout-form').submit();
        }
    });
  });
});
