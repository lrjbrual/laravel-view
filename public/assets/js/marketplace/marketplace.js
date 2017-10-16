$(document).ready(function() {
  $(".verify_sc").on("click",function(e) {
    var thisthis=this;
    var mkp_sellerid = $(this).parent().siblings('.siddiv').find('input').val();
    var mkp_authtoken = $(this).parent().siblings('.authtokendiv').find('input').val();
    var mkpid = $(this).attr('channel');
    var sys_sellerid = 1;
    var country = $(this).attr('country');


    // var growlp = $.growl({
    //   title: "Saving",
    //   style: "primary",
    //   message: 'Please wait... "> ',
    //   duration: 0,
    //   close: ''
    // });

    $(thisthis).attr('disabled','');
    $.ajax({
    type: "POST",
    "url": "marketplace/saveMarketplace",
    'data': {"mkp_sellerid" : mkp_sellerid,"mkp_authtoken" : mkp_authtoken,"mkpid" : mkpid, "sys_sellerid":sys_sellerid, "country":country,"_token":"{{ csrf_token() }}"},
    cache: false,
    success: function(r)
    {
      console.log(r);
      // growlp.dismiss();
      // growlp.$growl().remove();

            if(r == 'failed') {
                // $.growl.error({
                //     title: "Failed",
                //     message: "Verification Failed."
                // });

                $(thisthis).parent().siblings('.siddiv').find('input').val('');
                $(thisthis).parent().siblings('.authtokendiv').find('input').val('');

                $(thisthis).removeClass('btn-primary');
                $(thisthis).addClass('btn-danger');
                $(thisthis).removeAttr('disabled','');
            } else if(r == 'success') {
                // $.growl.notice({
                //     title: "Success",
                //     message: "Data is currently being populated. This can take up to a few hours."
                // });

                $(thisthis).removeClass('btn-primary');
                $(thisthis).removeClass('btn-danger');
                $(thisthis).addClass('btn-success');

                $(thisthis).attr('disabled','');
                $(thisthis).siblings('.remove_sc').removeAttr('disabled');
            } else if (r == 'none') {
                $(thisthis).removeClass('btn-success');
                $(thisthis).removeClass('btn-danger');
                $(thisthis).addClass('btn-primary');
            } else if (r == 'exist') {
                // $.growl.warning({
                //     title: "Notice",
                //     message: "Please remove the exising marketplace details before verifying."
                // });
                $(thisthis).attr('disabled','');
                $(thisthis).removeClass('btn-primary');
                $(thisthis).removeClass('btn-danger');
                $(thisthis).addClass('btn-success');
            }


    }success: function(r)
    {
      console.log('zxc');
    }
    });

  });



  $(".remove_sc").on("click",function(e) {


    var thisthis=this;
    var country = $(this).attr('country');
    var mkpid = $(this).attr('channel');
    var sys_sellerid = 1;
    // var growlp = $.growl({
    //   title: "Removing sales channel data",
    //   style: "primary",
    //   message: 'Please wait...  ',
    //   duration: 0,
    //   close: ''
    // });

    $.ajax({
      type: "POST",
      "url": "marketplace/removeMarketplace",
      'data': {"mkpid" : mkpid,"sys_sellerid":sys_sellerid, "country":country,"_token":"{{ csrf_token() }}"},
      cache: false,
      success: function(r)
      {
      console.log(r);
          // growlp.dismiss();
          // growlp.$growl().remove();
          //
          // $.growl.notice({
          //     title: "Success",
          //     message: "Sales channel data removed."
          // });

          $(thisthis).parent().siblings('.siddiv').find('input').val('');
          $(thisthis).parent().siblings('.authtokendiv').find('input').val('');

          $(thisthis).attr('disabled','');
          $(thisthis).siblings('.verify_sc').removeAttr('disabled');

          $(thisthis).prev().removeClass('btn-success');
          $(thisthis).prev().removeClass('btn-danger');
          $(thisthis).prev().addClass('btn-primary');
      }
    });

  });
});
