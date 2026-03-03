<?php
$pageTitle = "Financial Dashboard";
require_once __DIR__ . '/includes/auth.php';

// Handle Receipt Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_receipt_id'])) {
    $id = (int)$_POST['delete_receipt_id'];
    $conn->query("DELETE FROM receipts WHERE id = $id");
    header("Location: financials.php?msg=receipt_deleted");
    exit;
}

// Handle Expense Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_expense_id'])) {
    $id = (int)$_POST['delete_expense_id'];
    $conn->query("DELETE FROM expenses WHERE id = $id");
    header("Location: financials.php?msg=expense_deleted");
    exit;
}

$msg = $_GET['msg'] ?? '';

// ── Period selection ──────────────────────────────────────────────────────────
$view_month  = $_GET['month'] ?? date('Y-m');
$view_label  = date('F Y', strtotime($view_month . '-01'));
$prev_month  = date('Y-m', strtotime($view_month . '-01 -1 month'));
$next_month  = date('Y-m', strtotime($view_month . '-01 +1 month'));
$is_current  = ($view_month === date('Y-m'));

// ── ALL-TIME totals ───────────────────────────────────────────────────────────
$all_revenue  = 0;
$all_expenses = 0;
try {
    $r = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM receipts");
    if ($r) $all_revenue = floatval($r->fetch_assoc()['s']);
    $r = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM expenses");
    if ($r) $all_expenses = floatval($r->fetch_assoc()['s']);
} catch (Throwable $e) {}
$all_net = $all_revenue - $all_expenses;

// ── SELECTED MONTH totals ─────────────────────────────────────────────────────
$month_revenue  = 0;
$month_expenses = 0;
$month_paid     = 0;
$month_pending  = 0;
try {
    $vm = $conn->escape_string($view_month);
    $r = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM receipts WHERE DATE_FORMAT(receipt_date,'%Y-%m')='$vm'");
    if ($r) $month_revenue = floatval($r->fetch_assoc()['s']);
    $r = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM receipts WHERE DATE_FORMAT(receipt_date,'%Y-%m')='$vm' AND status='Paid'");
    if ($r) $month_paid = floatval($r->fetch_assoc()['s']);
    $r = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM receipts WHERE DATE_FORMAT(receipt_date,'%Y-%m')='$vm' AND (status='Pending' OR status IS NULL OR status='')");
    if ($r) $month_pending = floatval($r->fetch_assoc()['s']);
    $r = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM expenses WHERE DATE_FORMAT(expense_date,'%Y-%m')='$vm'");
    if ($r) $month_expenses = floatval($r->fetch_assoc()['s']);
} catch (Throwable $e) {}
$month_net = $month_revenue - $month_expenses;

// ── PREV MONTH for comparison ─────────────────────────────────────────────────
$prev_revenue = 0;
$prev_expense_total = 0;
try {
    $pm = $conn->escape_string($prev_month);
    $r = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM receipts WHERE DATE_FORMAT(receipt_date,'%Y-%m')='$pm'");
    if ($r) $prev_revenue = floatval($r->fetch_assoc()['s']);
    $r = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM expenses WHERE DATE_FORMAT(expense_date,'%Y-%m')='$pm'");
    if ($r) $prev_expense_total = floatval($r->fetch_assoc()['s']);
} catch (Throwable $e) {}

$rev_change   = $prev_revenue > 0 ? round((($month_revenue - $prev_revenue) / $prev_revenue) * 100, 1) : null;
$exp_change   = $prev_expense_total > 0 ? round((($month_expenses - $prev_expense_total) / $prev_expense_total) * 100, 1) : null;

// ── LAST 6 MONTHS chart data ──────────────────────────────────────────────────
$chart_months = [];
$chart_rev    = [];
$chart_exp    = [];
for ($i = 5; $i >= 0; $i--) {
    $m  = date('Y-m', strtotime($view_month . '-01 -' . $i . ' months'));
    $ml = date('M', strtotime($m . '-01'));
    $chart_months[] = $ml;
    try {
        $me = $conn->escape_string($m);
        $rv = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM receipts WHERE DATE_FORMAT(receipt_date,'%Y-%m')='$me'");
        $chart_rev[] = $rv ? floatval($rv->fetch_assoc()['s']) : 0;
        $xe = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM expenses WHERE DATE_FORMAT(expense_date,'%Y-%m')='$me'");
        $chart_exp[] = $xe ? floatval($xe->fetch_assoc()['s']) : 0;
    } catch (Throwable $e) {
        $chart_rev[] = 0;
        $chart_exp[] = 0;
    }
}
$chart_max = max(array_merge($chart_rev, $chart_exp, [1]));

