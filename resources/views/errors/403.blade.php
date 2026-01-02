@extends('layouts.app')

@section('title', 'Unauthorized - Madrasa ERP')

@section('content')
<div style="max-width:600px;margin:100px auto;text-align:center">
    <div style="background:var(--card);padding:40px;border-radius:var(--radius);border:1px solid rgba(255,255,255,0.1)">
        <!-- Error Icon -->
        <div style="width:100px;height:100px;background:rgba(255,78,78,0.2);border-radius:50%;margin:0 auto 24px;display:flex;align-items:center;justify-content:center">
            <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#ff4e4e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>

        <!-- Error Message -->
        <h1 style="color:var(--danger);margin:0 0 16px 0;font-size:28px">Access Denied</h1>
        <p style="color:var(--muted);font-size:16px;margin:0 0 24px 0">
            {{ $exception->getMessage() ?: 'You don\'t have permission to access this page.' }}
        </p>
        
        <div style="padding:16px;background:rgba(255,78,78,0.1);border-radius:6px;border-left:4px solid var(--danger);margin-bottom:24px">
            <p style="margin:0;color:var(--text);font-size:14px">
                <strong>Error Code:</strong> 403 - Unauthorized<br>
                <strong>Reason:</strong> Insufficient permissions
            </p>
        </div>

        <!-- Action Buttons -->
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
            <a href="javascript:history.back()" class="btn ghost" style="display:inline-flex;align-items:center;gap:8px">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Go Back
            </a>
            <a href="{{ route('dashboard') }}" class="btn" style="display:inline-flex;align-items:center;gap:8px">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                Go to Dashboard
            </a>
        </div>

        <!-- Help Text -->
        <p style="margin-top:32px;color:var(--muted);font-size:13px">
            If you believe this is an error, please contact your administrator.
        </p>
    </div>
</div>
@endsection

@section('extra-styles')
<style>
.btn {
    padding: 10px 20px;
    border-radius: var(--radius);
    border: none;
    cursor: pointer;
    font-size: 14px;
    background: var(--accent);
    color: #000;
    text-decoration: none;
    transition: all 0.2s;
    font-weight: 500;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(227,120,20,0.3);
}

.btn.ghost {
    background: transparent;
    border: 1px solid var(--accent);
    color: var(--accent);
}

.btn.ghost:hover {
    background: rgba(227,120,20,0.1);
}
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Show SweetAlert popup on page load
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'error',
        title: 'Access Denied',
        text: "{{ $exception->getMessage() ?: 'You don\'t have permission to access this page.' }}",
        confirmButtonText: 'OK',
        confirmButtonColor: '#e37814',
        background: '#0f1416',
        color: '#e6eef3',
        backdrop: 'rgba(0,0,0,0.8)',
        customClass: {
            popup: 'dark-popup',
            confirmButton: 'custom-confirm-btn'
        }
    });
});
</script>

<style>
.dark-popup {
    border: 1px solid rgba(255,255,255,0.1) !important;
}
.custom-confirm-btn {
    font-weight: 500 !important;
}
</style>
@endsection
