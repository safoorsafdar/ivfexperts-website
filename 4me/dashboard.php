<?php
$pageTitle = "Dashboard Overview";
require_once "includes/auth.php";

// ─── Clinical Stats (safe queries that won't 500) ──────────────────
function safe_count(mysqli $conn, string $table, string $where = ''): int
{
    $res = $conn->query("SHOW TABLES LIKE '$table'");
    if (!$res || $res->num_rows === 0)
        return 0;
    $q = $conn->query("SELECT COUNT(id) as c FROM `$table`" . ($where ? " WHERE $where" : ''));
    return $q ? (int)($q->fetch_assoc()['c'] ?? 0) : 0;
}

$total_patients = safe_count($conn, 'patients');
$total_rx = safe_count($conn, 'prescriptions');
$total_labs = safe_count($conn, 'patient_lab_results');
$total_ultrasound = safe_count($conn, 'patient_ultrasounds');
$total_semen = safe_count($conn, 'semen_analyses');
$total_procedures = safe_count($conn, 'advised_procedures');

// Recent Patients
$recent_patients = [];
$r = $conn->query("SELECT id, mr_number, first_name, last_name, gender, created_at FROM patients ORDER BY created_at DESC LIMIT 8");
if ($r)
    while ($row = $r->fetch_assoc())
        $recent_patients[] = $row;

// Activity timeline last 30 days (patients registered per day)
$thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
$chart_dates = [];
$chart_data = [];
$chart_query = $conn->query("SELECT DATE(created_at) as d, COUNT(*) as c FROM patients WHERE created_at >= '$thirty_days_ago' GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC");
if ($chart_query)
    while ($row = $chart_query->fetch_assoc()) {
        $chart_dates[] = date('M j', strtotime($row['d']));
        $chart_data[] = (int)$row['c'];    }
$js_dates = json_encode($chart_dates);
$js_data = json_encode($chart_data);

include __DIR__ . '/includes/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-2xl font-semibold text-slate-800">Clinical Overview</h1>
    <p class="text-slate-400 text-sm mt-1">Welcome back, <?php echo esc($_SESSION['admin_username'] ?? 'Doctor'); ?>. Here's what's happening today.</p>
</div>

<!-- Stat Cards -->
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
    <?php
$stats = [
    ['label' => 'Patients', 'value' => $total_patients, 'icon' => 'fa-users', 'color' => 'brand', 'link' => 'patients.php'],
    ['label' => 'Prescriptions', 'value' => $total_rx, 'icon' => 'fa-prescription', 'color' => 'violet', 'link' => 'prescriptions.php'],
    ['label' => 'Lab Results', 'value' => $total_labs, 'icon' => 'fa-vials', 'color' => 'amber', 'link' => 'lab_tests.php'],
    ['label' => 'Ultrasounds', 'value' => $total_ultrasound, 'icon' => 'fa-image', 'color' => 'sky', 'link' => 'ultrasounds.php'],
    ['label' => 'Semen Analyses', 'value' => $total_semen, 'icon' => 'fa-flask-vial', 'color' => 'rose', 'link' => 'semen_analyses.php'],
    ['label' => 'Procedures', 'value' => $total_procedures, 'icon' => 'fa-clipboard-check', 'color' => 'emerald', 'link' => 'procedures.php'],
];
$color_map = [
    'brand' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-600', 'num' => 'text-teal-700'],
    'violet' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'num' => 'text-violet-700'],
    'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'num' => 'text-amber-700'],
    'sky' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-600', 'num' => 'text-sky-700'],
    'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'num' => 'text-rose-700'],
    'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'num' => 'text-emerald-700'],
];
foreach ($stats as $s):
    $c = $color_map[$s['color']];
?>
    <a href="<?php echo $s['link']; ?>" class="bg-white rounded-2xl p-5 border border-gray-100 hover:shadow-lg hover:border-gray-200 transition-all duration-200 group">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 <?php echo $c['bg']; ?> <?php echo $c['text']; ?> rounded-xl flex items-center justify-center">
                <i class="fa-solid <?php echo $s['icon']; ?> text-sm"></i>
            </div>
        </div>
        <div class="text-2xl font-semibold <?php echo $c['num']; ?> leading-none mb-1"><?php echo number_format($s['value']); ?></div>
        <div class="text-xs text-slate-400 font-medium"><?php echo $s['label']; ?></div>
    </a>
    <?php
