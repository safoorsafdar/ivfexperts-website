<?php
$pageTitle = "Ultrasound Templates";
require_once __DIR__ . '/includes/auth.php';

$error = '';
$success = '';

// Handle Add/Edit Template
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_template'])) {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $template_id = intval($_POST['template_id'] ?? 0);

    if (empty($title) || empty($body)) {
        $error = "Title and Template Content are required.";
    }
    else {
        if ($template_id > 0) {
            $stmt = $conn->prepare("UPDATE ultrasound_templates SET title=?, body=? WHERE id=?");
            if ($stmt) {
                $stmt->bind_param("ssi", $title, $body, $template_id);
                $stmt->execute();
                $success = "Template updated successfully.";
            }
        }
        else {
            $stmt = $conn->prepare("INSERT INTO ultrasound_templates (title, body) VALUES (?, ?)");
            if ($stmt) {
                $stmt->bind_param("ss", $title, $body);
                $stmt->execute();
                $success = "Template saved successfully.";
            }
        }
    }
}

// Handle Delete (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM ultrasound_templates WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = "Template deleted successfully.";
        }
    }
}

// Fetch Templates
$templates = [];
try {
    $res = $conn->query("SELECT * FROM ultrasound_templates ORDER BY title ASC");
    if ($res) {
        while ($row = $res->fetch_assoc())
            $templates[] = $row;
    }
}
catch (Exception $e) {
}

// Include TinyMCE
include __DIR__ . '/includes/header.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: "#template_body",
    plugins: "lists link table code",
    toolbar: "undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table code",
    menubar: false,
    height: 400
  });
</script>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    <!-- List of Templates -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h3 class="font-bold text-gray-800"><i class="fa-solid fa-list text-teal-600 mr-2"></i> Saved Templates</h3>
            <span class="text-xs text-gray-500 bg-white px-3 py-1 rounded-full border border-gray-200"><?php echo count($templates); ?> templates</span>
        </div>
        
        <div class="p-6">
            <?php if (empty($templates)): ?>
                <div class="text-center py-8 text-gray-400 bg-gray-50 rounded-xl border border-gray-100 border-dashed">
                    No templates created yet.
                </div>
            <?php
else: ?>
                <div class="space-y-3">
                    <?php foreach ($templates as $t): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-teal-300 transition-colors flex justify-between items-start bg-gray-50">
                            <div>
                                <h4 class="font-bold text-gray-800"><?php echo esc($t['title']); ?></h4>
                                <div class="text-xs text-gray-500 mt-1 truncate w-64 md:w-80">
                                    <?php echo esc(strip_tags($t['body'])); ?>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <button onclick="editTemplate(<?php echo $t['id']; ?>, '<?php echo addslashes(htmlspecialchars_decode($t['title'], ENT_QUOTES)); ?>', `<?php echo addslashes(htmlspecialchars_decode($t['body'], ENT_QUOTES)); ?>`)" class="bg-white border border-gray-300 hover:bg-teal-50 hover:text-teal-700 px-3 py-1.5 rounded text-xs font-medium transition-colors">
                                    Edit
                                </button>
                                <form method="POST" onsubmit="return confirm('Delete this template permanently?');">
                                    <input type="hidden" name="delete_id" value="<?php echo $t['id']; ?>">
                                    <button type="submit" class="w-full bg-white border border-red-200 text-red-400 hover:bg-red-50 hover:text-red-700 px-3 py-1.5 rounded text-[10px] font-medium transition-colors cursor-pointer">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php
    endforeach; ?>
                </div>
            <?php
endif; ?>
        </div>
    </div>

    <!-- Create/Edit Form -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-6">
        <div class="px-6 py-4 border-b border-gray-100 bg-teal-900 text-white flex justify-between items-center">
            <h3 class="font-bold flex items-center gap-2">
                <i class="fa-solid fa-file-pen text-teal-300"></i> <span id="form_title">Create New Template</span>
            </h3>
            <button type="button" onclick="resetForm()" class="text-xs bg-teal-800 hover:bg-teal-700 px-2 py-1 rounded transition-colors" id="btn_new" style="display:none;">
                + New
            </button>
        </div>
        <div class="p-6">
            <?php if (!empty($error)): ?>
                <div class="text-sm text-red-600 bg-red-50 p-3 rounded-lg mb-4 border border-red-100 flex gap-2"><i class="fa-solid fa-circle-exclamation mt-0.5"></i> <?php echo esc($error); ?></div>
            <?php
endif; ?>
            <?php if (!empty($success)): ?>
                <div class="text-sm text-emerald-700 bg-emerald-50 p-3 rounded-lg mb-4 border border-emerald-100 flex gap-2"><i class="fa-solid fa-circle-check mt-0.5"></i> <?php echo esc($success); ?></div>
            <?php
endif; ?>

            <form method="POST" id="template_form">
                <input type="hidden" name="template_id" id="template_id" value="0">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Template Title *</label>
                    <input type="text" name="title" id="title" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-teal-500 focus:border-teal-500" placeholder="e.g. Penile Doppler Assessment" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Template Content *</label>
                    <textarea name="body" id="template_body" placeholder="Write HTML template here..."></textarea>
                    <p class="text-xs text-gray-400 mt-2">Use the editor to format tables and text. Variables like [PATIENT_NAME] or [DOB] can be used manually.</p>
                </div>
                <div class="flex justify-end mt-6">
                    <button type="submit" name="save_template" id="btn_save" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-2.5 px-6 rounded-lg transition-colors shadow-sm w-full">
                        Save Template
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function editTemplate(id, title, body) {
    document.getElementById('template_id').value = id;
    document.getElementById('title').value = title;
    
    if(tinymce.get('template_body')) {
        tinymce.get('template_body').setContent(body);
    } else {
        document.getElementById('template_body').value = body;
    }
    
    document.getElementById('form_title').innerText = "Edit Template";
    document.getElementById('btn_save').innerText = "Update Template";
    document.getElementById('btn_new').style.display = "block";
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('template_id').value = 0;
    document.getElementById('title').value = '';
    
    if(tinymce.get('template_body')) {
        tinymce.get('template_body').setContent('');
    } else {
        document.getElementById('template_body').value = '';
    }
    
    document.getElementById('form_title').innerText = "Create New Template";
    document.getElementById('btn_save').innerText = "Save Template";
    document.getElementById('btn_new').style.display = "none";
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
