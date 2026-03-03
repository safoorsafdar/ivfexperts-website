<?php
$pageTitle = "Dashboard Overview";
require_once "includes/auth.php";
require_once "includes/header.php";

// Fetch high-level statistics for the dashboard
// 1. Total Leads
$total_leads_query = $conn->query("SELECT COUNT(id) as count FROM leads");
$total_leads = $total_leads_query->fetch_assoc()['count'] ?? 0;

// 2. New (Uncontacted) Leads
$new_leads_query = $conn->query("SELECT COUNT(id) as count FROM leads WHERE status = 'new'");
$new_leads = $new_leads_query->fetch_assoc()['count'] ?? 0;

// 3. Consultations Booked
$booked_query = $conn->query("SELECT COUNT(id) as count FROM leads WHERE status = 'consultation_booked'");
$booked_leads = $booked_query->fetch_assoc()['count'] ?? 0;

// 4. Recent Leads (Last 5)
$recent_leads_query = $conn->query("SELECT patient_name, inquiry_type, created_at FROM leads ORDER BY created_at DESC LIMIT 5");

// 5. Analytics: Leads per day over the last 30 days
$thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
$chart_query = $conn->query("
    SELECT DATE(created_at) as log_date, COUNT(*) as daily_count 
    FROM leads 
    WHERE created_at >= '$thirty_days_ago' 
    GROUP BY DATE(created_at) 
    ORDER BY DATE(created_at) ASC
");

// Process Data for Chart.js
$chart_dates = [];
$chart_data = [];
if ($chart_query) {
    while ($row = $chart_query->fetch_assoc()) {
        $chart_dates[] = date('M j', strtotime($row['log_date']));
        $chart_data[] = (int)$row['daily_count'];
    }
}
// Convert to JSON for Javascript
$js_dates = json_encode($chart_dates);
$js_data = json_encode($chart_data);
?>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <!-- STAT CARD 1: New Leads -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex items-center gap-6 relative overflow-hidden group">
        <div class="absolute right-0 top-0 w-24 h-24 bg-rose-50 rounded-bl-full -z-10 group-hover:scale-110 transition-transform"></div>
        <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
            <i class="ph ph-bell text-2xl"></i>
        </div>
        <div>
            <p class="text-sm font-semibold text-slate-500 mb-1 uppercase tracking-wider">New Inquiries</p>
            <h3 class="text-3xl font-extrabold text-slate-900"><?= number_format($new_leads) ?></h3>
        </div>
    </div>

    <!-- STAT CARD 2: Total Leads -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex items-center gap-6 relative overflow-hidden group">
        <div class="absolute right-0 top-0 w-24 h-24 bg-sky-50 rounded-bl-full -z-10 group-hover:scale-110 transition-transform"></div>
        <div class="w-14 h-14 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center shrink-0">
            <i class="ph ph-users text-2xl"></i>
        </div>
        <div>
            <p class="text-sm font-semibold text-slate-500 mb-1 uppercase tracking-wider">Total Leads</p>
            <h3 class="text-3xl font-extrabold text-slate-900"><?= number_format($total_leads) ?></h3>
        </div>
    </div>

    <!-- STAT CARD 3: Booked Consultations -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex items-center gap-6 relative overflow-hidden group">
        <div class="absolute right-0 top-0 w-24 h-24 bg-emerald-50 rounded-bl-full -z-10 group-hover:scale-110 transition-transform"></div>
        <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
            <i class="ph ph-calendar-check text-2xl"></i>
        </div>
        <div>
            <p class="text-sm font-semibold text-slate-500 mb-1 uppercase tracking-wider">Booked Consults</p>
            <h3 class="text-3xl font-extrabold text-slate-900"><?= number_format($booked_leads) ?></h3>
        </div>
    </div>

</div>

<!-- Recent Activity / Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-10">
    
    <!-- Left Column: Recent Activity (Placeholder for now) -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-slate-800">Recent Inquiries</h3>
            <a href="leads.php" class="text-sm text-teal-600 font-semibold hover:underline">View All</a>
        </div>
        <div class="p-0">
            <?php if ($recent_leads_query && $recent_leads_query->num_rows > 0): ?>
                <ul class="divide-y divide-slate-100">
                    <?php while ($lead = $recent_leads_query->fetch_assoc()): ?>
                        <li class="px-6 py-4 flex justify-between items-center hover:bg-slate-50 transition-colors">
                            <div>
                                <p class="font-semibold text-slate-800"><?= htmlspecialchars($lead['patient_name']) ?></p>
                                <p class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($lead['inquiry_type']) ?></p>
                            </div>
                            <span class="text-xs text-slate-400 font-medium">
                                <?= date('M j, Y', strtotime($lead['created_at'])) ?>
                            </span>
                        </li>
                    <?php endformwhile; ?>
                </ul>
            <?php else: ?>
                <div class="p-8 text-center text-slate-500">
                    <i class="ph ph-envelope-simple-open text-4xl mb-3 opacity-20"></i>
                    <p>No new inquiries yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column: System Status & Quick Links -->
    <div class="space-y-6">
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden">
            <div class="absolute right-0 top-0 opacity-10">
                <i class="ph-fill ph-shield-check text-9xl -mr-6 -mt-6"></i>
            </div>
            <h3 class="text-xl font-bold mb-2 relative z-10">System Status: Optimal</h3>
            <p class="text-slate-400 text-sm mb-6 relative z-10 max-w-[80%]">The dashboard, database, and all security protocols are functioning online.</p>
            
            <div class="grid grid-cols-2 gap-4 relative z-10">
                <a href="leads.php" class="bg-white/10 hover:bg-white/20 transition-colors rounded-xl p-4 border border-white/5 flex items-center gap-3">
                    <i class="ph ph-users-three text-xl text-teal-400"></i>
                    <span class="font-semibold text-sm">Manage Leads</span>
                </a>
                <a href="settings.php" class="bg-white/10 hover:bg-white/20 transition-colors rounded-xl p-4 border border-white/5 flex items-center gap-3">
                    <i class="ph ph-gear text-xl text-indigo-400"></i>
                    <span class="font-semibold text-sm">Global Settings</span>
                </a>
            </div>
        </div>
        
        <!-- Analytics Chart -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
            <h3 class="font-bold text-slate-800 mb-4">Lead Acquisition (30 Days)</h3>
            <div class="relative h-64 w-full">
                <canvas id="leadsChart"></canvas>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('leadsChart').getContext('2d');
    
    // Gradient Fill
    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(13, 148, 136, 0.5)'); // Teal 600
    gradient.addColorStop(1, 'rgba(13, 148, 136, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= $js_dates ?>,
            datasets: [{
                label: 'New Inquiries',
                data: <?= $js_data ?>,
                borderColor: '#0d9488', // Teal 600
                backgroundColor: gradient,
                borderWidth: 3,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#0d9488',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4 // Smooth curves
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    titleFont: { family: 'Inter', size: 13 },
                    bodyFont: { family: 'Inter', size: 14, weight: 'bold' },
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' Inquiries';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0,
                        font: { family: 'Inter', size: 11 },
                        color: '#64748b'
                    },
                    grid: { color: '#f1f5f9', drawBorder: false }
                },
                x: {
                    ticks: {
                        font: { family: 'Inter', size: 11 },
                        color: '#64748b',
                        maxTicksLimit: 7
                    },
                    grid: { display: false, drawBorder: false }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index',
            },
        }
    });
});
</script>

<?php require_once "includes/footer.php"; ?>