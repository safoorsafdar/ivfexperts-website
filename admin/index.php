<?php
$pageTitle = "Dashboard";
require_once __DIR__ . '/includes/auth.php';

$currentMonth = date('Y-m');
$today        = date('Y-m-d');

// ── Stats ──────────────────────────────────────────────────────────────────────
$stats = [
    'patients_total'    => 0,
    'patients_month'    => 0,
    'prescriptions_month' => 0,
    'pending_labs'      => 0,
    'revenue_month'     => 0,
    'procedures_month'  => 0,
];

try {
    $m = $conn->escape_string($currentMonth);
    $t = $conn->escape_string($today);

    $r = $conn->query("SELECT COUNT(*) AS c FROM patients"); if ($r) $stats['patients_total'] = $r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) AS c FROM patients WHERE DATE_FORMAT(created_at,'%Y-%m') = '$m'"); if ($r) $stats['patients_month'] = $r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) AS c FROM prescriptions WHERE DATE_FORMAT(created_at,'%Y-%m') = '$m'"); if ($r) $stats['prescriptions_month'] = $r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) AS c FROM patient_lab_results WHERE status = 'Pending'"); if ($r) $stats['pending_labs'] = $r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM receipts WHERE DATE_FORMAT(receipt_date,'%Y-%m') = '$m' AND status = 'Paid'"); if ($r) $stats['revenue_month'] = $r->fetch_assoc()['s'];
    $r = $conn->query("SELECT COUNT(*) AS c FROM advised_procedures WHERE DATE_FORMAT(date_advised,'%Y-%m') = '$m'"); if ($r) $stats['procedures_month'] = $r->fetch_assoc()['c'];
} catch (Exception $e) { /* Graceful — stats default to 0 */ }

// ── Recent Patients ────────────────────────────────────────────────────────────
$recent_patients = [];
try {
    $res = $conn->query("SELECT id, mr_number, first_name, last_name, gender, patient_age, phone, created_at FROM patients ORDER BY created_at DESC LIMIT 8");
    if ($res) while ($row = $res->fetch_assoc()) $recent_patients[] = $row;
} catch (Exception $e) {}

// ── Recent Activity Feed ───────────────────────────────────────────────────────
$activity = [];
try {
    // Recent prescriptions
    $res = $conn->query("SELECT p.created_at AS dt, CONCAT(pt.first_name,' ',pt.last_name) AS name, pt.mr_number, p.patient_id AS pid FROM prescriptions p JOIN patients pt ON p.patient_id = pt.id ORDER BY p.created_at DESC LIMIT 4");
    if ($res) while ($row = $res->fetch_assoc()) $activity[] = ['type'=>'rx','icon'=>'fa-prescription','color'=>'indigo','label'=>'Prescription issued','name'=>$row['name'],'mr'=>$row['mr_number'],'pid'=>$row['pid'],'dt'=>$row['dt']];

    // Recent semen analyses
    $res = $conn->query("SELECT s.collection_time AS dt, CONCAT(pt.first_name,' ',pt.last_name) AS name, pt.mr_number, s.patient_id AS pid FROM semen_analyses s JOIN patients pt ON s.patient_id = pt.id ORDER BY s.id DESC LIMIT 3");
    if ($res) while ($row = $res->fetch_assoc()) $activity[] = ['type'=>'sa','icon'=>'fa-flask-vial','color'=>'sky','label'=>'Semen analysis recorded','name'=>$row['name'],'mr'=>$row['mr_number'],'pid'=>$row['pid'],'dt'=>$row['dt']];

    // Recent lab results
    $res = $conn->query("SELECT plt.created_at AS dt, ltd.test_name, CONCAT(pt.first_name,' ',pt.last_name) AS name, pt.mr_number, plt.patient_id AS pid FROM patient_lab_results plt JOIN patients pt ON plt.patient_id = pt.id JOIN lab_tests_directory ltd ON plt.test_id = ltd.id ORDER BY plt.id DESC LIMIT 3");
    if ($res) while ($row = $res->fetch_assoc()) $activity[] = ['type'=>'lab','icon'=>'fa-vials','color'=>'amber','label'=>'Lab result posted ('.$row['test_name'].')','name'=>$row['name'],'mr'=>$row['mr_number'],'pid'=>$row['pid'],'dt'=>$row['dt']];

    // Sort by date desc
    usort($activity, fn($a,$b) => strtotime($b['dt']) - strtotime($a['dt']));
    $activity = array_slice($activity, 0, 8);
} catch (Exception $e) {}

// ── Month Label ────────────────────────────────────────────────────────────────
$monthLabel = date('F Y');

