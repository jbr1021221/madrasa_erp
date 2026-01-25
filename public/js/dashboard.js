/**
 * Dashboard Page JavaScript
 * Handles Chart.js initialization for admissions and earnings charts
 */

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function () {
    // Get data from window object (set by Blade)
    const monthlyAdmissions = window.dashboardData?.admissions || [];
    const monthlyEarnings = window.dashboardData?.earnings || [];
    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    // Admissions Chart
    const admissionChartEl = document.getElementById("admissionChart");
    if (admissionChartEl) {
        new Chart(admissionChartEl, {
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
                plugins: {
                    legend: {
                        labels: { color: "white" }
                    }
                },
                scales: {
                    x: { ticks: { color: "white" } },
                    y: { ticks: { color: "white" }, beginAtZero: true }
                }
            }
        });
    }

    // Earnings Chart
    const earnChartEl = document.getElementById("earnChart");
    if (earnChartEl) {
        new Chart(earnChartEl, {
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
                plugins: {
                    legend: {
                        labels: { color: "white" }
                    }
                },
                scales: {
                    x: { ticks: { color: "white" } },
                    y: { ticks: { color: "white" }, beginAtZero: true }
                }
            }
        });
    }
});
