@extends('layouts.app')

@section('title', 'Dashboard - Madrasa ERP')

@section('head-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('extra-styles')
<style>
.chart-card{margin-top:14px;padding:18px;background:var(--card);border-radius:var(--radius);}
canvas{margin-top:10px !important;height:260px !important;}
</style>
@endsection

@section('content')
<div class="top">
  <h1 style="margin:0;font-size:24px">Dashboard</h1>
  <p style="margin:4px 0 0 0;color:var(--muted);font-size:14px">Overall Statistics Summary</p>
</div>

<div class="grid" style="margin-top:20px;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr))">
  <div class="card">
    <div class="label">Total Students</div>
    <div class="stat">{{ $totalStudents }}</div>
  </div>
  <div class="card">
    <div class="label">Total Earn This Session</div>
    <div class="stat">৳ {{ number_format($totalEarnings, 0) }}</div>
  </div>
  <div class="card">
    <div class="label">Today's Earnings</div>
    <div class="stat">৳ {{ number_format($todaysEarnings, 0) }}</div>
  </div>
  <div class="card">
    <div class="label">Total Classes</div>
    <div class="stat">{{ $totalClasses }}</div>
  </div>
  <div class="card">
    <div class="label">Total Users</div>
    <div class="stat">{{ $totalUsers }}</div>
  </div>
</div>

<div class="card" style="margin-top:20px">
  <strong>Class Wise Admission</strong>
  <table>
    <thead>
      <tr>
        <th>Class</th>
        <th>Total Students</th>
      </tr>
    </thead>
    <tbody>
      @forelse($classWiseData as $row)
      <tr>
        <td>{{ $row->className }}</td>
        <td>{{ $row->total }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="2" style="color:var(--muted)">No data available</td>
      </tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr>
        <td>Grand Total</td>
        <td>{{ $totalStudents }}</td>
      </tr>
    </tfoot>
  </table>
</div>

<!-- CHART SECTION -->
<div class="chart-card">
  <strong>Monthly Admission Overview</strong>
  <canvas id="admissionChart"></canvas>
</div>

<div class="chart-card">
  <strong>Monthly Earnings</strong>
  <canvas id="earnChart"></canvas>
</div>
@endsection

@section('scripts')
<script>
const monthlyAdmissions = @json($admissions);
const monthlyEarnings = @json($earnings);
const months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];

// Admissions Chart
new Chart(document.getElementById("admissionChart"), {
  type: "bar",
  data: {
    labels: months,
    datasets: [{
      label: "Admissions",
      data: monthlyAdmissions,
      borderWidth: 1,
      backgroundColor: "rgba(227,120,20,0.7)"
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { labels: { color: "white" } } },
    scales: {
      x: { ticks: { color: "white" } },
      y: { ticks: { color: "white" }, beginAtZero: true }
    }
  }
});

// Earnings Chart
new Chart(document.getElementById("earnChart"), {
  type: "line",
  data: {
    labels: months,
    datasets: [{
      label: "Earnings (৳)",
      data: monthlyEarnings,
      borderWidth: 2,
      borderColor: "rgba(227,120,20,1)",
      tension: 0.35,
      fill: false
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { labels: { color: "white" } } },
    scales: {
      x: { ticks: { color: "white" } },
      y: { ticks: { color: "white" }, beginAtZero: true }
    }
  }
});
</script>
@endsection