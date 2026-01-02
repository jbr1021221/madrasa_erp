<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login – Madrasa ERP</title>
<style>
:root{
  --bg:#0b0d0f;--panel:#111316;--card:#0f1416;--text:#e6eef3;--muted:#98a0a6;
  --accent:#e37814;--danger:#ff4e4e;--radius:6px;
}
body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,system-ui,sans-serif;
  display:flex;justify-content:center;align-items:center;height:100vh;}
.login-card{
  background:var(--panel);padding:40px;border-radius:var(--radius);
  border:1px solid rgba(255,255,255,0.05);width:100%;max-width:400px;
}
.brand{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;margin-bottom:30px}
.logo{
  width:50px;height:50px;border-radius:var(--radius);background:var(--accent);color:#041617;
  display:flex;align-items:center;justify-content:center;font-weight:700;font-size:24px;
}
h1{text-align:center;margin:0 0 30px 0;font-size:24px}
label{display:block;margin-bottom:8px;font-size:14px;color:var(--muted)}
input{
  width:100%;padding:10px;background:#1b1f22;border:1px solid rgba(255,255,255,0.15);
  color:var(--text);border-radius:var(--radius);font-size:14px;box-sizing:border-box;
  margin-bottom:20px;
}
button{
  width:100%;background:var(--accent);color:#041617;border:0;padding:12px;
  border-radius:var(--radius);cursor:pointer;font-size:16px;font-weight:600;
}
.error{color:var(--danger);font-size:14px;margin-bottom:20px;text-align:center}
</style>
</head>
<body>

<div class="login-card">
  <div class="brand">
    <img src="{{ asset('madrasa-logo.jpeg') }}" alt="Logo" style="width: 60px; height: 60px;">
    <h1>Al Akhirah Academy</h1>
  </div>
  
  <h1>Login</h1>

  @if ($errors->any())
    <div class="error">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
  @endif

  <form action="{{ route('login') }}" method="POST">
    @csrf
    
    <label>Email Address</label>
    <input type="email" name="email" value="{{ old('email') }}" required autofocus>
    
    <label>Password</label>
    <input type="password" name="password" required>
    
    <button type="submit">Sign In</button>
  </form>
</div>

</body>
</html>
