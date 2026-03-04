<?php
$pageTitle = isset($_GET['edit']) ? "Edit Receipt" : "Generate Receipt";
require_once __DIR__ . '/includes/auth.php';

$error = '';
$edit_id = intval($_GET['edit'] ?? 0);
$edit_data = null;

if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM receipts WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
    if (!$edit_data) {
        header("Location: financials.php?error=NotFound");
        exit;
    }
}

$pre_patient_id = $_GET['patient_id'] ?? ($edit_data['patient_id'] ?? '');
$pre_procedure_id = $_GET['procedure_id'] ?? ($edit_data['advised_procedure_id'] ?? null);
$qrcode_hash = $edit_data['qrcode_hash'] ?? bin2hex(random_bytes(16));

// Fetch required data
$patients = [];
$res = $conn->query("SELECT id, mr_number, first_name, last_name, phone FROM patients ORDER BY id DESC");
if ($res) {
    while ($row = $res->fetch_assoc())
        $patients[] = $row;
}

$hospitals = [];
$res = $conn->query("SELECT id, name FROM hospitals ORDER BY name ASC");
if ($res) {
    while ($row = $res->fetch_assoc())
        $hospitals[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_receipt'])) {
    $patient_id = intval($_POST['patient_id'] ?? 0);
    $hospital_id = intval($_POST['hospital_id'] ?? 0);
    $proc_name = trim($_POST['procedure_name'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $date = $_POST['receipt_date'] ?? date('Y-m-d');
    $notes = trim($_POST['notes'] ?? '');

    $status = $_POST['status'] ?? 'Paid';
    $payment_method = trim($_POST['payment_method'] ?? 'Cash');
    $advised_procedure_id = !empty($_POST['advised_procedure_id']) ? intval($_POST['advised_procedure_id']) : null;
    $current_edit_id = intval($_POST['edit_id'] ?? 0);

    if (empty($patient_id) || empty($hospital_id) || empty($proc_name) || $amount < 0) {
        $error = "Patient, Hospital, Procedure Name, and Amount are required.";
    }
    else {
        if ($current_edit_id > 0) {
            $stmt = $conn->prepare("UPDATE receipts SET patient_id=?, hospital_id=?, procedure_name=?, amount=?, status=?, payment_method=?, advised_procedure_id=?, receipt_date=?, notes=? WHERE id=?");
            $stmt->bind_param("iisdssissi", $patient_id, $hospital_id, $proc_name, $amount, $status, $payment_method, $advised_procedure_id, $date, $notes, $current_edit_id);
        }
        else {
            $stmt = $conn->prepare("INSERT INTO receipts (patient_id, hospital_id, procedure_name, amount, status, payment_method, advised_procedure_id, receipt_date, qrcode_hash, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisdssisss", $patient_id, $hospital_id, $proc_name, $amount, $status, $payment_method, $advised_procedure_id, $date, $qrcode_hash, $notes);
        }

        if ($stmt) {
            if ($stmt->execute()) {
                // If it's linked to an advised procedure, automatically set that procedure to 'In Progress' if payment is made
                if ($advised_procedure_id > 0 && ($status === 'Paid' || $status === 'Pending')) {
                    $conn->query("UPDATE advised_procedures SET status = 'In Progress' WHERE id = " . $advised_procedure_id . " AND status = 'Advised'");
                }

                header("Location: financials.php?msg=receipt_saved");
                exit;
            }
            else {
                $error = "DB Error: " . $stmt->error;
            }
        }
    }
}

// Fetch Procedure Name if pre_procedure_id is set
$pre_proc_name = '';
if ($pre_procedure_id) {
    try {
        $pst = $conn->prepare("SELECT procedure_name FROM advised_procedures WHERE id = ?");
        $pst->bind_param("i", $pre_procedure_id);
        $pst->execute();
        $pres = $pst->get_result()->fetch_assoc();
        if ($pres) {
            $pre_proc_name = $pres['procedure_name'];
        }
    }
    catch (Exception $e) {
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="financials.php" class="text-sm text-gray-500 hover:text-emerald-600 font-medium flex items-center gap-1">
            <i class="fa-solid fa-arrow-left"></i> Back to Financials
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 border-b border-gray-100 text-slate-800 flex justify-between items-center">
            <h3 class="font-bold"><i class="fa-solid fa-file-invoice-dollar text-emerald-300 mr-2"></i> <?php echo $edit_id ? 'Edit' : 'Generate'; ?> Patient Receipt</h3>
        </div>
        
        <div class="p-6 md:p-8">
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 flex gap-2"><i class="fa-solid fa-circle-exclamation mt-1"></i> <?php echo esc($error); ?></div>
            <?php
endif; ?>

            <form method="POST">
                <?php if ($edit_id): ?>
                    <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
                <?php
endif; ?>
                <div class="space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Patient *</label>
                            <select name="patient_id" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                <option value="">-- Choose Patient --</option>
                                <?php foreach ($patients as $p): ?>
                                    <option value="<?php echo $p['id']; ?>" <?php echo($pre_patient_id == $p['id']) ? 'selected' : ''; ?>>
                                        <?php echo esc($p['mr_number'] . ' | ' . $p['first_name'] . ' ' . $p['last_name']); ?>
                                    </option>
                                <?php
endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hospital (Print Location) *</label>
                            <select name="hospital_id" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                <?php foreach ($hospitals as $h): ?>
                                    <option value="<?php echo $h['id']; ?>" <?php echo($h['id'] == ($edit_data['hospital_id'] ?? '')) ? 'selected' : ''; ?>><?php echo esc($h['name']); ?></option>
                                <?php
endforeach; ?>
                            </select>
                            <p class="text-[10px] text-gray-400 mt-1">Receipt will be printed respecting the hospital's letterhead margin setup.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Consultation / Procedure Name *</label>
                        <input type="text" name="procedure_name" value="<?php echo esc($edit_data['procedure_name'] ?? $pre_proc_name); ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-lg text-emerald-900 font-medium" placeholder="e.g. Initial Consultation, ICSI Cycle, Micro-TESE..." required>
                        <?php if ($pre_procedure_id): ?>
                            <input type="hidden" name="advised_procedure_id" value="<?php echo esc($pre_procedure_id); ?>">
                            <p class="text-xs text-indigo-600 mt-1 font-medium"><i class="fa-solid fa-link"></i> Linked to Advised Treatment Plan</p>
                        <?php
endif; ?>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Amount Billed (Rs) *</label>
                            <input type="number" step="0.01" name="amount" value="<?php echo esc($edit_data['amount'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono text-xl" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Payment Status *</label>
                            <select name="status" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                <?php $s = $edit_data['status'] ?? 'Paid'; ?>
                                <option value="Paid"     <?php echo $s == 'Paid'     ? 'selected' : ''; ?>>Paid</option>
                                <option value="Pending"  <?php echo $s == 'Pending'  ? 'selected' : ''; ?>>Pending</option>
                                <option value="Unpaid"   <?php echo $s == 'Unpaid'   ? 'selected' : ''; ?>>Unpaid</option>
                                <option value="Past Due" <?php echo $s == 'Past Due' ? 'selected' : ''; ?>>Past Due</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Payment Method *</label>
                            <select name="payment_method" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                <?php $pm = $edit_data['payment_method'] ?? 'Cash'; ?>
                                <option value="Cash"          <?php echo $pm == 'Cash'          ? 'selected' : ''; ?>>Cash</option>
                                <option value="Card"          <?php echo $pm == 'Card'          ? 'selected' : ''; ?>>Credit / Debit Card</option>
                                <option value="Online"        <?php echo $pm == 'Online'        ? 'selected' : ''; ?>>Online Transfer</option>
                                <option value="Cheque"        <?php echo $pm == 'Cheque'        ? 'selected' : ''; ?>>Cheque</option>
                                <option value="Insurance"     <?php echo $pm == 'Insurance'     ? 'selected' : ''; ?>>Insurance</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                            <input type="date" name="receipt_date" value="<?php echo esc($edit_data['receipt_date'] ?? date('Y-m-d')); ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Notes / Method</label>
                        <textarea name="notes" rows="2" placeholder="e.g. Paid in cash, pending clearance..." class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500"><?php echo esc($edit_data['notes'] ?? ''); ?></textarea>
                    </div>

                </div>

                <div class="flex justify-end mt-8">
                    <button type="submit" name="save_receipt" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-8 rounded-lg shadow-md hover:shadow-lg transition-all focus:outline-none w-full flex items-center justify-center gap-2">
                        <i class="fa-solid fa-file-invoice text-lg"></i> <?php echo $edit_id ? 'Update Receipt' : 'Issue Receipt & Get Print Code'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
