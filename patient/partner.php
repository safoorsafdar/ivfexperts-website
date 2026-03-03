<?php
session_start();
if (!isset($_SESSION['portal_patient_id'])) {
    header("Location: index.php");
    exit;
}

require_once dirname(__DIR__) . '/4me/config/db.php';
$patient_id = intval($_SESSION['portal_patient_id']);

// Fetch Primary Patient Info
$stmt = $conn->prepare("SELECT mr_number, first_name, last_name, gender, cnic, phone, spouse_name FROM patients WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if (!$patient || empty($patient['spouse_name'])) {
    header("Location: dashboard.php");
    exit;
}

// Find Spouse Record
$cnic_clean = preg_replace('/[^0-9]/', '', $patient['cnic'] ?? '');
$phone = $patient['phone'] ?? '';
$mr = $patient['mr_number'] ?? '';

$stmt_spouse = $conn->prepare("SELECT * FROM patients WHERE first_name = ? AND (phone = ? OR mr_number = ? OR REPLACE(cnic, '-', '') = ?)");
$stmt_spouse->bind_param("ssss", $patient['spouse_name'], $phone, $mr, $cnic_clean);
$stmt_spouse->execute();
$partner = $stmt_spouse->get_result()->fetch_assoc();

if (!$partner) {
    die("Partner record not found in system. Please contact clinic to link your partner's profile.");
}

$partner_id = $partner['id'];

// Fetch Partner's Specific History/Labs
$histories = [];
$resH = $conn->query("SELECT * FROM patient_histories WHERE patient_id = $partner_id ORDER BY created_at DESC");
if ($resH)
    while ($row = $resH->fetch_assoc())
        $histories[] = $row;

$latest_semen = null;
$resS = $conn->query("SELECT * FROM semen_analyses WHERE patient_id = $partner_id ORDER BY collection_time DESC LIMIT 1");
if ($resS)
    $latest_semen = $resS->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Profile — IVF Experts</title>
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
    </style>
</head>
<body class="medical-bg min-h-screen text-slate-900">
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-4xl mx-auto px-6 h-16 flex justify-between items-center">
            <a href="dashboard.php" class="text-slate-400 hover:text-cyan-600 transition-colors">
                <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
            </a>
            <div class="font-bold text-slate-800">Partner Medical Profile</div>
            <div class="w-10"></div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-6 py-10">
        <!-- Partner Hero -->
        <div class="bg-white rounded-[2.5rem] border border-slate-200 p-8 md:p-10 shadow-sm mb-8 text-center md:text-left flex flex-col md:flex-row items-center gap-8">
            <div class="w-24 h-24 rounded-3xl bg-pink-50 text-pink-500 flex items-center justify-center text-4xl shadow-lg shadow-pink-100 shrink-0">
                <i class="fa-solid fa-user-venus-mars"></i>
            </div>
            <div>
                <div class="text-[10px] font-black text-pink-500 uppercase tracking-[0.2em] mb-2">Linked Medical Partner</div>
                <h1 class="text-3xl font-bold text-slate-900 mb-2"><?php echo htmlspecialchars($partner['first_name'] . ' ' . $partner['last_name']); ?></h1>
                <div class="flex flex-wrap items-center gap-4 text-sm font-medium text-slate-500">
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-id-card text-xs text-slate-300"></i> MR# <?php echo htmlspecialchars($partner['mr_number']); ?></span>
                    <span class="text-slate-200">•</span>
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-droplet text-xs text-rose-400"></i> Blood: <?php echo htmlspecialchars($partner['blood_group'] ?: 'Not Set'); ?></span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Left: Quick Info -->
            <div class="md:col-span-1 space-y-6">
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Vital Details</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="text-[9px] font-black text-slate-400 uppercase leading-none mb-1">Date of Birth</div>
                            <div class="text-xs font-bold text-slate-700"><?php echo $partner['dob'] ? date('d M Y', strtotime($partner['dob'])) : 'Not recorded'; ?></div>
                        </div>
                        <div>
                            <div class="text-[9px] font-black text-slate-400 uppercase leading-none mb-1">Phone Number</div>
                            <div class="text-xs font-bold text-slate-700"><?php echo htmlspecialchars($partner['phone']); ?></div>
                        </div>
                        <div>
                            <div class="text-[9px] font-black text-slate-400 uppercase leading-none mb-1">CNIC (Identificaton)</div>
                            <div class="text-xs font-bold text-slate-700"><?php echo substr($partner['cnic'], 0, 5); ?>-XXXXXXX-X</div>
                        </div>
                    </div>
                </div>

                <?php if ($latest_semen): ?>
                <div class="bg-teal-50 border border-teal-100 rounded-3xl p-6">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-teal-600 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-microscope text-xs"></i> Latest Semen Analysis
                    </h3>
                    <div class="text-xs font-bold text-slate-800 mb-1">Collection Date</div>
                    <div class="text-sm font-black text-teal-700"><?php echo date('d M Y', strtotime($latest_semen['collection_time'])); ?></div>
                    <a href="view.php?type=sa&hash=<?php echo $latest_semen['qrcode_hash']; ?>" target="_blank" class="mt-4 block w-full bg-white border border-teal-100 text-teal-600 text-center py-2.5 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-teal-600 hover:text-white transition-all">
                        View Full Report
                    </a>
                </div>
                <?php
endif; ?>
            </div>

            <!-- Right: Medical History -->
            <div class="md:col-span-2 space-y-6">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-history text-cyan-600"></i> Partner Consultation History
                </h3>
                
                <?php if (empty($histories)): ?>
                    <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center text-slate-400 font-bold">
                        No separate medical history recorded for partner.
                    </div>
                <?php
else: ?>
                    <div class="space-y-4">
                        <?php foreach ($histories as $h): ?>
                        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                            <div class="flex justify-between items-start mb-4">
                                <div class="text-[10px] font-black text-cyan-600 uppercase tracking-widest"><?php echo date('d M Y', strtotime($h['created_at'])); ?></div>
                                <div class="text-[9px] font-bold text-slate-300 uppercase">Consultation #<?php echo $h['id']; ?></div>
                            </div>
                            <div class="prose prose-slate prose-sm max-w-none text-slate-700 font-medium">
                                <?php echo nl2br(htmlspecialchars($h['summary'] ?? $h['notes'])); ?>
                            </div>
                        </div>
                        <?php
    endforeach; ?>
                    </div>
                <?php
endif; ?>
            </div>
        </div>
    </main>

    <footer class="max-w-4xl mx-auto px-6 py-10 text-center border-t border-slate-100 mt-10">
        <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em]">Confidential Medical Record · IVF Experts</p>
    </footer>
</body>
</html>
