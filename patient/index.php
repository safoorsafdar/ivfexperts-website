<?php
session_start();

// Already logged in → go to dashboard
if (isset($_SESSION['portal_patient_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once dirname(__DIR__) . '/4me/config/db.php';
require_once __DIR__ . '/includes/rate_limit.php';
require_once __DIR__ . '/includes/csrf.php';
$error = '';

// Preserve QR redirect across login flow
if (!empty($_GET['redirect_hash'])) {
    $_SESSION['portal_redirect_hash'] = preg_replace('/[^a-f0-9]/', '', $_GET['redirect_hash']);
    $_SESSION['portal_redirect_type'] = in_array($_GET['type'] ?? '', ['rx', 'sa', 'usg', 'receipt']) ? $_GET['type'] : 'rx';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = "Security token mismatch. Please try again.";
    }
    elseif (!check_rate_limit('login_attempts')) {
        $error = "Too many attempts. Please wait a few minutes and try again.";
    }
    else {
        $phone_mr = trim($_POST['phone_mr'] ?? '');
        $cnic_clean = preg_replace('/[^0-9]/', '', $_POST['cnic'] ?? '');

        if (empty($phone_mr) || empty($cnic_clean)) {
            $error = "Please fill in both fields.";
        }
        else {
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
                    session_regenerate_id(true);
                    $_SESSION['portal_patient_id'] = $row['id'];
                    $_SESSION['session_start'] = time();
                    $_SESSION['last_activity'] = time();
                    if (!empty($_SESSION['portal_redirect_hash'])) {
                        $h = $_SESSION['portal_redirect_hash'];
                        $t = $_SESSION['portal_redirect_type'];
                        unset($_SESSION['portal_redirect_hash'], $_SESSION['portal_redirect_type']);
                        header("Location: verify.php?hash={$h}&type={$t}");
                    }
                    else {
                        header("Location: dashboard.php");
                    }
                    exit;
                }
                else {
                    $error = "Details not found. Please check your Phone/MR Number and CNIC.";
                }
            }
            catch (Exception $e) {
                $error = "System error. Please try again later.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Portal — IVF Experts</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700&family=Noto+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Noto Sans', sans-serif; }
        h1, h2, h3, .font-heading { font-family: 'Figtree', sans-serif; }
        .medical-bg {
            background-color: #F8FAFC;
            background-image: radial-gradient(at 0% 0%, hsla(192, 91%, 91%, 1) 0, transparent 50%), 
                              radial-gradient(at 50% 0%, hsla(152, 63%, 92%, 1) 0, transparent 50%);
        }
        .white-card {
            background: #FFFFFF;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.02), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        }
        .clean-input {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            color: #1E293B;
            transition: all 0.2s;
        }
        .clean-input:focus {
            background: #FFFFFF;
            border-color: #0891B2;
            box-shadow: 0 0 0 4px rgba(8, 145, 178, 0.1);
            outline: none;
        }
    </style>
</head>
<body class="medical-bg min-h-screen flex items-center justify-center p-6 text-slate-900">

    <div class="max-w-md w-full">
        <!-- Logo Area -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-cyan-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-cyan-100">
                    <i class="fa-solid fa-heart-pulse text-xl"></i>
                </div>
                <span class="font-black text-3xl tracking-tight text-slate-800">IVF<span class="text-cyan-600">EXPERTS</span></span>
            </div>
            <p class="text-slate-400 font-bold uppercase tracking-[0.2em] text-[10px]">Patient Digital Portal</p>
        </div>

        <div class="white-card rounded-[2.5rem] p-8 md:p-10 relative overflow-hidden">
            <!-- Subtle Decorative Element -->
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-cyan-50 rounded-full blur-3xl opacity-60"></div>
            
            <div class="relative">
                <h1 class="text-2xl font-bold text-slate-800 mb-2">Welcome Back</h1>
                <p class="text-slate-500 text-sm mb-8 leading-relaxed">Please sign in to access your clinical records, test results, and treatment timeline.</p>

                <?php if ($error): ?>
                    <div class="bg-rose-50 border border-rose-100 text-rose-600 px-4 py-3 rounded-2xl text-xs font-bold mb-6 flex items-center gap-3">
                        <i class="fa-solid fa-circle-exclamation text-base"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php
endif; ?>

                <?php if (isset($_GET['expired'])): ?>
                    <div class="bg-amber-50 border border-amber-100 text-amber-600 px-4 py-3 rounded-2xl text-xs font-bold mb-6 flex items-center gap-3">
                        <i class="fa-solid fa-clock-rotate-left text-base"></i>
                        Session expired for your security. Please log in again.
                    </div>
                <?php
endif; ?>

                <form method="POST" class="space-y-5">
                    <input type="hidden" name="_csrf" value="<?php echo csrf_token(); ?>">
                    
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Phone / MR Number</label>
                        <div class="relative">
                            <i class="fa-solid fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                            <input type="text" name="phone_mr" required 
                                   class="clean-input w-full pl-11 pr-4 py-4 rounded-2xl text-sm font-medium placeholder:text-slate-300"
                                   placeholder="03001234567 or MR-1234"
                                   value="<?php echo htmlspecialchars($_POST['phone_mr'] ?? ''); ?>"
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
    ['icon' => 'fa-prescription-bottle-medical', 'c' => 'indigo', 'title' => 'Prescriptions', 'desc' => 'Digital Rx with QR verification'],
    ['icon' => 'fa-vials', 'c' => 'teal', 'title' => 'Lab Results', 'desc' => 'Real-time blood test results'],
    ['icon' => 'fa-image', 'c' => 'emerald', 'title' => 'Scan Reports', 'desc' => 'Ultrasound & follicular monitoring'],
    ['icon' => 'fa-microscope', 'c' => 'sky', 'title' => 'Semen Analysis', 'desc' => 'WHO 6th edition andrology reports'],
    ['icon' => 'fa-receipt', 'c' => 'amber', 'title' => 'Billing', 'desc' => 'Full payment & receipt history'],
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
                    <?php
endforeach; ?>
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
