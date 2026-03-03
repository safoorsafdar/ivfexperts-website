<?php
$pageTitle = "Asset & Inventory Tracker";
require_once __DIR__ . '/includes/auth.php';

$success = '';
$error = '';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'saved')
        $success = "Asset/inventory item saved successfully.";
}

// Handle Delete (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM assets WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = "Item deleted.";
    }
    else {
        $error = "Error deleting item.";
    }
}

// Handle stock adjustment
if (isset($_POST['adjust_stock'])) {
    $asset_id = intval($_POST['asset_id']);
    $adjustment = intval($_POST['adjustment']);
    $stmt = $conn->prepare("UPDATE assets SET stock_quantity = GREATEST(0, stock_quantity + ?) WHERE id = ?");
    $stmt->bind_param("ii", $adjustment, $asset_id);
    if ($stmt->execute()) {
        $success = "Stock updated.";
    }
}

// Fetch all assets
$filter_type = $_GET['type'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM assets WHERE 1=1";
$params = [];
$types = "";

if ($filter_type === 'Fixed' || $filter_type === 'Disposable') {
    $sql .= " AND type = ?";
    $params[] = $filter_type;
    $types .= "s";
}

if ($filter_type === 'low_stock') {
    $sql .= " AND type = 'Disposable' AND stock_quantity <= minimum_threshold AND minimum_threshold > 0";
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR category LIKE ? OR barcode_string LIKE ? OR location LIKE ?)";
    $s = "%" . $search . "%";
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
    $types .= "ssss";
}

$sql .= " ORDER BY name ASC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$assets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Count low stock items
$low_stock_count = 0;
try {
    $res = $conn->query("SELECT COUNT(*) as cnt FROM assets WHERE type = 'Disposable' AND stock_quantity <= minimum_threshold AND minimum_threshold > 0");
    $low_stock_count = $res->fetch_assoc()['cnt'] ?? 0;
}
catch (Exception $e) {
}

// Quick stats
$total_fixed = 0;
$total_disposable = 0;
$total_value = 0;
try {
    $res = $conn->query("SELECT type, COUNT(*) as cnt, SUM(purchase_price) as val FROM assets GROUP BY type");
    while ($row = $res->fetch_assoc()) {
        if ($row['type'] === 'Fixed') {
            $total_fixed = $row['cnt'];
        }
        else {
            $total_disposable = $row['cnt'];
        }
        $total_value += floatval($row['val']);
    }
}
catch (Exception $e) {
}

include __DIR__ . '/includes/header.php';
?>

<!-- Low Stock Alert -->
<?php if ($low_stock_count > 0): ?>
<div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 flex items-center gap-3">
    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center shrink-0">
        <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
    </div>
    <div>
        <div class="font-bold text-red-800">Low Stock Warning</div>
        <div class="text-red-600 text-sm"><?php echo $low_stock_count; ?> item(s) have reached or fallen below their minimum threshold. <a href="?type=low_stock" class="underline font-bold">View Low Stock Items →</a></div>
    </div>
