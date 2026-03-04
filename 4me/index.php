<?php
$pageTitle = "Dashboard";
require_once __DIR__ . '/includes/auth.php';

$currentMonth = date('Y-m');
$today = date('Y-m-d');

// ── Stats ──────────────────────────────────────────────────────────────────────
$stats = [
    'patients_total' => 0,
    'patients_month' => 0,
    'prescriptions_month' => 0,
    'pending_labs' => 0,
    'revenue_month' => 0,
    'procedures_month' => 0,
];

try {
    $m = $conn->escape_string($currentMonth);
    $t = $conn->escape_string($today);

    $r = $conn->query("SELECT COUNT(*) AS c FROM patients");
    if ($r)
        $stats['patients_total'] = $r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) AS c FROM patients WHERE DATE_FORMAT(created_at,'%Y-%m') = '$m'");
    if ($r)
        $stats['patients_month'] = $r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) AS c FROM prescriptions WHERE DATE_FORMAT(created_at,'%Y-%m') = '$m'");
    if ($r)
        $stats['prescriptions_month'] = $r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) AS c FROM patient_lab_results WHERE status = 'Pending'");
    if ($r)
        $stats['pending_labs'] = $r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM receipts WHERE DATE_FORMAT(receipt_date,'%Y-%m') = '$m' AND status = 'Paid'");
    if ($r)
        $stats['revenue_month'] = $r->fetch_assoc()['s'];
    $r = $conn->query("SELECT COUNT(*) AS c FROM advised_procedures WHERE DATE_FORMAT(date_advised,'%Y-%m') = '$m'");
    if ($r)
        $stats['procedures_month'] = $r->fetch_assoc()['c'];
}
catch (Exception $e) { /* Graceful — stats default to 0 */
}

// ── Recent Patients ────────────────────────────────────────────────────────────
$recent_patients = [];
try {
    $res = $conn->query("SELECT id, mr_number, first_name, last_name, gender, patient_age, phone, created_at FROM patients ORDER BY created_at DESC LIMIT 8");
    if ($res)
        while ($row = $res->fetch_assoc())
            $recent_patients[] = $row;
}
catch (Exception $e) {
}

// ── Recent Activity Feed ───────────────────────────────────────────────────────
$activity = [];
try {
    // Recent prescriptions
    $res = $conn->query("SELECT p.created_at AS dt, CONCAT(pt.first_name,' ',pt.last_name) AS name, pt.mr_number, p.patient_id AS pid FROM prescriptions p JOIN patients pt ON p.patient_id = pt.id ORDER BY p.created_at DESC LIMIT 4");
    if ($res)
        while ($row = $res->fetch_assoc())
            $activity[] = ['type' => 'rx', 'icon' => 'fa-prescription', 'color' => 'indigo', 'label' => 'Prescription issued', 'name' => $row['name'], 'mr' => $row['mr_number'], 'pid' => $row['pid'], 'dt' => $row['dt']];

    // Recent semen analyses
    $res = $conn->query("SELECT s.collection_time AS dt, CONCAT(pt.first_name,' ',pt.last_name) AS name, pt.mr_number, s.patient_id AS pid FROM semen_analyses s JOIN patients pt ON s.patient_id = pt.id ORDER BY s.id DESC LIMIT 3");
    if ($res)
        while ($row = $res->fetch_assoc())
            $activity[] = ['type' => 'sa', 'icon' => 'fa-flask-vial', 'color' => 'sky', 'label' => 'Semen analysis recorded', 'name' => $row['name'], 'mr' => $row['mr_number'], 'pid' => $row['pid'], 'dt' => $row['dt']];

    // Recent lab results
    $res = $conn->query("SELECT plt.created_at AS dt, ltd.test_name, CONCAT(pt.first_name,' ',pt.last_name) AS name, pt.mr_number, plt.patient_id AS pid FROM patient_lab_results plt JOIN patients pt ON plt.patient_id = pt.id JOIN lab_tests_directory ltd ON plt.test_id = ltd.id ORDER BY plt.id DESC LIMIT 3");
    if ($res)
        while ($row = $res->fetch_assoc())
            $activity[] = ['type' => 'lab', 'icon' => 'fa-vials', 'color' => 'amber', 'label' => 'Lab result posted (' . $row['test_name'] . ')', 'name' => $row['name'], 'mr' => $row['mr_number'], 'pid' => $row['pid'], 'dt' => $row['dt']];

    // Sort by date desc
    usort($activity, fn($a, $b) => strtotime($b['dt']) - strtotime($a['dt']));
    $activity = array_slice($activity, 0, 8);
}
catch (Exception $e) {
}

