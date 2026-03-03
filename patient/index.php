<?php
session_start();

// Already logged in → go to dashboard
if (isset($_SESSION['portal_patient_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once dirname(__DIR__) . '/4me/config/db.php';
require_once __DIR__ . '/includes/rate_limit.php';
$error = '';

// Preserve QR redirect across login flow
if (!empty($_GET['redirect_hash'])) {
    $_SESSION['portal_redirect_hash'] = preg_replace('/[^a-f0-9]/', '', $_GET['redirect_hash']);
    $_SESSION['portal_redirect_type'] = in_array($_GET['type'] ?? '', ['rx','sa','usg','receipt']) ? $_GET['type'] : 'rx';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_rate_limit('login_attempts')) {
        $error = "Too many attempts. Please wait a few minutes and try again.";
    } else {
        $phone_mr   = trim($_POST['phone_mr'] ?? '');
    $cnic_clean = preg_replace('/[^0-9]/', '', $_POST['cnic'] ?? '');

    if (empty($phone_mr) || empty($cnic_clean)) {
        $error = "Please fill in both fields.";
    } else {
        try {
            $stmt = $conn->prepare(
                "SELECT id FROM patients
                 WHERE ((phone = ? OR mr_number = ?) AND REPLACE(cnic,'-','') = ?)
                    OR ((spouse_phone = ? OR mr_number = ?) AND REPLACE(spouse_cnic,'-','') = ?)
                 LIMIT 1"
            );
            $stmt->bind_param('ssssss', $phone_mr, $phone_mr, $cnic_clean, $phone_mr, $phone_mr, $cnic_clean);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();

            if ($row) {
                $_SESSION['portal_patient_id'] = $row['id'];
                if (!empty($_SESSION['portal_redirect_hash'])) {
                    $h = $_SESSION['portal_redirect_hash'];
                    $t = $_SESSION['portal_redirect_type'];
                    unset($_SESSION['portal_redirect_hash'], $_SESSION['portal_redirect_type']);
                    header("Location: verify.php?hash={$h}&type={$t}");
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            } else {
                $error = "Details not found. Please check your Phone/MR Number and CNIC.";
            }
        } catch (Exception $e) {
            $error = "System error. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Portal — IVF Experts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; }
        .gradient-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #1e3a5f 100%);
        }
        .glass-card {
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .glass-input {
            background: rgba(255,255,255,0.07);
            border: 1.5px solid rgba(255,255,255,0.12);
            color: white;
            transition: all 0.2s;
        }
        .glass-input::placeholder { color: rgba(255,255,255,0.3); }
        .glass-input:focus {
            background: rgba(255,255,255,0.12);
            border-color: rgba(99,102,241,0.7);
            outline: none;
            box-shadow: 0 0 0 4px rgba(99,102,241,0.15);
        }
        .scan-bar { animation: scan 2.5s ease-in-out infinite; }
        @keyframes scan {
            0%, 100% { top: 4px; opacity: 0.5; }
            50% { top: calc(100% - 6px); opacity: 1; }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4 md:p-8">

    <!-- Ambient glow blobs -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute -top-32 -right-32 w-80 h-80 bg-indigo-600/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-32 -left-32 w-80 h-80 bg-purple-700/20 rounded-full blur-3xl"></div>
    </div>

    <div class="relative z-10 w-full max-w-4xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-10 items-center">

            <!-- LOGIN CARD (3 cols) -->
            <div class="lg:col-span-3 glass-card rounded-3xl p-8 md:p-10 shadow-2xl">

                <!-- Brand Mark -->
                <div class="flex items-center gap-3 mb-10">
                    <div class="w-11 h-11 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-900/50">
                        <i class="fa-solid fa-heart-pulse text-white text-lg"></i>
                    </div>
                    <div>
                        <span class="font-black text-xl text-white tracking-tight">IVF<span class="text-indigo-400">EXPERTS</span></span>
                        <div class="text-[9px] font-black text-white/30 uppercase tracking-[0.25em]">Patient Portal</div>
                    </div>
                </div>

                <!-- Heading -->
                <h1 class="text-2xl font-black text-white mb-1 leading-snug">
                    Access your records,<br>
                    <span class="text-indigo-400">securely.</span>
                </h1>
                <p class="text-white/40 text-sm font-medium mb-8">Prescriptions, lab results, scan reports & billing — all in one place.</p>

                <!-- Error Message -->
                <?php if ($error): ?>
                <div class="flex items-center gap-2.5 bg-rose-500/15 border border-rose-500/25 text-rose-300 px-4 py-3 rounded-2xl text-sm font-bold mb-6">
                    <i class="fa-solid fa-circle-exclamation shrink-0 text-rose-400"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-[9px] font-black text-white/35 uppercase tracking-[0.2em] mb-2.5">
                            Mobile Number or MR Number
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-indigo-400/70 text-sm"></i>
                            <input type="text"
                                   name="phone_mr"
                                   value="<?php echo htmlspecialchars($_POST['phone_mr'] ?? ''); ?>"
                                   autofocus
                                   placeholder="03XX-XXXXXXX or IVF-XXXXXX"
                                   class="glass-input w-full pl-11 pr-5 py-4 rounded-2xl text-sm font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[9px] font-black text-white/35 uppercase tracking-[0.2em] mb-2.5">
                            CNIC Number
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-indigo-400/70 text-sm"></i>
                            <input type="text"
                                   name="cnic"
                                   value="<?php echo htmlspecialchars($_POST['cnic'] ?? ''); ?>"
                                   placeholder="XXXXX-XXXXXXX-X"
                                   maxlength="15"
                                   oninput="formatCNIC(this)"
                                   class="glass-input w-full pl-11 pr-5 py-4 rounded-2xl text-sm font-bold font-mono">
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-black py-4 rounded-2xl transition-all shadow-xl shadow-indigo-900/50 active:scale-[0.98] flex items-center justify-center gap-2.5 text-sm mt-2">
                        <i class="fa-solid fa-shield-halved"></i> Access My Records
                    </button>
                </form>

                <!-- QR Alternative Note -->
                <div class="mt-6 glass-card rounded-2xl p-4 flex items-center gap-4">
                    <!-- Animated QR scanner icon -->
                    <div class="relative w-9 h-9 shrink-0 border border-indigo-400/40 rounded-lg overflow-hidden">
                        <div class="absolute inset-0 grid grid-cols-3 grid-rows-3 gap-[2px] p-[3px]">
                            <div class="bg-white/50 rounded-[1px]"></div><div></div><div class="bg-white/50 rounded-[1px]"></div>
                            <div></div><div class="bg-white/25 rounded-[1px]"></div><div></div>
                            <div class="bg-white/50 rounded-[1px]"></div><div></div><div class="bg-white/50 rounded-[1px]"></div>
                        </div>
                        <div class="scan-bar absolute left-0 right-0 h-[2px] bg-indigo-400/90 rounded-full shadow-sm shadow-indigo-400"></div>
                    </div>
                    <div>
                        <div class="text-white/70 text-xs font-black">Scan a QR Code</div>
                        <div class="text-white/35 text-[10px] font-medium mt-0.5 leading-snug">Find the QR at the bottom of any printed report. Scan → enter CNIC → view instantly.</div>
                    </div>
                </div>

                <p class="text-center text-white/15 text-[9px] font-bold mt-6 uppercase tracking-widest">
                    Secure &nbsp;·&nbsp; Private &nbsp;·&nbsp; <?php echo date('Y'); ?>
                </p>

            </div>

            <!-- RIGHT COLUMN: Features (2 cols) -->
            <div class="hidden lg:block lg:col-span-2">
                <div class="space-y-4">
                    <?php
                    $features = [
                        ['icon' => 'fa-prescription-bottle-medical', 'c' => 'indigo',  'title' => 'Prescriptions',   'desc' => 'Digital Rx with QR verification'],
                        ['icon' => 'fa-vials',                        'c' => 'teal',    'title' => 'Lab Results',     'desc' => 'Real-time blood test results'],
                        ['icon' => 'fa-image',                        'c' => 'emerald', 'title' => 'Scan Reports',   'desc' => 'Ultrasound & follicular monitoring'],
                        ['icon' => 'fa-microscope',                   'c' => 'sky',     'title' => 'Semen Analysis', 'desc' => 'WHO 6th edition andrology reports'],
                        ['icon' => 'fa-receipt',                      'c' => 'amber',   'title' => 'Billing',        'desc' => 'Full payment & receipt history'],
                    ];
                    foreach ($features as $f):
                    ?>
                    <div class="glass-card rounded-2xl p-4 flex items-center gap-4 hover:border-<?php echo $f['c']; ?>-500/30 transition-colors">
                        <div class="w-10 h-10 bg-<?php echo $f['c']; ?>-500/20 rounded-xl flex items-center justify-center shrink-0">
                            <i class="fa-solid <?php echo $f['icon']; ?> text-<?php echo $f['c']; ?>-400 text-base"></i>
                        </div>
                        <div>
                            <div class="text-white/90 font-black text-sm"><?php echo $f['title']; ?></div>
                            <div class="text-white/35 text-[10px] font-medium mt-0.5"><?php echo $f['desc']; ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

    <script>
    function formatCNIC(el) {
        let v = el.value.replace(/\D/g, '').slice(0, 13);
        if (v.length > 12)      v = v.slice(0,5) + '-' + v.slice(5,12) + '-' + v.slice(12);
        else if (v.length > 5)  v = v.slice(0,5) + '-' + v.slice(5);
        el.value = v;
    }
    </script>

</body>
</html>
