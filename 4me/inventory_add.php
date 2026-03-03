<?php
$pageTitle = "Add / Edit Inventory Item";
require_once __DIR__ . '/includes/auth.php';

$error = '';
$editing = false;
$item = ['id' => 0, 'name' => '', 'type' => 'Fixed', 'category' => '', 'barcode_string' => '', 'purchase_date' => date('Y-m-d'), 'purchase_price' => '', 'stock_quantity' => 0, 'minimum_threshold' => 0, 'location' => '', 'notes' => ''];

// Load existing item for editing
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM assets WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res) {
        $item = $res;
        $editing = true;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_item'])) {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $type = $_POST['type'] ?? 'Fixed';
    $category = trim($_POST['category'] ?? '');
    $barcode = trim($_POST['barcode_string'] ?? '');
    $purchase_date = $_POST['purchase_date'] ?: null;
    $purchase_price = floatval($_POST['purchase_price'] ?? 0);
    $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
    $min_threshold = intval($_POST['minimum_threshold'] ?? 0);
    $location = trim($_POST['location'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    // Auto-generate barcode if empty
    if (empty($barcode)) {
        $barcode = strtoupper(substr($type, 0, 3)) . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    }

    if (empty($name)) {
        $error = "Item name is required.";
    }
    else {
        if ($id > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE assets SET name=?, type=?, category=?, barcode_string=?, purchase_date=?, purchase_price=?, stock_quantity=?, minimum_threshold=?, location=?, notes=? WHERE id=?");
            $stmt->bind_param("sssssdiissi", $name, $type, $category, $barcode, $purchase_date, $purchase_price, $stock_quantity, $min_threshold, $location, $notes, $id);
        }
        else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO assets (name, type, category, barcode_string, purchase_date, purchase_price, stock_quantity, minimum_threshold, location, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssdiiss", $name, $type, $category, $barcode, $purchase_date, $purchase_price, $stock_quantity, $min_threshold, $location, $notes);
        }

        if ($stmt->execute()) {
            // Auto-log as expense in financials for new assets with purchase price
            if ($id === 0 && $purchase_price > 0) {
                try {
                    $exp_title = "Asset Purchase: " . $name;
                    $exp_cat = "Asset / Inventory";
                    $exp_date = $purchase_date ?: date('Y-m-d');
                    $exp_notes = "Auto-logged from Inventory. Barcode: " . $barcode;
                    $est = $conn->prepare("INSERT INTO expenses (title, amount, expense_date, category, notes) VALUES (?, ?, ?, ?, ?)");
                    $est->bind_param("sdsss", $exp_title, $purchase_price, $exp_date, $exp_cat, $exp_notes);
                    $est->execute();
                }
                catch (Exception $e) {
                // Non-critical, suppress
                }
            }

            header("Location: inventory.php?msg=saved");
            exit;
        }
        else {
            $error = "Database error: " . $stmt->error;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="inventory.php" class="text-sm text-gray-500 hover:text-indigo-600 font-medium flex items-center gap-1">
            <i class="fa-solid fa-arrow-left"></i> Back to Inventory
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h3 class="font-bold text-gray-800"><?php echo $editing ? 'Edit Inventory Item' : 'Add New Inventory Item'; ?></h3>
        </div>

        <div class="p-6 md:p-8">
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php
endif; ?>

            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">

                <div class="space-y-6">
                    <!-- Name & Type -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Item Name *</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($item['name']); ?>" required class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g. Nikon Eclipse Ti2 Microscope">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Type *</label>
                            <select name="type" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                <option value="Fixed" <?php echo $item['type'] === 'Fixed' ? 'selected' : ''; ?>>Fixed Asset</option>
                                <option value="Disposable" <?php echo $item['type'] === 'Disposable' ? 'selected' : ''; ?>>Disposable</option>
                            </select>
                        </div>
                    </div>

                    <!-- Category & Barcode -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="category" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                <option value="" <?php echo empty($item['category']) ? 'selected' : ''; ?>>-- Uncategorized --</option>
                                <option value="Lab Equipment" <?php echo $item['category'] === 'Lab Equipment' ? 'selected' : ''; ?>>Lab Equipment</option>
                                <option value="IVF Consumables" <?php echo $item['category'] === 'IVF Consumables' ? 'selected' : ''; ?>>IVF Consumables</option>
                                <option value="Media & Reagents" <?php echo $item['category'] === 'Media & Reagents' ? 'selected' : ''; ?>>Media & Reagents</option>
                                <option value="Surgical Instruments" <?php echo $item['category'] === 'Surgical Instruments' ? 'selected' : ''; ?>>Surgical Instruments</option>
                                <option value="Ultrasound" <?php echo $item['category'] === 'Ultrasound' ? 'selected' : ''; ?>>Ultrasound</option>
                                <option value="Office Equipment" <?php echo $item['category'] === 'Office Equipment' ? 'selected' : ''; ?>>Office Equipment</option>
                                <option value="Furniture" <?php echo $item['category'] === 'Furniture' ? 'selected' : ''; ?>>Furniture</option>
                                <option value="Other" <?php echo $item['category'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Barcode / Serial No.</label>
                            <input type="text" name="barcode_string" value="<?php echo htmlspecialchars($item['barcode_string']); ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono" placeholder="Auto-generated if blank">
                            <p class="text-[10px] text-gray-400 mt-1">Leave empty to auto-generate a unique barcode ID.</p>
                        </div>
                    </div>

                    <!-- Purchase Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Date</label>
                            <input type="date" name="purchase_date" value="<?php echo htmlspecialchars($item['purchase_date'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Price (Rs.)</label>
                            <input type="number" step="0.01" name="purchase_price" value="<?php echo $item['purchase_price']; ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono" placeholder="0.00">
                        </div>
                    </div>

                    <!-- Stock (only for Disposable) -->
                    <div class="bg-teal-50 border border-teal-100 rounded-xl p-5">
                        <h4 class="font-bold text-teal-800 text-sm mb-3"><i class="fa-solid fa-boxes-stacked mr-1"></i> Stock Management (Disposables Only)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-teal-700 mb-1">Current Stock Quantity</label>
                                <input type="number" name="stock_quantity" value="<?php echo $item['stock_quantity']; ?>" class="w-full px-4 py-3 border border-teal-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white font-bold text-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-teal-700 mb-1">Minimum Threshold (Alert)</label>
                                <input type="number" name="minimum_threshold" value="<?php echo $item['minimum_threshold']; ?>" class="w-full px-4 py-3 border border-teal-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
                                <p class="text-[10px] text-teal-600 mt-1">Alert triggers when stock ≤ this number.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Location & Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Storage Location</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($item['location']); ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g. IVF Lab Room 3, Shelf B2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"><?php echo htmlspecialchars($item['notes']); ?></textarea>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end gap-3">
                    <a href="inventory.php" class="px-6 py-3 font-medium text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors border border-gray-200">Cancel</a>
                    <button type="submit" name="save_item" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg transition-colors shadow-lg shadow-indigo-200 flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> <?php echo $editing ? 'Update Item' : 'Save Item'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
