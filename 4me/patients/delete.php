<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/auth.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/config/db.php";

// Get ID
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: list.php");
    exit();
}

// Delete action (only POST)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirm_delete'])) {
    $stmt = $conn->prepare("DELETE FROM patients WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: list.php?deleted=1");
        exit();
    } else {
        $error = "Error deleting patient: " . $conn->error;
    }
    $stmt->close();
}

// Fetch patient for confirmation
$stmt = $conn->prepare("SELECT name, mr_number FROM patients WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$patient) {
    header("Location: list.php");
    exit();
}

$pageTitle = "Delete Patient - IVF Experts Admin";

include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/header.php";
?>

<div class="p-6 lg:p-10 max-w-2xl mx-auto">
    <h1 class="text-3xl lg:text-4xl font-bold text-red-700 mb-2">Delete Patient</h1>
    <p class="text-gray-600 mb-10">Confirm deletion of patient record</p>

    <?php if (isset($error)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-6 mb-8 rounded-xl">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-lg p-8 lg:p-10 mb-10">
        <p class="text-xl font-medium mb-6">
            Are you sure you want to delete <strong><?= htmlspecialchars($patient['name']) ?></strong> 
            (MR: <?= htmlspecialchars($patient['mr_number']) ?>)?
        </p>
        <p class="text-gray-600 mb-8">
            This action cannot be undone. All related records (e.g. semen reports) will also be deleted.
        </p>

        <form method="POST" class="flex gap-6">
            <button type="submit" name="confirm_delete" 
                    class="bg-red-600 hover:bg-red-700 text-white px-10 py-4 rounded-xl font-semibold transition shadow-md hover:shadow-lg">
                Yes, Delete Patient
            </button>

            <a href="list.php" 
               class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-10 py-4 rounded-xl font-semibold transition">
                Cancel
            </a>
        </form>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/footer.php"; ?>