<aside class="sidebar">
  <div class="brand" style="display:flex;justify-content:center;margin-bottom:20px;">
    <img src="{{ asset('madrasa-logo.jpeg') }}" alt="Madrasa Logo" class="logo" style="width:120px;height:120px;">
  </div>

  <nav class="nav">
    <a class="nav-btn {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
      Dashboard
    </a>
    <a class="nav-btn {{ request()->routeIs('students.*') ? 'active' : '' }}" href="/students">
      Students
    </a>
    <a class="nav-btn {{ request()->routeIs('classrooms.*') ? 'active' : '' }}" href="{{ route('classrooms.index') }}">
      Classes
    </a>
    <a class="nav-btn {{ request()->routeIs('accounts.*') ? 'active' : '' }}" href="/accounts">
      Accounts
    </a>
  </nav>

  <div class="small">© 2025 Madrasa ERP</div>
</aside>