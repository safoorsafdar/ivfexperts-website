<?php
$pageTitle = "Edit Hospital";
require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';

$hospital = [
    'name' => '',
    'address' => '',
    'phone' => '',
    'margin_top' => '20mm',
    'margin_bottom' => '20mm',
    'margin_left' => '20mm',
    'margin_right' => '20mm',
    'logo_path' => '',
    'digital_signature_path' => '',
    'letterhead_image_path' => ''
];

if ($id > 0) {
    try {
        $stmt = $conn->prepare("SELECT * FROM hospitals WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $hospital = $res->fetch_assoc();
        }
        else {
            $error = "Hospital not found.";
        }
    }
    catch (Exception $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Ensure Upload directories exist
$upload_dir = dirname(__DIR__) . '/assets/uploads/';
if (!is_dir($upload_dir))
    mkdir($upload_dir, 0755, true);
if (!is_dir($upload_dir . 'logos/'))
    mkdir($upload_dir . 'logos/', 0755, true);
if (!is_dir($upload_dir . 'signatures/'))
    mkdir($upload_dir . 'signatures/', 0755, true);
if (!is_dir($upload_dir . 'letterheads/'))
    mkdir($upload_dir . 'letterheads/', 0755, true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_hospital'])) {
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $mt = trim($_POST['margin_top'] ?? '20mm');
    $mb = trim($_POST['margin_bottom'] ?? '20mm');
    $ml = trim($_POST['margin_left'] ?? '20mm');
    $mr = trim($_POST['margin_right'] ?? '20mm');

    // Keep old paths if no new file is uploaded
    $logo_path = $hospital['logo_path'];
    $sig_path = $hospital['digital_signature_path'];
    $letterhead_path = $hospital['letterhead_image_path'];

    // Handle Logo Upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . 'logos/' . $filename)) {
            $logo_path = 'assets/uploads/logos/' . $filename;
        }
    }

    // Handle Signature Upload
    if (isset($_FILES['signature']) && $_FILES['signature']['error'] == 0) {
        $ext = pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION);
        $filename = 'sig_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['signature']['tmp_name'], $upload_dir . 'signatures/' . $filename)) {
            $sig_path = 'assets/uploads/signatures/' . $filename;
        }
    }

    // Handle Letterhead Upload
    if (isset($_FILES['letterhead']) && $_FILES['letterhead']['error'] == 0) {
        $ext = pathinfo($_FILES['letterhead']['name'], PATHINFO_EXTENSION);
        $filename = 'lh_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['letterhead']['tmp_name'], $upload_dir . 'letterheads/' . $filename)) {
            $letterhead_path = 'assets/uploads/letterheads/' . $filename;
        }
    }

    if (empty($name)) {
        $error = "Hospital name is required.";
    }
    else {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE hospitals SET name=?, address=?, phone=?, margin_top=?, margin_bottom=?, margin_left=?, margin_right=?, logo_path=?, digital_signature_path=?, letterhead_image_path=? WHERE id=?");
            $stmt->bind_param("ssssssssssi", $name, $address, $phone, $mt, $mb, $ml, $mr, $logo_path, $sig_path, $letterhead_path, $id);
            if ($stmt->execute()) {
                header("Location: hospitals.php?msg=saved");
                exit;
            }
            else {
                $error = "Update failed: " . $stmt->error;
            }
        }
        else {
            $stmt = $conn->prepare("INSERT INTO hospitals (name, address, phone, margin_top, margin_bottom, margin_left, margin_right, logo_path, digital_signature_path, letterhead_image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", $name, $address, $phone, $mt, $mb, $ml, $mr, $logo_path, $sig_path, $letterhead_path);
            if ($stmt->execute()) {
                header("Location: hospitals.php?msg=saved");
                exit;
            }
            else {
                $error = "Insert failed: " . $stmt->error;
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    
    <div class="mb-6 flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center gap-3">
            <a href="hospitals.php" class="text-gray-400 hover:text-indigo-600 transition-colors w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 border border-gray-100"><i class="fa-solid fa-arrow-left"></i></a>
            <h2 class="text-xl font-bold text-gray-800"><?php echo $id > 0 ? 'Configure Hospital' : 'Register New Hospital'; ?></h2>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 flex gap-2">
            <i class="fa-solid fa-circle-exclamation mt-1"></i> <?php echo esc($error); ?>
        </div>
    <?php
endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100 bg-indigo-900 text-white flex justify-between items-center">
            <h3 class="font-bold flex items-center gap-2">
                <i class="fa-regular fa-hospital text-indigo-300"></i> Clinic Information
            </h3>
        </div>
        
        <div class="p-6 md:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hospital / Clinic Name *</label>
                    <input type="text" name="name" value="<?php echo esc($hospital['name']); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50 uppercase text-gray-800 font-bold" required>
                </div>
                <div>
                   <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                   <input type="text" name="phone" value="<?php echo esc($hospital['phone']); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g. +92 3XX XXXXXXX">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Physical Address</label>
                <textarea name="address" rows="2" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Full clinic address for receipts..."><?php echo esc($hospital['address']); ?></textarea>
            </div>

            <!-- Margins -->
            <div class="mb-8 p-5 bg-gray-50 border border-gray-200 rounded-xl relative">
                <div class="absolute -top-3 left-4 bg-gray-100 px-3 py-1 rounded text-[10px] font-bold uppercase tracking-wider text-gray-500 border border-gray-200">Letterhead Margins</div>
                
                <p class="text-xs text-gray-500 mb-4 whitespace-normal">These margins strictly control where clinical content (Ultrasounds, Prescriptions) begins rendering on A4 PDF printouts. They are tailored precisely to each hospital's physical pre-printed letterhead.</p>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Top Margin</label>
                        <input type="text" name="margin_top" value="<?php echo esc($hospital['margin_top']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded font-mono text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Bottom Margin</label>
                        <input type="text" name="margin_bottom" value="<?php echo esc($hospital['margin_bottom']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded font-mono text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Left Margin</label>
                        <input type="text" name="margin_left" value="<?php echo esc($hospital['margin_left']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded font-mono text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Right Margin</label>
                        <input type="text" name="margin_right" value="<?php echo esc($hospital['margin_right']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded font-mono text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Uploads -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-6">
                <!-- Logo -->
                <div class="border border-gray-200 rounded-lg p-5">
                    <h4 class="font-bold text-gray-800 text-sm mb-1 uppercase tracking-wider">Hospital Logo</h4>
                    <p class="text-[11px] text-gray-500 mb-4">PNG/JPG format. Used on plain-paper prints (like SA).</p>
                    
                    <?php if (!empty($hospital['logo_path'])): ?>
                        <div class="mb-4 bg-gray-50 border border-gray-200 p-2 rounded inline-block">
                            <img src="../<?php echo esc($hospital['logo_path']); ?>" alt="Current Logo" class="h-16 object-contain">
                        </div>
                    <?php
endif; ?>
                    <input type="file" name="logo" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors">
                </div>

                <!-- Signature -->
                <div class="border border-gray-200 rounded-lg p-5">
                    <h4 class="font-bold text-gray-800 text-sm mb-1 uppercase tracking-wider">Digital Signature</h4>
                    <p class="text-[11px] text-gray-500 mb-4">Transparent PNG recommended. Appears at bottom of reports.</p>
                    
                    <?php if (!empty($hospital['digital_signature_path'])): ?>
                        <div class="mb-4 bg-gray-50 border border-gray-200 p-2 rounded inline-block">
                            <img src="../<?php echo esc($hospital['digital_signature_path']); ?>" alt="Current Signature" class="h-16 object-contain bg-white">
                        </div>
                    <?php
endif; ?>
                    <input type="file" name="signature" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors">
                </div>
                
                <!-- Digital Letterhead -->
                <div class="border border-indigo-200 bg-indigo-50/30 rounded-lg p-5 md:col-span-2">
                    <h4 class="font-bold text-indigo-900 text-sm mb-1 uppercase tracking-wider"><i class="fa-regular fa-image border border-indigo-300 p-1 rounded bg-white text-indigo-600 mr-1"></i> Full Digital Letterhead Graphic</h4>
                    <p class="text-[12px] text-gray-600 mb-4">Used EXCLUSIVELY for generating Digital PDFs for patients online. <strong>Upload a high-quality A4 graphic with nothing but the empty letterhead frame (no patient data).</strong></p>
                    
                    <?php if (!empty($hospital['letterhead_image_path'])): ?>
                        <div class="mb-4 bg-gray-100 border border-gray-300 p-2 rounded inline-block">
                            <a href="../<?php echo esc($hospital['letterhead_image_path']); ?>" target="_blank" class="text-xs text-indigo-700 font-bold hover:underline mb-2 block"><i class="fa-solid fa-eye"></i> View Current Letterhead Letterhead</a>
                            <img src="../<?php echo esc($hospital['letterhead_image_path']); ?>" alt="Letterhead Preview" class="h-24 w-auto object-contain bg-white border border-gray-200 shadow-sm">
                        </div>
                    <?php
endif; ?>
                    <input type="file" name="letterhead" accept="image/*,application/pdf" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-100 file:text-indigo-800 hover:file:bg-indigo-200 transition-colors">
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-100 gap-3">
                <a href="hospitals.php" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-6 py-2.5 rounded-lg font-medium transition-colors shadow-sm">Cancel</a>
                <button type="submit" name="save_hospital" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Save Settings
                </button>
            </div>
            
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
