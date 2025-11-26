<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Madrasa ERP')</title>
<meta name="viewport" content="width=device-width,initial-scale=1" />

@yield('head-scripts')

<style>
/* ======================================
   GLOBAL DARK SMART UI
====================================== */
:root{
  --bg:#0b0d0f;
  --panel:#111316;
  --card:#0f1416;
  --muted:#98a0a6;
  --text:#e6eef3;
  --accent:#e37814;
  --danger:#ff4e4e;
  --radius:6px;
}
*{box-sizing:border-box}
html,body{
  margin:0;padding:0;height:100%;
  background:var(--bg);color:var(--text);
  font-family:'Inter',system-ui,sans-serif;
}
.container{
  width:100%;
  max-width:1400px;
  margin:auto;
  display:grid;
  grid-template-columns:240px 1fr;
  gap:18px;
  padding:22px;
}

/* Sidebar */
.sidebar{
  background:var(--panel);border:1px solid rgba(255,255,255,0.05);
  border-radius:var(--radius);padding:16px;height:calc(100vh - 44px);
  display:flex;flex-direction:column;
}
.brand{display:flex;align-items:center;gap:10px;margin-bottom:12px}
.logo{
  width:42px;height:42px;border-radius:var(--radius);
  background:var(--accent);color:#041617;
  display:flex;justify-content:center;align-items:center;
  font-size:18px;font-weight:700;
}
.nav{display:flex;flex-direction:column;gap:4px;margin-top:10px}
.nav-btn{
  display:block;color:var(--muted);padding:10px 12px;text-decoration:none;
  font-size:14px;border-radius:var(--radius);transition:all 0.2s;
}
.nav-btn.active,.nav-btn:hover{
  background:rgba(227,120,20,0.12);color:var(--accent);
  border-left:3px solid var(--accent);padding-left:9px;
}
.small{font-size:12px;color:var(--muted);text-align:center;margin-top:auto}

/* Main Panel */
.panel{
  background:var(--panel);border-radius:var(--radius);
  padding:20px;border:1px solid rgba(255,255,255,0.06);
  min-height:calc(100vh - 44px);
}

/* Common Styles */
.btn{
  background:var(--accent);color:#041617;border:0;padding:7px 14px;
  border-radius:var(--radius);cursor:pointer;font-size:14px;
  text-decoration:none;display:inline-block;
}
.btn.ghost{
  background:transparent;border:1px solid var(--accent);color:var(--accent)
}

.alert{padding:12px 16px;border-radius:var(--radius);margin-bottom:16px;border:1px solid;}
.alert.success{background:rgba(34,197,94,0.1);border-color:rgba(34,197,94,0.3);color:#4ade80;}
.alert.error{background:rgba(239,68,68,0.1);border-color:rgba(239,68,68,0.3);color:#f87171;}

input,select{
  background:#1b1f22;color:var(--text);border:1px solid rgba(255,255,255,0.15);
  padding:8px 12px;border-radius:var(--radius);font-size:14px;
}

table{width:100%;border-collapse:collapse;margin-top:18px;text-align:center;}
th,td{padding:10px;font-size:14px;border-bottom:1px solid rgba(255,255,255,0.06);}
th{color:var(--muted);font-weight:600}
tbody tr:nth-child(odd){background:rgba(255,255,255,0.04);}
tbody tr:nth-child(even){background:rgba(255,255,255,0.09);}
tbody tr:hover{background:rgba(227,120,20,0.15);}
tfoot td{font-weight:bold;}

.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.card{
  background:var(--card);padding:14px;border-radius:var(--radius);
  border:1px solid rgba(255,255,255,0.05);
}
.label{font-size:13px;color:var(--muted)}
.stat{font-size:26px;font-weight:700}

@yield('extra-styles')
</style>
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

</body>
</html>