// ── Month Label ────────────────────────────────────────────────────────────────
$monthLabel = date('F Y');

include __DIR__ . '/includes/header.php';
?>

<div class="space-y-10">

    <!-- Welcome Strip -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">
                Good <?php echo date('H') < 12 ? 'Morning' : (date('H') < 18 ? 'Afternoon' : 'Evening'); ?>,
                <span class="text-brand-600"><?php echo esc($_SESSION['admin_username'] ?? 'Doctor'); ?></span>
            </h1>
            <p class="text-sm text-gray-400 font-bold mt-1 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                <?php echo date('l, d F Y'); ?> &nbsp;·&nbsp; IVF Experts Clinical Intelligence System
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="patients_add.php"
               class="inline-flex items-center gap-2.5 bg-teal-600 hover:bg-teal-700 text-white px-6 py-4 rounded-2xl font-semibold text-sm shadow-sm transition-all hover:-translate-y-1 active:scale-95">
                <i class="fa-solid fa-user-plus text-brand-400"></i> New Patient Registration
            </a>
        </div>
    </div>

    <!-- ── Stats Grid ──────────────────────────────────────────────────────── -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <?php
$stat_cards = [
    ['value' => number_format($stats['patients_total']), 'label' => 'Patient Registry', 'sub' => '+' . number_format($stats['patients_month']) . ' new this month', 'icon' => 'fa-users', 'gradient' => 'from-brand-600 to-brand-400', 'href' => 'patients.php'],
    ['value' => number_format($stats['prescriptions_month']), 'label' => 'Active Cases', 'sub' => 'Prescriptions issued', 'icon' => 'fa-prescription', 'gradient' => 'from-indigo-600 to-indigo-400', 'href' => 'prescriptions.php'],
    ['value' => number_format($stats['pending_labs']), 'label' => 'Lab Queue', 'sub' => 'Awaiting results', 'icon' => 'fa-flask-vial', 'gradient' => 'from-amber-500 to-orange-400', 'href' => 'lab_results.php'],
    ['value' => 'Rs. ' . number_format($stats['revenue_month']), 'label' => 'Total Revenue', 'sub' => 'Collected this month', 'icon' => 'fa-wallet', 'gradient' => 'from-emerald-600 to-teal-400', 'href' => 'financials.php'],
];
foreach ($stat_cards as $c):
?>
        <a href="<?php echo $c['href']; ?>"
           class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover-lift group relative overflow-hidden">
            <!-- Subtle Background Icon -->
            <i class="fa-solid <?php echo $c['icon']; ?> absolute -right-4 -bottom-4 text-7xl text-gray-50/50 group-hover:text-gray-100/50 transition-colors"></i>
            
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-gradient-to-tr <?php echo $c['gradient']; ?> text-white rounded-2xl flex items-center justify-center shadow-lg shadow-black/5 group-hover:rotate-6 transition-transform duration-300">
                    <i class="fa-solid <?php echo $c['icon']; ?> text-xl"></i>
                </div>
                <div>
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1"><?php echo $c['label']; ?></div>
                    <div class="text-xs font-bold text-gray-500"><?php echo $c['sub']; ?></div>
                </div>
            </div>
            
            <div class="flex items-end justify-between">
                <div class="text-3xl font-black text-gray-900 tracking-tight leading-none"><?php echo $c['value']; ?></div>
                <div class="w-8 h-8 rounded-full border border-gray-100 flex items-center justify-center text-gray-300 group-hover:bg-brand-50 group-hover:text-brand-600 group-hover:border-brand-100 transition-all">
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </div>
            </div>
        </a>
        <?php
