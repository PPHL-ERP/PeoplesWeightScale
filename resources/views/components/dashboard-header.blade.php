{{-- Dashboard header extracted from index.html --}}
<header class="top-header">
  <nav class="navbar navbar-expand">
    <div class="mobile-toggle-icon d-xl-none">
      <i class="bi bi-list"></i>
    </div>
    <div class="top-navbar d-none d-xl-block">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item">
          <a class="nav-link" href="{{ url('dashboard') }}">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Email</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="javascript:;">Projects</a>
        </li>
        <li class="nav-item d-none d-xxl-block">
          <a class="nav-link" href="javascript:;">Events</a>
        </li>
        <li class="nav-item d-none d-xxl-block">
          <a class="nav-link" href="#">Todo</a>
        </li>
      </ul>
    </div>
    <div class="search-toggle-icon d-xl-none ms-auto">
      <i class="bi bi-search"></i>
    </div>
    <form class="searchbar d-none d-xl-flex ms-auto">
      <div class="position-absolute top-50 translate-middle-y search-icon ms-3"><i class="bi bi-search"></i></div>
      <input class="form-control" type="text" placeholder="Type here to search">
      <div class="position-absolute top-50 translate-middle-y d-block d-xl-none search-close-icon"><i class="bi bi-x-lg"></i></div>
    </form>
    <div class="d-flex align-items-center ms-3 gap-3">
      <span class="fw-bold">Welcome {{ session('user')['name'] ?? 'User' }}</span>
      <a href="{{ route('logout') }}" class="btn btn-danger btn-sm">Logout</a>
    </div>
    {{-- User menu and notifications omitted for brevity --}}
  </nav>
</header>
