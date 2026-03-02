<?php
session_start();
if (isset($_SESSION['portal_patient_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone_mr = trim($_POST['phone_mr'] ?? '');
    $cnic_raw = trim($_POST['cnic'] ?? '');
    $cnic_clean = preg_replace('/[^0-9]/', '', $cnic_raw); // Strip dashes automatically

    if (empty($phone_mr) || empty($cnic_clean)) {
        $error = "Please provide both fields to login.";
    }
    else {
        // Check both main patient and spouse fields
        $stmt = $conn->prepare("SELECT id FROM patients 
                                WHERE ((phone = ? OR mr_number = ?) AND REPLACE(cnic, '-', '') = ?)
                                OR ((spouse_phone = ? OR mr_number = ?) AND REPLACE(spouse_cnic, '-', '') = ?)");
        $stmt->bind_param("ssssss", $phone_mr, $phone_mr, $cnic_clean, $phone_mr, $phone_mr, $cnic_clean);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $patient = $res->fetch_assoc();
            $_SESSION['portal_patient_id'] = $patient['id'];
            header("Location: dashboard.php");
            exit;
        }
        else {
            $error = "Invalid details. The Phone/MR Number and CNIC did not match our records (neither Patient nor Spouse).";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Portal Login - IVF Experts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 max-w-md w-full">
        <div class="text-center mb-6">
            <img src="/assets/images/logo.png" alt="IVF Experts" class="h-20 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Patient Portal Login</h1>
            <p class="text-sm text-gray-500">Access your comprehensive EMR records, ultrasounds, prescriptions, and semen analyses securely.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-50 text-red-600 text-sm p-3 rounded-lg mb-4 border border-red-100 text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php
endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-2">Mobile Number OR MR Number</label>
                <input type="text" name="phone_mr" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500" placeholder="e.g. 03001234567 or IVF-2310..." required autofocus value="<?php echo htmlspecialchars($_POST['phone_mr'] ?? ''); ?>">
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">CNIC Number</label>
                <input type="text" name="cnic" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 font-mono" placeholder="13 digits (dashes will be ignored)" required value="<?php echo htmlspecialchars($_POST['cnic'] ?? ''); ?>">
            </div>
            
            <button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white font-bold py-3 rounded-lg transition-colors shadow-md mb-4">
                Secure Login
            </button>
        </form>

        <div class="bg-sky-50 rounded-xl p-4 border border-sky-100 text-center mt-4">
            <i class="fa-solid fa-qrcode text-3xl text-sky-400 mb-2 block"></i>
            <p class="text-xs text-sky-700 font-medium">Or simply scan the QR code located at the bottom of any physical report provided by our clinic to log in instantly.</p>
        </div>

        <div class="text-xs text-center text-gray-400 mt-6 pt-4 border-t border-gray-100">
            Secured by IVF Experts 2FA EMR System &copy; <?php echo date('Y'); ?>
        </div>
    </div>

</body>
</html>
