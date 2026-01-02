<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Madrasa ERP')</title>
<meta name="viewport" content="width=device-width,initial-scale=1" />

<!-- Application Styles -->
<link rel="stylesheet" href="{{ asset('css/app.css') }}">

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

@yield('head-scripts')

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Layout Specific Styles -->
<style>
/* ======================================
   LAYOUT SPECIFIC STYLES
====================================== */
.container {
  width: 100%;
  max-width: 1400px;
  margin: auto;
  display: grid;
  grid-template-columns: 240px 1fr;
  gap: 18px;
  padding: 22px;
}

/* Sidebar */
.sidebar {
  background: var(--panel);
  border: 1px solid rgba(255,255,255,0.05);
  border-radius: var(--radius);
  padding: 16px;
  height: calc(100vh - 44px);
  display: flex;
  flex-direction: column;
}

.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
}

.logo {
  width: 42px;
  height: 42px;
  border-radius: var(--radius);
  object-fit: cover;
  border: 1px solid rgba(255,255,255,0.1);
}

.nav {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-top: 10px;
}

.nav-btn {
  display: block;
  color: var(--muted);
  padding: 10px 12px;
  text-decoration: none;
  font-size: 14px;
  border-radius: var(--radius);
  transition: all 0.2s;
}

.nav-btn.active,
.nav-btn:hover {
  background: rgba(227,120,20,0.12);
  color: var(--accent);
  border-left: 3px solid var(--accent);
  padding-left: 9px;
}

.small {
  font-size: 12px;
  color: var(--muted);
  text-align: center;
  margin-top: auto;
}

/* Main Panel */
.panel {
  background: var(--panel);
  border-radius: var(--radius);
  padding: 20px;
  border: 1px solid rgba(255,255,255,0.06);
  min-height: calc(100vh - 44px);
}

/* Dashboard Grid */
.grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px;
}

.stat {
  font-size: 26px;
  font-weight: 700;
}

@media (max-width: 768px) {
  .container {
    grid-template-columns: 1fr;
    padding: 12px;
  }
  
  .sidebar {
    height: auto;
  }
  
  .grid {
    grid-template-columns: 1fr;
  }
}
</style>
@yield('extra-styles')
</head>
<body>

<div class="container">
  <!-- Sidebar Component -->
  @include('components.sidebar')

  <!-- Main Content -->
  <main class="panel">
    @yield('content')
  </main>
</div>

@yield('scripts')

<!-- SweetAlert for success messages -->
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
        confirmButtonColor: '#e37814',
        timer: 3000,
        timerProgressBar: true
    });
</script>
@endif

</body>
</html>