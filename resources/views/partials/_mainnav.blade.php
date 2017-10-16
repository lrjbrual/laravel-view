<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed mobile-collapse-margin" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand font-logo"  href="/">
        <span id="nav-color-orange">Trendle</span>
        <span id="nav-color-blue">Analytics</span>
      </a>
    </div> <!-- end of of logo -->
    <!-- navbar to collapse / for non collapsable -->
        <div class="nav_center_text text-orange">The Leading All-In-One Tool For Amazon Sellers</div>
      <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav navbar-right">
          <!-- <li><a href="/" class="active menu-orange">{{ strtoupper(trans('home.home')) }}</a></li> -->
          <li><a href="..#features">{{ mb_strtoupper(trans('home.features'), 'UTF-8') }}</a></li>
          <li><a href="/pricing">{{ mb_strtoupper(trans('home.pricing'), 'UTF-8') }}</a></li>
          <li><a href="/contact">{{ strtoupper(trans('home.contact')) }}</a></li>
          @if (Auth::guest())
            <li>
              <a href="/login">{{ strtoupper(trans('home.login')) }}</a>
            </li>
            <!-- <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                @if(Config::get('app.locale') == 'en')
                  <img src="{{ url('/images/icons/en.png') }}">
                @else
                  <img src="{{ url('/images/icons/fn.png') }}">
                @endif
              </a>
              <ul class="dropdown-menu">
                @if (! empty($locales))
                @foreach ($locales as $key => $value)
                  <li>
                    <a href="/setLang/{{$key}}">{{ $value }}</a>
                  </li>
                @endforeach
                @else
                  <li>
                    <a href="/setLang/en">English</a>
                  </li>
                  <li>
                    <a href="/setLang/fr">Français</a>
                  </li>
                @endif
              </ul>
            </li> -->
              <a href="/register" type="submit" class="btn btn-sm register-color" >REGISTER</a>
          @else
              <a href="/home" class="btn btn-trendle">App Trendle</a>
              <a href="{{ url('/logout') }}"
                  onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();" class="btn btn-sm btn-logout">
                  Logout
                <form id="logout-form" action="{{ url('/logout') }}" method="POST">
                  {{ csrf_field() }}
                </form>
              </a>
          @endif
        </ul>
      </div> <!-- end navbar to collapse -->
    </div>
  </div>
</nav>
