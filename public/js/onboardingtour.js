var href = $('.marketPlaceTour a').attr('href');
var tourOnboarding = new Tour({
  baseUrl: window.location.origin,
  template: function () {
    return (
       "<div class='popover tour'>" + // No.
          "<div class='arrow'></div>" +
          "<h3 class='popover-title'></h3>" +
          "<div class='popover-content'></div>" +
          "<div class='popover-navigation'>" +
            "<button class='btn btn-primary btn-sm' data-role='prev'>« Prev</button>" +
            "<span data-role='separator'> </span>" +
            "<button class='btn btn-primary btn-sm btnNext' data-role='next'>Next »</button>" +
            "<button class='btn btn-danger no-radius btn-sm dontdisplay btnCloseTour' data-role='end'>End tour</button>" +
          "</div>" +
        "</div>"
    );
  },
  steps: [
  {
    element: ".marketPlaceTour",
    title: "Welcome to Trendle Analytics!",
    content: "To get started, please authorise us to access your data. Simply follow the steps on the \"Marketplaces\" page.",
    backdrop: true,
    backdropContainer : ".menu_scroll",
    onShow: function (tourOnboarding) {
      $('#dashboardErrorModal').modal('hide');
      $('.settingsNav').addClass('active');
      $('.marketPlaceTour').css('background','white');
      $('.marketPlaceTour a').attr('href','#');
    },
    function (tourOnboarding) {
      $('.settingsNav').removeClass('active');
      $('.marketPlaceTour').css('background','white');
    },
    path: '/home'
  },
  {
    element: ".marketplaceCountainerTour",
    title: "Authorise Trendle",
    content: "Please follow the instructions on the page. It should take you less than 5 minutes to complete. Once you have entered your authorisation token, we will begin downloading your data which may take a few hours. In the meantime, check your subscription options and your billing details. These need to be up to date when the Free Trial ends.",
    smartPlacement: true,
    backdrop: true,
    backdropContainer : ".bg-container",
    path: '/marketplace',
    onShow: function (tourOnboarding) {
      $('.settingsNav').addClass('active');
      $('.marketPlaceTour').css('background','white');
      $('.marketPlaceTour a').attr('href',href);
    },
    onShown: function (tourOnboarding) {
      $.ajax({
        url: 'get_on_board',
        method: 'GET',
        success: function(result){
          response = jQuery.parseJSON(result)
          if (response[0].mkp == 'true') {
              // tourOnboarding.goTo(2)
          }
        }
      })
    },
    function (tourOnboarding) {
      $('.settingsNav').removeClass('active');
      $('.marketPlaceTour').css('background','white');
    },
  },
  {
    element: ".login_amazon",
    title: "Welcome to market places page!",
    content: "Click on the button (Login with Amazon) and login your Amazon account",
    smartPlacement: true,
    backdrop: true,
    backdropContainer : ".bg-container",
    path: '/marketplace',
    onShown: function (tourOnboarding) {
      $.ajax({
        url: 'get_on_board',
        method: 'GET',
        success: function(result){
          response = jQuery.parseJSON(result)
          if (response[0].login_amazon == 'true') {
              // tourOnboarding.goTo(3)
          }
        }
      })
    },
    onShow: function (tourOnboarding) {
      $('.settingsNav').addClass('active');
      $('.subscriptionTourNav').css('background','white');
      
    },
    onPrev: function (tourOnboarding) {
      $.ajax({
        url: 'get_on_board',
        method: 'GET',
        success: function(result){
          response = jQuery.parseJSON(result)
          if (response[0].mkp == 'true') {
              // tourOnboarding.goTo(0)
          }
        }
      })
    },
    function (tourOnboarding) {
      $('.settingsNav').removeClass('active');
      $('.subscriptionTourNav').css('background','white');
    },
  },
  {
    element: ".subscriptionTour",
    title: "Manage Your Subscription",
    content: "Choose the subscription you want to use during the Free Trial. You can change this at anytime. You will not be billed whilst in the Free Trial period.",
    placement: 'top',
    backdrop: true,
    backdropContainer : ".bg-container",
    path: '/subscription',
    onShow: function (tourOnboarding) {
      $('.btnCloseTour').hide();
      $('.settingsNav').addClass('active');
      $('.subscriptionTourNav').css('background','white');
    },
    onPrev: function (tourOnboarding) {
      $.ajax({
        url: 'get_on_board',
        method: 'GET',
        success: function(result){
          response = jQuery.parseJSON(result)
          if (response[0].login_amazon == 'false') {
              // tourOnboarding.goTo(2)
          }
        }
      })
    },
    onShown: function (tourOnboarding) {
      $.ajax({
        url: 'get_on_board',
        method: 'GET',
        success: function(result){
          response = jQuery.parseJSON(result)
          if (response[0].billing == 'false') {
              $('.btnCloseTour').show();
              $('.btnNext').attr('disabled',true)
          }
        }
      })
    },
    function (tourOnboarding) {
      $('.settingsNav').removeClass('active');
      $('.subscriptionTourNav').css('background','white');
    },
  },
  {
    element: ".billingTour",
    title: "Update Your Billing Information",
    content: "To ensure an uninterrupted service after the end of your Free Trial period, make sure your billing address (including VAT number if applicable) and payment method are up to date. You can also set your preferred currency to be billed in.",
    placement: 'top',
    backdrop: true,
    backdropContainer : ".bg-container",
    path: '/billing',
    onShow: function () {
      $('.settingsNav').addClass('active');
      $('.billingTourNav').css('background','white');
    },
    onShown: function (tourOnboarding) {
      $('#billingErrorModal').modal('hide');
      $('.btnCloseTour').show();
      $.ajax({
        url: 'get_on_board',
        method: 'GET',
        success: function(result){
          response = jQuery.parseJSON(result)
          if (response[0].billing == 'true') {
                
                $.ajax({
                  url: 'update_onboaring_billing',
                  method: 'GET',
                  success: function(result){}
                })

          }
        }
      })
    },
    function (tourOnboarding) {
      $('.settingsNav').removeClass('active');
      $('.billingTourNav').css('background','white');
    },
  }
]});

tourOnboarding.init(); 
// Start the tour
$.ajax({
  url: 'get_on_board',
  method: 'GET',
  success: function(result){
    response = jQuery.parseJSON(result)
    if (response[0].mkp == 'false' || response[0].login_amazon == 'false' || response[0].billing == 'true') {
      tourOnboarding.start(); 
    }
  }
})