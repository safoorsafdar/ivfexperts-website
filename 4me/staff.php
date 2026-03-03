<?php
$pageTitle = "Staff Management";
require_once __DIR__ . '/includes/auth.php';

$msg   = $_GET['msg'] ?? '';
$error = '';

// ── Handle Delete ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del = intval($_POST['delete_id']);
    if ($del > 0) {
        $stmt = $conn->prepare("DELETE FROM staff WHERE id = ?");
        $stmt->bind_param("i", $del);
        $stmt->execute();
        header("Location: staff.php?msg=deleted");
        exit;
    }
}

// ── Handle Add / Edit ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_staff'])) {
    $staff_id   = intval($_POST['staff_id'] ?? 0);
    $name       = trim($_POST['name'] ?? '');
    $role       = trim($_POST['role'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $cnic       = trim($_POST['cnic'] ?? '');
    $salary     = floatval($_POST['salary'] ?? 0);
    $join_date  = $_POST['join_date'] ?? date('Y-m-d');
    $status     = $_POST['status'] ?? 'Active';
    $notes      = trim($_POST['notes'] ?? '');

    if (empty($name) || empty($role)) {
        $error = "Name and Role are required.";
    } else {
        if ($staff_id > 0) {
            $stmt = $conn->prepare("UPDATE staff SET name=?, role=?, phone=?, email=?, cnic=?, salary=?, join_date=?, status=?, notes=? WHERE id=?");
            $stmt->bind_param("sssssdssssi", $name, $role, $phone, $email, $cnic, $salary, $join_date, $status, $notes, $staff_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO staff (name, role, phone, email, cnic, salary, join_date, status, notes) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssdsss", $name, $role, $phone, $email, $cnic, $salary, $join_date, $status, $notes);
        }
        if ($stmt && $stmt->execute()) {
            header("Location: staff.php?msg=" . ($staff_id > 0 ? 'updated' : 'added'));
            exit;
        } else {
            $error = "Database error: " . ($stmt ? $stmt->error : $conn->error);
        }
    }
}

// ── Fetch Stats ─────────────────────────────────────────────────────────────
$total_staff  = 0;
$total_salary = 0;
try {
    $r = $conn->query("SELECT COUNT(*) AS c, COALESCE(SUM(salary),0) AS s FROM staff WHERE status = 'Active'");
    if ($r) { $row = $r->fetch_assoc(); $total_staff = $row['c']; $total_salary = $row['s']; }
} catch (Exception $e) {}

// ── Fetch All Staff ──────────────────────────────────────────────────────────
$staff_list = [];
try {
    $res = $conn->query("SELECT * FROM staff ORDER BY status ASC, name ASC");
    if ($res) while ($row = $res->fetch_assoc()) $staff_list[] = $row;
} catch (Exception $e) {}

$role_colors = [
    'Doctor'        => ['bg'=>'bg-indigo-50',  'text'=>'text-indigo-700'],
    'Embryologist'  => ['bg'=>'bg-violet-50',  'text'=>'text-violet-700'],
    'Nurse'         => ['bg'=>'bg-pink-50',    'text'=>'text-pink-700'],
    'Lab Technician'=> ['bg'=>'bg-sky-50',     'text'=>'text-sky-700'],
    'Receptionist'  => ['bg'=>'bg-amber-50',   'text'=>'text-amber-700'],
    'Accountant'    => ['bg'=>'bg-emerald-50', 'text'=>'text-emerald-700'],
    'Cleaner'       => ['bg'=>'bg-gray-50',    'text'=>'text-gray-600'],
];
$default_role_color = ['bg'=>'bg-teal-50','text'=>'text-teal-700'];

include __DIR__ . '/includes/header.php';
?>

<?php if ($msg === 'added'):   ?><div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3 rounded-xl text-sm font-bold mb-6"><i class="fa-solid fa-circle-check text-emerald-500"></i> Staff member added.</div><?php endif; ?>
<?php if ($msg === 'updated'): ?><div class="flex items-center gap-3 bg-sky-50 border border-sky-200 text-sky-800 px-5 py-3 rounded-xl text-sm font-bold mb-6"><i class="fa-solid fa-circle-check text-sky-500"></i> Staff record updated.</div><?php endif; ?>
<?php if ($msg === 'deleted'): ?><div class="flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 px-5 py-3 rounded-xl text-sm font-bold mb-6"><i class="fa-solid fa-circle-check text-rose-500"></i> Staff member removed.</div><?php endif; ?>

<div x-data="{ showModal: false, editStaff: null }" @keydown.escape.window="showModal = false">

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-gray-800 tracking-tight">Staff Management</h1>
        <p class="text-sm text-gray-400 font-bold mt-0.5"><?php echo $total_staff; ?> active staff · Rs. <?php echo number_format($total_salary, 0); ?>/month payroll</p>
    </div>
    <button @click="editStaff = null; showModal = true"
            class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white font-black py-3 px-5 rounded-xl transition-all shadow-lg shadow-teal-100 active:scale-95 text-sm">
        <i class="fa-solid fa-user-plus"></i> Add Staff Member
    </button>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <?php
    $roles_count = [];
    foreach ($staff_list as $s) {
        if ($s['status'] === 'Active') $roles_count[$s['role']] = ($roles_count[$s['role']] ?? 0) + 1;
    }
    $top_roles = ['Doctor','Embryologist','Nurse','Receptionist'];
    foreach ($top_roles as $tr):
        $c = $roles_count[$tr] ?? 0;
        $rc = $role_colors[$tr] ?? $default_role_color;
    ?>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-2">
            <div class="w-9 h-9 <?php echo $rc['bg'] . ' ' . $rc['text']; ?> rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-user-tie text-sm"></i>
            </div>
            <span class="text-2xl font-black text-gray-800"><?php echo $c; ?></span>
        </div>
        <div class="text-xs font-bold text-gray-400"><?php echo $tr; ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Staff Grid / Table -->
<?php if (empty($staff_list)): ?>
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
    <i class="fa-solid fa-user-tie text-5xl text-gray-100 mb-4 block"></i>
    <h3 class="text-lg font-bold text-gray-400 mb-2">No staff records yet</h3>
    <p class="text-sm text-gray-400 mb-4">Add your clinic team members to track roles and payroll.</p>
    <button @click="showModal = true" class="inline-block bg-teal-600 text-white px-6 py-2.5 rounded-xl font-black text-sm hover:bg-teal-700 transition-all">Add First Staff Member</button>
</div>
<?php else: ?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($staff_list as $s):
        $rc = $role_colors[$s['role']] ?? $default_role_color;
        $is_active = $s['status'] === 'Active';
    ?>
    <div class="bg-white rounded-2xl border <?php echo $is_active ? 'border-gray-100' : 'border-gray-100 opacity-60'; ?> shadow-sm p-5 hover:shadow-md transition-all">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl <?php echo $rc['bg'] . ' ' . $rc['text']; ?> font-black text-lg flex items-center justify-center shrink-0">
                    <?php echo strtoupper(substr($s['name'], 0, 1)); ?>
                </div>
                <div>
                    <div class="font-black text-gray-800 leading-tight"><?php echo esc($s['name']); ?></div>
                    <span class="text-[10px] font-black uppercase tracking-wider <?php echo $rc['bg'] . ' ' . $rc['text']; ?> px-2 py-0.5 rounded-full mt-1 inline-block">
                        <?php echo esc($s['role']); ?>
                    </span>
                </div>
            </div>
            <span class="text-[9px] font-black uppercase px-2 py-1 rounded-lg <?php echo $is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-400'; ?>">
                <?php echo $s['status']; ?>
            </span>
        </div>

        <div class="space-y-1.5 text-xs mb-4">
            <?php if (!empty($s['phone'])): ?>
            <div class="flex items-center gap-2 text-gray-600">
                <i class="fa-solid fa-phone text-gray-300 w-3"></i>
                <span class="font-bold"><?php echo esc($s['phone']); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($s['email'])): ?>
            <div class="flex items-center gap-2 text-gray-600">
                <i class="fa-solid fa-envelope text-gray-300 w-3"></i>
                <span><?php echo esc($s['email']); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($s['cnic'])): ?>
            <div class="flex items-center gap-2 text-gray-500 font-mono">
                <i class="fa-solid fa-id-card text-gray-300 w-3"></i>
                <span><?php echo esc($s['cnic']); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($s['join_date']): ?>
            <div class="flex items-center gap-2 text-gray-400">
                <i class="fa-solid fa-calendar text-gray-300 w-3"></i>
                <span>Joined <?php echo date('d M Y', strtotime($s['join_date'])); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($s['salary'] > 0): ?>
        <div class="bg-gray-50 rounded-xl px-3 py-2 mb-4 flex items-center justify-between">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Monthly Salary</span>
            <span class="font-black text-gray-800 font-mono">Rs. <?php echo number_format($s['salary'], 0); ?></span>
        </div>
        <?php endif; ?>

        <?php if (!empty($s['notes'])): ?>
        <p class="text-xs text-gray-400 italic mb-4 line-clamp-2"><?php echo esc($s['notes']); ?></p>
        <?php endif; ?>

        <div class="flex gap-2 pt-3 border-t border-gray-50">
            <button @click="editStaff = <?php echo htmlspecialchars(json_encode([
                'id'        => $s['id'],
                'name'      => $s['name'],
                'role'      => $s['role'],
                'phone'     => $s['phone'] ?? '',
                'email'     => $s['email'] ?? '',
                'cnic'      => $s['cnic'] ?? '',
                'salary'    => $s['salary'] ?? 0,
                'join_date' => $s['join_date'] ?? '',
                'status'    => $s['status'],
                'notes'     => $s['notes'] ?? '',
            ]), ENT_QUOTES); ?>; showModal = true"
                    class="flex-1 py-2 bg-gray-100 hover:bg-indigo-600 text-gray-500 hover:text-white rounded-xl font-black text-xs transition-all flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-pen text-[10px]"></i> Edit
            </button>
            <button onclick="confirmDeleteStaff(<?php echo $s['id']; ?>, '<?php echo esc(addslashes($s['name'])); ?>')"
                    class="w-9 h-9 bg-gray-100 hover:bg-rose-500 text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all text-sm shrink-0">
                <i class="fa-solid fa-trash text-xs"></i>
            </button>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Add / Edit Modal -->
