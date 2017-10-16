<script type="text/javascript" src="{{asset('assets/js/custom.js')}}"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.12.0/moment-with-locales.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://www.appelsiini.net/download/jquery.jeditable.mini.js"></script>

<!--plugin scripts-->
<script type="text/javascript" src="{{asset('assets/vendors/select2/js/select2.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/datatables/js/jquery.dataTables.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/pluginjs/dataTables.tableTools.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/datatables/js/dataTables.colReorder.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/datatables/js/dataTables.bootstrap.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/datatables/js/dataTables.buttons.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/datatables/js/dataTables.responsive.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/datatables/js/dataTables.rowReorder.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/datatables/js/buttons.colVis.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/datatables/js/buttons.html5.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/datatables/js/buttons.bootstrap.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/datatables/js/buttons.print.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/datatables/js/dataTables.scroller.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/bootstrap-switch/js/bootstrap-switch.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/switchery/js/switchery.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/flip/js/jquery.flip.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/sweetalert/js/sweetalert2.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/countUp.js/js/countUp.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/swiper/js/swiper.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/slimscroll/js/jquery.slimscroll.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/raphael/js/raphael-min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/d3/js/d3.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/c3/js/c3.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/toastr/js/toastr.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/datepicker/js/bootstrap-datepicker.min.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/vendors/bootstrap-tour/js/bootstrap-tour-standalone.min.js')}}"></script>
<!-- end of plugin scripts -->

<!--Page level scripts-->
<script type="text/javascript" src="{{asset('assets/js/pages/radio_checkbox.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/pages/cards.js')}}"></script>
@if(Request::is('campaign'))
<script src="{{ url('js/automaticemailtour.js') }}"></script>
@elseif(Request::segment(2) == 'newcampaign' || Request::segment(2) == 'loadcampaign')
<script src="{{ url('js/newcampaigntour.js') }}"></script>
@elseif(Request::is('refund'))
<script src="{{ url('js/fbatour.js') }}"></script>
@endif

@if(Request::segment(1) != "admin" )
@if(Request::segment(2) == "" )
  <script type="text/javascript" src="{{ url('js/onboardingtour.js') }}"></script>
@endif
@endif
<!--End of Page level scripts-->

<script type="text/javascript" src="{{ url('js/trendlehelder.js') }}"></script>
<!-- fb and google tags -->
<script type="text/javascript" src="{{asset('js/fb-google-tags.js')}}"></script>
<!-- end -->
