<nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">
          <li class="nav-item">
            <a class="nav-link" href="{{route('dashboard')}}">
              
              <span class="menu-title">Dashboard</span>
            </a>
          </li>
          <li class="nav-item {{ Request::is('user') ||  Request::is('user/create')? 'active' : '' }} ">
            <a class="nav-link" href="{{route('user')}}">
              
              <span class="menu-title">User</span>
            </a>
          </li>

          <!-- <li class="nav-item {{ Request::is('penempatan') ||  Request::is('penempatan/create')? 'active' : '' }}">
            <a class="nav-link" href="{{route('penempatan')}}">
              
              <span class="menu-title">Penempatan</span>
            </a>
          </li> -->
          
<li class="nav-item">
  <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="{{ Request::is('penempatan') ||  Request::is('penempatan/create') ||  Request::is('posisi/create')||  Request::is('karyawan/create') ? 'true' : 'false' }}" aria-controls="ui-basic">
    <span class="menu-title">Master Data</span>
    <i class="menu-arrow"></i>
  </a>
  <div class="collapse {{ Request::is('penempatan') ||  Request::is('penempatan/create') || Request::is('holiday/create') || Request::is('holiday') ||     Request::is('posisi/create')||  Request::is('karyawan/create') ||  Request::is('gaji') ||  Request::is('gaji/create') ? 'show' : '' }}" id="ui-basic">
    <ul class="nav flex-column sub-menu">
    <li class="nav-item {{ Request::is('holiday') ||  Request::is('holiday/create')? 'active' : '' }}"> 
        <a class="nav-link" href="{{ route('holiday') }}">Data Libur</a>
      </li>
      <li class="nav-item {{ Request::is('penempatan') ||  Request::is('penempatan/create')? 'active' : '' }}"> 
        <a class="nav-link" href="{{ route('penempatan') }}">Penempatan</a>
      </li>
      <li class="nav-item {{ Request::is('posisi') ||  Request::is('posisi/create')? 'active' : '' }}"> 
        <a class="nav-link" href="{{ route('posisi') }}">Posisi</a>
      </li>
      
      <li class="nav-item {{ Request::is('karyawan') ||  Request::is('karyawan/create')? 'active' : '' }}"> 
        <a class="nav-link" href="{{ route('karyawan') }}">Karyawan</a>
      </li>    
      <li class="nav-item {{ Request::is('gaji') ||  Request::is('gaji/create')? 'active' : '' }}"> 
        <a class="nav-link" href="{{ route('gaji') }}">Gaji & Tunjangan</a>
      </li>
    
    </ul>
  </div>
</li>


          <li class="nav-item">
          <li class="nav-item {{ Request::is('mad') ||  Request::is('mad/create')? 'active' : '' }} ">
            <a class="nav-link" href="{{route('mad')}}">
              
              <span class="menu-title">MAD</span>
            </a>
          </li>
        
        </ul>
      </nav>