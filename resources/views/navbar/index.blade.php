@php
    use App\Models\AdminModel;

    $admin = null;
    if (session()->has('admin_id')) {
        $admin = AdminModel::with('pegawai')->find(session('admin_id'));
    }
@endphp

<nav class="main-header navbar navbar-expand navbar-dark" style="background-color:#11375c;">
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button">
        <i class="fas fa-bars"></i>
      </a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="{{ route('home.index') }}" class="nav-link">Home</a>
    </li>
  </ul>

  <ul class="navbar-nav ml-auto">
    <li class="nav-item dropdown user user-menu">
      <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
        <img src="{{ asset('master/images/administrator.png') }}" 
             class="user-image img-circle elevation-2" alt="User Image">
        <span class="d-none d-md-inline">
          {{ $admin ? $admin->nama : 'Saya' }}
        </span>
      </a>

      <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <li class="user-header bg-info text-center">
          <img src="{{ asset('master/images/administrator.png') }}" 
               class="img-circle elevation-2 mb-2" alt="User Image" 
               style="width: 80px; height: 80px;">
          
          @if($admin)
            <p class="mb-0">
              <strong>{{ $admin->nama }}</strong><br>
              <small>
                NIP: {{ $admin->nip }}<br>
                {{ ucfirst($admin->tipe_admin) }}
              </small>
            </p>
          @else
            <p>Guest</p>
          @endif
        </li>
        <li class="user-footer px-3 py-2">
          <div class="d-flex justify-content-between w-100">
            <a href="{{ route('password.index') }}" class="btn btn-sm btn-outline-primary">
              <i class="fas fa-key mr-1"></i> Ganti Password
            </a>

            <a href="{{ route('login.logout') }}" class="btn btn-sm btn-outline-danger"
              onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              <i class="fas fa-sign-out-alt mr-1"></i> Keluar
            </a>
          </div>

          <form id="logout-form" action="{{ route('login.logout') }}" method="GET" style="display: none;">
            @csrf
          </form>
        </li>
      </ul>
    </li>
  </ul>
</nav>
