<?php
require_once __DIR__ . '/../config/db.php';

// Check if a specific article slug is requested
$slug = isset($_GET['article']) ? trim($_GET['article']) : '';

if (!empty($slug)) {
    // Single article view
    $stmt = $conn->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 'Published'");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $article = $stmt->get_result()->fetch_assoc();

    if (!$article) {
        header("HTTP/1.0 404 Not Found");
        $pageTitle = "Article Not Found | IVF Experts Blog";
        $metaDescription = "The requested article could not be found.";
    }
    else {
        $pageTitle = $article['meta_title'] ?: $article['title'] . ' | IVF Experts Blog';
        $metaDescription = $article['meta_description'] ?: substr(strip_tags($article['content']), 0, 160);
    }
}
else {
    // Blog listing
    $pageTitle = "Blog & Articles | IVF Experts – Dr. Adnan Jabbar";
    $metaDescription = "Evidence-based articles on fertility, IVF, ICSI, male and female infertility, stem cell therapy and reproductive medicine by Dr. Adnan Jabbar.";

    $posts = [];
    try {
        $res = $conn->query("SELECT * FROM blog_posts WHERE status = 'Published' ORDER BY published_at DESC");
        if ($res) {
            while ($row = $res->fetch_assoc())
                $posts[] = $row;
        }
    }
    catch (Exception $e) {
    }
}

$schemaType = 'Article';
$medicalSpecialty = 'Reproductive Medicine';
$breadcrumbs = [
    ['name' => 'Home', 'url' => 'https://ivfexperts.pk/'],
    ['name' => 'Blog', 'url' => 'https://ivfexperts.pk/blog/']
];

$articleSchema = '';
if (!empty($slug) && $article) {
    $breadcrumbs[] = ['name' => htmlspecialchars($article['title']), 'url' => 'https://ivfexperts.pk/blog/' . htmlspecialchars($slug) . '/'];

    // Build BlogPosting JSON-LD schema
    $schemaDescription = !empty($article['excerpt'])
        ? $article['excerpt']
        : substr(strip_tags($article['content']), 0, 160);

    $schemaData = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BlogPosting',
        'headline'        => $article['title'],
        'description'     => $schemaDescription,
        'datePublished'   => date('c', strtotime($article['created_at'])),
        'dateModified'    => date('c', strtotime($article['updated_at'])),
        'author'          => [
            '@type' => 'Person',
            'name'  => 'Dr. Adnan Jabbar',
            'url'   => 'https://ivfexperts.pk/about/',
        ],
        'publisher'       => [
            '@type' => 'Organization',
            'name'  => 'IVF Experts',
            'url'   => 'https://ivfexperts.pk',
        ],
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id'   => 'https://ivfexperts.pk/blog/' . rawurlencode($slug) . '/',
        ],
    ];

    if (!empty($article['featured_image'])) {
        $schemaData['image'] = $article['featured_image'];
    }

    $articleSchema = json_encode($schemaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
}

include __DIR__ . '/../includes/header.php';

if (!empty($slug) && $article):
    // ============== SINGLE ARTICLE VIEW ==============
