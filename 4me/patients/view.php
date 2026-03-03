<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/auth.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/config/db.php";

$pageTitle = "Patient Details - IVF Experts Admin";

// Get ID
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

include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/header.php";
?>

<div class="p-6 lg:p-10 max-w-4xl mx-auto">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-10">
        <div>
            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900">
                Patient: <?= htmlspecialchars($patient['name'] ?? 'N/A') ?>
            </h1>
            <p class="text-gray-600 mt-2">MR Number: <?= htmlspecialchars($patient['mr_number'] ?? 'N/A') ?></p>
        </div>

        <div class="flex gap-4">
            <a href="edit.php?id=<?= $id ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold transition">
                Edit Patient
            </a>
            <a href="list.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-xl font-semibold transition">
                Back to List
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg p-8 lg:p-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Basic Information</h3>
                <dl class="space-y-4 text-gray-700">
                    <div>
                        <dt class="font-medium text-gray-500">MR Number</dt>
                        <dd class="mt-1"><?= htmlspecialchars($patient['mr_number'] ?? 'N/A') ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Full Name</dt>
                        <dd class="mt-1"><?= htmlspecialchars($patient['name'] ?? 'N/A') ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Age</dt>
                        <dd class="mt-1"><?= htmlspecialchars($patient['age'] ?? 'N/A') ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1"><?= htmlspecialchars($patient['phone'] ?? 'N/A') ?></dd>
                    </div>
                </dl>
            </div>

            <div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Additional Details</h3>
                <dl class="space-y-4 text-gray-700">
                    <div>
                        <dt class="font-medium text-gray-500">CNIC</dt>
                        <dd class="mt-1"><?= htmlspecialchars($patient['cnic'] ?? 'N/A') ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Email</dt>
                        <dd class="mt-1"><?= htmlspecialchars($patient['email'] ?? 'N/A') ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Address</dt>
                        <dd class="mt-1"><?= nl2br(htmlspecialchars($patient['address'] ?? 'N/A')) ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Registered On</dt>
                        <dd class="mt-1"><?= $patient['created_at'] ? date('d M Y H:i', strtotime($patient['created_at'])) : 'N/A' ?></dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/footer.php"; ?>