include __DIR__ . '/includes/header.php';
?>

<div class="space-y-8">

    <!-- Welcome Strip -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-800 tracking-tight">Good <?php echo date('H') < 12 ? 'Morning' : (date('H') < 18 ? 'Afternoon' : 'Evening'); ?>,
                <span class="text-teal-600"><?php echo esc($_SESSION['admin_username'] ?? 'Doctor'); ?></span>
            </h1>
            <p class="text-sm text-gray-400 font-bold mt-0.5"><?php echo date('l, d F Y'); ?> &nbsp;·&nbsp; IVF Experts Clinical EMR</p>
        </div>
        <a href="patients_add.php"
           class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white px-5 py-3 rounded-xl font-black text-sm shadow-lg shadow-teal-100 transition-all active:scale-95">
            <i class="fa-solid fa-user-plus"></i> Register New Patient
        </a>
    </div>

    <!-- ── Stats Grid ──────────────────────────────────────────────────────── -->
    <div>
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
            <i class="fa-solid fa-calendar-day text-teal-500 text-xs"></i> <?php echo $monthLabel; ?> — Monthly Snapshot
        </p>
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <?php
            $stat_cards = [
                ['value' => number_format($stats['patients_total']),    'label' => 'Total Patients',   'sub' => '+'.number_format($stats['patients_month']).' this month', 'icon' => 'fa-users',             'color' => 'teal',   'href' => 'patients.php'],
                ['value' => number_format($stats['patients_month']),    'label' => 'New This Month',   'sub' => 'Registered patients', 'icon' => 'fa-user-plus',          'color' => 'emerald', 'href' => 'patients.php'],
                ['value' => number_format($stats['prescriptions_month']),'label'=> 'Prescriptions',    'sub' => 'Issued this month',   'icon' => 'fa-prescription',        'color' => 'indigo',  'href' => 'prescriptions.php'],
                ['value' => number_format($stats['pending_labs']),      'label' => 'Pending Labs',     'sub' => 'Awaiting results',    'icon' => 'fa-flask-vial',          'color' => 'amber',   'href' => 'lab_results.php'],
                ['value' => 'Rs. '.number_format($stats['revenue_month']),'label'=> 'Revenue',         'sub' => 'Collected this month','icon' => 'fa-wallet',              'color' => 'sky',     'href' => 'financials.php'],
                ['value' => number_format($stats['procedures_month']),  'label' => 'Procedures',       'sub' => 'Advised this month',  'icon' => 'fa-clipboard-check',     'color' => 'rose',    'href' => 'procedures.php'],
            ];
            foreach ($stat_cards as $c):
            ?>
            <a href="<?php echo $c['href']; ?>"
               class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 group">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-<?php echo $c['color']; ?>-50 text-<?php echo $c['color']; ?>-600 rounded-xl flex items-center justify-center group-hover:bg-<?php echo $c['color']; ?>-100 transition-colors">
                        <i class="fa-solid <?php echo $c['icon']; ?> text-base"></i>
                    </div>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-200 group-hover:text-<?php echo $c['color']; ?>-400 transition-colors mt-1"></i>
                </div>
                <div class="text-2xl font-black text-gray-800 tracking-tight mb-0.5 leading-none"><?php echo $c['value']; ?></div>
                <div class="text-xs font-black text-gray-600 mb-0.5"><?php echo $c['label']; ?></div>
                <div class="text-[10px] font-bold text-gray-400"><?php echo $c['sub']; ?></div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── Main Content: Recent Patients + Activity ─────────────────────────── -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- Recent Patients -->
        <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <h3 class="font-black text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-teal-500 text-sm"></i> Recently Registered
                </h3>
                <a href="patients.php" class="text-xs font-black text-teal-600 hover:text-teal-700 flex items-center gap-1">
                    View All <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            <?php if (empty($recent_patients)): ?>
            <div class="p-12 text-center text-gray-400">
                <i class="fa-solid fa-users text-4xl mb-3 block text-gray-100"></i>
                <p class="font-bold">No patients registered yet.</p>
                <a href="patients_add.php" class="mt-3 inline-block text-teal-600 font-black text-sm">Register first patient →</a>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[9px] font-black uppercase tracking-widest text-gray-400 bg-gray-50/50 border-b border-gray-50">
                            <th class="px-6 py-3">Patient</th>
                            <th class="px-6 py-3">Contact</th>
                            <th class="px-6 py-3">Gender / Age</th>
                            <th class="px-6 py-3">Registered</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach ($recent_patients as $p): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-teal-50 text-teal-600 rounded-xl flex items-center justify-center shrink-0 font-black text-sm">
                                        <?php echo strtoupper(substr($p['first_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="font-black text-gray-800 text-sm leading-tight"><?php echo esc($p['first_name'] . ' ' . $p['last_name']); ?></div>
                                        <div class="text-[10px] font-mono text-teal-600 font-bold"><?php echo esc($p['mr_number']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="text-xs text-gray-500 font-bold"><?php echo esc($p['phone'] ?: '—'); ?></span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase <?php echo $p['gender'] === 'Female' ? 'bg-pink-50 text-pink-700' : 'bg-indigo-50 text-indigo-700'; ?>">
                                    <?php echo esc($p['gender'] ?? '—'); ?>
                                    <?php if ($p['patient_age']): ?> · <?php echo $p['patient_age']; ?>y<?php endif; ?>
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="text-[10px] font-bold text-gray-400"><?php echo date('d M Y', strtotime($p['created_at'])); ?></span>
                            </td>
                            <td class="px-6 py-3.5">
                                <a href="patients_view.php?id=<?php echo $p['id']; ?>"
                                   class="opacity-0 group-hover:opacity-100 w-8 h-8 bg-teal-50 hover:bg-teal-600 hover:text-white text-teal-600 rounded-xl flex items-center justify-center transition-all text-xs">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Quick Actions + Activity -->
        <div class="space-y-6">

            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-black text-gray-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-bolt text-amber-500 text-xs"></i> Quick Actions
                    </h3>
                </div>
                <div class="p-4 grid grid-cols-2 gap-3">
                    <?php
                    $actions = [
                        ['href'=>'patients_add.php',      'icon'=>'fa-user-plus',              'label'=>'New Patient',   'color'=>'teal'],
                        ['href'=>'semen_analyses_add.php', 'icon'=>'fa-flask-vial',             'label'=>'New SA',        'color'=>'sky'],
                        ['href'=>'prescriptions_add.php',  'icon'=>'fa-prescription',           'label'=>'Write Rx',      'color'=>'indigo'],
                        ['href'=>'lab_results_add.php',    'icon'=>'fa-vials',                  'label'=>'Post Lab',      'color'=>'amber'],
                        ['href'=>'ultrasounds_add.php',    'icon'=>'fa-image',                  'label'=>'Add Scan',      'color'=>'emerald'],
                        ['href'=>'receipts_add.php',       'icon'=>'fa-file-invoice-dollar',    'label'=>'Bill Patient',  'color'=>'rose'],
                    ];
                    foreach ($actions as $a):
                    ?>
                    <a href="<?php echo $a['href']; ?>"
                       class="flex flex-col items-center justify-center gap-1.5 p-3 bg-<?php echo $a['color']; ?>-50 hover:bg-<?php echo $a['color']; ?>-100 rounded-xl transition-colors group">
                        <i class="fa-solid <?php echo $a['icon']; ?> text-<?php echo $a['color']; ?>-600 text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[10px] font-black text-<?php echo $a['color']; ?>-700 uppercase tracking-wide"><?php echo $a['label']; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Activity Feed -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-black text-gray-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-activity text-teal-500 text-xs"></i> Recent Activity
                    </h3>
                </div>
                <?php if (empty($activity)): ?>
                <div class="p-8 text-center text-gray-400">
                    <i class="fa-solid fa-inbox text-3xl mb-2 block text-gray-100"></i>
                    <p class="text-sm font-bold">No activity yet.</p>
                </div>
                <?php else: ?>
                <div class="divide-y divide-gray-50">
                    <?php foreach ($activity as $act): ?>
                    <div class="px-5 py-3 flex items-center gap-3 hover:bg-gray-50/50 transition-colors">
                        <div class="w-8 h-8 bg-<?php echo $act['color']; ?>-50 text-<?php echo $act['color']; ?>-600 rounded-xl flex items-center justify-center shrink-0 text-xs">
                            <i class="fa-solid <?php echo $act['icon']; ?>"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-bold text-gray-700 truncate"><?php echo esc($act['name']); ?></div>
                            <div class="text-[9px] font-bold text-gray-400 truncate"><?php echo $act['label']; ?></div>
                        </div>
                        <div class="flex flex-col items-end gap-1 shrink-0">
                            <span class="text-[9px] font-bold text-gray-300"><?php echo date('d M', strtotime($act['dt'])); ?></span>
                            <a href="patients_view.php?id=<?php echo $act['pid']; ?>"
                               class="text-[9px] font-black text-teal-600 hover:text-teal-800 leading-none">View →</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