?>
<?php if (!empty($articleSchema)): ?>
<script type="application/ld+json"><?php echo $articleSchema; ?></script>
<?php endif; ?>
<article class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <div class="mb-8">
        <a href="/blog/" class="text-teal-600 hover:text-teal-700 text-sm font-semibold flex items-center gap-1 mb-4">
            <i class="fa-solid fa-arrow-left"></i> Back to Articles
        </a>
        <div class="flex items-center gap-3 mb-4">
            <span class="bg-teal-50 text-teal-700 text-xs font-bold px-3 py-1 rounded-full border border-teal-200"><?php echo htmlspecialchars($article['category']); ?></span>
            <span class="text-gray-400 text-xs"><?php echo date('d M Y', strtotime($article['published_at'])); ?></span>
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight mb-4"><?php echo htmlspecialchars($article['title']); ?></h1>
        <div class="flex items-center gap-3 text-sm text-gray-500">
            <div class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center text-teal-700 font-bold text-sm">
                <?php echo strtoupper(substr($article['author'], 0, 1)); ?>
            </div>
            <span class="font-medium text-gray-700"><?php echo htmlspecialchars($article['author']); ?></span>
        </div>
    </div>

    <?php if (!empty($article['featured_image'])): ?>
    <div class="mb-10 rounded-2xl overflow-hidden shadow-lg">
        <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-auto object-cover max-h-[500px]">
    </div>
    <?php
    endif; ?>

    <!-- Table of Contents (JS-generated from H2s) -->
    <div id="toc-container" class="hidden mb-8 bg-slate-50 border border-slate-200 rounded-xl p-5">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">In this article</p>
        <ul id="toc-list" class="space-y-1 text-sm text-teal-700 font-medium"></ul>
    </div>

    <!-- Article Content -->
    <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed
        prose-headings:text-gray-900 prose-headings:font-bold
        prose-a:text-teal-600 prose-a:no-underline hover:prose-a:underline
        prose-img:rounded-xl prose-img:shadow-lg
        prose-blockquote:border-teal-500 prose-blockquote:bg-teal-50 prose-blockquote:py-4 prose-blockquote:px-6 prose-blockquote:rounded-r-xl">
        <?php echo $article['content']; ?>
    </div>

    <!-- Tags -->
    <?php if (!empty($article['tags'])): ?>
    <div class="mt-10 pt-6 border-t border-gray-100">
        <div class="flex flex-wrap gap-2">
            <?php foreach (explode(',', $article['tags']) as $tag):
            $tag = trim($tag);
            if (!empty($tag)): ?>
                <span class="bg-gray-100 text-gray-600 text-xs px-3 py-1 rounded-full border border-gray-200 font-medium">#<?php echo htmlspecialchars($tag); ?></span>
            <?php
            endif;
        endforeach; ?>
        </div>
    </div>
    <?php
    endif; ?>

    <!-- Social Share -->
    <div class="mt-8 pt-6 border-t border-gray-100">
        <p class="text-sm font-bold text-gray-500 mb-3">Share this article:</p>
        <div class="flex items-center gap-3">
            <?php
    $share_url = urlencode('https://ivfexperts.pk/blog/' . $article['slug']);
    $share_title = urlencode($article['title']);
?>
            <a href="https://wa.me/?text=<?php echo $share_title . '%20' . $share_url; ?>" target="_blank" class="w-10 h-10 bg-emerald-500 hover:bg-emerald-600 rounded-full flex items-center justify-center text-white transition-colors shadow-sm" title="Share on WhatsApp">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.767 5.766 0 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.767-5.766-.001-3.187-2.575-5.77-5.764-5.771z"/></svg>
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" target="_blank" class="w-10 h-10 bg-blue-600 hover:bg-blue-700 rounded-full flex items-center justify-center text-white transition-colors shadow-sm" title="Share on Facebook">
                <i class="fa-brands fa-facebook-f"></i>
            </a>
            <a href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" class="w-10 h-10 bg-gray-800 hover:bg-gray-900 rounded-full flex items-center justify-center text-white transition-colors shadow-sm" title="Share on X/Twitter">
                <i class="fa-brands fa-x-twitter"></i>
            </a>
            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $share_url; ?>&title=<?php echo $share_title; ?>" target="_blank" class="w-10 h-10 bg-sky-700 hover:bg-sky-800 rounded-full flex items-center justify-center text-white transition-colors shadow-sm" title="Share on LinkedIn">
                <i class="fa-brands fa-linkedin-in"></i>
            </a>
            <button onclick="navigator.clipboard.writeText(decodeURIComponent('<?php echo $share_url; ?>'));this.innerHTML='<i class=\'fa-solid fa-check\'></i>';setTimeout(()=>this.innerHTML='<i class=\'fa-solid fa-link\'></i>',2000);" class="w-10 h-10 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center text-gray-600 transition-colors shadow-sm" title="Copy Link">
                <i class="fa-solid fa-link"></i>
            </button>
        </div>
    </div>

    <!-- Author Bio -->
    <div class="mt-12 pt-8 border-t border-slate-200 flex items-start gap-5">
        <img src="/assets/images/dr-adnan.jpg" alt="Dr. Adnan Jabbar" class="w-16 h-16 rounded-full object-cover flex-shrink-0">
        <div>
            <p class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-1">Written by</p>
            <p class="text-lg font-bold text-slate-900 font-[Outfit]">Dr. Adnan Jabbar</p>
            <p class="text-sm text-slate-600 mt-1">Fertility Specialist &amp; Clinical Embryologist with 15+ years of experience in IVF, ICSI, and reproductive medicine at IVF Experts, Lahore.</p>
            <a href="/about/" class="text-sm text-teal-700 font-semibold hover:underline mt-2 inline-block">Read full profile &rarr;</a>
        </div>
    </div>

    <!-- Consultation CTA -->
    <div class="mt-10 bg-teal-50 border border-teal-200 rounded-2xl p-8 text-center">
        <p class="text-xl font-bold text-slate-900 font-[Outfit] mb-2">Have questions about your fertility journey?</p>
        <p class="text-slate-600 mb-6">Dr. Adnan Jabbar offers free initial consultations. Reach out today on WhatsApp.</p>
        <a href="https://wa.me/923111101483?text=Hi%20Dr.%20Adnan%2C%20I%20read%20your%20article%20and%20have%20questions%20about%20my%20fertility."
           target="_blank" rel="noopener noreferrer"
           class="btn-primary inline-flex items-center gap-2 px-8 py-4">
            <i class="fab fa-whatsapp text-xl"></i>
            Chat with Dr. Adnan on WhatsApp
        </a>
    </div>
