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

// Quick metrics
$revenue = 0;
$expenses = 0;

try {
    $resR = $conn->query("SELECT SUM(amount) as r FROM receipts");
    if ($resR)
        $revenue = floatval($resR->fetch_assoc()['r'] ?? 0);

    $resE = $conn->query("SELECT SUM(amount) as e FROM expenses");
    if ($resE)
        $expenses = floatval($resE->fetch_assoc()['e'] ?? 0);
}
catch (Throwable $e) {
}

$net_profit = $revenue - $expenses;
$profit_margin = $revenue > 0 ? round(($net_profit / $revenue) * 100, 1) : 0;

// Fetch Recent Income (Receipts)
$recent_receipts = [];
try {
    $stmt = $conn->query("SELECT r.id, r.receipt_date, r.procedure_name, r.amount, p.first_name, p.last_name FROM receipts r JOIN patients p ON r.patient_id = p.id ORDER BY r.receipt_date DESC LIMIT 10");
    if ($stmt) {
        while ($row = $stmt->fetch_assoc())
            $recent_receipts[] = $row;
    }
}
catch (Throwable $e) {
}

// Fetch Recent Expenses
$recent_expenses = [];
try {
    $stmt = $conn->query("SELECT * FROM expenses ORDER BY expense_date DESC LIMIT 10");
    if ($stmt) {
        while ($row = $stmt->fetch_assoc())
            $recent_expenses[] = $row;
    }
}
catch (Throwable $e) {
}

include __DIR__ . '/includes/header.php';
?>

<?php if ($msg === 'receipt_deleted'): ?>
    <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 border border-red-100 flex items-center gap-3 shadow-sm">
        <i class="fa-solid fa-trash text-xl"></i>
        <span class="font-bold">Receipt deleted successfully.</span>
    </div>
<?php
elseif ($msg === 'expense_deleted'): ?>
    <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 border border-red-100 flex items-center gap-3 shadow-sm">
        <i class="fa-solid fa-trash text-xl"></i>
        <span class="font-bold">Expense deleted successfully.</span>
    </div>
<?php
endif; ?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-bold text-gray-800">Financial Overview</h2>
    <div class="flex gap-2">
        <a href="expenses_add.php" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-minus cursor-pointer"></i> Log Expense
        </a>
        <a href="receipts_add.php" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-plus cursor-pointer"></i> Generate Receipt
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 mt-2">
    <!-- Revenue -->
    <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 p-6 relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full flex items-center justify-center opacity-50">
            <i class="fa-solid fa-money-bill-wave text-3xl text-emerald-300 ml-4 mt-4"></i>
        </div>
        <p class="text-sm font-medium text-gray-500 mb-1">Total Revenue</p>
        <h3 class="text-3xl font-bold text-gray-800">Rs. <?php echo number_format($revenue, 2); ?></h3>
    </div>
    
    <!-- Expenses -->
    <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-6 relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-red-50 rounded-full flex items-center justify-center opacity-50">
            <i class="fa-solid fa-chart-line text-3xl text-red-300 ml-4 mt-4"></i>
        </div>
        <p class="text-sm font-medium text-gray-500 mb-1">Total Expenses (IVF, IUI, Meds)</p>
        <h3 class="text-3xl font-bold text-gray-800">Rs. <?php echo number_format($expenses, 2); ?></h3>
    </div>

    <!-- Net Profit -->
    <div class="bg-white rounded-2xl shadow-sm border <?php echo $net_profit >= 0 ? 'border-teal-200 bg-teal-50' : 'border-amber-200 bg-amber-50'; ?> p-6">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium <?php echo $net_profit >= 0 ? 'text-teal-700' : 'text-amber-700'; ?> mb-1">Net Profit</p>
                <h3 class="text-3xl font-bold <?php echo $net_profit >= 0 ? 'text-teal-900' : 'text-amber-900'; ?>">Rs. <?php echo number_format($net_profit, 2); ?></h3>
            </div>
            <div class="<?php echo $net_profit >= 0 ? 'bg-teal-100 text-teal-800' : 'bg-amber-100 text-amber-800'; ?> text-xs font-bold px-2 py-1 rounded">
                <?php echo $profit_margin; ?>% margin
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    
    <!-- Income Stream -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-emerald-50/50">
            <h3 class="font-bold text-gray-800"><i class="fa-solid fa-arrow-trend-up text-emerald-600 mr-2"></i> Recent Income</h3>
        </div>
        <div class="p-0">
            <table class="w-full text-left text-sm text-gray-600">
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($recent_receipts)): ?>
                        <tr><td class="p-6 text-center text-gray-400">No income recorded.</td></tr>
                    <?php
