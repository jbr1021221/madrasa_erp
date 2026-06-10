@extends('layouts.app')

@section('title', 'Dashboard - Madrasa ERP')

@section('head-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('extra-styles')
<style>
    /* Header */
    .dash-header { margin-bottom: 24px; }
    .dash-header h1 { margin: 0; font-size: 22px; }
    .dash-header p  { margin: 4px 0 0; color: var(--muted); font-size: 13px; }

    /* Stat Cards */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }
    .stat-card {
        background: var(--card);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: var(--radius);
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: border-color 0.2s, transform 0.2s;
    }
    .stat-card:hover { border-color: rgba(255,255,255,0.15); transform: translateY(-2px); }
    .stat-icon {
        width: 46px; height: 46px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .stat-icon svg { width: 22px; height: 22px; }
    .stat-icon.orange { background: rgba(227,120,20,0.15); color: var(--accent); }
    .stat-icon.green  { background: rgba(76,175,80,0.15);  color: #4caf50; }
    .stat-icon.blue   { background: rgba(33,150,243,0.15); color: #2196f3; }
    .stat-icon.purple { background: rgba(156,39,176,0.15); color: #ab47bc; }
    .stat-icon.yellow { background: rgba(255,193,7,0.15);  color: #ffc107; }
    .stat-body { min-width: 0; }
    .stat-label { font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
    .stat-value { font-size: 22px; font-weight: 700; color: var(--text); line-height: 1.2; }

    /* Section Title */
    .section-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.07em;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: rgba(255,255,255,0.07);
    }

    /* Class Table Card */
    .dash-card {
        background: var(--card);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: var(--radius);
        padding: 20px;
        margin-bottom: 20px;
    }
    .dash-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .dash-table th {
        background: rgba(255,255,255,0.06);
        padding: 10px 14px;
        font-size: 12px;
        text-align: left;
        color: var(--muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .dash-table th:last-child { text-align: right; }
    .dash-table td {
        padding: 11px 14px;
        font-size: 14px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        color: var(--text);
    }
    .dash-table td:last-child { text-align: right; font-weight: 600; color: #4caf50; }
    .dash-table tbody tr:hover { background: rgba(255,255,255,0.04); }
    .dash-table tfoot td {
        border-top: 1px solid rgba(255,255,255,0.12);
        border-bottom: none;
        font-weight: 700;
        font-size: 14px;
        color: var(--accent);
    }
    .dash-table tfoot td:last-child { color: var(--accent); }

    /* Chart Cards */
    .chart-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 20px;
    }
    @media (max-width: 900px) { .chart-grid { grid-template-columns: 1fr; } }
    .chart-card {
        background: var(--card);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: var(--radius);
        padding: 20px;
    }
    .chart-card-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 16px;
    }
    canvas { height: 240px !important; }
</style>
@endsection

@section('content')

    {{-- Header --}}
    <div class="dash-header">
        <h1>Dashboard</h1>
        <p>Welcome back — here's what's happening today</p>
    </div>

    {{-- Stat Cards --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            </div>
            <div class="stat-body">
                <div class="stat-label">Total Classes</div>
                <div class="stat-value">{{ $totalClasses }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="stat-body">
                <div class="stat-label">Total Students</div>
                <div class="stat-value">{{ $totalStudents }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
            <div class="stat-body">
                <div class="stat-label">Session Earnings</div>
                <div class="stat-value">৳ {{ number_format($totalEarnings, 0) }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon yellow">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="stat-body">
                <div class="stat-label">Today's Earnings</div>
                <div class="stat-value">৳ {{ number_format($todaysEarnings, 0) }}</div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="section-title">Analytics</div>
    <div class="chart-grid">
        <div class="chart-card">
            <div class="chart-card-title">Monthly Admissions</div>
            <canvas id="admissionChart"></canvas>
        </div>
        <div class="chart-card">
            <div class="chart-card-title">Monthly Earnings</div>
            <canvas id="earnChart"></canvas>
        </div>
    </div>

    {{-- Class Wise Table --}}
    <div class="section-title">Class Overview</div>
    <div class="dash-card">
        <table class="dash-table">
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
                        <td colspan="2" style="color:var(--muted);padding:30px;text-align:center">No data available</td>
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

@endsection

@section('scripts')
<script>
const months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
const monthlyAdmissions = @json($admissions);
const monthlyEarnings   = @json($earnings);

const chartDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { labels: { color: "#98a0a6", font: { size: 12 } } },
        tooltip: { backgroundColor: "#1b1f22", borderColor: "rgba(255,255,255,0.1)", borderWidth: 1, titleColor: "#e6eef3", bodyColor: "#98a0a6" }
    },
    scales: {
        x: { ticks: { color: "#98a0a6", font: { size: 11 } }, grid: { color: "rgba(255,255,255,0.05)" } },
        y: { ticks: { color: "#98a0a6", font: { size: 11 } }, grid: { color: "rgba(255,255,255,0.05)" }, beginAtZero: true }
    }
};

new Chart(document.getElementById("admissionChart"), {
    type: "bar",
    data: {
        labels: months,
        datasets: [{
            label: "Admissions",
            data: monthlyAdmissions,
            backgroundColor: "rgba(227,120,20,0.6)",
            borderColor: "rgba(227,120,20,1)",
            borderWidth: 1,
            borderRadius: 4,
        }]
    },
    options: chartDefaults
});

new Chart(document.getElementById("earnChart"), {
    type: "line",
    data: {
        labels: months,
        datasets: [{
            label: "Earnings (৳)",
            data: monthlyEarnings,
            borderWidth: 2,
            borderColor: "#4caf50",
            backgroundColor: "rgba(76,175,80,0.1)",
            tension: 0.4,
            fill: true,
            pointBackgroundColor: "#4caf50",
            pointRadius: 4,
        }]
    },
    options: chartDefaults
});
</script>
@endsection
