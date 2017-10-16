<!-- .navbar -->
 <nav class="navbar navbar-static-top">
    <div class="container-fluid">
        <a class="navbar-brand text-xs-center" href="/home">
            <h3>
              <span id="nav-color-orange">Trendle</span>
              <span id="nav-color-blue">Analytics</span>
              <span id="nav-color-orange">Admin</span>
            </h3>
        </a>
        <div class="menu">
            <span class="toggle-left" id="menu-toggle">
                <i class="fa fa-bars"></i>
            </span>
        </div>
        <div class="topnav dropdown-menu-right float-xs-right">
          <div class="btn-group">
                <div class="user-settings no-bg">
                    <button type="button" class="btn btn-default no-bg micheal_btn" data-toggle="dropdown">
                        <strong>{{ Auth::user()->email }}</strong>
                        <span class="fa fa-sort-down white_bg"></span>
                    </button>
                    <div class="dropdown-menu admire_admin">
                        <a class="dropdown-item" href="{{ url('/logout') }}" onclick="event.preventDefault();
                                  document.getElementById('logout-form').submit();"><i class="fa fa-sign-out"></i>
                            Log Out
                            <form id="logout-form" action="{{ url('/logout') }}" method="POST">
                              {{ csrf_field() }}
                            </form>
                        </a>
                    </div>
                </div>
            </div>
        </div>
      </div>
<!-- /.head -->
</div>
<!-- /#top -->
<div class="wrapper">
<div id="left">
    <div class="menu_scroll">
        <div class="left_media">
            <div class="media user-media bg-dark dker">
                <div class="user-media-toggleHover">
                    <span class="fa fa-user"></span>
                </div>
                <div class="user-wrapper bg-blue">
                    <a class="user-link" href="#">
                        <p style="color: #ffffff;" class="user-info menu_hide container">
                            {{ Auth::user()->name }}
                        </p>
                    </a>
                </div>
            </div>
            <hr/>
        </div>
        <ul id="menu">
            <li {!! (Request::is('admin/')? 'class="active"':"") !!}>
                <a href="{{ URL::to('admin/') }} ">
                    <i class="fa fa-home"></i>
                    <span class="link-title menu_hide">&nbsp;Dashboard</span>
                </a>
            </li>

            @if(Auth::user()->class == "admin" || Auth::user()->class == "cs")
            <li  {!! (Request::is('admin/fbarefund')? 'class="active"':"") !!}>
                <a href="{{ URL::to('admin/fbarefund') }} ">
                    <i><img class="fa refunds-small-icon" src="{{ url('/images/icons/refunds.png') }}"></i>
                    <span class="link-title menu_hide">&nbsp;FBA Refund</span>
                </a>
            </li>
            @endif

            @if(Auth::user()->class == "admin" || Auth::user()->class == "dev")
            <li {!! (Request::is('admin/cronscheduling')? 'class="active"':"") !!}>
                <a href="{{ URL::to('admin/cronscheduling') }} ">
                    <i class="fa fa-calendar"></i>
                    <span class="link-title menu_hide">&nbsp;Cron Scheduling</span>
                </a>
            </li>

            <li {!! (Request::is('admin/cronlog')? 'class="active"':"") !!}>
                <a href="{{ URL::to('admin/cronlog') }} ">
                    <i class="fa fa-bar-chart"></i>
                    <span class="link-title menu_hide">&nbsp;Cronlogs</span>
                </a>
            </li>
            @endif
        </ul>
        <!-- /#menu -->
    </div>
</div>
<!-- /#left -->
<script>

var xmlHttp;

function srvTime(){
    try {
        //FF, Opera, Safari, Chrome
        xmlHttp = new XMLHttpRequest();
    }
    catch (err1) {
        //IE
        try {
            xmlHttp = new ActiveXObject('Msxml2.XMLHTTP');
        }
        catch (err2) {
            try {
                xmlHttp = new ActiveXObject('Microsoft.XMLHTTP');
            }
            catch (eerr3) {
                //AJAX not supported, use CPU time.
                alert("AJAX not supported");
            }
        }
    }
    xmlHttp.open('HEAD',window.location.href.toString(),false);
    xmlHttp.setRequestHeader("Content-Type", "text/html");
    xmlHttp.send('');
    return xmlHttp.getResponseHeader("Date");
}
var pageuptime=0;
function updateClock (st)
{

  var currentTime1 = new Date(st);

  var currentTime = new Date(currentTime1.getTime() + (pageuptime*1000));
  var currentHours = addpad(currentTime.getHours());
  var currentMinutes = addpad(currentTime.getMinutes());
  var currentSeconds = addpad(currentTime.getSeconds());


  var currentTimeString = currentHours + ":" + currentMinutes + ":" + currentSeconds;


  $("#clock").html(currentTimeString);
  pageuptime++;
}

function addpad(str){
  var str = "" + str;
  var pad = "00"
  var r = pad.substring(0, pad.length - str.length) + str;
  return r;
}

$(document).ready(function()
{
  var st = srvTime() + 1;
   setInterval('updateClock("'+st+'")', 1000);
});

</script>
