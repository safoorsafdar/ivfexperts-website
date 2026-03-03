<!-- Sidebar -->
<aside :class="sidebarOpen ? 'w-64' : 'w-0 -ml-64 sm:ml-0 sm:w-16'"
       class="bg-slate-900 text-slate-300 flex flex-col transition-all duration-300 z-20 shrink-0 h-screen overflow-y-auto overflow-x-hidden shadow-2xl">

    <!-- Brand -->
    <div class="h-16 flex items-center px-4 border-b border-slate-800 shrink-0 bg-slate-950">
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="w-8 h-8 bg-teal-500 rounded-xl flex items-center justify-center shrink-0 shadow-lg shadow-teal-900/50">
                <i class="fa-solid fa-dna text-white text-sm"></i>
            </div>
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="overflow-hidden">
                <div class="text-white font-black text-sm tracking-tight leading-none">IVF Experts</div>
                <div class="text-teal-400 text-[9px] font-black uppercase tracking-widest leading-none mt-0.5">Clinical EMR</div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-2 py-4 space-y-0.5 overflow-y-auto scrollbar-hide">
        <?php
        $current_page = basename(explode('?', $_SERVER['PHP_SELF'])[0]);

        // Active page detection
        $active_map = [
            'patients.php'    => ['patients_add.php', 'patients_edit.php', 'patients_view.php'],
            'semen_analyses.php' => ['semen_analyses_add.php'],
            'lab_tests.php'   => ['lab_results.php', 'lab_results_add.php'],
            'ultrasounds.php' => ['ultrasounds_add.php'],
            'prescriptions.php' => ['prescriptions_add.php', 'prescriptions_print.php'],
            'procedures.php'  => ['procedures_add.php'],
            'financials.php'  => ['receipts_add.php', 'expenses_add.php'],
            'inventory.php'   => ['inventory_add.php', 'inventory_label.php'],
            'blog.php'        => ['blog_add.php'],
        ];

        function is_nav_active($item_url, $current_page, $active_map) {
            if ($item_url === $current_page) return true;
            return in_array($current_page, $active_map[$item_url] ?? []);
        }

        // Group structure
        $groups = [
            'CLINICAL' => [
                ['url' => 'index.php',          'icon' => 'fa-gauge-high',         'label' => 'Dashboard'],
                ['url' => 'patients.php',        'icon' => 'fa-users',              'label' => 'Patients'],
                ['url' => 'prescriptions.php',   'icon' => 'fa-prescription',       'label' => 'Prescriptions'],
                ['url' => 'semen_analyses.php',  'icon' => 'fa-flask-vial',         'label' => 'Semen Analysis'],
                ['url' => 'lab_tests.php',       'icon' => 'fa-vials',              'label' => 'Laboratory'],
                ['url' => 'ultrasounds.php',     'icon' => 'fa-image',              'label' => 'Ultrasounds'],
                ['url' => 'procedures.php',      'icon' => 'fa-clipboard-check',    'label' => 'Procedures'],
                ['url' => 'medications.php',     'icon' => 'fa-pills',              'label' => 'Medications'],
            ],
            'ADMIN' => [
                ['url' => 'financials.php',      'icon' => 'fa-wallet',             'label' => 'Financials'],
                ['url' => 'inventory.php',       'icon' => 'fa-boxes-stacked',      'label' => 'Inventory'],
                ['url' => 'hospitals.php',       'icon' => 'fa-hospital',           'label' => 'Hospitals'],
                ['url' => 'leads.php',           'icon' => 'fa-handshake',          'label' => 'Leads / CRM'],
                ['url' => 'staff.php',           'icon' => 'fa-user-tie',           'label' => 'Staff'],
                ['url' => 'blog.php',            'icon' => 'fa-newspaper',          'label' => 'Blog'],
                ['url' => 'settings.php',        'icon' => 'fa-gear',               'label' => 'Settings'],
            ],
        ];
        ?>

        <?php foreach ($groups as $group_label => $items): ?>

        <!-- Group Label -->
        <div x-show="sidebarOpen" class="px-3 pt-5 pb-1">
            <span class="text-[9px] font-black text-slate-600 uppercase tracking-[0.2em]"><?php echo $group_label; ?></span>
        </div>
        <div x-show="!sidebarOpen" class="px-2 pt-4 pb-1">
            <div class="border-t border-slate-800"></div>
        </div>

        <?php foreach ($items as $item):
            $active = is_nav_active($item['url'], $current_page, $active_map);
        ?>
        <a href="<?php echo esc($item['url']); ?>"
           title="<?php echo esc($item['label']); ?>"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150 group
                  <?php echo $active
                    ? 'bg-teal-600 text-white shadow-lg shadow-teal-900/40'
                    : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
            <i class="fa-solid <?php echo $item['icon']; ?> w-5 text-center text-base shrink-0
                       <?php echo $active ? 'text-white' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span x-show="sidebarOpen"
                  x-transition:enter="transition-opacity duration-100"
                  x-transition:enter-start="opacity-0"
                  x-transition:enter-end="opacity-100"
                  class="text-sm font-bold whitespace-nowrap truncate"><?php echo esc($item['label']); ?></span>
            <?php if ($active): ?>
            <span x-show="sidebarOpen" class="ml-auto w-1.5 h-1.5 bg-teal-300 rounded-full shrink-0"></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>

    <!-- Footer -->
    <div class="p-3 border-t border-slate-800 shrink-0">
        <a href="logout.php"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 transition-all group">
            <i class="fa-solid fa-right-from-bracket w-5 text-center text-base shrink-0 group-hover:scale-110 transition-transform"></i>
            <span x-show="sidebarOpen" class="text-sm font-bold whitespace-nowrap">Sign Out</span>
        </a>
        <div x-show="sidebarOpen" class="px-3 pt-2">
            <div class="text-[9px] font-bold text-slate-700">EMR v3.0 · <?php echo date('Y'); ?></div>
        </div>
    </div>

</aside>

<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