</div>
<?php
endif; ?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 border-l-4 border-indigo-600 pl-3">Asset & Inventory</h1>
        <p class="text-gray-500 text-sm mt-1">Manage fixed clinical assets and disposable stock levels.</p>
    </div>
    <a href="inventory_add.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg shadow-sm text-sm font-medium transition-colors flex items-center gap-2 shrink-0">
        <i class="fa-solid fa-plus"></i> Add New Item
    </a>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <a href="?type=Fixed" class="bg-white rounded-xl border <?php echo $filter_type === 'Fixed' ? 'border-indigo-400 ring-2 ring-indigo-200' : 'border-gray-100'; ?> p-4 hover:shadow-md transition-all">
        <div class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-1">Fixed Assets</div>
        <div class="text-2xl font-bold text-gray-900"><?php echo $total_fixed; ?></div>
    </a>
    <a href="?type=Disposable" class="bg-white rounded-xl border <?php echo $filter_type === 'Disposable' ? 'border-teal-400 ring-2 ring-teal-200' : 'border-gray-100'; ?> p-4 hover:shadow-md transition-all">
        <div class="text-xs font-bold text-teal-600 uppercase tracking-wider mb-1">Disposables</div>
        <div class="text-2xl font-bold text-gray-900"><?php echo $total_disposable; ?></div>
    </a>
    <a href="?type=low_stock" class="bg-white rounded-xl border <?php echo $filter_type === 'low_stock' ? 'border-red-400 ring-2 ring-red-200' : 'border-gray-100'; ?> p-4 hover:shadow-md transition-all">
        <div class="text-xs font-bold text-red-600 uppercase tracking-wider mb-1">Low Stock</div>
        <div class="text-2xl font-bold <?php echo $low_stock_count > 0 ? 'text-red-600' : 'text-gray-900'; ?>"><?php echo $low_stock_count; ?></div>
    </a>
    <div class="bg-white rounded-xl border border-gray-100 p-4">
        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Value</div>
        <div class="text-2xl font-bold text-gray-900">Rs. <?php echo number_format($total_value, 0); ?></div>
    </div>
</div>

<!-- Search Bar -->
<div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-6">
    <form method="GET" class="flex gap-2">
        <?php if ($filter_type): ?>
            <input type="hidden" name="type" value="<?php echo htmlspecialchars($filter_type); ?>">
        <?php
endif; ?>
        <div class="relative flex-1">
            <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name, category, barcode, or location..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>
        <button type="submit" class="bg-indigo-50 text-indigo-700 px-6 py-2 rounded-lg hover:bg-indigo-100 font-medium transition-colors border border-indigo-100 shrink-0">Search</button>
        <?php if (!empty($filter_type) || !empty($search)): ?>
            <a href="inventory.php" class="bg-gray-50 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-100 font-medium transition-colors border border-gray-200 shrink-0">Clear</a>
        <?php
endif; ?>
    </form>
</div>

<?php if ($success): ?>
    <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 border border-emerald-100 flex items-center gap-2">
        <i class="fa-solid fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
    </div>
<?php
endif; ?>
<?php if ($error): ?>
    <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php
endif; ?>

<!-- Assets Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-visible">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="p-4 font-medium border-b border-gray-100">Item Name</th>
                    <th class="p-4 font-medium border-b border-gray-100 w-24">Type</th>
                    <th class="p-4 font-medium border-b border-gray-100 w-28">Category</th>
                    <th class="p-4 font-medium border-b border-gray-100 w-28">Stock</th>
                    <th class="p-4 font-medium border-b border-gray-100 w-28">Price</th>
                    <th class="p-4 font-medium border-b border-gray-100 w-28 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-sm">
                <?php if (empty($assets)): ?>
                <tr>
                    <td colspan="6" class="p-8 text-center text-gray-400 font-medium">
                        <i class="fa-solid fa-boxes-stacked text-3xl mb-3 block text-gray-300"></i>
                        No inventory items found. Click "Add New Item" to start tracking.
                    </td>
                </tr>
                <?php
else:
    foreach ($assets as $a):
        $is_low = ($a['type'] === 'Disposable' && $a['minimum_threshold'] > 0 && $a['stock_quantity'] <= $a['minimum_threshold']);