// ── MONTH receipts ────────────────────────────────────────────────────────────
$month_receipts = [];
try {
    $vm = $conn->escape_string($view_month);
    $stmt = $conn->query("SELECT r.id, r.receipt_date, r.procedure_name, r.amount, r.status, p.first_name, p.last_name
                          FROM receipts r JOIN patients p ON r.patient_id = p.id
                          WHERE DATE_FORMAT(r.receipt_date,'%Y-%m') = '$vm'
                          ORDER BY r.receipt_date DESC");
    if ($stmt) while ($row = $stmt->fetch_assoc()) $month_receipts[] = $row;
} catch (Throwable $e) {}

// ── MONTH expenses ────────────────────────────────────────────────────────────
$month_expense_rows = [];
try {
    $vm = $conn->escape_string($view_month);
    $stmt = $conn->query("SELECT * FROM expenses WHERE DATE_FORMAT(expense_date,'%Y-%m') = '$vm' ORDER BY expense_date DESC");
    if ($stmt) while ($row = $stmt->fetch_assoc()) $month_expense_rows[] = $row;
} catch (Throwable $e) {}

include __DIR__ . '/includes/header.php';
?>

<?php if ($msg === 'receipt_deleted'): ?>
    <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-5 border border-red-100 flex items-center gap-3 shadow-sm">
        <i class="fa-solid fa-trash text-xl"></i>
        <span class="font-bold">Receipt deleted successfully.</span>
    </div>
<?php elseif ($msg === 'expense_deleted'): ?>
    <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-5 border border-red-100 flex items-center gap-3 shadow-sm">
        <i class="fa-solid fa-trash text-xl"></i>
        <span class="font-bold">Expense deleted successfully.</span>
    </div>
<?php endif; ?>

<!-- Page Header -->
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-black text-gray-800">Financial Dashboard</h2>
        <p class="text-sm text-gray-400 font-medium">Track income, expenses, and profitability</p>
    </div>
    <div class="flex gap-2">
        <a href="expenses_add.php" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-4 rounded-xl transition-colors flex items-center gap-2 shadow-sm text-sm">
            <i class="fa-solid fa-minus"></i> Log Expense
        </a>
        <a href="receipts_add.php" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl transition-colors flex items-center gap-2 shadow-sm text-sm">
            <i class="fa-solid fa-plus"></i> Generate Receipt
        </a>
    </div>
</div>

<!-- All-time summary strip -->
<div class="bg-slate-800 rounded-2xl p-5 mb-6 flex flex-wrap gap-6 items-center text-white shadow-lg">
    <div class="shrink-0">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">All-Time Revenue</p>
        <p class="text-2xl font-black">Rs. <?php echo number_format($all_revenue, 0); ?></p>
    </div>
    <div class="w-px h-10 bg-slate-600 hidden sm:block"></div>
    <div class="shrink-0">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">All-Time Expenses</p>
        <p class="text-2xl font-black text-red-400">Rs. <?php echo number_format($all_expenses, 0); ?></p>
    </div>
    <div class="w-px h-10 bg-slate-600 hidden sm:block"></div>
    <div class="shrink-0">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Net Profit</p>
        <p class="text-2xl font-black <?php echo $all_net >= 0 ? 'text-emerald-400' : 'text-amber-400'; ?>">Rs. <?php echo number_format($all_net, 0); ?></p>
    </div>
    <?php if ($all_revenue > 0): ?>
    <div class="ml-auto hidden md:block">
        <div class="text-[10px] text-slate-400 mb-1 font-bold uppercase">Overall Margin</div>
        <div class="text-3xl font-black <?php echo $all_net >= 0 ? 'text-emerald-400' : 'text-amber-400'; ?>">
            <?php echo round(($all_net / $all_revenue) * 100, 1); ?>%
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Month Navigator -->
<div class="flex items-center gap-3 mb-6">
    <a href="financials.php?month=<?php echo $prev_month; ?>" class="bg-white border border-gray-200 hover:border-teal-400 text-gray-600 hover:text-teal-700 font-bold py-2 px-4 rounded-xl text-sm transition-all shadow-sm">
        <i class="fa-solid fa-chevron-left"></i>
    </a>
    <div class="bg-white border border-gray-200 rounded-xl px-5 py-2 font-black text-gray-800 text-base shadow-sm min-w-[140px] text-center">
        <?php echo $view_label; ?>
        <?php if ($is_current): ?><span class="ml-1 text-[10px] text-teal-600 font-black uppercase tracking-wider bg-teal-50 px-2 py-0.5 rounded-full">Current</span><?php endif; ?>
    </div>
    <?php if (!$is_current): ?>
    <a href="financials.php?month=<?php echo $next_month; ?>" class="bg-white border border-gray-200 hover:border-teal-400 text-gray-600 hover:text-teal-700 font-bold py-2 px-4 rounded-xl text-sm transition-all shadow-sm">
        <i class="fa-solid fa-chevron-right"></i>
    </a>
    <?php else: ?>
    <span class="bg-gray-100 text-gray-300 font-bold py-2 px-4 rounded-xl text-sm cursor-not-allowed"><i class="fa-solid fa-chevron-right"></i></span>
    <?php endif; ?>
    <?php if (!$is_current): ?>
    <a href="financials.php" class="ml-2 text-xs font-bold text-teal-600 hover:text-teal-800 underline">Back to Current Month</a>
    <?php endif; ?>
</div>

<!-- Month Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">

    <!-- Revenue -->
    <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 p-5 relative overflow-hidden">
        <div class="absolute -right-3 -top-3 w-20 h-20 bg-emerald-50 rounded-full opacity-60"></div>
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Revenue</p>
        <p class="text-2xl font-black text-gray-900">Rs. <?php echo number_format($month_revenue, 0); ?></p>
        <?php if ($rev_change !== null): ?>
            <p class="text-xs font-bold mt-1 <?php echo $rev_change >= 0 ? 'text-emerald-600' : 'text-red-500'; ?>">
                <i class="fa-solid fa-arrow-<?php echo $rev_change >= 0 ? 'up' : 'down'; ?>"></i>
                <?php echo abs($rev_change); ?>% vs last month
            </p>
        <?php endif; ?>
        <div class="absolute right-4 top-4 w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
            <i class="fa-solid fa-money-bill-wave text-emerald-600 text-base"></i>
        </div>
    </div>

    <!-- Paid -->
    <div class="bg-white rounded-2xl shadow-sm border border-teal-100 p-5">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Paid</p>
        <p class="text-2xl font-black text-teal-700">Rs. <?php echo number_format($month_paid, 0); ?></p>
        <?php if ($month_revenue > 0): ?>
            <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full bg-teal-500 rounded-full" style="width: <?php echo min(100, round(($month_paid/$month_revenue)*100)); ?>%"></div>
            </div>
            <p class="text-[10px] text-gray-400 mt-1"><?php echo round(($month_paid/$month_revenue)*100); ?>% collected</p>
        <?php endif; ?>
    </div>

    <!-- Expenses -->
    <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-5">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Expenses</p>
        <p class="text-2xl font-black text-red-600">Rs. <?php echo number_format($month_expenses, 0); ?></p>
        <?php if ($exp_change !== null): ?>
            <p class="text-xs font-bold mt-1 <?php echo $exp_change <= 0 ? 'text-emerald-600' : 'text-red-500'; ?>">
                <i class="fa-solid fa-arrow-<?php echo $exp_change >= 0 ? 'up' : 'down'; ?>"></i>
                <?php echo abs($exp_change); ?>% vs last month
            </p>
        <?php endif; ?>
    </div>

    <!-- Net Profit -->
    <div class="bg-white rounded-2xl shadow-sm border <?php echo $month_net >= 0 ? 'border-teal-200' : 'border-amber-200'; ?> p-5">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Net Profit</p>
        <p class="text-2xl font-black <?php echo $month_net >= 0 ? 'text-teal-700' : 'text-amber-600'; ?>">
            Rs. <?php echo number_format($month_net, 0); ?>
        </p>
        <?php if ($month_revenue > 0): ?>
            <p class="text-xs text-gray-400 mt-1 font-bold">
                Margin: <?php echo round(($month_net / $month_revenue) * 100, 1); ?>%
            </p>
        <?php endif; ?>
    </div>

</div>

<!-- 6-Month Mini Bar Chart (pure CSS) -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
    <h3 class="font-black text-gray-700 text-sm mb-5 uppercase tracking-widest">6-Month Revenue vs Expenses</h3>
    <div class="flex items-end gap-4 h-32">
        <?php foreach ($chart_months as $i => $lbl): ?>
            <?php
            $r_h = $chart_max > 0 ? round(($chart_rev[$i] / $chart_max) * 100) : 0;
            $e_h = $chart_max > 0 ? round(($chart_exp[$i] / $chart_max) * 100) : 0;
            $is_view = ($lbl === date('M', strtotime($view_month . '-01')));
            ?>
            <div class="flex-1 flex flex-col items-center gap-1">
                <div class="w-full flex items-end gap-1 h-24">
                    <div class="flex-1 rounded-t-lg bg-emerald-400 transition-all <?php echo $is_view ? 'opacity-100 ring-2 ring-emerald-500' : 'opacity-70'; ?>"
                         style="height: <?php echo max(4, $r_h); ?>%"
                         title="Revenue: Rs. <?php echo number_format($chart_rev[$i], 0); ?>"></div>
                    <div class="flex-1 rounded-t-lg bg-red-400 transition-all <?php echo $is_view ? 'opacity-100 ring-2 ring-red-400' : 'opacity-50'; ?>"
                         style="height: <?php echo max(2, $e_h); ?>%"
                         title="Expenses: Rs. <?php echo number_format($chart_exp[$i], 0); ?>"></div>
                </div>
                <span class="text-[10px] font-bold text-gray-500 <?php echo $is_view ? 'text-teal-700' : ''; ?>"><?php echo $lbl; ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="flex gap-4 mt-3 justify-end">
        <span class="text-[10px] font-bold text-gray-500 flex items-center gap-1"><span class="w-3 h-3 bg-emerald-400 rounded inline-block"></span> Revenue</span>
        <span class="text-[10px] font-bold text-gray-500 flex items-center gap-1"><span class="w-3 h-3 bg-red-400 rounded inline-block"></span> Expenses</span>
    </div>
</div>

<!-- Month Transactions -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Income for month -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-emerald-50/50 flex items-center justify-between">
            <h3 class="font-black text-gray-800 text-sm">
                <i class="fa-solid fa-arrow-trend-up text-emerald-600 mr-2"></i>
                Income — <?php echo $view_label; ?>
            </h3>
            <span class="text-xs font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full"><?php echo count($month_receipts); ?> receipts</span>
        </div>
        <div>
            <table class="w-full text-left text-sm text-gray-600">
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($month_receipts)): ?>
                        <tr><td class="p-8 text-center text-gray-300 text-xs font-bold uppercase tracking-widest">No income this month</td></tr>
                    <?php else: ?>
                        <?php foreach ($month_receipts as $r): ?>
                            <tr class="hover:bg-gray-50 group">
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-gray-800 text-xs"><?php echo esc($r['procedure_name']); ?></div>
                                    <div class="text-[11px] text-gray-400 mt-0.5 flex items-center gap-2">
                                        <span><?php echo esc($r['first_name'] . ' ' . $r['last_name']); ?></span>
                                        <span class="text-gray-300">•</span>
                                        <span><?php echo date('d M', strtotime($r['receipt_date'])); ?></span>
                                        <?php $sp = strtolower($r['status'] ?? ''); ?>
                                        <span class="text-[10px] font-black px-1.5 py-0.5 rounded <?php echo $sp==='paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'; ?>">
                                            <?php echo esc($r['status'] ?: 'Pending'); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <span class="font-black text-emerald-600 text-sm">Rs. <?php echo number_format($r['amount'], 0); ?></span>
                                    <div class="text-[10px] mt-1 flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="receipts_print.php?id=<?php echo $r['id']; ?>" target="_blank" class="text-emerald-600 hover:text-emerald-800" title="Print"><i class="fa-solid fa-print"></i></a>
                                        <a href="receipts_add.php?edit=<?php echo $r['id']; ?>" class="text-blue-600 hover:text-blue-800" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <button onclick="confirmDeleteReceipt(<?php echo $r['id']; ?>, '<?php echo esc($r['procedure_name']); ?>')" class="text-red-500 hover:text-red-700 cursor-pointer" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Expenses for month -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-red-50/50 flex items-center justify-between">
            <h3 class="font-black text-gray-800 text-sm">
                <i class="fa-solid fa-arrow-trend-down text-red-600 mr-2"></i>
                Expenses — <?php echo $view_label; ?>
            </h3>
            <span class="text-xs font-bold text-red-700 bg-red-100 px-2 py-0.5 rounded-full"><?php echo count($month_expense_rows); ?> entries</span>
        </div>
        <div>
            <table class="w-full text-left text-sm text-gray-600">
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($month_expense_rows)): ?>
                        <tr><td class="p-8 text-center text-gray-300 text-xs font-bold uppercase tracking-widest">No expenses this month</td></tr>
                    <?php else: ?>
                        <?php foreach ($month_expense_rows as $e): ?>
                            <tr class="hover:bg-gray-50 group">
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-gray-800 text-xs"><?php echo esc($e['title']); ?></div>
                                    <div class="text-[11px] text-gray-400 mt-0.5">
                                        <?php echo esc($e['category'] ?: 'Uncategorized'); ?>
                                        <span class="text-gray-300 mx-1">•</span>
                                        <?php echo date('d M', strtotime($e['expense_date'])); ?>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <span class="font-black text-red-600 text-sm">Rs. <?php echo number_format($e['amount'], 0); ?></span>
                                    <div class="text-[10px] mt-1 flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="expenses_add.php?edit=<?php echo $e['id']; ?>" class="text-blue-600 hover:text-blue-800" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <button onclick="confirmDeleteExpense(<?php echo $e['id']; ?>, '<?php echo esc($e['title']); ?>')" class="text-red-500 hover:text-red-700 cursor-pointer" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Delete Modals -->
