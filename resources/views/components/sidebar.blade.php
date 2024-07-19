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

    <li class="nav-item">
      <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="{{ Request::is('penempatan') ||  Request::is('penempatan/create') ||  Request::is('posisi/create')||  Request::is('karyawan/create') ||Request::is('organisasi/create')||Request::is('organisasi') |Request::is('divisi/create')||Request::is('divisi')? 'true' : 'false' }}" aria-controls="ui-basic">
        <span class="menu-title">Master Data</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse {{ Request::is('penempatan') ||  Request::is('penempatan/create') || Request::is('holiday/create') || Request::is('holiday') ||     Request::is('posisi/create')||  Request::is('karyawan/create') ||  Request::is('gaji') ||  Request::is('gaji/create') |Request::is('organisasi/create')||Request::is('organisasi')|Request::is('divisi/create')||Request::is('divisi')? 'show' : '' }}" id="ui-basic">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item {{ Request::is('holiday') ||  Request::is('holiday/create')? 'active' : '' }}"> 
            <a class="nav-link" href="{{ route('holiday') }}">Data Libur</a>
          </li>
          <li class="nav-item {{ Request::is('organisasi') ||  Request::is('organisasi/create')? 'active' : '' }}"> 
            <a class="nav-link" href="{{ route('organisasi') }}">Organisasi</a>
          </li>
          <li class="nav-item {{ Request::is('divisi') ||  Request::is('divisi/create')? 'active' : '' }}"> 
            <a class="nav-link" href="{{ route('divisi') }}">Divisi</a>
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

          <li class="nav-item {{ Request::is('kontrak') ||  Request::is('kontrak/create')? 'active' : '' }}"> 
            <a class="nav-link" href="{{ route('kontrak') }}">Kontrak Karyawan</a>
          </li>
          <li class="nav-item {{ Request::is('gaji') ||  Request::is('gaji/create')? 'active' : '' }}"> 
            <a class="nav-link" href="{{ route('gaji') }}">Gaji & Tunjangan</a>
          </li>
        </ul>
      </div>
    </li>

    <li class="nav-item {{ Request::is('konfigurasi') ? 'active' : '' }} ">
      <a class="nav-link" href="{{route('konfigurasi')}}">
        <span class="menu-title">Konfigurasi</span>
      </a>
    </li>

    <li class="nav-item {{ Request::is('attendance') ? 'active' : '' }} ">
      <a class="nav-link" href="{{route('attendance')}}">
        <span class="menu-title">Attendance</span>
      </a>
    </li>

    <li class="nav-item {{ Request::is('overtime') ? 'active' : '' }} ">
      <a class="nav-link" href="{{route('overtime')}}">
        <span class="menu-title">Overtime</span>
      </a>
    </li>


    <li class="nav-item {{ Request::is('allowance') ? 'active' : '' }}"> 
            <a class="nav-link" href="{{ route('allowance') }}">Uang Saku & Insentif</a>
          </li>

  
    <li class="nav-item">
      <a class="nav-link" data-toggle="collapse" href="#laporan-payroll" aria-expanded="{{ Request::is('kompensasi') ||  Request::is('lembur') ||  Request::is('allowance') ||  Request::is('payroll')? 'true' : 'false' }}" aria-controls="laporan-payroll">
        <span class="menu-title">Laporan Payroll</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse {{ Request::is('kompensasi') ||  Request::is('lembur') ||  Request::is('allowance') ||  Request::is('payroll')? 'show' : '' }}" id="laporan-payroll">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item {{ Request::is('kompensasi') ? 'active' : '' }}"> 
            <a class="nav-link" href="{{ route('kompensasi') }}">Kompensasi</a>
          </li>
          <li class="nav-item {{ Request::is('lembur') ? 'active' : '' }}"> 
            <a class="nav-link" href="{{ route('lembur') }}">Laporan Lembur</a>
          </li>
        
          <li class="nav-item {{ Request::is('payroll') ? 'active' : '' }}"> 
            <a class="nav-link" href="{{ route('payroll') }}"> Payroll</a>
          </li>
        </ul>
      </div>
    </li>

   

    <li class="nav-item {{ Request::is('invoice') ? 'active' : '' }} ">
      <a class="nav-link" href="{{route('invoice')}}">  
        <span class="menu-title">Invoice</span>
      </a>
    </li>


    <li class="nav-item {{ Request::is('gajitm') ? 'active' : '' }} ">
      <a class="nav-link" href="{{route('gajitm')}}">  
        <span class="menu-title">Gaji dan Cadangan Transfer <br>Knowledge Tester Manual</span>
      </a>
    </li>


    <li class="nav-item {{ Request::is('testermanual') ? 'active' : '' }} ">
      <a class="nav-link" href="{{route('testermanual')}}">  
        <span class="menu-title">Invoice Tester Manual</span>
      </a>
    </li>


    <li class="nav-item {{ Request::is('mad') ||  Request::is('mad/create')? 'active' : '' }} ">
      <a class="nav-link" href="{{route('mad')}}">
        <span class="menu-title">Laporan MAD</span>
      </a>
    </li>
  </ul>
</nav>