?>
                <tr class="hover:bg-gray-50/50 transition-colors group <?php echo $is_low ? 'bg-red-50/30' : ''; ?>">
                    <td class="p-4">
                        <div class="font-bold text-gray-900"><?php echo htmlspecialchars($a['name']); ?></div>
                        <?php if (!empty($a['location'])): ?>
                            <div class="text-[10px] text-gray-500 mt-0.5"><i class="fa-solid fa-location-dot mr-1"></i><?php echo htmlspecialchars($a['location']); ?></div>
                        <?php
        endif; ?>
                        <?php if (!empty($a['barcode_string'])): ?>
                            <div class="text-[10px] text-indigo-600 font-mono mt-0.5"><?php echo htmlspecialchars($a['barcode_string']); ?></div>
                        <?php
        endif; ?>
                    </td>
                    <td class="p-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold border <?php echo $a['type'] === 'Fixed' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-teal-50 text-teal-700 border-teal-200'; ?>">
                            <?php echo $a['type']; ?>
                        </span>
                    </td>
                    <td class="p-4 text-gray-600 text-xs"><?php echo htmlspecialchars($a['category'] ?: '-'); ?></td>
                    <td class="p-4">
                        <?php if ($a['type'] === 'Disposable'): ?>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-lg <?php echo $is_low ? 'text-red-600' : 'text-gray-900'; ?>"><?php echo $a['stock_quantity']; ?></span>
                                <?php if ($is_low): ?>
                                    <i class="fa-solid fa-triangle-exclamation text-red-500 text-xs"></i>
                                <?php
            endif; ?>
                            </div>
                            <div class="text-[10px] text-gray-400">Min: <?php echo $a['minimum_threshold']; ?></div>
                        <?php
        else: ?>
                            <span class="text-gray-300">N/A</span>
                        <?php
        endif; ?>
                    </td>
                    <td class="p-4 text-gray-600 font-mono text-xs">
                        <?php echo $a['purchase_price'] > 0 ? 'Rs. ' . number_format($a['purchase_price'], 0) : '-'; ?>
                        <?php if (!empty($a['purchase_date'])): ?>
                            <div class="text-[10px] text-gray-400"><?php echo date('d M Y', strtotime($a['purchase_date'])); ?></div>
                        <?php
        endif; ?>
                    </td>
                    <td class="p-4 text-right">
                        <div class="relative inline-block text-left" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false" class="text-gray-400 hover:text-gray-600 p-2 rounded hover:bg-gray-100 transition-colors">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <div x-show="open" x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 class="absolute right-0 z-50 mt-2 w-52 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100">
                                
                                <?php if ($a['type'] === 'Disposable'): ?>
                                <div class="py-1">
                                    <div class="px-4 py-2 text-[10px] font-bold uppercase text-gray-400 tracking-wider">Stock Adjustment</div>
                                    <form method="POST" class="px-4 py-2 flex gap-2">
                                        <input type="hidden" name="asset_id" value="<?php echo $a['id']; ?>">
                                        <input type="number" name="adjustment" placeholder="+5 or -3" class="w-24 px-2 py-1 border border-gray-200 rounded text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500" required>
                                        <button type="submit" name="adjust_stock" class="bg-indigo-600 text-white px-3 py-1 rounded text-xs font-bold hover:bg-indigo-700">Go</button>
                                    </form>
                                </div>
                                <?php
        endif; ?>

                                <div class="py-1">
                                    <a href="inventory_add.php?edit=<?php echo $a['id']; ?>" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <i class="fa-solid fa-edit mr-3 text-gray-400"></i> Edit Item
                                    </a>
                                    <a href="inventory_label.php?id=<?php echo $a['id']; ?>" target="_blank" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <i class="fa-solid fa-qrcode mr-3 text-gray-400"></i> Print Label
                                    </a>
                                    <?php if ($a['purchase_price'] > 0): ?>
                                    <a href="expenses_add.php?title=<?php echo urlencode('Asset: ' . $a['name']); ?>&amount=<?php echo $a['purchase_price']; ?>" class="group flex items-center px-4 py-2 text-sm text-emerald-700 hover:bg-emerald-50">
                                        <i class="fa-solid fa-receipt mr-3 text-emerald-400"></i> Log as Expense
                                    </a>
                                    <?php
        endif; ?>
                                </div>

                                <div class="py-1">
                                    <form method="POST" onsubmit="return confirm('Delete this inventory item permanently?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $a['id']; ?>">
                                        <button type="submit" class="w-full text-left group flex items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                            <i class="fa-solid fa-trash mr-3 text-red-400"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
