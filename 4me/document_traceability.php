<?php
/**
 * Document Traceability & Security Audit Log
 * Admin tool for tracking all document views, downloads, prints and shares.
 */
$pageTitle = "Document Traceability";
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/traceability.php';

// Ensure the tracking table exists
$conn->query("CREATE TABLE IF NOT EXISTS document_download_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_type VARCHAR(50) NOT NULL COMMENT 'prescription|lab_result|ultrasound|semen_analysis',
    document_id INT NOT NULL,
    patient_id INT,
    admin_id INT,
    action VARCHAR(30) DEFAULT 'download' COMMENT 'download|print|view|share',
    tracking_code VARCHAR(20) UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (tracking_code),
    INDEX (document_type, document_id),
    INDEX (patient_id),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ─── SEARCH ───────────────────────────────────────────────────
$search_code = trim($_GET['code'] ?? '');
$filter_type = $_GET['type'] ?? '';
$filter_patient = trim($_GET['patient'] ?? '');
$page = max(1, intval($_GET['p'] ?? 1));
$per_page = 25;
$offset = ($page - 1) * $per_page;

// Single trace lookup
$single_record = null;
if ($search_code !== '') {
    $stmt = $conn->prepare("
        SELECT l.*, p.first_name, p.last_name, p.mr_number
        FROM document_download_logs l
        LEFT JOIN patients p ON p.id = l.patient_id
        WHERE l.tracking_code = ?
    ");
    $stmt->bind_param('s', $search_code);
    $stmt->execute();
    $single_record = $stmt->get_result()->fetch_assoc();
}

// Build filtered list
$where_parts = [];
$bind_types = '';
$bind_vals = [];

if ($filter_type !== '') {
    $where_parts[] = 'l.document_type = ?';
    $bind_types .= 's';
    $bind_vals[] = $filter_type;
}
if ($filter_patient !== '') {
    $where_parts[] = "(p.mr_number LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ?)";
    $bind_types .= 'sss';
    $like = "%{$filter_patient}%";
    $bind_vals[] = $like;
    $bind_vals[] = $like;
    $bind_vals[] = $like;
}

$where_sql = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';

// Count
$count_stmt = $conn->prepare("SELECT COUNT(*) as c FROM document_download_logs l LEFT JOIN patients p ON p.id = l.patient_id {$where_sql}");
if ($bind_types)
    $count_stmt->bind_param($bind_types, ...$bind_vals);
$count_stmt->execute();
$total_records = (int)$count_stmt->get_result()->fetch_assoc()['c'];
$total_pages = max(1, ceil($total_records / $per_page));

// Records
$logs = [];
$list_stmt = $conn->prepare("
    SELECT l.*, p.first_name, p.last_name, p.mr_number
    FROM document_download_logs l
    LEFT JOIN patients p ON p.id = l.patient_id
    {$where_sql}
    ORDER BY l.created_at DESC
    LIMIT ? OFFSET ?
");
$list_bind_types = $bind_types . 'ii';
$list_bind_vals = array_merge($bind_vals, [$per_page, $offset]);
$list_stmt->bind_param($list_bind_types, ...$list_bind_vals);
$list_stmt->execute();
$res = $list_stmt->get_result();
while ($row = $res->fetch_assoc())
    $logs[] = $row;

// Summary counts
$summary = [];
$sr = $conn->query("SELECT document_type, COUNT(*) as c FROM document_download_logs GROUP BY document_type");
if ($sr)
    while ($r = $sr->fetch_assoc())
        $summary[$r['document_type']] = $r['c'];

include __DIR__ . '/includes/header.php';
?>

<div class="max-w-6xl mx-auto">

    <!-- Page Header -->
    <div class="mb-6 flex items-start justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Document Traceability</h1>
            <p class="text-sm text-slate-400 mt-1">Security audit log for all document views, downloads, prints and shares</p>
        </div>
        <div class="flex items-center gap-3 text-xs text-slate-500">
            <span class="px-2.5 py-1 bg-slate-100 rounded-lg font-medium"><?php echo number_format($total_records); ?> total events</span>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <?php
$types = [
    'prescription' => ['label' => 'Prescriptions', 'icon' => 'fa-prescription', 'color' => 'violet'],
    'lab_result' => ['label' => 'Lab Results', 'icon' => 'fa-vials', 'color' => 'amber'],
    'ultrasound' => ['label' => 'Ultrasounds', 'icon' => 'fa-image', 'color' => 'sky'],
    'semen_analysis' => ['label' => 'Semen Reports', 'icon' => 'fa-flask-vial', 'color' => 'rose'],
];
$color_map = [
    'violet' => 'bg-violet-50 text-violet-600',
    'amber' => 'bg-amber-50 text-amber-600',
    'sky' => 'bg-sky-50 text-sky-600',
    'rose' => 'bg-rose-50 text-rose-600',
];
foreach ($types as $key => $t): ?>
        <a href="?type=<?php echo $key; ?>" class="bg-white rounded-2xl border border-gray-100 p-4 hover:shadow-sm transition-all <?php echo $filter_type === $key ? 'ring-2 ring-teal-300' : ''; ?>">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 <?php echo $color_map[$t['color']]; ?> rounded-lg flex items-center justify-center">
                    <i class="fa-solid <?php echo $t['icon']; ?> text-xs"></i>
                </div>
                <span class="text-xs text-slate-400"><?php echo $t['label']; ?></span>
            </div>
            <div class="text-2xl font-semibold text-slate-700"><?php echo number_format($summary[$key] ?? 0); ?></div>
        </a>
        <?php
endforeach; ?>
    </div>

    <!-- Search by Trace Code -->
    <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">
            <i class="fa-solid fa-magnifying-glass text-teal-500 mr-2"></i>Trace a Document by Code
        </h2>
        <form method="GET" class="flex gap-3">
            <input type="text" name="code" value="<?php echo esc($search_code); ?>"
                   placeholder="Enter 12-digit trace code (e.g. 260304001234)"
                   class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-teal-300 focus:border-teal-300 bg-gray-50">
            <button type="submit" class="px-6 py-3 bg-teal-600 text-white text-sm font-medium rounded-xl hover:bg-teal-700 transition-colors">
                <i class="fa-solid fa-search mr-2"></i>Trace
            </button>
            <?php if ($search_code): ?>
            <a href="document_traceability.php" class="px-4 py-3 border border-gray-200 text-gray-500 text-sm font-medium rounded-xl hover:bg-gray-50 transition-colors">Clear</a>
            <?php
endif; ?>
        </form>

        <?php if ($search_code && !$single_record): ?>
        <div class="mt-4 bg-rose-50 border border-rose-100 text-rose-700 text-sm px-4 py-3 rounded-xl">
            <i class="fa-solid fa-circle-exclamation mr-2"></i>No record found for trace code <strong><?php echo esc($search_code); ?></strong>
        </div>
        <?php
endif; ?>

        <?php if ($single_record): ?>
        <div id="trace-detail" class="mt-5 bg-gradient-to-br from-teal-50 to-slate-50 border border-teal-100 rounded-2xl p-6 print:border print:border-gray-300">
            <div class="flex items-start justify-between mb-5">
                <div>
                    <div class="text-xs text-teal-600 font-semibold uppercase tracking-wider mb-1">Document Trace Report</div>
                    <div class="font-mono text-2xl font-semibold text-slate-800"><?php echo esc($single_record['tracking_code']); ?></div>
                </div>
                <button onclick="window.print()" class="px-4 py-2 bg-white border border-gray-200 text-sm font-medium text-gray-600 rounded-xl hover:bg-gray-50 print:hidden">
                    <i class="fa-solid fa-print mr-2"></i>Print Report
                </button>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                <?php
    $fields = [
        'Document Type' => ucfirst(str_replace('_', ' ', $single_record['document_type'])),
        'Document ID' => '#' . $single_record['document_id'],
        'Action' => ucfirst($single_record['action'] ?? 'Download'),
        'Patient' => $single_record['first_name'] ? esc($single_record['first_name'] . ' ' . $single_record['last_name']) . ' (' . esc($single_record['mr_number']) . ')' : 'N/A',
        'Admin ID' => $single_record['admin_id'] ? '#' . $single_record['admin_id'] : 'Patient Portal',
        'IP Address' => esc($single_record['ip_address'] ?? '—'),
        'Date & Time' => date('d M Y, H:i:s', strtotime($single_record['created_at'])),
        'User Agent' => strlen($single_record['user_agent'] ?? '') > 60 ? substr($single_record['user_agent'], 0, 60) . '…' : esc($single_record['user_agent'] ?? '—'),
        'Notes' => esc($single_record['notes'] ?? '—'),
    ];
    foreach ($fields as $label => $val): ?>
                <div class="bg-white rounded-xl p-3 border border-white/60">
                    <div class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold mb-1"><?php echo $label; ?></div>
                    <div class="text-slate-700 font-medium text-xs break-words"><?php echo $val; ?></div>
                </div>
                <?php
    endforeach; ?>
            </div>
        </div>
        <?php
endif; ?>
    </div>

    <!-- Filter Bar + Log Table -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex flex-wrap items-center gap-3">
            <h2 class="text-sm font-semibold text-slate-700 flex-1">Security Event Log</h2>
            <form method="GET" class="flex gap-2 flex-wrap">
                <?php if ($search_code): ?><input type="hidden" name="code" value="<?php echo esc($search_code); ?>"><?php
endif; ?>
                <input type="text" name="patient" value="<?php echo esc($filter_patient); ?>"
                       placeholder="Search patient…"
                       class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-300 bg-gray-50 w-40">
                <select name="type" class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-300 bg-gray-50">
                    <option value="">All Types</option>
                    <?php foreach (array_keys($types) as $t): ?>
                    <option value="<?php echo $t; ?>" <?php echo $filter_type === $t ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_', ' ', $t)); ?></option>
                    <?php
endforeach; ?>
                </select>
                <button type="submit" class="px-3 py-1.5 bg-teal-600 text-white text-xs font-medium rounded-lg hover:bg-teal-700 transition-colors">Filter</button>
                <?php if ($filter_type || $filter_patient): ?>
                <a href="document_traceability.php" class="px-3 py-1.5 border border-gray-200 text-gray-500 text-xs rounded-lg hover:bg-gray-50">Clear</a>
                <?php
endif; ?>
            </form>
        </div>

        <?php if (!empty($logs)): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left font-semibold text-slate-400 uppercase tracking-wider">Trace Code</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-400 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-400 uppercase tracking-wider">Action</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-400 uppercase tracking-wider">Patient</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-400 uppercase tracking-wider">IP Address</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-400 uppercase tracking-wider">Date & Time</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-400 uppercase tracking-wider">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($logs as $log):
        $type_colors = ['prescription' => 'bg-violet-50 text-violet-700', 'lab_result' => 'bg-amber-50 text-amber-700', 'ultrasound' => 'bg-sky-50 text-sky-700', 'semen_analysis' => 'bg-rose-50 text-rose-700'];
        $action_colors = ['download' => 'bg-teal-50 text-teal-700', 'print' => 'bg-slate-50 text-slate-700', 'view' => 'bg-blue-50 text-blue-700', 'share' => 'bg-orange-50 text-orange-700'];
        $tc = $type_colors[$log['document_type']] ?? 'bg-gray-50 text-gray-700';
        $ac = $action_colors[$log['action'] ?? 'download'] ?? 'bg-gray-50 text-gray-700';
?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-mono text-slate-600 font-medium"><?php echo esc($log['tracking_code'] ?? '—'); ?></td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-md font-medium <?php echo $tc; ?>"><?php echo ucfirst(str_replace('_', ' ', $log['document_type'])); ?></span></td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-md font-medium <?php echo $ac; ?>"><?php echo ucfirst($log['action'] ?? 'Download'); ?></span></td>
                        <td class="px-4 py-3 text-slate-600">
                            <?php if ($log['first_name']): ?>
                            <a href="patients_view.php?id=<?php echo $log['patient_id']; ?>" class="text-teal-600 hover:underline"><?php echo esc($log['first_name'] . ' ' . $log['last_name']); ?></a>
                            <div class="text-slate-400"><?php echo esc($log['mr_number']); ?></div>
                            <?php
        else: ?><span class="text-slate-300">—</span><?php
        endif; ?>
                        </td>
                        <td class="px-4 py-3 font-mono text-slate-500"><?php echo esc($log['ip_address'] ?? '—'); ?></td>
                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap"><?php echo date('d M Y H:i', strtotime($log['created_at'])); ?></td>
                        <td class="px-4 py-3">
                            <a href="?code=<?php echo urlencode($log['tracking_code'] ?? ''); ?>" class="text-teal-600 hover:text-teal-800 transition-colors font-medium">
                                View <i class="fa-solid fa-arrow-right text-[9px]"></i>
                            </a>
                        </td>
                    </tr>
                    <?php
    endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-50 flex items-center justify-between">
            <div class="text-xs text-slate-400">
                Showing <?php echo($offset + 1); ?>–<?php echo min($offset + $per_page, $total_records); ?> of <?php echo number_format($total_records); ?> events
            </div>
            <div class="flex items-center gap-1">
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <a href="?p=<?php echo $i; ?>&type=<?php echo urlencode($filter_type); ?>&patient=<?php echo urlencode($filter_patient); ?>"
                   class="w-7 h-7 flex items-center justify-center rounded-lg text-xs font-medium transition-colors
                          <?php echo $i === $page ? 'bg-teal-600 text-white' : 'text-slate-500 hover:bg-gray-100'; ?>">
                    <?php echo $i; ?>
                </a>
                <?php
        endfor; ?>
            </div>
        </div>
        <?php
    endif; ?>

        <?php
else: ?>
        <div class="py-20 text-center">
            <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-shield-halved text-slate-300 text-2xl"></i>
            </div>
            <p class="text-sm text-slate-400">No document events logged yet.</p>
            <p class="text-xs text-slate-300 mt-1">Events are recorded when patients or admins download, print or view documents.</p>
        </div>
        <?php
endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
