"use strict";
$(document).ready(function() {
    var reviews_count = $("#seller_review_count").val();
    var total_owed = $("#total_owed").val();
    var total_reimburse = $("#total_reimburse").val();
    var number_sent = $("#number_sent").val();
    var credit = $("#credit").val();
    var options = {
        useEasing: true,
        useGrouping: true,
        separator: ',',
        decimal: '.',
        prefix: '',
        suffix: ''
    };
    new CountUp("sales_count", 0, total_owed, 0, 2.5, options).start();
    new CountUp("sales_count1", 0, total_reimburse, 0, 2.5, options).start();
    new CountUp("emails_sent1", 0, number_sent, 0, 2.5, options).start();
    new CountUp("emails_sent3", 0, credit, 0, 2.5, options).start();
    new CountUp("number_review1", 0, reviews_count, 0, 2.5, options).start();
});