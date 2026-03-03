<?php
$pageTitle = "System Settings & Hospitals";
require_once __DIR__ . '/includes/auth.php';

// Handle Setting Updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_settings'])) {
    $keys = ['print_margin_top', 'print_margin_bottom', 'print_margin_left', 'print_margin_right'];
    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            $val = $_POST[$key];
            $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            if ($stmt) {
                $stmt->bind_param("ss", $val, $key);
                $stmt->execute();
            }
        }
    }
    $successMsg = "Print margins updated successfully!";
}

// Handle Hospital Add
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_hospital'])) {
    $name = trim($_POST['hosp_name']);
    $address = trim($_POST['hosp_address']);
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO hospitals (name, address_footer) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("ss", $name, $address);
            $stmt->execute();
            $successMsg = "Hospital added successfully!";
        }
    }
}

// Fetch Settings
$settings = [];
try {
    $res = $conn->query("SELECT setting_key, setting_value FROM settings");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
}
catch (Exception $e) {
}

// Fetch Hospitals
$hospitals = [];
try {
    $res = $conn->query("SELECT * FROM hospitals ORDER BY name ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $hospitals[] = $row;
        }
    }
}
catch (Exception $e) {
}

include __DIR__ . '/includes/header.php';
?>

<?php if (isset($successMsg)): ?>
<div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl mb-6 flex items-center gap-3 border border-emerald-100">
    <i class="fa-solid fa-check-circle"></i> <?php echo esc($successMsg); ?>
</div>
<?php
endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    
    <!-- Global Settings -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h3 class="font-bold text-gray-800">Letterhead Print Margins</h3>
            <p class="text-xs text-gray-500 mt-1">These settings affect prescriptions and receipts printed on your letterhead.</p>
        </div>
        <div class="p-6">
            <form method="POST">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Top Margin</label>
                        <input type="text" name="print_margin_top" value="<?php echo esc($settings['print_margin_top'] ?? '40mm'); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bottom Margin</label>
                        <input type="text" name="print_margin_bottom" value="<?php echo esc($settings['print_margin_bottom'] ?? '20mm'); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Left Margin</label>
                        <input type="text" name="print_margin_left" value="<?php echo esc($settings['print_margin_left'] ?? '20mm'); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Right Margin</label>
                        <input type="text" name="print_margin_right" value="<?php echo esc($settings['print_margin_right'] ?? '20mm'); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                    </div>
                </div>
                <button type="submit" name="update_settings" class="bg-teal-600 hover:bg-teal-700 text-white font-medium py-2 px-6 rounded-lg transition-colors">Save Settings</button>
            </form>
        </div>
    </div>

    <!-- Hospitals Management -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h3 class="font-bold text-gray-800">Practice Locations (Hospitals)</h3>
        </div>
        
        <!-- Add New -->
        <div class="p-4 border-b border-gray-100 bg-gray-50">
            <form method="POST" class="flex gap-2">
                <input type="text" name="hosp_name" placeholder="Hospital Name..." class="flex-1 px-3 py-2 text-sm rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500" required>
                <input type="text" name="hosp_address" placeholder="Address footer..." class="flex-1 px-3 py-2 text-sm rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500">
                <button type="submit" name="add_hospital" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium">Add</button>
            </form>
        </div>

        <div class="p-0">
            <table class="w-full text-left text-sm text-gray-600">
                <tbody>
                    <?php if (empty($hospitals)): ?>
                        <tr><td class="p-4 text-center text-gray-400">No hospitals found.</td></tr>
                    <?php
else:
    foreach ($hospitals as $h): ?>
                        <tr class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-800">
                                <i class="fa-regular fa-hospital text-teal-600 mr-2"></i>
                                <?php echo esc($h['name']); ?>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                <?php echo esc($h['address_footer'] ?? 'No address'); ?>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