<div id="deleteReceiptModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.5);" onclick="if(event.target===this)this.style.display='none'">
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:16px;padding:32px;max-width:400px;width:90%;box-shadow:0 25px 50px rgba(0,0,0,0.15);">
        <div style="text-align:center;">
            <div style="width:56px;height:56px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;font-size:24px;"></i>
            </div>
            <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:8px;">Delete Receipt?</h3>
            <p style="color:#64748b;font-size:14px;margin-bottom:24px;">Delete receipt for <strong id="deleteReceiptName"></strong>? This cannot be undone.</p>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="delete_receipt_id" id="deleteReceiptId">
                <button type="button" onclick="document.getElementById('deleteReceiptModal').style.display='none'" style="padding:10px 24px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;color:#64748b;font-weight:600;font-size:14px;cursor:pointer;margin-right:8px;">Cancel</button>
                <button type="submit" style="padding:10px 24px;border-radius:8px;border:none;background:#ef4444;color:#fff;font-weight:600;font-size:14px;cursor:pointer;">Delete</button>
            </form>
        </div>
    </div>
</div>

<div id="deleteExpenseModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.5);" onclick="if(event.target===this)this.style.display='none'">
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:16px;padding:32px;max-width:400px;width:90%;box-shadow:0 25px 50px rgba(0,0,0,0.15);">
        <div style="text-align:center;">
            <div style="width:56px;height:56px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;font-size:24px;"></i>
            </div>
            <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:8px;">Delete Expense?</h3>
            <p style="color:#64748b;font-size:14px;margin-bottom:24px;">Delete <strong id="deleteExpenseName"></strong>? This cannot be undone.</p>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="delete_expense_id" id="deleteExpenseId">
                <button type="button" onclick="document.getElementById('deleteExpenseModal').style.display='none'" style="padding:10px 24px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;color:#64748b;font-weight:600;font-size:14px;cursor:pointer;margin-right:8px;">Cancel</button>
                <button type="submit" style="padding:10px 24px;border-radius:8px;border:none;background:#ef4444;color:#fff;font-weight:600;font-size:14px;cursor:pointer;">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDeleteReceipt(id, name) {
    document.getElementById('deleteReceiptId').value = id;
    document.getElementById('deleteReceiptName').textContent = name;
    document.getElementById('deleteReceiptModal').style.display = 'block';
}
function confirmDeleteExpense(id, name) {
    document.getElementById('deleteExpenseId').value = id;
    document.getElementById('deleteExpenseName').textContent = name;
    document.getElementById('deleteExpenseModal').style.display = 'block';
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