endforeach; ?>
    </div>

    <!-- ── Main Content: Recent Patients + Activity ─────────────────────────── -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

        <!-- Recent Patients -->
        <div class="xl:col-span-2 bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
            <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-black text-gray-900 flex items-center gap-2.5 text-lg">
                        <i class="fa-solid fa-clock-rotate-left text-brand-500 text-sm"></i> Patient Registry
                    </h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Recently onboarded medical records</p>
                </div>
                <a href="patients.php" class="px-4 py-2 rounded-xl bg-gray-50 text-[11px] font-black text-brand-600 hover:bg-brand-50 transition-colors flex items-center gap-2 border border-gray-100">
                    Full Registry <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            <?php if (empty($recent_patients)): ?>
            <div class="flex-1 flex flex-col items-center justify-center p-20 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                    <i class="fa-solid fa-users text-3xl text-gray-200"></i>
                </div>
                <p class="font-black text-gray-800">No Patient Records</p>
                <p class="text-xs text-gray-400 mt-2 max-w-[200px]">Start by registering your first patient to begin clinical management.</p>
                <a href="patients_add.php" class="mt-6 text-brand-600 font-black text-xs hover:underline decoration-2 underline-offset-4">Register Patient Now</a>
            </div>
            <?php
else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-black uppercase tracking-widest text-gray-400 bg-gray-50/30 border-b border-gray-50">
                            <th class="px-8 py-4">Clinical Profile</th>
                            <th class="px-8 py-4">Contact Logic</th>
                            <th class="px-8 py-4">Demographics</th>
                            <th class="px-8 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach ($recent_patients as $p): ?>
                        <tr class="hover:bg-brand-50/20 transition-all group">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 bg-gradient-to-tr from-gray-100 to-gray-50 text-gray-400 group-hover:from-brand-100 group-hover:to-brand-50 group-hover:text-brand-600 rounded-2xl flex items-center justify-center shrink-0 font-black text-sm transition-all border border-gray-100 group-hover:border-brand-100 shadow-sm group-hover:shadow-brand-100/20">
                                        <?php echo strtoupper(substr($p['first_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="font-black text-gray-900 text-sm leading-tight mb-1 group-hover:text-brand-900 transition-colors"><?php echo esc($p['first_name'] . ' ' . $p['last_name']); ?></div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-mono text-brand-600 font-black bg-brand-50 px-1.5 py-0.5 rounded leading-none"><?php echo esc($p['mr_number']); ?></span>
                                            <span class="text-[10px] font-bold text-gray-300"><?php echo date('d M Y', strtotime($p['created_at'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-gray-900 font-black tracking-tight"><?php echo esc($p['phone'] ?: '—'); ?></span>
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Primary Contact</span>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wide <?php echo $p['gender'] === 'Female' ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-indigo-50 text-indigo-600 border border-indigo-100'; ?>">
                                    <i class="fa-solid <?php echo $p['gender'] === 'Female' ? 'fa-venus' : 'fa-mars'; ?> text-[9px]"></i>
                                    <?php echo esc($p['gender'] ?? '—'); ?>
                                    <?php if ($p['patient_age']): ?> · <?php echo $p['patient_age']; ?>Y<?php
        endif; ?>
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <a href="patients_view.php?id=<?php echo $p['id']; ?>"
                                   class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-teal-600 hover:text-white text-slate-400 rounded-xl transition-all border border-gray-100 hover:border-teal-600 shadow-sm hover:shadow-xl hover:-translate-x-1">
                                    <i class="fa-solid fa-chevron-right text-xs"></i>
                                </a>
                            </td>
                        </tr>
                        <?php
    endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
endif; ?>
        </div>

        <!-- Right Column: Quick Actions + Activity -->
        <div class="space-y-8">

            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 overflow-hidden relative group">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-brand-500/10 rounded-full blur-3xl group-hover:bg-brand-500/20 transition-all duration-700"></div>
                <div class="relative z-10">
                    <h3 class="font-black text-white text-sm flex items-center gap-2 mb-6 uppercase tracking-[0.2em]">
                        <i class="fa-solid fa-bolt-lightning text-brand-400 text-xs"></i> Clinical Forge
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <?php
$actions = [
    ['href' => 'patients_add.php', 'icon' => 'fa-user-plus', 'label' => 'Register', 'color' => 'brand'],
    ['href' => 'semen_analyses_add.php', 'icon' => 'fa-flask-vial', 'label' => 'Semen Lab', 'color' => 'sky'],
    ['href' => 'prescriptions_add.php', 'icon' => 'fa-prescription', 'label' => 'Rx Write', 'color' => 'indigo'],
    ['href' => 'lab_results_add.php', 'icon' => 'fa-vials', 'label' => 'Lab Post', 'color' => 'amber'],
    ['href' => 'ultrasounds_add.php', 'icon' => 'fa-image', 'label' => 'Scan Add', 'color' => 'emerald'],
    ['href' => 'receipts_add.php', 'icon' => 'fa-file-invoice-dollar', 'label' => 'Billing', 'color' => 'rose'],
];
foreach ($actions as $a):
?>
                        <a href="<?php echo $a['href']; ?>"
                           class="flex flex-col items-center justify-center gap-2 p-4 bg-white/5 hover:bg-white/10 rounded-2xl transition-all border border-white/5 hover:border-white/10 group/btn">
                            <i class="fa-solid <?php echo $a['icon']; ?> text-brand-400 text-lg group-hover/btn:scale-110 transition-transform"></i>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest group-hover/btn:text-white transition-colors"><?php echo $a['label']; ?></span>
                        </a>
                        <?php
endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Activity Feed -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/30">
                    <h3 class="font-black text-gray-900 text-xs flex items-center gap-2 uppercase tracking-widest">
                        <i class="fa-solid fa-wave-square text-brand-500 text-xs"></i> Clinical Log
                    </h3>
                    <div class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></div>
                </div>
                <?php if (empty($activity)): ?>
                <div class="p-12 text-center">
                    <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-gray-100">
                        <i class="fa-solid fa-ghost text-gray-200"></i>
                    </div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Log is Silent</p>
                </div>
                <?php
else: ?>
                <div class="divide-y divide-gray-50">
                    <?php foreach ($activity as $act): ?>
                    <div class="px-6 py-4 flex items-start gap-4 hover:bg-brand-50/30 transition-all group">
                        <div class="w-10 h-10 bg-<?php echo $act['color']; ?>-50 text-<?php echo $act['color']; ?>-600 rounded-xl flex items-center justify-center shrink-0 text-sm border border-<?php echo $act['color']; ?>-100 group-hover:scale-110 transition-transform shadow-sm shadow-<?php echo $act['color']; ?>-100/50">
                            <i class="fa-solid <?php echo $act['icon']; ?>"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[11px] font-black text-gray-900 group-hover:text-brand-950 transition-colors uppercase tracking-tight"><?php echo esc($act['name']); ?></div>
                            <div class="text-[10px] font-bold text-gray-400 truncate mt-0.5"><?php echo $act['label']; ?></div>
                            <div class="mt-2 flex items-center gap-3">
                                <span class="text-[9px] font-black text-gray-300 uppercase tracking-tighter flex items-center gap-1">
                                    <i class="fa-regular fa-clock"></i> <?php echo date('H:i', strtotime($act['dt'])); ?>
                                </span>
                                <a href="patients_view.php?id=<?php echo $act['pid']; ?>"
                                   class="text-[9px] font-black text-brand-500 hover:text-brand-700 uppercase tracking-widest flex items-center gap-1">
                                    Open Case <i class="fa-solid fa-arrow-right-long text-[8px]"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php
    endforeach; ?>
                </div>
                <?php
endif; ?>
            </div>

        </div>
    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
