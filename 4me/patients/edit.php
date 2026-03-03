<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/auth.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/config/db.php";

$pageTitle = "Edit Patient - IVF Experts Admin";

$error = "";
$success = "";

// Get patient ID
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: list.php");
    exit();
}

// Fetch patient
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$patient) {
    header("Location: list.php");
    exit();
}

$formData = $patient; // prefill form

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mr_number = trim($_POST['mr_number'] ?? '');
    $name      = trim($_POST['name'] ?? '');
    $age       = !empty($_POST['age']) ? (int)$_POST['age'] : null;
    $phone     = trim($_POST['phone'] ?? '');
    $cnic      = trim($_POST['cnic'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $email     = trim($_POST['email'] ?? '');

    if (empty($mr_number) || empty($name)) {
        $error = "MR Number and Name are required.";
    } else {
        // Check duplicate MR (exclude current)
        $stmt = $conn->prepare("SELECT id FROM patients WHERE mr_number = ? AND id != ?");
        $stmt->bind_param("si", $mr_number, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "MR Number already exists.";
        } else {
            $stmt = $conn->prepare("UPDATE patients SET mr_number = ?, name = ?, age = ?, phone = ?, cnic = ?, address = ?, email = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("ssissssi", $mr_number, $name, $age, $phone, $cnic, $address, $email, $id);

            if ($stmt->execute()) {
                $success = "Patient updated successfully!";
                // Refresh data
                $stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $patient = $stmt->get_result()->fetch_assoc();
                $formData = $patient;
            } else {
                $error = "Error updating: " . $conn->error;
            }
        }
        $stmt->close();
    }
}

include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/header.php";
?>

<div class="p-6 lg:p-10 max-w-4xl mx-auto">
    <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">Edit Patient</h1>
    <p class="text-gray-600 mb-10">Update patient details below</p>

    <?php if ($error): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-6 mb-8 rounded-xl">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-6 mb-8 rounded-xl">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-8 bg-white rounded-2xl shadow-lg p-8 lg:p-10">
        <!-- Same form fields as add.php but pre-filled -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 font-medium mb-2">MR Number <span class="text-red-600">*</span></label>
                <input type="text" name="mr_number" value="<?= htmlspecialchars($formData['mr_number'] ?? '') ?>" required
                       class="w-full border border-gray-300 rounded-xl px-5 py-4 focus:ring-2 focus:ring-teal-500 outline-none transition">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Full Name <span class="text-red-600">*</span></label>
                <input type="text" name="name" value="<?= htmlspecialchars($formData['name'] ?? '') ?>" required
                       class="w-full border border-gray-300 rounded-xl px-5 py-4 focus:ring-2 focus:ring-teal-500 outline-none transition">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Age</label>
                <input type="number" name="age" value="<?= htmlspecialchars($formData['age'] ?? '') ?>" min="1"
                       class="w-full border border-gray-300 rounded-xl px-5 py-4 focus:ring-2 focus:ring-teal-500 outline-none transition">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Phone Number</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($formData['phone'] ?? '') ?>"
                       class="w-full border border-gray-300 rounded-xl px-5 py-4 focus:ring-2 focus:ring-teal-500 outline-none transition">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">CNIC / NIC</label>
                <input type="text" name="cnic" value="<?= htmlspecialchars($formData['cnic'] ?? '') ?>"
                       class="w-full border border-gray-300 rounded-xl px-5 py-4 focus:ring-2 focus:ring-teal-500 outline-none transition">
            </div>

            <div class="md:col-span-2">
                <label class="block text-gray-700 font-medium mb-2">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
                       class="w-full border border-gray-300 rounded-xl px-5 py-4 focus:ring-2 focus:ring-teal-500 outline-none transition">
            </div>

            <div class="md:col-span-2">
                <label class="block text-gray-700 font-medium mb-2">Address</label>
                <textarea name="address" rows="4"
                          class="w-full border border-gray-300 rounded-xl px-5 py-4 focus:ring-2 focus:ring-teal-500 outline-none transition"><?= htmlspecialchars($formData['address'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="list.php" class="px-8 py-4 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl font-medium transition">
                Cancel
            </a>
            <button type="submit" class="px-8 py-4 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-semibold transition shadow-md hover:shadow-lg">
                Update Patient
            </button>
        </div>
    </form>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/footer.php"; ?>