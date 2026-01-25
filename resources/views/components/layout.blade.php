<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? 'Madrasa ERP' }}</title>
<meta name="viewport" content="width=device-width,initial-scale=1" />

<!-- Application Styles -->
<link rel="stylesheet" href="{{ asset('css/app.css') }}">

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

{{ $headScripts ?? '' }}

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{ $extraStyles ?? '' }}
</head>
<body>

<div class="container">
  <!-- Sidebar Component -->
  @include('components.sidebar')

  <!-- Main Content -->
  <main class="panel">
    {{ $slot }}
  </main>
</div>

{{ $scripts ?? '' }}

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
