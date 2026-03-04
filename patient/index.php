<?php
session_start();

if (isset($_SESSION['portal_patient_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once dirname(__DIR__) . '/4me/config/db.php';
require_once __DIR__ . '/includes/rate_limit.php';
require_once __DIR__ . '/includes/csrf.php';
$error = '';

if (!empty($_GET['redirect_hash'])) {
    $_SESSION['portal_redirect_hash'] = preg_replace('/[^a-f0-9]/', '', $_GET['redirect_hash']);
    $_SESSION['portal_redirect_type'] = in_array($_GET['type'] ?? '', ['rx', 'sa', 'usg', 'receipt']) ? $_GET['type'] : 'rx';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = "Security token mismatch. Please try again.";
    } elseif (!check_rate_limit('login_attempts')) {
        $error = "Too many login attempts. Please wait a few minutes.";
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
                    session_regenerate_id(true);
                    $_SESSION['portal_patient_id'] = $row['id'];
                    $_SESSION['session_start']      = time();
                    $_SESSION['last_activity']      = time();
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
                    $error = "No record found. Please check your Phone / MR Number and CNIC.";
                }
            } catch (Exception $e) {
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }

        body {
            background: #f0f4f8;
            background-image:
                radial-gradient(ellipse 70% 50% at 0% 0%, rgba(13, 148, 136, 0.08) 0%, transparent 55%),
                radial-gradient(ellipse 50% 40% at 100% 100%, rgba(99, 102, 241, 0.06) 0%, transparent 55%);
            min-height: 100vh;
        }

        .login-card {
            background: #ffffff;
            border: 1px solid #e8edf2;
            box-shadow:
                0 1px 3px rgba(0,0,0,0.04),
                0 8px 24px rgba(0,0,0,0.06),
                0 24px 48px rgba(0,0,0,0.04);
        }

        .field-wrap {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .field-wrap:focus-within {
            background: #fff;
            border-color: #0d9488;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.12);
        }
        .field-wrap input {
            background: transparent;
            border: none;
            outline: none;
            width: 100%;
            color: #1e293b;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 0;
        }
        .field-wrap input::placeholder { color: #b0bec8; font-weight: 400; }

        .field-icon {
            color: #94a3b8;
            font-size: 0.85rem;
            flex-shrink: 0;
            width: 18px;
            text-align: center;
            transition: color 0.2s;
        }
        .field-wrap:focus-within .field-icon { color: #0d9488; }

        .btn-signin {
            background: linear-gradient(135deg, #0d9488 0%, #0891b2 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 13px 20px;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(13, 148, 136, 0.3);
        }
        .btn-signin:hover {
            background: linear-gradient(135deg, #0f766e 0%, #0e7490 100%);
            box-shadow: 0 6px 20px rgba(13, 148, 136, 0.4);
            transform: translateY(-1px);
        }
        .btn-signin:active { transform: translateY(0); }

        .qr-hint {
            background: #f0fdf9;
            border: 1px solid #ccfbee;
            border-radius: 12px;
        }

        .feature-chip {
            background: #f8fafc;
            border: 1px solid #e8edf2;
            border-radius: 10px;
            transition: all 0.15s;
        }
        .feature-chip:hover { background: #f0fdf9; border-color: #a7f3d0; }

        @keyframes scanBar {
            0%   { top: 3px; opacity: 0; }
            15%  { opacity: 1; }
            85%  { opacity: 1; }
            100% { top: calc(100% - 3px); opacity: 0; }
        }
        .scan-bar {
            position: absolute;
            left: 3px; right: 3px;
            height: 1.5px;
            background: linear-gradient(90deg, transparent, #0d9488, transparent);
            border-radius: 1px;
            animation: scanBar 2s ease-in-out infinite;
        }

        .error-box  { background: #fff5f5; border: 1px solid #fecaca; color: #dc2626; border-radius: 10px; }
        .warn-box   { background: #fffbeb; border: 1px solid #fde68a; color: #b45309; border-radius: 10px; }

        .divider { height: 1px; background: linear-gradient(to right, transparent, #e2e8f0, transparent); }
    </style>
</head>
<body class="flex items-center justify-center p-4 py-10 min-h-screen">

    <div class="w-full max-w-[420px]">

        <!-- Brand Header -->
        <div class="text-center mb-7">
            <div class="inline-flex items-center gap-3 mb-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-teal-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-teal-200">
                    <i class="fa-solid fa-heart-pulse text-white text-lg"></i>
                </div>
                <div class="text-left">
                    <div class="font-black text-2xl leading-none text-slate-800 tracking-tight">IVF<span class="text-teal-600">EXPERTS</span></div>
                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-0.5">Patient Portal</div>
                </div>
            </div>
        </div>

        <!-- Login Card -->
        <div class="login-card rounded-[24px] p-8">

            <h1 class="text-xl font-bold text-slate-800 mb-1">Welcome back</h1>
            <p class="text-slate-500 text-sm mb-6">Sign in to view your records, prescriptions & lab results.</p>

            <?php if ($error): ?>
                <div class="error-box px-4 py-3 text-sm font-medium mb-5 flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-exclamation mt-0.5 shrink-0"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['expired'])): ?>
                <div class="warn-box px-4 py-3 text-sm font-medium mb-5 flex items-start gap-2.5">
                    <i class="fa-solid fa-clock-rotate-left mt-0.5 shrink-0"></i>
                    <span>Session expired for your security. Please log in again.</span>
                </div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <input type="hidden" name="_csrf" value="<?php echo csrf_token(); ?>">

                <!-- Phone / MR Field -->
                <div class="mb-4">
                    <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-2">Phone Number / MR Number</label>
                    <div class="field-wrap flex items-center gap-3 px-4 py-3.5">
                        <i class="fa-solid fa-phone field-icon"></i>
                        <input type="text" name="phone_mr" required
                               placeholder="03001234567  or  MR-001"
                               value="<?php echo htmlspecialchars($_POST['phone_mr'] ?? ''); ?>"
                               autocomplete="tel">
                    </div>
                </div>

                <!-- CNIC Field -->
                <div class="mb-6">
                    <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-2">CNIC Number</label>
                    <div class="field-wrap flex items-center gap-3 px-4 py-3.5">
                        <i class="fa-solid fa-id-card field-icon"></i>
                        <input type="text" name="cnic"
                               placeholder="XXXXX-XXXXXXX-X"
                               maxlength="15"
                               oninput="formatCNIC(this)"
                               value="<?php echo htmlspecialchars($_POST['cnic'] ?? ''); ?>"
                               autocomplete="off"
                               class="font-mono tracking-wider">
                    </div>
                </div>

                <button type="submit" class="btn-signin flex items-center justify-center gap-2">
                    <i class="fa-solid fa-shield-halved"></i> Access My Records
                </button>
            </form>

            <!-- Divider -->
            <div class="divider my-6"></div>

            <!-- QR Hint -->
            <div class="qr-hint flex items-center gap-4 px-4 py-3.5">
                <!-- Mini QR Icon -->
                <div class="relative w-9 h-9 shrink-0 border border-teal-200 rounded-lg overflow-hidden bg-teal-50">
                    <div class="absolute inset-0 grid grid-cols-3 grid-rows-3 gap-[2px] p-[3px]">
                        <div class="bg-teal-500/60 rounded-[1px]"></div><div></div><div class="bg-teal-500/60 rounded-[1px]"></div>
                        <div></div><div class="bg-teal-300/40 rounded-[1px]"></div><div></div>
                        <div class="bg-teal-500/60 rounded-[1px]"></div><div></div><div class="bg-teal-500/60 rounded-[1px]"></div>
                    </div>
                    <div class="scan-bar"></div>
                </div>
                <div>
                    <div class="text-slate-700 text-xs font-bold">Scan a QR Code Instead</div>
                    <div class="text-slate-400 text-[10px] mt-0.5 leading-snug">Scan the QR on any printed report, then enter your CNIC to view instantly.</div>
                </div>
            </div>

        </div>

        <!-- Feature Chips -->
        <div class="grid grid-cols-3 gap-2 mt-4">
            <?php
            $features = [
                ['icon' => 'fa-prescription-bottle-medical', 'color' => 'text-indigo-500',  'label' => 'Prescriptions'],
                ['icon' => 'fa-vials',                       'color' => 'text-teal-500',    'label' => 'Lab Results'],
                ['icon' => 'fa-image',                       'color' => 'text-emerald-500', 'label' => 'Scan Reports'],
                ['icon' => 'fa-microscope',                  'color' => 'text-sky-500',     'label' => 'Semen Analysis'],
                ['icon' => 'fa-syringe',                     'color' => 'text-violet-500',  'label' => 'Procedures'],
                ['icon' => 'fa-receipt',                     'color' => 'text-amber-500',   'label' => 'Billing'],
            ];
            foreach ($features as $f): ?>
            <div class="feature-chip px-3 py-3 text-center">
                <i class="fa-solid <?php echo $f['icon']; ?> <?php echo $f['color']; ?> text-base mb-1.5 block"></i>
                <span class="text-[9px] font-semibold text-slate-500 leading-tight"><?php echo $f['label']; ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <p class="text-center text-slate-400 text-[9px] font-semibold uppercase tracking-widest mt-5">
            Secure &nbsp;·&nbsp; Private &nbsp;·&nbsp; <?php echo date('Y'); ?>
        </p>

    </div>

    <script>
    function formatCNIC(el) {
        let v = el.value.replace(/\D/g, '').slice(0, 13);
        if (v.length > 12)     v = v.slice(0,5) + '-' + v.slice(5,12) + '-' + v.slice(12);
        else if (v.length > 5) v = v.slice(0,5) + '-' + v.slice(5);
        el.value = v;
    }
    </script>

</body>
</html>
