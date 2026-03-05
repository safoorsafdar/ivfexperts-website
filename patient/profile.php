<?php
require_once __DIR__ . '/includes/auth.php';
require_once dirname(__DIR__) . '/4me/config/db.php';

$patient_id = intval($_SESSION['portal_patient_id']);

// Fetch comprehensive patient details
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if (!$patient) {
    die("Patient record not found.");
}

// Mask CNIC for privacy
$cnic = $patient['cnic'] ?? '';
$masked_cnic = !empty($cnic) ? substr($cnic, 0, 5) . '-XXXXXXX-' . substr($cnic, -1) : 'Not Provided';

$page_title = "My Profile";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> — IVF Experts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .gradient-bg { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #1e3a5f 100%); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen font-sans text-slate-900 pb-20 lg:pb-0">

    <!-- Header -->
    <header class="gradient-bg text-white pb-12 pt-6 px-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur">
                    <i class="fa-solid fa-user-circle text-indigo-300 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-black tracking-tight">Personal Profile</h1>
                    <p class="text-[10px] text-indigo-300 uppercase tracking-widest font-bold">Patient Information</p>
                </div>
            </div>
            <a href="dashboard.php" class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto -mt-6 px-4">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
            <!-- Alert -->
            <div class="bg-amber-50 border-b border-amber-100 px-6 py-3 flex items-center gap-3">
                <i class="fa-solid fa-circle-info text-amber-500"></i>
                <p class="text-xs text-amber-800 font-medium">To update your personal details, please contact the clinic reception.</p>
            </div>

            <div class="p-8 space-y-10">
                <!-- Section: Identity -->
                <section>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Primary Identity
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Full Name</label>
                            <div class="font-black text-slate-800 text-lg"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">MR Number</label>
                            <div class="font-mono font-black text-indigo-600 bg-indigo-50 px-3 py-1 rounded-lg inline-block"><?php echo htmlspecialchars($patient['mr_number']); ?></div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">CNIC (Identity Card)</label>
                            <div class="font-black text-slate-800"><?php echo htmlspecialchars($masked_cnic); ?></div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Gender / Blood Group</label>
                            <div class="font-black text-slate-800"><?php echo htmlspecialchars($patient['gender'] ?? 'Not Specified'); ?> • <?php echo htmlspecialchars($patient['blood_group'] ?? 'N/A'); ?></div>
                        </div>
                    </div>
                </section>

                <hr class="border-slate-100">

                <!-- Section: Contact -->
                <section>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Contact Details
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Phone Number</label>
                            <div class="font-black text-slate-800"><?php echo htmlspecialchars($patient['phone']); ?></div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Email Address</label>
                            <div class="font-black text-slate-800"><?php echo htmlspecialchars($patient['email'] ?: 'No email on record'); ?></div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Residential Address</label>
                            <div class="font-black text-slate-800"><?php echo htmlspecialchars($patient['address'] ?: 'No address on record'); ?></div>
                        </div>
                    </div>
                </section>

                <hr class="border-slate-100">

                <!-- Section: Partner -->
                <section>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span> Spouse / Partner Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Spouse Name</label>
                            <div class="font-black text-slate-800"><?php echo htmlspecialchars($patient['spouse_name'] ?: 'Not Linked'); ?></div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Spouse Phone</label>
                            <div class="font-black text-slate-800"><?php echo htmlspecialchars($patient['spouse_phone'] ?: 'N/A'); ?></div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="bg-slate-50 p-8 border-t border-slate-100 text-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Patient Portal — IVF Experts</p>
                <div class="flex justify-center gap-4">
                    <a href="dashboard.php" class="text-indigo-600 font-bold text-xs hover:underline">Return to Records</a>
                    <span class="text-slate-300">|</span>
                    <a href="dashboard.php?logout=1" class="text-rose-500 font-bold text-xs hover:underline">Logout Securely</a>
                </div>
            </div>
        </div>
    </main>

    <!-- Navigation (Mobile) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 px-6 py-3 flex justify-around items-center z-50">
        <a href="dashboard.php" class="flex flex-col items-center gap-1 text-slate-400">
            <i class="fa-solid fa-house-chimney text-lg"></i>
            <span class="text-[9px] font-black uppercase">Home</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center gap-1 text-indigo-600">
            <i class="fa-solid fa-user text-lg"></i>
            <span class="text-[9px] font-black uppercase">Profile</span>
        </a>
        <a href="dashboard.php?logout=1" class="flex flex-col items-center gap-1 text-slate-400">
            <i class="fa-solid fa-right-from-bracket text-lg"></i>
            <span class="text-[9px] font-black uppercase">Logout</span>
        </a>
    </nav>

</body>
</html>
