var tour = new Tour({
  // debug: true,
  storage: false,
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
            "<button class='btn btn-primary btn-sm' data-role='next'>Next »</button>" +
            "<button class='btn btn-danger no-radius btn-sm dontdisplay btnCloseTour' data-role='end'>End tour</button>" +
          "</div>" +
        "</div>"
    );
  },
  steps: [
  {
    element: ".automaticEmailNav",
    title: "",
    content: "With the \"Automatic\" Emails' feature you can create email templates and set rules for when each email should be sent to your customers. For more information, please visit our <a style='color:#FF8000' target='_blank' href='http://help.trendle.io/'>Help pages</a>.",
    backdrop: true,
    backdropContainer : ".menu_scroll",
    onShow: function (tour) {
      $('.automaticEmailNav').addClass('active');
    },
  },
  {
    element: ".instructions",
    title: "",
    content: "Make sure to follow this simple step to enable emails to reach your customers.",
    placement: 'bottom',
    backdrop: true,
    backdropContainer : ".bg-container",
    
  },
  {
    element: "#addnewcampaignmodalbtn",
    title: "",
    content: "To create a new Automatic Email campaign, click here.",
    smartPlacement: true,
    backdrop: true,
    backdropContainer : ".bg-container",
    onShown: function (tour) {
      // $('.btnCloseTour').hide();
    },
  },
  {
    element: ".camgain_div",
    title: "",
    content: "All your existing campaigns appear below here.",
    placement: 'top',
    backdrop: true,
    backdropPadding: 5,
    backdropContainer : ".bg-container",
    onShown: function (tour) {
      $('.btnCloseTour').show();
    },
  }
  
]});

// Initialize the tour

// Start the tour

$('.takeATour').click(function(){
    tour.init();
    tour.restart();
})