endforeach; ?>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <!-- Recent Patients -->
    <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-slate-800 text-sm">Recent Patients</h3>
                <p class="text-xs text-slate-400 mt-0.5">Latest registrations in your EMR</p>
            </div>
            <a href="patients.php" class="text-xs text-teal-600 font-medium hover:text-teal-700 transition-colors">View all →</a>
        </div>
        <?php if (!empty($recent_patients)): ?>
        <div class="divide-y divide-gray-50">
            <?php foreach ($recent_patients as $p): ?>
            <a href="patients_view.php?id=<?php echo $p['id']; ?>" class="flex items-center gap-4 px-6 py-3.5 hover:bg-gray-50 transition-colors group">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center text-white text-xs font-semibold shrink-0">
                    <?php echo strtoupper(substr($p['first_name'], 0, 1) . substr($p['last_name'] ?? '', 0, 1)); ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-slate-800 truncate group-hover:text-teal-700 transition-colors">
                        <?php echo esc($p['first_name'] . ' ' . $p['last_name']); ?>
                    </div>
                    <div class="text-xs text-slate-400"><?php echo esc($p['mr_number']); ?> · <?php echo esc($p['gender']); ?></div>
                </div>
                <div class="text-xs text-slate-300 shrink-0"><?php echo date('M j', strtotime($p['created_at'])); ?></div>
            </a>
            <?php
    endforeach; ?>
        </div>
        <?php
else: ?>
        <div class="py-16 text-center">
            <i class="fa-solid fa-users text-3xl text-gray-200 mb-3"></i>
            <p class="text-sm text-gray-400">No patients registered yet.</p>
            <a href="patients_add.php" class="mt-3 inline-block text-xs text-teal-600 font-medium hover:underline">+ Register first patient</a>
        </div>
        <?php
endif; ?>
    </div>

    <!-- Right Column -->
    <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="font-semibold text-slate-800 text-sm mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <?php
$qlinks = [
    ['href' => 'patients_add.php', 'icon' => 'fa-user-plus', 'label' => 'New Patient', 'color' => 'text-teal-600 bg-teal-50'],
    ['href' => 'prescriptions_add.php', 'icon' => 'fa-prescription', 'label' => 'New Rx', 'color' => 'text-violet-600 bg-violet-50'],
    ['href' => 'lab_results_add.php', 'icon' => 'fa-vial-circle-check', 'label' => 'Add Lab Result', 'color' => 'text-amber-600 bg-amber-50'],
    ['href' => 'document_traceability.php', 'icon' => 'fa-shield-halved', 'label' => 'Trace Document', 'color' => 'text-slate-600 bg-slate-50'],
];
foreach ($qlinks as $ql):
?>
                <a href="<?php echo $ql['href']; ?>" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-100 hover:border-gray-200 hover:shadow-sm transition-all group">
                    <div class="w-9 h-9 <?php echo $ql['color']; ?> rounded-xl flex items-center justify-center">
                        <i class="fa-solid <?php echo $ql['icon']; ?> text-sm"></i>
                    </div>
                    <span class="text-xs font-medium text-slate-600 text-center leading-tight"><?php echo $ql['label']; ?></span>
                </a>
                <?php
endforeach; ?>
            </div>
        </div>

        <!-- Activity Chart -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="font-semibold text-slate-800 text-sm mb-1">Patient Registrations</h3>
            <p class="text-xs text-slate-400 mb-4">Last 30 days</p>
            <div class="relative h-44">
                <canvas id="activityChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('activityChart').getContext('2d');
    let gradient = ctx.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, 'rgba(13, 148, 136, 0.15)');
    gradient.addColorStop(1, 'rgba(13, 148, 136, 0.0)');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo $js_dates; ?>,
            datasets: [{
                data: <?php echo $js_data; ?>,
                borderColor: '#0d9488',
                backgroundColor: gradient,
                borderWidth: 2,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#0d9488',
                pointBorderWidth: 2,
                pointRadius: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', padding: 10, displayColors: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10, family: 'Inter' }, color: '#94a3b8' }, grid: { color: '#f8fafc', drawBorder: false } },
                x: { ticks: { font: { size: 10, family: 'Inter' }, color: '#94a3b8', maxTicksLimit: 6 }, grid: { display: false, drawBorder: false } }
            }
        }
    });
});
</script>

<?php require_once "includes/footer.php"; ?>