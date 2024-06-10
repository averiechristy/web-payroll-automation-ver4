<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo mr-5" href="index.html"><img src="{{asset('images/logoexa.png')}}" class="mr-2" alt="logo"/></a>
        <a class="navbar-brand brand-logo-mini" href="index.html"><img src="{{asset('images/logokecil.png')}}" alt="logo"/></a>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
      
        <ul class="navbar-nav navbar-nav-right">

        <li class="nav-item">
            
              
              <span class="menu-title">{{Auth::user()->nama_user}}</span>
            
          </li>
          
          <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
              <img src="{{asset('images/user.png')}}" alt="profile"/>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
              <a href="{{route('password')}}" class="dropdown-item">
                <i class="ti-lock text-primary"></i>
               Ganti Password
              </a>
              <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                <i class="ti-power-off text-primary"></i>
                Keluar
              </a>
            </div>
          </li>
        
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
          <span class="icon-menu"></span>
        </button>
      </div>
    </nav>