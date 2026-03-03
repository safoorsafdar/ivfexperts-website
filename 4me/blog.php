<?php
$pageTitle = "Blog / Articles Manager";
require_once __DIR__ . '/includes/auth.php';

$success = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'saved')
        $success = "Article saved successfully.";
    if ($_GET['msg'] === 'published')
        $success = "Article published!";
}

// Handle Delete (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = "Article deleted.";
    }
}

// Handle Publish Toggle
if (isset($_GET['publish'])) {
    $id = intval($_GET['publish']);
    $conn->query("UPDATE blog_posts SET status = 'Published', published_at = NOW() WHERE id = $id");
    header("Location: blog.php?msg=published");
    exit;
}
if (isset($_GET['unpublish'])) {
    $id = intval($_GET['unpublish']);
    $conn->query("UPDATE blog_posts SET status = 'Draft' WHERE id = $id");
    header("Location: blog.php");
    exit;
}

// Fetch posts
$posts = [];
try {
    $res = $conn->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
    if ($res) {
        while ($row = $res->fetch_assoc())
            $posts[] = $row;
    }
}
catch (Exception $e) {
}

include __DIR__ . '/includes/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 border-l-4 border-orange-500 pl-3">Blog / Articles</h1>
        <p class="text-gray-500 text-sm mt-1">Write and publish medical articles, journals, and blog posts.</p>
    </div>
    <a href="blog_add.php" class="bg-orange-600 hover:bg-orange-700 text-white px-5 py-2.5 rounded-lg shadow-sm text-sm font-medium transition-colors flex items-center gap-2">
        <i class="fa-solid fa-pen-fancy"></i> Write New Article
    </a>
</div>

<?php if ($success): ?>
<div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl mb-6 border border-emerald-100 flex items-center gap-2">
    <i class="fa-solid fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
</div>
<?php
endif; ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="p-4 font-medium border-b border-gray-100">Title</th>
                    <th class="p-4 font-medium border-b border-gray-100 w-28">Category</th>
                    <th class="p-4 font-medium border-b border-gray-100 w-24">Status</th>
                    <th class="p-4 font-medium border-b border-gray-100 w-32">Date</th>
                    <th class="p-4 font-medium border-b border-gray-100 w-28 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-sm">
                <?php if (empty($posts)): ?>
                <tr>
                    <td colspan="5" class="p-8 text-center text-gray-400 font-medium">
                        <i class="fa-solid fa-newspaper text-3xl mb-3 block text-gray-300"></i>
                        No articles yet. Click "Write New Article" to get started.
                    </td>
                </tr>
                <?php
else:
    foreach ($posts as $p): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="p-4">
                        <div class="font-bold text-gray-900"><?php echo htmlspecialchars($p['title']); ?></div>
                        <div class="text-xs text-gray-400 mt-0.5">
                            /blog/<?php echo htmlspecialchars($p['slug']); ?>
                        </div>
                    </td>
                    <td class="p-4 text-xs text-gray-600"><?php echo htmlspecialchars($p['category']); ?></td>
                    <td class="p-4">
                        <?php if ($p['status'] === 'Published'): ?>
                            <span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full text-xs font-bold border border-emerald-200">Published</span>
                        <?php
        else: ?>
                            <span class="bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full text-xs font-bold border border-gray-200">Draft</span>
                        <?php
        endif; ?>
                    </td>
                    <td class="p-4 text-xs text-gray-500">
                        <?php echo date('d M Y', strtotime($p['created_at'])); ?>
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="blog_add.php?edit=<?php echo $p['id']; ?>" class="text-indigo-600 hover:text-indigo-800 text-sm"><i class="fa-solid fa-edit"></i></a>
                            <?php if ($p['status'] === 'Draft'): ?>
                                <a href="?publish=<?php echo $p['id']; ?>" class="text-emerald-600 hover:text-emerald-800 text-sm" title="Publish"><i class="fa-solid fa-rocket"></i></a>
                            <?php
        else: ?>
                                <a href="?unpublish=<?php echo $p['id']; ?>" class="text-amber-600 hover:text-amber-800 text-sm" title="Unpublish"><i class="fa-solid fa-eye-slash"></i></a>
                            <?php
        endif; ?>
                            <a href="/blog/<?php echo htmlspecialchars($p['slug']); ?>" target="_blank" class="text-gray-400 hover:text-gray-600 text-sm"><i class="fa-solid fa-external-link"></i></a>
                            
                            <form method="POST" onsubmit="return confirm('Delete this article?')" class="inline">
                                <input type="hidden" name="delete_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" class="text-red-400 hover:text-red-600 text-sm"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php
    endforeach;
endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
