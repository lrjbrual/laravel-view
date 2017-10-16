$(document).ready(function() {
  $("#pricing-currency").on('hidden.bs.select', function (e) {
    currency = $(this).val();

    if (currency == "gbp") {
      $("#crm-xs").html("£5");
      $("#crm-s").html("£10");
      $("#crm-m").html("£25");
      $("#crm-l").html("£40");
      $("#crm-xl").html("£60");

      $("#p-reviews-all").html("£25");
      $("#p-reviews-xs").html("£25");
      $("#p-reviews-s").html("£50");
      $("#p-reviews-m").html("£75");
      $("#p-reviews-l").html("£100");
      $("#p-reviews-xl").html("£250");

      $("#keywords-xs").html("£5");
      $("#keywords-s").html("£50");
      $("#keywords-m").html("£100");
      $("#keywords-l").html("£200");
      $("#keywords-xl").html("£400");

      $("#analytics-start-price").html("£10");
      $("#analytics-end-price").html("£100");
    } else if(currency == "eur"){
      $("#crm-xs").html("€6");
      $("#crm-s").html("€12");
      $("#crm-m").html("€30");
      $("#crm-l").html("€47");
      $("#crm-xl").html("€70");

      $("#p-reviews-all").html("€30");
      $("#p-reviews-xs").html("€30");
      $("#p-reviews-s").html("€60");
      $("#p-reviews-m").html("€89");
      $("#p-reviews-l").html("€120");
      $("#p-reviews-xl").html("€299");

      $("#keywords-xs").html("€6");
      $("#keywords-s").html("€60");
      $("#keywords-m").html("€120");
      $("#keywords-l").html("€240");
      $("#keywords-xl").html("€480");

      $("#analytics-start-price").html("€12");
      $("#analytics-end-price").html("€120");
    } else if(currency == "usd"){
      $("#crm-xs").html("$7");
      $("#crm-s").html("$13");
      $("#crm-m").html("$32");
      $("#crm-l").html("$50");
      $("#crm-xl").html("$75");

      $("#p-reviews-all").html("$32");
      $("#p-reviews-xs").html("$32");
      $("#p-reviews-s").html("$64");
      $("#p-reviews-m").html("$95");
      $("#p-reviews-l").html("$130");
      $("#p-reviews-xl").html("$310");

      $("#keywords-xs").html("$7");
      $("#keywords-s").html("$64");
      $("#keywords-m").html("$130");
      $("#keywords-l").html("$260");
      $("#keywords-xl").html("$520");

      $("#analytics-start-price").html("$13");
      $("#analytics-end-price").html("$130");
    }

  });

});
