<div class="col-md-12 m-t-25">
  @include('trendle.marketplace.partials._upperdiv')
  @include('trendle.marketplace.partials._inputdiv')
</div>

<script>
$(document).ready(function() {
  $(".verify_sc").on("click",function(e) {
    var thisthis=this;
    var mkp_sellerid = $(this).closest('.panel-body').find('.siddiv').find('input').val();
    var mkp_authtoken = $(this).closest('.panel-body').find('.authtokendiv').find('input').val();
    var mkpid = $(this).attr('channel');
    var sys_sellerid = <?php echo $seller_id ?>;
    var country = $(this).attr('country');

    $(thisthis).attr('disabled','');

    $.ajax({
    type: "POST",
    "url": "marketplace/saveMarketplace",
    'data': {"mkp_sellerid" : mkp_sellerid,"mkp_authtoken" : mkp_authtoken,"mkpid" : mkpid, "sys_sellerid":sys_sellerid, "country":country,"_token":"{{ csrf_token() }}"},
    cache: false,
    success: function(r)
    {
        var rr = JSON.parse(r);
          if(rr.status == 'failed') {
                swal({
                    title: 'Failed',
                    text: 'Verification Failed.',
                    confirmButtonColor: '#4fb7fe'
                }).done();

                $(thisthis).parent().siblings('.siddiv').find('input').val('');
                $(thisthis).parent().siblings('.authtokendiv').find('input').val('');

                $(thisthis).removeClass('btn-primary');
                $(thisthis).addClass('btn-primary');
                $(thisthis).removeAttr('disabled','');
            } else if(rr.status == 'success') {
                swal({
                    title: 'Success',
                    text: 'Data is currently being populated. This can take up to a few hours.',
                    confirmButtonColor: '#4fb7fe'
                }).done();
                return false;

                $(thisthis).removeClass('btn-primary');
                $(thisthis).removeClass('btn-danger');
                $(thisthis).addClass('btn-success');

                $(thisthis).attr('disabled','');
                $(thisthis).siblings('.remove_sc').removeAttr('disabled');
            } else if (rr.status == 'none') {
                $(thisthis).removeClass('btn-success');
                $(thisthis).removeClass('btn-danger');
                $(thisthis).addClass('btn-primary');
            } else if (rr.status == 'exist') {
                swal({
                    title: 'Notice',
                    text: 'Please remove the exising marketplace details before verifying.',
                    confirmButtonColor: '#4fb7fe'
                }).done();
                return false;

                $(thisthis).attr('disabled','');
                $(thisthis).removeClass('btn-primary');
                $(thisthis).removeClass('btn-danger');
                $(thisthis).addClass('btn-success');
            } else if (rr.status == 'error') {
              c = Object.keys(rr.details).length;
              var textoutput='';
              for(var key in rr.details){
                if (!rr.details.hasOwnProperty(key)) continue;
                var obj = rr.details[key];
                for (var prop in obj) {
                    // skip loop if the property is from prototype
                    if(!obj.hasOwnProperty(prop)) continue;

                    // your code
                    textoutput += obj[prop] + '<br/>';
                }
              }
              swal({
                  title: 'Failed',
                  text: textoutput,
                  confirmButtonColor: '#4fb7fe'
              }).done();
              // return false;

              $(thisthis).parent().siblings('.siddiv').find('input').val('');
              $(thisthis).parent().siblings('.authtokendiv').find('input').val('');

              $(thisthis).removeClass('btn-primary');
              $(thisthis).addClass('btn-primary');
              $(thisthis).removeAttr('disabled','');

            }
    }
    });

  });



  $(".remove_sc").on("click",function(e) {


    var thisthis=this;
    var country = $(this).attr('country');
    var mkpid = $(this).attr('channel');
    var sys_sellerid = <?php echo $seller_id ?>;

    $.ajax({
      type: "POST",
      "url": "marketplace/removeMarketplace",
      'data': {"mkpid" : mkpid,"sys_sellerid":sys_sellerid, "country":country,"_token":"{{ csrf_token() }}"},
      cache: false,
      success: function(r)
      {
          swal({
              title: 'Success',
              text: 'Sales channel data removed.',
              confirmButtonColor: '#4fb7fe'
          }).done();
          return false;

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

</script>
