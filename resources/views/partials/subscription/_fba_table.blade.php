  <table class="table baseSubscriptionTable" style="width: 100%">
  <thead>
      <tr class="headerSubscription">
          <th width="16.66%" class="card-header text-center align_middle"></th>
          <th width="30%" class="card-header background-orange text-center text-white">Managed Service</th>
          <!-- <th width="16.66%" class="card-header background-orange text-center text-white">DIY (Do It Yourself)</th> -->
      </tr>
  </thead>
  <tbody>
      <tr class="bodySubscription with_bg">
          <td class="text-center">Fees</td>
          <td class="text-center">{{ trans('home.refundsrecover') }}</td>
          <!-- <td class="text-center">{{ trans('home.refundsrecoverdiy1') }} <span class="fbaRateDiy">$30</span> {{ trans('home.refundsrecoverdiy2') }}</td> -->
      </tr>
  </tbody>
</table>
<script type="text/javascript">
  var xs_gbp;

  $.ajax({
      type: "GET",
      url: 'convertFbaRate',
      success: function(result){
        response = JSON.parse(result);
        
        fbaDiyRate_gbp = response.gbp_fba;
        fbaDiyRate_eur = response.eur_fba;

        convertFbaRate()

    }
  });
  
  function convertFbaRate(){
    var currency = $('#preferredCurrencyEmail').attr('data-preferred-currency');
    if (currency == 'gbp') {
      var currency = "£";
      var rate = fbaDiyRate_gbp.toString();
      rate = rate.split('.');
      (rate[1]) ? rate[1] = '.'+rate[1] : rate[1] = '';
      $('.fbaRateDiy').html(currency+''+rate[0]+''+rate[1].substring(0,3))

    }else if(currency == 'eur'){
      var currency = "€";
      var rate = fbaDiyRate_eur.toString();
      rate = rate.split('.');
      (rate[1]) ? rate[1] = '.'+rate[1] : rate[1] = '';
      $('.fbaRateDiy').html(currency+''+rate[0]+''+rate[1].substring(0,3))

    }else if(currency == 'usd'){
      var fbaDiyRate_usd = 30;
      var currency = "$";
      var rate = fbaDiyRate_usd.toString();
      rate = rate.split('.');
      (rate[1]) ? rate[1] = '.'+rate[1] : rate[1] = '';
      $('.fbaRateDiy').html(currency+''+rate[0]+''+rate[1].substring(0,3))
      
    }
  }
    
</script>