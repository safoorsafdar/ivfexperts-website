<!-- Sidebar — Light Mode -->
<aside :class="sidebarOpen ? 'w-64' : 'w-0 -ml-64 sm:ml-0 sm:w-20'"
       class="bg-white text-slate-600 flex flex-col transition-all duration-300 z-20 shrink-0 h-screen overflow-y-auto overflow-x-hidden border-r border-slate-100 shadow-sm">

    <!-- Brand -->
    <div class="h-20 flex items-center px-5 border-b border-slate-100 shrink-0">
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="w-9 h-9 bg-gradient-to-tr from-teal-600 to-teal-400 rounded-xl flex items-center justify-center shrink-0 shadow-md shadow-teal-100">
                <i class="fa-solid fa-dna text-white text-sm"></i>
            </div>
            <div x-show="sidebarOpen" x-transition:enter="transition-all duration-300" x-transition:enter-start="opacity-0 -translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="overflow-hidden">
                <div class="text-slate-800 font-semibold text-sm leading-none tracking-tight">IVF Experts</div>
                <div class="text-teal-500 text-[10px] font-medium uppercase tracking-widest leading-none mt-0.5">Clinical EMR</div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto scrollbar-hide">
        <?php
$current_page = basename(explode('?', $_SERVER['PHP_SELF'])[0]);
$active_map = [
    'patients.php' => ['patients_add.php', 'patients_edit.php', 'patients_view.php'],
    'semen_analyses.php' => ['semen_analyses_add.php'],
    'lab_tests.php' => ['lab_results.php', 'lab_results_add.php'],
    'ultrasounds.php' => ['ultrasounds_add.php'],
    'prescriptions.php' => ['prescriptions_add.php', 'prescriptions_print.php'],
    'procedures.php' => ['procedures_add.php'],
    'financials.php' => ['receipts_add.php', 'expenses_add.php'],
    'inventory.php' => ['inventory_add.php', 'inventory_label.php'],
    'blog.php' => ['blog_add.php'],
    'document_traceability.php' => [],
];
function is_nav_active($item_url, $current_page, $active_map)
{
    if ($item_url === $current_page)
        return true;
    return in_array($current_page, $active_map[$item_url] ?? []);
}
$groups = [
    'CLINICAL' => [
        ['url' => 'index.php', 'icon' => 'fa-gauge-high', 'label' => 'Dashboard'],
        ['url' => 'patients.php', 'icon' => 'fa-users', 'label' => 'Patients Hub'],
        ['url' => 'prescriptions.php', 'icon' => 'fa-prescription', 'label' => 'Prescriptions'],
        ['url' => 'semen_analyses.php', 'icon' => 'fa-flask-vial', 'label' => 'Semen Analysis'],
        ['url' => 'lab_tests.php', 'icon' => 'fa-vials', 'label' => 'Laboratory'],
        ['url' => 'ultrasounds.php', 'icon' => 'fa-image', 'label' => 'Ultrasounds'],
        ['url' => 'procedures.php', 'icon' => 'fa-clipboard-check', 'label' => 'Procedures'],
        ['url' => 'medications.php', 'icon' => 'fa-pills', 'label' => 'Medications'],
    ],
    'ADMINISTRATION' => [
        ['url' => 'financials.php', 'icon' => 'fa-wallet', 'label' => 'Financials'],
        ['url' => 'inventory.php', 'icon' => 'fa-boxes-stacked', 'label' => 'Inventory'],
        ['url' => 'hospitals.php', 'icon' => 'fa-hospital', 'label' => 'Hospitals'],
        ['url' => 'leads.php', 'icon' => 'fa-handshake', 'label' => 'Leads / CRM'],
        ['url' => 'staff.php', 'icon' => 'fa-user-tie', 'label' => 'Staff'],
        ['url' => 'blog.php', 'icon' => 'fa-newspaper', 'label' => 'Clinical Blog'],
        ['url' => 'document_traceability.php', 'icon' => 'fa-shield-halved', 'label' => 'Doc Traceability'],
        ['url' => 'settings.php', 'icon' => 'fa-gear', 'label' => 'System Settings'],
    ],
];
?>

        <?php foreach ($groups as $group_label => $items): ?>
        <div x-show="sidebarOpen" class="px-3 pt-5 pb-1">
            <span class="text-[10px] font-semibold text-slate-300 uppercase tracking-widest"><?php echo $group_label; ?></span>
        </div>
        <div x-show="!sidebarOpen" class="px-2 pt-4 pb-1">
            <div class="border-t border-slate-100"></div>
        </div>

        <?php foreach ($items as $item):
        $active = is_nav_active($item['url'], $current_page, $active_map);
?>
        <a href="<?php echo esc($item['url']); ?>"
           title="<?php echo esc($item['label']); ?>"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150 group relative
                  <?php echo $active
            ? 'bg-teal-50 text-teal-700'
            : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'; ?>">

            <?php if ($active): ?>
            <div class="absolute left-0 w-0.5 h-5 bg-teal-500 rounded-r-full"></div>
            <?php
        endif; ?>

            <i class="fa-solid <?php echo $item['icon']; ?> w-5 text-center text-sm shrink-0
                       <?php echo $active ? 'text-teal-600' : 'text-slate-400 group-hover:text-slate-600 transition-colors'; ?>"></i>

            <span x-show="sidebarOpen"
                  x-transition:enter="transition duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                  class="text-sm font-medium whitespace-nowrap truncate"><?php echo esc($item['label']); ?></span>
        </a>
        <?php
    endforeach;
endforeach; ?>
    </nav>

    <!-- Footer -->
    <div class="p-3 border-t border-slate-100 shrink-0">
        <a href="logout.php"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all group">
            <i class="fa-solid fa-right-from-bracket w-5 text-center text-sm shrink-0"></i>
            <span x-show="sidebarOpen" class="text-sm font-medium">Sign Out</span>
        </a>
        <div x-show="sidebarOpen" class="px-3 pt-2">
            <div class="flex items-center gap-2">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div>
                <div class="text-[10px] text-slate-300 font-medium">EMR Engine v4.0 · Online</div>
            </div>
        </div>
    </div>
</aside>
<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
