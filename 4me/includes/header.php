<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — IVF Experts EMR' : 'IVF Experts EMR'; ?></title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS via CDN (required: dynamic PHP class composition can't be statically compiled) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdfa', 100: '#ccfbf1', 200: '#99f6e4',
                            300: '#5eead4', 400: '#2dd4bf', 500: '#14b8a6',
                            600: '#0d9488', 700: '#0f766e', 800: '#115e59',
                            900: '#134e4a', 950: '#042f2e',
                        },
                    },
                    boxShadow: {
                        'premium': '0 10px 40px -10px rgba(0, 0, 0, 0.1)',
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js (self-hosted — no CDN) -->
    <script defer src="/4me/assets/js/alpine.min.js"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <style>
        [x-cloak] { display: none !important; }
        
        /* Premium Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .text-gradient {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hover-lift {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        /* Form validation feedback */
        input:invalid:not(:placeholder-shown),
        select:invalid:not(:placeholder-shown),
        textarea:invalid:not(:placeholder-shown) {
            border-color: #fca5a5 !important;
            background-color: #fff5f5 !important;
        }
        .field-error {
            color: #dc2626;
            font-size: 0.7rem;
            font-weight: 700;
            margin-top: 4px;
        }

        /* ── Global UI Component Classes ──────────────────────────────── */
        .btn-primary   { display:inline-flex; align-items:center; gap:6px; padding:8px 18px; border-radius:12px; font-size:13px; font-weight:700; color:#fff; background:#0d9488; border:none; cursor:pointer; text-decoration:none; transition:all .15s; }
        .btn-primary:hover   { background:#0f766e; }
        .btn-secondary { display:inline-flex; align-items:center; gap:6px; padding:8px 18px; border-radius:12px; font-size:13px; font-weight:700; color:#374151; background:#f3f4f6; border:none; cursor:pointer; text-decoration:none; transition:all .15s; }
        .btn-secondary:hover { background:#e5e7eb; }
        .btn-danger    { display:inline-flex; align-items:center; gap:6px; padding:8px 18px; border-radius:12px; font-size:13px; font-weight:700; color:#fff; background:#e11d48; border:none; cursor:pointer; text-decoration:none; transition:all .15s; }
        .btn-danger:hover    { background:#be123c; }
        .btn-sm { padding:5px 12px; font-size:11px; }

        .badge-green  { display:inline-flex; align-items:center; padding:2px 8px; border-radius:9999px; font-size:10px; font-weight:700; background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
        .badge-red    { display:inline-flex; align-items:center; padding:2px 8px; border-radius:9999px; font-size:10px; font-weight:700; background:#fff1f2; color:#9f1239; border:1px solid #fecdd3; }
        .badge-blue   { display:inline-flex; align-items:center; padding:2px 8px; border-radius:9999px; font-size:10px; font-weight:700; background:#f0f9ff; color:#0c4a6e; border:1px solid #bae6fd; }
        .badge-amber  { display:inline-flex; align-items:center; padding:2px 8px; border-radius:9999px; font-size:10px; font-weight:700; background:#fffbeb; color:#92400e; border:1px solid #fde68a; }
        .badge-gray   { display:inline-flex; align-items:center; padding:2px 8px; border-radius:9999px; font-size:10px; font-weight:700; background:#f9fafb; color:#374151; border:1px solid #e5e7eb; }
        .badge-teal   { display:inline-flex; align-items:center; padding:2px 8px; border-radius:9999px; font-size:10px; font-weight:700; background:#f0fdfa; color:#115e59; border:1px solid #99f6e4; }
        .badge-violet { display:inline-flex; align-items:center; padding:2px 8px; border-radius:9999px; font-size:10px; font-weight:700; background:#f5f3ff; color:#4c1d95; border:1px solid #ddd6fe; }

        .card { background:#fff; border-radius:16px; border:1px solid #f3f4f6; box-shadow:0 1px 3px rgba(0,0,0,.05); overflow:hidden; }
        .card-header { padding:16px 24px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between; }

        /* Standard data table */
        .data-table { width:100%; border-collapse:collapse; }
        .data-table th { padding:10px 16px; text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; background:#f9fafb; border-bottom:1px solid #f3f4f6; }
        .data-table td { padding:12px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f9fafb; vertical-align:middle; }
        .data-table tbody tr:hover { background:#fafafa; }
        .data-table tbody tr:last-child td { border-bottom:none; }

        /* Empty state */
        .empty-state { padding:80px 24px; text-align:center; }
        .empty-state i { font-size:48px; color:#e5e7eb; margin-bottom:16px; display:block; }
        .empty-state h3 { font-size:16px; font-weight:700; color:#9ca3af; }
        .empty-state p  { font-size:13px; color:#d1d5db; margin-top:4px; }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden" x-data="{ sidebarOpen: true }">

    <!-- Include Sidebar -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- Top Navbar -->
        <header class="glass-panel border-b border-gray-200/80 h-20 flex items-center justify-between px-8 shrink-0 z-10 shadow-sm">
            <div class="flex items-center gap-6">
                <button @click="sidebarOpen = !sidebarOpen" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 text-gray-400 hover:text-brand-600 hover:bg-brand-50 transition-all border border-gray-100">
                    <i :class="sidebarOpen ? 'fa-solid fa-indent' : 'fa-solid fa-outdent'" class="text-lg"></i>
                </button>
                <div class="h-8 w-px bg-gray-200 hidden sm:block"></div>
                <h2 class="text-xl font-black text-gray-800 tracking-tight hidden sm:block">
                    <?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard Overview'; ?>
                </h2>
            </div>
            
            <div class="flex items-center gap-6">
                <!-- Quick Search Trigger -->
                <button onclick="window.location='patients.php'" class="hidden lg:flex items-center gap-3 px-4 py-2 bg-gray-50 border border-gray-100 rounded-xl text-gray-400 hover:border-brand-200 transition-all group">
                    <i class="fa-solid fa-magnifying-glass text-sm group-hover:text-brand-500"></i>
                    <span class="text-xs font-bold">Search patients...</span>
                    <span class="text-[10px] font-black bg-white border border-gray-200 px-1.5 py-0.5 rounded-md ml-4 tracking-tighter shadow-sm">⌘K</span>
                </button>

                <div class="flex items-center gap-3 group cursor-pointer">
                    <div class="text-right hidden md:block">
                        <div class="text-xs font-black text-gray-900 leading-none"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Clinical Lead'); ?></div>
                        <div class="text-[10px] font-bold text-brand-600 uppercase tracking-widest mt-1">Administrator</div>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-brand-600 to-brand-400 p-0.5 shadow-lg shadow-brand-100 transform group-hover:scale-105 transition-transform">
                        <div class="w-full h-full rounded-[14px] bg-white flex items-center justify-center text-brand-700 font-bold overflow-hidden">
                            <i class="fa-solid fa-user-doctor text-brand-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="h-8 w-px bg-gray-200"></div>
                
                <a href="logout.php" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-all border border-gray-100 group" title="Sign Out">
                    <i class="fa-solid fa-power-off text-lg group-hover:rotate-12 transition-transform"></i>
                </a>
            </div>
        </header>

        <!-- Main Scrollable Area -->
        <main class="flex-1 overflow-y-auto bg-[#f8fafc] p-8 custom-scrollbar">

        <?php
// ─── Global Flash Toast ─────────────────────────────────────
$flash = get_flash();
if ($flash):
    $fc = [
        'success' => 'bg-emerald-50 border-emerald-300 text-emerald-800',
        'error' => 'bg-rose-50 border-rose-300 text-rose-800',
        'warning' => 'bg-amber-50 border-amber-300 text-amber-800',
        'info' => 'bg-sky-50 border-sky-300 text-sky-800',
    ];
    $fi = [
        'success' => 'fa-circle-check text-emerald-500',
        'error' => 'fa-circle-exclamation text-rose-500',
        'warning' => 'fa-triangle-exclamation text-amber-500',
        'info' => 'fa-circle-info text-sky-500',
    ];
    $c = $fc[$flash['type']] ?? $fc['info'];
    $i = $fi[$flash['type']] ?? $fi['info'];
?>
        <div x-data="{show:true}" x-show="show" x-cloak
             x-init="setTimeout(()=>show=false,6000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="fixed top-6 right-6 z-[9999] max-w-sm w-full">
            <div class="flex items-start gap-3 px-5 py-4 rounded-2xl border shadow-2xl shadow-black/10 <?php echo $c; ?>">
                <i class="fa-solid <?php echo $i; ?> text-lg mt-0.5 shrink-0"></i>
                <div class="flex-1 text-sm font-semibold leading-snug"><?php echo esc($flash['msg']); ?></div>
                <button @click="show=false" class="shrink-0 opacity-50 hover:opacity-100 transition-opacity">
                    <i class="fa-solid fa-times text-xs"></i>
                </button>
            </div>
        </div>
        <?php
endif; ?>