<div x-show="showModal" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showModal = false"></div>
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg z-10 overflow-hidden my-8"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="scale-95 opacity-0"
         x-transition:enter-end="scale-100 opacity-100">

        <div class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between">
            <h3 class="font-black text-base" x-text="editStaff ? 'Edit Staff Member' : 'Add Staff Member'"></h3>
            <button @click="showModal = false" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all">
                <i class="fa-solid fa-times text-sm"></i>
            </button>
        </div>

        <?php if (!empty($error)): ?>
        <div class="mx-6 mt-4 bg-red-50 text-red-600 px-4 py-3 rounded-xl border border-red-100 text-sm font-bold flex gap-2">
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i><?php echo esc($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="save_staff" value="1">
            <input type="hidden" name="staff_id" :value="editStaff ? editStaff.id : 0">

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="name" :value="editStaff ? editStaff.name : ''"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 bg-gray-50 text-sm font-medium" required>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Role *</label>
                    <select name="role" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 bg-gray-50 text-sm font-medium" required>
                        <?php
                        $roles = ['Doctor','Embryologist','Nurse','Lab Technician','Receptionist','Accountant','Cleaner','Other'];
                        foreach ($roles as $r):
                        ?>
                        <option value="<?php echo $r; ?>" x-bind:selected="editStaff && editStaff.role === '<?php echo $r; ?>'"><?php echo $r; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" :value="editStaff ? editStaff.phone : ''"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 bg-gray-50 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" :value="editStaff ? editStaff.email : ''"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 bg-gray-50 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">CNIC</label>
                    <input type="text" name="cnic" :value="editStaff ? editStaff.cnic : ''"
                           placeholder="12345-6789012-3"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 bg-gray-50 text-sm font-mono">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Monthly Salary (Rs)</label>
                    <input type="number" step="500" name="salary" :value="editStaff ? editStaff.salary : ''"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 bg-gray-50 text-sm font-mono">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Joining Date</label>
                    <input type="date" name="join_date" :value="editStaff ? editStaff.join_date : '<?php echo date('Y-m-d'); ?>'"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 bg-gray-50 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 bg-gray-50 text-sm font-bold">
                        <option value="Active"   x-bind:selected="!editStaff || editStaff.status === 'Active'">Active</option>
                        <option value="Inactive" x-bind:selected="editStaff && editStaff.status === 'Inactive'">Inactive</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" x-bind:value="editStaff ? editStaff.notes : ''"
                              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 bg-gray-50 text-sm resize-none"
                              placeholder="Qualifications, special skills, remarks..."></textarea>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" @click="showModal = false"
                        class="flex-1 py-3 rounded-2xl font-black text-gray-600 bg-gray-100 hover:bg-gray-200 transition-all text-sm">Cancel</button>
                <button type="submit"
                        class="flex-1 py-3 rounded-2xl font-black text-white bg-teal-600 hover:bg-teal-700 transition-all shadow-lg shadow-teal-100 text-sm">
                    <i class="fa-solid fa-save mr-1"></i>
                    <span x-text="editStaff ? 'Update' : 'Add Staff'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteStaffModal" x-data="{ show: false, name: '', sid: 0 }"
     x-show="show"
     @keydown.escape.window="show = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none;">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="show = false"></div>
    <div class="relative bg-white rounded-3xl shadow-2xl p-8 max-w-sm w-full z-10 text-center">
        <div class="w-14 h-14 bg-rose-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-2xl"></i>
        </div>
        <h3 class="text-xl font-black text-gray-800 mb-2">Remove Staff Member?</h3>
        <p class="font-black text-gray-800 text-lg mb-6" x-text="name"></p>
        <form method="POST" class="flex gap-3">
            <input type="hidden" name="delete_id" :value="sid">
            <button type="button" @click="show = false"
                    class="flex-1 py-3 rounded-2xl font-black text-gray-600 bg-gray-100 hover:bg-gray-200 transition-all text-sm">Cancel</button>
            <button type="submit"
                    class="flex-1 py-3 rounded-2xl font-black text-white bg-rose-500 hover:bg-rose-600 transition-all text-sm">Remove</button>
        </form>
    </div>
</div>

</div>

<script>
function confirmDeleteStaff(id, name) {
    const modal = document.getElementById('deleteStaffModal');
    modal._x_dataStack[0].sid  = id;
    modal._x_dataStack[0].name = name;
    modal._x_dataStack[0].show = true;
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
