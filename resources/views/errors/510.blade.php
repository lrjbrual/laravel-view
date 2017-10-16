<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials._sassbootstrap')
    <title>Error 510</title>

    <!-- Styles -->
    <link type="text/css" rel="stylesheet" href="{{asset('assets/css/error.css')}}"/>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <a class="navbar-brand font-logo" href="/">
                    <span id="nav-color-orange">Trendle</span>
                    <span id="nav-color-blue">Analytics</span>
                </a>
                <br />
                <!-- content -->
                <div>
                  
                </div>
                <div>
                    <span class="error">ERROR: </span>
                    <span class="error-text">Sorry, further extensions to the request are required for the server to fulfill it.</span>
                </div>
                <br />
                <a href="{{ url('/') }}" class="btn-home">RETURN HOME</a>
                <!-- /content -->
            </div>
        </div>
    </body>
</html>
