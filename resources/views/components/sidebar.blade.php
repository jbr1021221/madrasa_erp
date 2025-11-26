<aside class="sidebar">
  <div class="brand">
    <div class="logo">M</div>
    <div>
      <h2 style="margin:0;font-size:18px">Madrasa</h2>
      <p class="small" style="margin:0">Smart Dashboard</p>
    </div>
  </div>

  <nav class="nav">
    <a class="nav-btn {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
      📊 Dashboard
    </a>
    <a class="nav-btn {{ request()->routeIs('students.*') ? 'active' : '' }}" href="/students">
      👨‍🎓 Students
    </a>
    <a class="nav-btn {{ request()->routeIs('classrooms.*') ? 'active' : '' }}" href="{{ route('classrooms.index') }}">
      🏫 Classes
    </a>
    <a class="nav-btn {{ request()->routeIs('accounts.*') ? 'active' : '' }}" href="/accounts">
      💰 Accounts
    </a>
  </nav>

  <div class="small">© 2025 Madrasa ERP</div>
</aside>