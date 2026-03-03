<?php
/**
 * IVF Experts Admin - Patients Module Landing Page
 * Quick overview + links to patient management actions
 */

session_start();

// Require authentication
require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/auth.php";

// Database connection
require_once $_SERVER['DOCUMENT_ROOT'] . "/config/db.php";

// Page title
$pageTitle = "Patients Dashboard - IVF Experts Admin";

// Fetch quick stats
$totalPatients = $conn->query("SELECT COUNT(*) as c FROM patients")->fetch_assoc()['c'] ?? 0;
$recentPatients = $conn->query("SELECT id, mr_number, name, created_at 
                                FROM patients 
                                ORDER BY created_at DESC 
                                LIMIT 5")->fetch_all(MYSQLI_ASSOC);

include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/header.php";
?>

<div class="p-6 lg:p-10">
    <!-- Module Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-12">
        <div>
            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900">
                Patients Module
            </h1>
            <p class="text-gray-600 mt-2">
                Manage patient records, search, add, edit, and view profiles
            </p>
        </div>

        <a href="add.php" 
           class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-xl font-semibold transition shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Register New Patient
        </a>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <div class="bg-white rounded-2xl shadow-lg p-8 border-l-4 border-teal-600 hover:shadow-xl transition">
            <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wide">Total Patients</h3>
            <p class="text-5xl font-extrabold text-gray-900 mt-4"><?= number_format($totalPatients) ?></p>
            <p class="text-sm text-gray-500 mt-2">Registered fertility patients</p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-8 border-l-4 border-blue-600 hover:shadow-xl transition">
            <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wide">Recent Activity</h3>
            <p class="text-5xl font-extrabold text-gray-900 mt-4"><?= count($recentPatients) ?></p>
            <p class="text-sm text-gray-500 mt-2">Patients added in last 30 days</p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-8 border-l-4 border-purple-600 hover:shadow-xl transition">
            <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wide">Quick Actions</h3>
            <div class="mt-6 space-y-3">
                <a href="list.php" class="block text-teal-600 hover:text-teal-800 font-medium">View All Patients →</a>
                <a href="add.php" class="block text-teal-600 hover:text-teal-800 font-medium">Add New Patient →</a>
            </div>
        </div>
    </div>

    <!-- Recent Patients -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
        <div class="p-8 lg:p-10">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Recent Patients</h2>
                <a href="list.php" class="text-teal-600 hover:text-teal-800 font-medium flex items-center gap-2">
                    View All →
                </a>
            </div>

            <?php if (empty($recentPatients)): ?>
                <div class="text-center py-12 text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-lg font-medium">No recent patients</p>
                    <a href="add.php" class="text-teal-600 hover:underline mt-2 inline-block">Add your first patient</a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MR Number</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recentPatients as $patient): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-5 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($patient['mr_number'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-700">
                                        <?= htmlspecialchars($patient['name'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600">
                                        <?= date('d M Y', strtotime($patient['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="view.php?id=<?= $patient['id'] ?>" class="text-teal-600 hover:text-teal-900">
                                            View Profile →
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/footer.php"; ?>