<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="{{asset('assets/img/logo1.ico')}}"/>
    <!-- global styles-->
    <link type="text/css" rel="stylesheet" href="{{asset('assets/css/components.css')}}"/>
    <link type="text/css" rel="stylesheet" href="{{asset('assets/css/app.css')}}"/>
    <link type="text/css" rel="stylesheet" href="#" id="skin_change"/>

    @yield('header_styles')

    <title>Trendle Analytics @yield('title')</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" >
    @include('partials._head')
    @include('partials._sassbootstrap')
    @include('partials._googleTagManager')
  </head>
  <body>
    @include('partials._googleTagManagerBody')
    @include('partials._preloader')
    <div class="bg-dark" id="wrap" style="margin: 0;">
        <div id="top">
            @include('partials._usernav')

            <div id="content" class="bg-container">
                <!-- Content -->
            @yield('user')
            <!-- Content end -->
            </div>
        </div>
        <!-- /#content -->
    </div>
    <!-- /#wrap -->

    <!-- page level js -->
    @yield('footer_scripts')

    <!-- end page level js -->
    @include('partials._jsbootstrap')
    <!-- include('layouts.right_sidebar') -->
    <!-- Script for Chat
    <script type="text/javascript"> var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date(); (function(){ var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0]; s1.async=true; s1.src='https://embed.tawk.to/5975870c0d1bb37f1f7a589a/default'; s1.charset='UTF-8'; s1.setAttribute('crossorigin','*'); s0.parentNode.insertBefore(s1,s0); })(); </script>
    end -->
  </body>
</html>
@include('partials._csrf')