else:
    foreach ($recent_receipts as $r): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800"><?php echo esc($r['procedure_name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo esc($r['first_name'] . ' ' . $r['last_name']); ?> • <?php echo date('d M', strtotime($r['receipt_date'])); ?></div>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <span class="font-bold text-emerald-600">+ Rs. <?php echo number_format($r['amount'], 2); ?></span>
                                <div class="text-xs mt-1 flex items-center justify-end gap-2">
                                    <a href="receipts_print.php?id=<?php echo $r['id']; ?>" target="_blank" class="text-emerald-600 hover:text-emerald-800" title="Print"><i class="fa-solid fa-print"></i></a>
                                    <a href="receipts_add.php?edit=<?php echo $r['id']; ?>" class="text-blue-600 hover:text-blue-800" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <button onclick="confirmDeleteReceipt(<?php echo $r['id']; ?>, '<?php echo esc($r['procedure_name']); ?>')" class="text-red-500 hover:text-red-700 cursor-pointer" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php
    endforeach;
endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Expense Stream -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-red-50/50">
            <h3 class="font-bold text-gray-800"><i class="fa-solid fa-arrow-trend-down text-red-600 mr-2"></i> Recent Expenses</h3>
        </div>
        <div class="p-0">
            <table class="w-full text-left text-sm text-gray-600">
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($recent_expenses)): ?>
                        <tr><td class="p-6 text-center text-gray-400">No expenses recorded.</td></tr>
                    <?php
else:
    foreach ($recent_expenses as $e): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800"><?php echo esc($e['title']); ?></div>
                                <div class="text-xs text-gray-500">Cat: <?php echo esc($e['category'] ?: 'Uncategorized'); ?> • <?php echo date('d M', strtotime($e['expense_date'])); ?></div>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <span class="font-bold text-red-600">- Rs. <?php echo number_format($e['amount'], 2); ?></span>
                                <div class="text-xs mt-1 flex items-center justify-end gap-2">
                                    <a href="expenses_add.php?edit=<?php echo $e['id']; ?>" class="text-blue-600 hover:text-blue-800" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <button onclick="confirmDeleteExpense(<?php echo $e['id']; ?>, '<?php echo esc($e['title']); ?>')" class="text-red-500 hover:text-red-700 cursor-pointer" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php
    endforeach;
endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Delete Receipt Modal -->
<div id="deleteReceiptModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.5);" onclick="if(event.target===this)this.style.display='none'">
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:16px;padding:32px;max-width:400px;width:90%;box-shadow:0 25px 50px rgba(0,0,0,0.15);">
        <div style="text-align:center;">
            <div style="width:56px;height:56px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;font-size:24px;"></i>
            </div>
            <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:8px;">Delete Receipt?</h3>
            <p style="color:#64748b;font-size:14px;margin-bottom:24px;">Are you sure you want to delete receipt for <strong id="deleteReceiptName"></strong>? This cannot be undone.</p>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="delete_receipt_id" id="deleteReceiptId">
                <button type="button" onclick="document.getElementById('deleteReceiptModal').style.display='none'" style="padding:10px 24px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;color:#64748b;font-weight:600;font-size:14px;cursor:pointer;margin-right:8px;">Cancel</button>
                <button type="submit" style="padding:10px 24px;border-radius:8px;border:none;background:#ef4444;color:#fff;font-weight:600;font-size:14px;cursor:pointer;">Delete</button>
            </form>
        </div>
    </div>
</div>

<!-- Delete Expense Modal -->
<div id="deleteExpenseModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.5);" onclick="if(event.target===this)this.style.display='none'">
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:16px;padding:32px;max-width:400px;width:90%;box-shadow:0 25px 50px rgba(0,0,0,0.15);">
        <div style="text-align:center;">
            <div style="width:56px;height:56px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;font-size:24px;"></i>
            </div>
            <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:8px;">Delete Expense?</h3>
            <p style="color:#64748b;font-size:14px;margin-bottom:24px;">Are you sure you want to delete <strong id="deleteExpenseName"></strong>? This cannot be undone.</p>
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
