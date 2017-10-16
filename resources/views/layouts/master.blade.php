<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trendle @yield('title')</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" >

    <script src="//code.jquery.com/jquery-1.12.3.js"></script>
    <!-- fontawesome -->
    <link href="https://opensource.keycdn.com/fontawesome/4.7.0/font-awesome.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Oswald" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet">

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link type="text/css" rel="stylesheet" href="{{asset('assets/vendors/tipso/css/tipso.min.css')}}"/>

    <!-- include bootstrap-select css/js-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/css/bootstrap-select.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>

    @include('partials._head')
    <link href="{!! asset('css/app.css') !!}" media="all" rel="stylesheet" type="text/css" />
    @include('partials._googleTagManager')
  </head>
  <body>
    @include('partials._googleTagManagerBody')
    @include('partials._mainnav')
      @yield('content')
    @include('partials._footer')
    <!-- remove for now @include('partials._gotop') -->
    <!--Start Cookie Script-->
    @include('partials._cookie')
    <!--End Cookie Script-->
    <!-- fb and google
    <script type="text/javascript" src="{{asset('js/fb-google-tags.js')}}"></script>
     end -->
    <!-- Script for Chat 
    <script type="text/javascript"> var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date(); (function(){ var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0]; s1.async=true; s1.src='https://embed.tawk.to/5975870c0d1bb37f1f7a589a/default'; s1.charset='UTF-8'; s1.setAttribute('crossorigin','*'); s0.parentNode.insertBefore(s1,s0); })(); </script>
     end -->
    <!-- Plug ins -->
    <script type="text/javascript" src="{{asset('assets/vendors/tipso/js/tipso.min.js')}}"></script>
    <!-- end -->
    <!-- helper -->
    <script type="text/javascript" src="{{ url('js/trendlehelder.js') }}"></script>
    <!-- end -->
  </body>
</html>
