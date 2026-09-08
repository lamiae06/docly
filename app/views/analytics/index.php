<div class="page-header">
    <h1><i class="fa-solid fa-chart-pie"></i> Analytics</h1>
</div>
<div class="dashboard-grid">
    <div class="dashboard-card wide">
        <div class="card-header"><h3><i class="fa-solid fa-chart-line"></i> Vue d'ensemble</h3></div>
        <div class="card-body">
            <div class="chart-grid">
                <div class="chart-container"><canvas id="aptChart"></canvas></div>
                <div class="chart-container"><canvas id="patChart"></canvas></div>
                <div class="chart-container"><canvas id="revChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="dashboard-card">
        <div class="card-header"><h3><i class="fa-solid fa-user-doctor"></i> Consultations par médecin</h3></div>
        <div class="card-body"><div class="chart-container"><canvas id="docChart"></canvas></div></div>
    </div>
    <div class="dashboard-card">
        <div class="card-header"><h3><i class="fa-solid fa-percent"></i> Taux de présence</h3></div>
        <div class="card-body"><div class="chart-container"><canvas id="presenceChart"></canvas></div></div>
    </div>
</div>

<script>
fetch('/api/analytics/charts').then(r => r.json()).then(data => {
    new Chart(document.getElementById('aptChart'), {
        type: 'line',
        data: { labels: data.appointments.map(d => d.date), datasets: [{ label: 'RDV', data: data.appointments.map(d => d.count), borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,0.1)', fill: true, tension: 0.4 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });
    new Chart(document.getElementById('patChart'), {
        type: 'bar',
        data: { labels: data.patients.map(d => d.month), datasets: [{ label: 'Patients', data: data.patients.map(d => d.count), backgroundColor: '#10b981', borderRadius: 4 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });
    new Chart(document.getElementById('revChart'), {
        type: 'line',
        data: { labels: data.revenue.map(d => d.month), datasets: [{ label: window.DOCLY_CURRENCY_SYMBOL || '€', data: data.revenue.map(d => d.revenue), borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.1)', fill: true, tension: 0.4 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });
    new Chart(document.getElementById('docChart'), {
        type: 'doughnut',
        data: { labels: data.doctors.map(d => d.last_name), datasets: [{ data: data.doctors.map(d => d.count), backgroundColor: ['#0ea5e9','#10b981','#f59e0b','#ef4444','#8b5cf6'] }] },
        options: { responsive: true, maintainAspectRatio: false }
    });
    new Chart(document.getElementById('presenceChart'), {
        type: 'doughnut',
        data: { labels: ['Présents', 'Absents', 'Annulés'], datasets: [{ data: [data.presence?.present || 85, data.presence?.no_show || 10, data.presence?.cancelled || 5], backgroundColor: ['#10b981','#f59e0b','#ef4444'] }] },
        options: { responsive: true, maintainAspectRatio: false }
    });
});
</script>
