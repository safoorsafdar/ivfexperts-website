<?php
$pageTitle = "Leads & CRM";
require_once __DIR__ . '/includes/auth.php';

$msg   = $_GET['msg'] ?? '';
$error = '';

// ── Handle Delete ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del = intval($_POST['delete_id']);
    if ($del > 0) {
        $conn->query("DELETE FROM leads WHERE id = $del");
        header("Location: leads.php?msg=deleted");
        exit;
    }
}

// ── Handle Add / Edit ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_lead'])) {
    $lead_id    = intval($_POST['lead_id'] ?? 0);
    $name       = trim($_POST['patient_name'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $inq_type   = trim($_POST['inquiry_type'] ?? '');
    $source     = trim($_POST['source'] ?? '');
    $status     = $_POST['status'] ?? 'new';
    $notes      = trim($_POST['notes'] ?? '');

    $allowed_statuses = ['new','contacted','consultation_booked','closed'];
    if (!in_array($status, $allowed_statuses)) $status = 'new';

    if (empty($name)) {
        $error = "Patient name is required.";
    } else {
        if ($lead_id > 0) {
            $stmt = $conn->prepare("UPDATE leads SET patient_name=?, phone=?, email=?, inquiry_type=?, source=?, status=?, notes=? WHERE id=?");
            $stmt->bind_param("sssssssi", $name, $phone, $email, $inq_type, $source, $status, $notes, $lead_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO leads (patient_name, phone, email, inquiry_type, source, status, notes) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssss", $name, $phone, $email, $inq_type, $source, $status, $notes);
        }
        if ($stmt && $stmt->execute()) {
            header("Location: leads.php?msg=" . ($lead_id > 0 ? 'updated' : 'added'));
            exit;
        } else {
            $error = "Database error: " . ($stmt ? $stmt->error : $conn->error);
        }
    }
}

// ── Fetch Stats ─────────────────────────────────────────────────────────────
$stats = ['total'=>0,'new'=>0,'contacted'=>0,'booked'=>0,'closed'=>0];
try {
    $r = $conn->query("SELECT status, COUNT(*) AS c FROM leads GROUP BY status");
    if ($r) while ($row = $r->fetch_assoc()) {
        $stats['total'] += $row['c'];
        $k = $row['status'] === 'consultation_booked' ? 'booked' : $row['status'];
        if (isset($stats[$k])) $stats[$k] = $row['c'];
    }
} catch (Exception $e) {}

// ── Fetch Leads ──────────────────────────────────────────────────────────────
$filter = trim($_GET['status'] ?? '');
$leads  = [];
try {
    $sql = "SELECT * FROM leads";
    if ($filter) $sql .= " WHERE status = '" . $conn->escape_string($filter) . "'";
    $sql .= " ORDER BY created_at DESC LIMIT 200";
    $res = $conn->query($sql);
    if ($res) while ($row = $res->fetch_assoc()) $leads[] = $row;
} catch (Exception $e) {}

$status_map = [
    'new'                  => ['label' => 'New',          'bg' => 'bg-sky-100',    'text' => 'text-sky-700'],
    'contacted'            => ['label' => 'Contacted',    'bg' => 'bg-amber-100',  'text' => 'text-amber-700'],
    'consultation_booked'  => ['label' => 'Appt. Booked', 'bg' => 'bg-indigo-100', 'text' => 'text-indigo-700'],
    'closed'               => ['label' => 'Closed',       'bg' => 'bg-emerald-100','text' => 'text-emerald-700'],
];

include __DIR__ . '/includes/header.php';
?>

<?php if ($msg === 'added'):   ?><div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3 rounded-xl text-sm font-bold mb-6"><i class="fa-solid fa-circle-check text-emerald-500"></i> Lead added successfully.</div><?php endif; ?>
<?php if ($msg === 'updated'): ?><div class="flex items-center gap-3 bg-sky-50 border border-sky-200 text-sky-800 px-5 py-3 rounded-xl text-sm font-bold mb-6"><i class="fa-solid fa-circle-check text-sky-500"></i> Lead updated.</div><?php endif; ?>
<?php if ($msg === 'deleted'): ?><div class="flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 px-5 py-3 rounded-xl text-sm font-bold mb-6"><i class="fa-solid fa-circle-check text-rose-500"></i> Lead deleted.</div><?php endif; ?>

<div x-data="{ showModal: false, editLead: null }" @keydown.escape.window="showModal = false">

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-gray-800 tracking-tight">Leads & CRM</h1>
        <p class="text-sm text-gray-400 font-bold mt-0.5"><?php echo number_format($stats['total']); ?> total enquiries tracked</p>
    </div>
    <button @click="editLead = null; showModal = true"
            class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white font-black py-3 px-5 rounded-xl transition-all shadow-lg shadow-teal-100 active:scale-95 text-sm">
        <i class="fa-solid fa-plus"></i> Add New Lead
    </button>
</div>

<!-- Stats Strip -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <?php
    $stat_items = [
        ['label'=>'New Enquiries',     'val'=>$stats['new'],      'icon'=>'fa-bell',            'color'=>'sky',    'filter'=>'new'],
        ['label'=>'Contacted',         'val'=>$stats['contacted'], 'icon'=>'fa-phone-volume',    'color'=>'amber',  'filter'=>'contacted'],
        ['label'=>'Appt. Booked',      'val'=>$stats['booked'],   'icon'=>'fa-calendar-check',  'color'=>'indigo', 'filter'=>'consultation_booked'],
        ['label'=>'Closed / Converted','val'=>$stats['closed'],   'icon'=>'fa-circle-check',    'color'=>'emerald','filter'=>'closed'],
    ];
    foreach ($stat_items as $s):
    ?>
    <a href="leads.php?status=<?php echo $s['filter']; ?><?php echo $filter === $s['filter'] ? '' : ''; ?>"
       class="bg-white rounded-2xl border <?php echo $filter === $s['filter'] ? 'border-' . $s['color'] . '-400 shadow-md' : 'border-gray-100 shadow-sm'; ?> p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all group">
        <div class="flex items-center justify-between mb-2">
            <div class="w-9 h-9 bg-<?php echo $s['color']; ?>-50 text-<?php echo $s['color']; ?>-600 rounded-xl flex items-center justify-center group-hover:bg-<?php echo $s['color']; ?>-100 transition-colors">
                <i class="fa-solid <?php echo $s['icon']; ?> text-sm"></i>
            </div>
            <span class="text-2xl font-black text-gray-800"><?php echo $s['val']; ?></span>
        </div>
        <div class="text-xs font-bold text-gray-400"><?php echo $s['label']; ?></div>
    </a>
    <?php endforeach; ?>
</div>

<!-- Filter Bar -->
<?php if ($filter): ?>
<div class="flex items-center gap-3 mb-4">
    <span class="text-sm font-bold text-gray-500">Filtering by: <span class="text-teal-600"><?php echo $status_map[$filter]['label'] ?? $filter; ?></span></span>
    <a href="leads.php" class="text-xs font-black text-gray-400 hover:text-gray-700 bg-gray-100 px-3 py-1 rounded-lg transition-all"><i class="fa-solid fa-times mr-1"></i>Clear</a>
</div>
<?php endif; ?>

<!-- Leads Table -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <?php if (empty($leads)): ?>
    <div class="p-16 text-center">
        <i class="fa-solid fa-handshake text-5xl text-gray-100 mb-4 block"></i>
        <h3 class="text-lg font-bold text-gray-400 mb-2">No leads found</h3>
        <p class="text-sm text-gray-400 mb-4">Start tracking patient enquiries to convert them into consultations.</p>
        <button @click="showModal = true" class="inline-block bg-teal-600 text-white px-6 py-2.5 rounded-xl font-black text-sm hover:bg-teal-700 transition-all">Add First Lead</button>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50/80 text-[9px] font-black uppercase tracking-widest text-gray-400 border-b border-gray-100">
                    <th class="px-6 py-4">Patient / Enquiry</th>
                    <th class="px-6 py-4">Contact</th>
                    <th class="px-6 py-4">Source</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Date</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($leads as $lead):
                    $st = $status_map[$lead['status']] ?? ['label'=>ucfirst($lead['status']),'bg'=>'bg-gray-100','text'=>'text-gray-600'];
                ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-700 font-black text-sm flex items-center justify-center shrink-0">
                                <?php echo strtoupper(substr($lead['patient_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="font-black text-gray-800 text-sm"><?php echo esc($lead['patient_name']); ?></div>
                                <?php if (!empty($lead['inquiry_type'])): ?>
                                <div class="text-[10px] text-gray-400 font-bold mt-0.5"><?php echo esc($lead['inquiry_type']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <?php if (!empty($lead['phone'])): ?>
                        <div class="text-sm text-gray-700 font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-phone text-gray-300 text-xs"></i><?php echo esc($lead['phone']); ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($lead['email'])): ?>
                        <div class="text-xs text-gray-400 mt-0.5"><?php echo esc($lead['email']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs text-gray-500 font-bold"><?php echo esc($lead['source'] ?: '—'); ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wide <?php echo $st['bg'] . ' ' . $st['text']; ?>">
                            <?php echo $st['label']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs text-gray-400 font-bold"><?php echo date('d M Y', strtotime($lead['created_at'])); ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <button @click="editLead = <?php echo htmlspecialchars(json_encode([
                                'id'           => $lead['id'],
                                'patient_name' => $lead['patient_name'],
                                'phone'        => $lead['phone'] ?? '',
                                'email'        => $lead['email'] ?? '',
                                'inquiry_type' => $lead['inquiry_type'] ?? '',
                                'source'       => $lead['source'] ?? '',
                                'status'       => $lead['status'],
                                'notes'        => $lead['notes'] ?? '',
                            ]), ENT_QUOTES); ?>; showModal = true"
                                    class="w-9 h-9 bg-gray-100 hover:bg-indigo-600 text-gray-500 hover:text-white rounded-xl flex items-center justify-center transition-all text-sm"
                                    title="Edit Lead">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </button>
                            <?php if (!empty($lead['phone'])): ?>
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $lead['phone']); ?>?text=<?php echo urlencode('Dear ' . $lead['patient_name'] . ', Thank you for your enquiry at IVF Experts. We would like to schedule a consultation with Dr. Adnan Jabbar. Please reply to confirm a suitable time. – IVF Experts Team'); ?>"
                               target="_blank"
                               class="w-9 h-9 bg-[#dcfce7] hover:bg-[#25D366] text-[#15803d] hover:text-white rounded-xl flex items-center justify-center transition-all text-sm"
                               title="WhatsApp">
                                <i class="fa-brands fa-whatsapp"></i>
                            </a>
                            <?php endif; ?>
                            <button onclick="confirmDeleteLead(<?php echo $lead['id']; ?>, '<?php echo esc(addslashes($lead['patient_name'])); ?>')"
                                    class="w-9 h-9 bg-gray-100 hover:bg-rose-500 text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all text-sm"
                                    title="Delete">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="px-6 py-3 border-t border-gray-50 bg-gray-50/30">
        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest"><?php echo count($leads); ?> leads shown</span>
    </div>
    <?php endif; ?>
</div>

<!-- Add / Edit Modal -->
<div x-show="showModal" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showModal = false"></div>
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg z-10 overflow-hidden"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="scale-95 opacity-0"
         x-transition:enter-end="scale-100 opacity-100">

        <div class="bg-teal-900 text-white px-6 py-4 flex items-center justify-between">
            <h3 class="font-black text-base" x-text="editLead ? 'Edit Lead' : 'Add New Lead'"></h3>
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
            <input type="hidden" name="save_lead" value="1">
            <input type="hidden" name="lead_id" :value="editLead ? editLead.id : 0">

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Patient Name *</label>
                    <input type="text" name="patient_name" :value="editLead ? editLead.patient_name : ''"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent bg-gray-50 text-sm font-medium" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" :value="editLead ? editLead.phone : ''"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 bg-gray-50 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" :value="editLead ? editLead.email : ''"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 bg-gray-50 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Inquiry Type</label>
                    <select name="inquiry_type" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 bg-gray-50 text-sm font-medium">
                        <?php
                        $inq_types = ['IVF Consultation','IUI','ICSI','Egg Freezing','Semen Analysis','Fertility Assessment','General Enquiry','Other'];
                        foreach ($inq_types as $it):
                        ?>
                        <option value="<?php echo $it; ?>" x-bind:selected="editLead && editLead.inquiry_type === '<?php echo $it; ?>'"><?php echo $it; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Lead Source</label>
                    <select name="source" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 bg-gray-50 text-sm font-medium">
                        <?php
                        $sources = ['Website','WhatsApp','Phone Call','Walk-in','Facebook','Instagram','Referral','Other'];
                        foreach ($sources as $src):
                        ?>
                        <option value="<?php echo $src; ?>" x-bind:selected="editLead && editLead.source === '<?php echo $src; ?>'"><?php echo $src; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Status</label>
                    <div class="grid grid-cols-4 gap-2">
                        <?php foreach ($status_map as $key => $s): ?>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="status" value="<?php echo $key; ?>"
                                   x-bind:checked="editLead ? editLead.status === '<?php echo $key; ?>' : '<?php echo $key; ?>' === 'new'"
                                   class="peer sr-only">
                            <div class="p-2 text-center border rounded-xl transition-all peer-checked:<?php echo $s['bg'] . ' peer-checked:' . $s['text']; ?> peer-checked:border-current border-gray-200 text-gray-500 hover:bg-gray-50 text-[10px] font-black uppercase">
                                <?php echo $s['label']; ?>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" x-bind:value="editLead ? editLead.notes : ''"
                              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 bg-gray-50 text-sm resize-none"
                              placeholder="Follow-up notes, patient concerns..."></textarea>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" @click="showModal = false"
                        class="flex-1 py-3 rounded-2xl font-black text-gray-600 bg-gray-100 hover:bg-gray-200 transition-all text-sm">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 py-3 rounded-2xl font-black text-white bg-teal-600 hover:bg-teal-700 transition-all shadow-lg shadow-teal-100 text-sm">
                    <i class="fa-solid fa-save mr-1"></i>
                    <span x-text="editLead ? 'Update Lead' : 'Add Lead'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteLeadModal" x-data="{ show: false, name: '', lid: 0 }"
     x-show="show"
     @keydown.escape.window="show = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none;">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="show = false"></div>
    <div class="relative bg-white rounded-3xl shadow-2xl p-8 max-w-sm w-full z-10 text-center">
        <div class="w-14 h-14 bg-rose-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-2xl"></i>
        </div>
        <h3 class="text-xl font-black text-gray-800 mb-2">Delete Lead?</h3>
        <p class="text-gray-500 text-sm mb-1">Remove enquiry from</p>
        <p class="font-black text-gray-800 text-lg mb-6" x-text="name"></p>
        <form method="POST" class="flex gap-3">
            <input type="hidden" name="delete_id" :value="lid">
            <button type="button" @click="show = false"
                    class="flex-1 py-3 rounded-2xl font-black text-gray-600 bg-gray-100 hover:bg-gray-200 transition-all text-sm">Cancel</button>
            <button type="submit"
                    class="flex-1 py-3 rounded-2xl font-black text-white bg-rose-500 hover:bg-rose-600 transition-all text-sm">Delete</button>
        </form>
    </div>
</div>

</div>

<script>
function confirmDeleteLead(id, name) {
    const modal = document.getElementById('deleteLeadModal');
    modal._x_dataStack[0].lid  = id;
    modal._x_dataStack[0].name = name;
    modal._x_dataStack[0].show = true;
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