</article>

<?php
elseif (!empty($slug) && !$article):
    // 404
?>
<div class="max-w-2xl mx-auto text-center py-24">
    <div class="text-6xl mb-4">📰</div>
    <h1 class="text-3xl font-bold text-gray-900 mb-3">Article Not Found</h1>
    <p class="text-gray-500 mb-6">The article you're looking for doesn't exist or has been removed.</p>
    <a href="/blog/" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i> Browse All Articles
    </a>
</div>

<?php
else:
    // ============== BLOG LISTING VIEW ==============
?>
<div class="max-w-6xl mx-auto px-6 py-12">
    <!-- Hero -->
    <div class="text-center mb-16">
        <div class="inline-flex items-center gap-2 bg-orange-100 text-orange-700 text-xs font-bold px-4 py-1.5 rounded-full mb-6 border border-orange-200 uppercase tracking-wider">
            <i class="fa-solid fa-newspaper"></i> Blog & Articles
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 tracking-tight mb-4">Medical Insights & Research</h1>
        <p class="text-lg text-gray-500 max-w-2xl mx-auto">Evidence-based articles on fertility treatments, reproductive medicine, and the latest advancements in IVF and stem cell therapy.</p>
    </div>

    <!-- Articles Grid -->
    <?php if (empty($posts)): ?>
    <div class="text-center py-16">
        <div class="text-5xl mb-4">📝</div>
        <p class="text-gray-400 font-medium">No articles published yet. Check back soon!</p>
    </div>
    <?php
    else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($posts as $p): ?>
        <a href="/blog/<?php echo htmlspecialchars($p['slug']); ?>/" class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:border-teal-200 transition-all duration-300 hover:-translate-y-1">
            <?php if (!empty($p['featured_image'])): ?>
            <div class="h-48 overflow-hidden">
                <img src="<?php echo htmlspecialchars($p['featured_image']); ?>" alt="<?php echo htmlspecialchars($p['title']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </div>
            <?php
            else: ?>
            <div class="h-48 bg-gradient-to-br from-teal-50 to-teal-100 flex items-center justify-center">
                <i class="fa-solid fa-newspaper text-4xl text-teal-300"></i>
            </div>
            <?php
            endif; ?>
            <div class="p-6">
                <div class="flex items-center gap-2 mb-3">
                    <span class="bg-teal-50 text-teal-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-teal-100 uppercase tracking-wider"><?php echo htmlspecialchars($p['category']); ?></span>
                    <span class="text-gray-400 text-xs"><?php echo date('d M Y', strtotime($p['published_at'])); ?></span>
                </div>
                <h2 class="text-lg font-bold text-gray-900 group-hover:text-teal-700 transition-colors leading-snug mb-2"><?php echo htmlspecialchars($p['title']); ?></h2>
                <p class="text-sm text-gray-500 leading-relaxed line-clamp-3"><?php echo htmlspecialchars($p['excerpt'] ?: substr(strip_tags($p['content']), 0, 120) . '...'); ?></p>
                <div class="mt-4 flex items-center gap-2 text-teal-600 text-sm font-semibold group-hover:gap-3 transition-all">
                    Read Article <i class="fa-solid fa-arrow-right text-xs"></i>
                </div>
            </div>
        </a>
        <?php
        endforeach; ?>
    </div>
    <?php
    endif; ?>
</div>
<?php
endif; ?>

<script>
(function() {
    var content = document.querySelector('.prose');
    if (!content) return;
    var headings = content.querySelectorAll('h2');
    if (headings.length < 2) return;
    var list = document.getElementById('toc-list');
    var container = document.getElementById('toc-container');
    headings.forEach(function(h, i) {
        var id = 'toc-' + i;
        h.id = id;
        var li = document.createElement('li');
        var a = document.createElement('a');
        a.href = '#' + id;
        a.textContent = h.textContent;
        a.className = 'hover:text-teal-900 transition-colors block py-0.5';
        li.appendChild(a);
        list.appendChild(li);
    });
    container.classList.remove('hidden');
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
