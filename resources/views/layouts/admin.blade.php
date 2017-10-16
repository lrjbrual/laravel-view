<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Trendle Analytics @yield('title')</title>
    {{-- <link rel="shortcut icon" href="{{asset('assets/img/logo1.ico')}}"/> --}}
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" >
    <!-- global styles-->
    <link type="text/css" rel="stylesheet" href="{{asset('assets/css/components.css')}}"/>
    <link type="text/css" rel="stylesheet" href="{{asset('assets/css/custom.css')}}"/>
    @yield('header_styles')
    @include('partials._head')
    @include('partials._sassbootstrap')
    @include('partials._facebookpixel')
  </head>
  <body>
    @include('partials._preloader')
    <div class="bg-dark">
        <div id="top">
            @include('partials._adminnav')

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
  </body>
</html>
@include('partials._csrf')