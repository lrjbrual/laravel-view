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
    element: "#mkpt_chosen",
    title: "",
    content: "Choose the country that the email should be sent to. You can choose one or several at a time.",
    backdrop: true,
    placement: 'right',
    backdropContainer : ".menu_scroll",
    backdropPadding: 5,
    // onShow: function (tour) {
    //   $('.automaticEmailNav').addClass('active');
    // },
  },
  {
    element: "#campaignname",
    title: "",
    content: "Give the email a name. This is just for you. You can create up to 5 emails per campaign.",
    placement: 'right',
    backdropPadding: 5,
    backdrop: true,
    backdropContainer : ".bg-container",
    
  },
  {
    element: ".switchTour",
    title: "",
    content: "Set whether the email is Active or Inactive.",
    smartPlacement: true,
    backdropPadding: 10,
    backdrop: true,
    backdropContainer : ".bg-container",
    onShown: function (tour) {
      // $('.btnCloseTour').hide();
    },
  },
  {
    element: ".messageDayTour",
    title: "",
    content: "Set how many days after the trigger event the email should be sent",
    placement: 'right',
    backdrop: true,
    backdropPadding: 13,
    backdropContainer : ".bg-container",
    onShown: function (tour) {
      // $('.btnCloseTour').show();
    },
  },
  {
    element: ".sendMessageTour",
    title: "",
    content: "Choose the trigger event to send the email",
    placement: 'right',
    backdrop: true,
    backdropPadding: 13,
    backdropContainer : ".bg-container",
    onShown: function (tour) {
      // $('.btnCloseTour').show();
    },
  },
  {
    element: ".autofillTour",
    title: "",
    content: "Use \"Auto Fill Tags\" to personalise the email subject",
    placement: 'top',
    backdrop: true,
    backdropPadding: 13,
    backdropContainer : ".bg-container",
    onShown: function (tour) {
      // $('.btnCloseTour').show();
    },
  },
  {
    element: ".note-tags",
    title: "",
    content: "Use \"Auto Fill Tags\" to personalise the email body",
    placement: 'right',
    backdrop: true,
    backdropPadding: 13,
    backdropContainer : ".bg-container",
    onShown: function (tour) {
      // $('.btnCloseTour').show();
    },
  },
  {
    element: ".spanbtn",
    title: "",
    content: "Add an attachment to send with the email",
    placement: 'top',
    backdrop: true,
    backdropContainer : ".bg-container",
    onShown: function (tour) {
      // $('.btnCloseTour').show();
    },
  },
  {
    element: ".sendtest",
    title: "",
    content: "Send a test to any email address to visual the end result. Note: The Auto Fill Tags will not be filled",
    placement: 'top',
    backdrop: true,
    backdropPadding: 5,
    backdropContainer : ".bg-container",
    onShown: function (tour) {
      // $('.btnCloseTour').show();
    },
  },
  {
    element: ".savechanges_forshowbtn",
    title: "",
    content: "Save the email",
    placement: 'top',
    backdrop: true,
    backdropPadding: 5,
    backdropContainer : ".bg-container",
    onShown: function (tour) {
      $('.btnCloseTour').hide();
    },
  },
  {
    element: ".savetemp",
    title: "",
    content: "Save the email as a template so you can re-use it for another campaign",
    placement: 'top',
    backdrop: true,
    backdropPadding: 5,
    backdropContainer : ".bg-container",
    onShown: function (tour) {
      $('.btnCloseTour').show();
    },
  }

]});

// Start the tour

$('.takeATour').click(function(){
    tour.init();
    tour.restart();
})