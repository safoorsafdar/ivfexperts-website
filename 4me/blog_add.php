<?php
$pageTitle = "Write / Edit Article";
require_once __DIR__ . '/includes/auth.php';

$error = '';
$editing = false;
$post = ['id' => 0, 'title' => '', 'slug' => '', 'excerpt' => '', 'content' => '', 'featured_image' => '', 'category' => 'General', 'tags' => '', 'meta_title' => '', 'meta_description' => '', 'status' => 'Draft', 'author' => 'Dr. Adnan Jabbar'];

// Load for editing
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res) {
        $post = $res;
        $editing = true;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_post'])) {
    $id = intval($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $category = trim($_POST['category'] ?? 'General');
    $tags = trim($_POST['tags'] ?? '');
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $status = $_POST['status'] ?? 'Draft';
    $author = trim($_POST['author'] ?? 'Dr. Adnan Jabbar');

    // Auto-generate slug
    if (empty($slug)) {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
        $slug = trim($slug, '-');
    }

    // Auto-generate meta
    if (empty($meta_title))
        $meta_title = $title . ' | IVF Experts Blog';
    if (empty($meta_description))
        $meta_description = substr(strip_tags($content), 0, 160);

    // Handle featured image upload
    $featured_image = $post['featured_image'] ?? '';
    if (!empty($_FILES['featured_image']['name'])) {
        $upload_dir = dirname(__DIR__) . '/uploads/blog/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0775, true);
        $ext = strtolower(pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $filename = $slug . '-' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_dir . $filename)) {
                $featured_image = '/uploads/blog/' . $filename;
            }
        }
    }

    if (empty($title)) {
        $error = "Title is required.";
    }
    else {
        $published_at = ($status === 'Published') ? date('Y-m-d H:i:s') : null;

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE blog_posts SET title=?, slug=?, excerpt=?, content=?, featured_image=?, category=?, tags=?, meta_title=?, meta_description=?, status=?, author=? WHERE id=?");
            $stmt->bind_param("ssssssssssssi", $title, $slug, $excerpt, $content, $featured_image, $category, $tags, $meta_title, $meta_description, $status, $author, $id);
        }
        else {
            $stmt = $conn->prepare("INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, category, tags, meta_title, meta_description, status, author, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssssss", $title, $slug, $excerpt, $content, $featured_image, $category, $tags, $meta_title, $meta_description, $status, $author, $published_at);
        }

        if ($stmt->execute()) {
            header("Location: blog.php?msg=saved");
            exit;
        }
        else {
            $error = "Database error: " . $stmt->error;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- Quill CSS & JS -->
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="blog.php" class="text-sm text-gray-500 hover:text-orange-600 font-medium flex items-center gap-1">
            <i class="fa-solid fa-arrow-left"></i> Back to Blog Manager
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-orange-50/50">
            <h3 class="font-bold text-gray-800"><?php echo $editing ? 'Edit Article' : 'Write New Article'; ?></h3>
        </div>

        <div class="p-6 md:p-8">
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php
endif; ?>

            <form method="POST" enctype="multipart/form-data" onsubmit="document.getElementById('hidden_content').value = quillEditor.root.innerHTML;">
                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">

                <div class="space-y-6">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Article Title *</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 text-lg font-semibold" placeholder="e.g. Understanding IVF Success Rates in 2026">
                    </div>

                    <!-- Slug & Category -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL Slug</label>
                            <input type="text" name="slug" value="<?php echo htmlspecialchars($post['slug']); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 font-mono text-sm" placeholder="auto-generated-from-title">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="category" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 bg-white">
                                <?php foreach (['General', 'IVF', 'Male Infertility', 'Female Infertility', 'Stem Cell', 'Lifestyle', 'Research', 'Case Study'] as $cat): ?>
                                    <option value="<?php echo $cat; ?>" <?php echo($post['category'] === $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                                <?php
endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Excerpt -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Short Excerpt</label>
                        <textarea name="excerpt" rows="2" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm" placeholder="Brief summary shown on the blog listing page..."><?php echo htmlspecialchars($post['excerpt']); ?></textarea>
                    </div>

                    <!-- Content Editor -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Article Content *</label>
                        <div id="blog-editor" style="height:400px;"><?php echo $post['content']; ?></div>
                        <input type="hidden" name="content" id="hidden_content">
                    </div>

                    <!-- Featured Image -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Featured Image</label>
                        <?php if (!empty($post['featured_image'])): ?>
                            <div class="mb-2"><img src="<?php echo htmlspecialchars($post['featured_image']); ?>" class="w-48 h-32 object-cover rounded-lg border"></div>
                        <?php
endif; ?>
                        <input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp" class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm">
                    </div>

                    <!-- Tags -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tags (comma-separated)</label>
                        <input type="text" name="tags" value="<?php echo htmlspecialchars($post['tags']); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm" placeholder="ivf, fertility, infertility, pcos">
                    </div>

                    <!-- SEO Section -->
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                        <h4 class="font-bold text-gray-700 text-sm mb-3"><i class="fa-solid fa-search mr-1"></i> SEO Settings (Auto-generated if left blank)</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Meta Title</label>
                                <input type="text" name="meta_title" value="<?php echo htmlspecialchars($post['meta_title']); ?>" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Meta Description</label>
                                <textarea name="meta_description" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm"><?php echo htmlspecialchars($post['meta_description']); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Publish Settings -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-white">
                                <option value="Draft" <?php echo($post['status'] === 'Draft') ? 'selected' : ''; ?>>📝 Save as Draft</option>
                                <option value="Published" <?php echo($post['status'] === 'Published') ? 'selected' : ''; ?>>🚀 Publish Now</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Author</label>
                            <input type="text" name="author" value="<?php echo htmlspecialchars($post['author']); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm">
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end gap-3">
                    <a href="blog.php" class="px-6 py-3 font-medium text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200">Cancel</a>
                    <button type="submit" name="save_post" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg shadow-orange-200 flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> <?php echo $editing ? 'Update Article' : 'Save Article'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var quillEditor;
document.addEventListener('DOMContentLoaded', function() {
    quillEditor = new Quill('#blog-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['blockquote', 'link', 'image'],
                [{ 'align': [] }],
                ['clean']
            ]
        },
        placeholder: 'Start writing your article here...'
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
