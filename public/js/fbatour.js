$('.modal').css('z-index','1200')
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
            "<button class='btn btn-primary btn-sm btn-nxt' data-role='next'>Next »</button>" +
            "<button class='btn btn-danger no-radius btn-sm dontdisplay btnCloseTour' data-role='end'>End tour</button>" +
          "</div>" +
        "</div>"
    );
  },
  steps: [
  {
    element: ".fbaRefundNav",
    title: "",
    content: "With FBA Refunds, we show you how much money Amazon owes you for damaged and un-returned products.",
    backdrop: true,
    backdropContainer : ".menu_scroll",
    onShow: function (tour) {
      $('.fbaRefundNav').addClass('active');
    },
  },
  {
    element: ".switchery ",
    title: "",
    content: "To use this feature, you need to activate it here.",
    smartPlacement: true,
    backdrop: true,
    backdropContainer : ".bg-container",
  },
  {
    element: "#refunds-setup",
    title: "",
    content: "You can update your filing preferences. You can choose from DIY (Do It Yourself) or Managed, where our trained team handle all cases on your behalf.\nFor more information on each option and billing, please visit our <a style='color:#ff5722' href='http://help.trendle.io/' target='_blank'>Help pages</a>.",
    smartPlacement: true,
    backdrop: true,
    backdropContainer : ".bg-container",
    onShown: function (tour) {
      // $('.btnClose').hide();
      // $('#refunds-setup').attr('disabled', true); 
    },
  },
  {
    element: ".instructions",
    title: "",
    content: "Follow the instructions in this table. The instructions will be different depending on your filing preference.",
    placement: "top",
    backdrop: true,
    backdropContainer : ".bg-container",
    onShown: function (tour) {
      $('.toggleInstruction').css('color','#fff');
      // $('#refunds-setup').attr('disabled', false);
    },
  },
  {
    element: ".toggleInstruction",
    title: "",
    content: "You can minimise the instructions by clicking here.",
    smartPlacement: true,
    backdrop: true,
    backdropPadding: 5,
    backdropContainer : ".bg-container",
    onShown: function (tour) {
      $('.toggleInstruction').css('color','#333333');
      $('.btnCloseTour').hide();
      if (!$('#country-cards-div').length) {
        $('.btnCloseTour').show();
        $('.btn-nxt').attr('disabled',true)
      }
    },
  },
  {
    element: "#country-cards-div",
    title: "",
    content: "Each flip card shows how much you are owed (estimated figures) and how much has been reimbursed so far in each country.\nThese figures are updated every day.",
    smartPlacement: true,
    backdrop: true,
    backdropPadding: 5,
    backdropContainer : ".bg-container",
    onShown: function (tour) {
      $('.toggleInstruction').css('color','#fff');
      $('.btnCloseTour').hide();
      if (!$('.diyTable').length) {
        $('.btnCloseTour').show();
        $('.btn-nxt').attr('disabled',true)
      }
    },
  },
  {
    element: ".diyTable",
    title: "",
    content: "In this table you can see the status of each claim you need to file. For more information on how to use this table and how to file claims, please visit our Help pages.",
    placement: "top",
    backdrop: true,
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