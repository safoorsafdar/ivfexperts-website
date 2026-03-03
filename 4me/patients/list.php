<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/auth.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/config/db.php";

$pageTitle = "Patients List - IVF Experts Admin";

// Pagination
$perPage = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Search
$search = trim($_GET['search'] ?? '');
$searchTerm = '%' . $search . '%';

// Build query
$sql = "SELECT id, mr_number, name, phone, cnic, created_at FROM patients";
$countSql = "SELECT COUNT(*) as total FROM patients";

$params = [];
$types = "";

if ($search) {
    $sql .= " WHERE mr_number LIKE ? OR name LIKE ? OR phone LIKE ? OR cnic LIKE ?";
    $countSql .= " WHERE mr_number LIKE ? OR name LIKE ? OR phone LIKE ? OR cnic LIKE ?";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
    $types = "ssss";
}

// Count total
$countStmt = $conn->prepare($countSql);
if ($search) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['total'] ?? 0;
$countStmt->close();

// Main query + limit
$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$patients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$totalPages = max(1, ceil($total / $perPage));

include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/header.php";
?>

<div class="p-6 lg:p-10">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-10">
        <div>
            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900">Patients</h1>
            <p class="text-gray-600 mt-2">Manage all registered patients</p>
        </div>
        <a href="add.php" class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-xl font-semibold transition shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add New Patient
        </a>
    </div>

    <!-- Search -->
    <form method="get" class="mb-10">
        <div class="flex flex-col sm:flex-row gap-4 max-w-2xl">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                   placeholder="Search MR, Name, Phone or CNIC..." 
                   class="flex-1 border border-gray-300 rounded-xl px-5 py-4 focus:ring-2 focus:ring-teal-500 outline-none transition">
            <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-8 py-4 rounded-xl font-semibold transition flex items-center justify-center gap-2 min-w-[140px]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Search
            </button>
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
        <?php if (empty($patients)): ?>
            <div class="text-center py-16 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xl font-medium">No patients found</p>
                <p class="mt-2"><?= $search ? 'Try different search or ' : '' ?><a href="add.php" class="text-teal-600 hover:underline">add a new patient</a>.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MR Number</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CNIC</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($patients as $p): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-5 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($p['mr_number'] ?? 'N/A') ?></td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($p['name'] ?? 'N/A') ?></td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($p['phone'] ?? 'N/A') ?></td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($p['cnic'] ?? 'N/A') ?></td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600"><?= $p['created_at'] ? date('d M Y', strtotime($p['created_at'])) : 'N/A' ?></td>
                                <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="view.php?id=<?= $p['id'] ?>" class="text-teal-600 hover:text-teal-900 mr-4">View</a>
                                    <a href="edit.php?id=<?= $p['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-4">Edit</a>
                                    <a href="delete.php?id=<?= $p['id'] ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Delete this patient? This cannot be undone.')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="px-6 py-5 flex items-center justify-between border-t border-gray-200">
                    <div class="text-sm text-gray-700">
                        Showing <?= ($offset + 1) ?>–<?= min($offset + $perPage, $total) ?> of <?= number_format($total) ?>
                    </div>
                    <div class="flex gap-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="px-4 py-2 border rounded-lg <?= $i === $page ? 'bg-teal-600 text-white' : 'hover:bg-gray-50' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/footer.php"; ?>