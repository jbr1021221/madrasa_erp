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
    @if(auth()->user()->isAdmin())
    <a class="nav-btn {{ request()->routeIs('accounts.*') || request()->routeIs('payments.*') ? 'active' : '' }}" href="/accounts">
      Accounts
    </a>
    <a class="nav-btn {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
      Users
    </a>
    @endif
    
    <form action="{{ route('logout') }}" method="POST" style="margin-top:20px">
      @csrf
      <button type="submit" class="nav-btn" style="width:100%;text-align:left;background:transparent;border:0;cursor:pointer;color:var(--danger);font-family:inherit">
        Logout
      </button>
    </form>
  </nav>

  <div class="small">© 2025 Madrasa ERP</div>
</aside>