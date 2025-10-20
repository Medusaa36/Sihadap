
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('home.index') }}" class="brand-link">
        <img src="{{ asset('master/images/logokumham.jpg') }}" alt="Kemenkum Kepri" 
             class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Kemenkum Kepri</span>
    </a>

    <div class="sidebar">
        @php
            use App\Models\AdminModel;

            $admin = null;
            if (session()->has('admin_id')) {
                $admin = AdminModel::find(session('admin_id'));
            }
        @endphp

        <div class="info">
            @if($admin)
                <a href="#" class="d-block">
                    <strong>{{ $admin->nama }}</strong><br>
                    NIP: {{ $admin->nip }}<br>
                    <small>{{ ucfirst($admin->tipe_admin) }}</small>
                </a>
            @else
                <a href="#" class="d-block">Guest</a>
            @endif
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" 
                data-widget="treeview" role="menu" data-accordion="false">

                <li class="nav-item">
                    <a href="{{ route('home.index') }}" 
                       class="nav-link {{ request()->routeIs('home.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-header">ABSENSI</li>
                <li class="nav-item">
                    <a href="{{ route('absensi.index') }}" 
                       class="nav-link {{ request()->routeIs('absensi.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-calendar-check"></i>
                        <p>Absensi</p>
                    </a>
                </li>

                <li class="nav-header">DATA MASTER</li>
                <li class="nav-item">
                    <a href="{{ route('admin.index') }}" 
                       class="nav-link {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-shield"></i>
                        <p>Data Admin</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pegawai.index') }}" 
                       class="nav-link {{ request()->routeIs('pegawai.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Data Pegawai</p>
                    </a>
                </li>

                <li class="nav-header">
                    <a href="{{ route('login.logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i> KELUAR
                    </a>
                    <form id="logout-form" action="{{ route('login.logout') }}" method="GET" style="display: none;">
                        @csrf
                    </form>
                </li>

            </ul>
        </nav>
    </div>
</aside>
