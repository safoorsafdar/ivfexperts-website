<?php
$pageTitle = "Register New Patient";
require_once __DIR__ . '/includes/auth.php';

$error = '';
$success = '';

// Auto-generate MR Number proposal
$auto_mr = "IVF-" . date("ymd") . "-" . rand(1000, 9999);

// Fetch Hospitals for dropdown
$hospitals = [];
try {
    $res = $conn->query("SELECT id, name FROM hospitals ORDER BY name ASC");
    if ($res) {
        while ($row = $res->fetch_assoc())
            $hospitals[] = $row;
    }
}
catch (Exception $e) {
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mr_number = trim($_POST['mr_number']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $patient_age = !empty($_POST['patient_age']) ? intval($_POST['patient_age']) : null;
    $date_of_birth = !empty($_POST['date_of_birth']) ? trim($_POST['date_of_birth']) : null;
    $blood_group = trim($_POST['blood_group'] ?? '');
    $gender = $_POST['gender'];
    $marital_status = $_POST['marital_status'] ?? 'Single';
    $gravida = intval($_POST['gravida'] ?? 0);
    $para = intval($_POST['para'] ?? 0);
    $abortions = intval($_POST['abortions'] ?? 0);
    $years_married = !empty($_POST['years_married']) ? intval($_POST['years_married']) : null;
    $cnic = trim($_POST['cnic']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $hospital_id = !empty($_POST['hospital_id']) ? $_POST['hospital_id'] : null;

    // Spouse details
    $spouse_name = trim($_POST['spouse_name'] ?? '');
    $spouse_age = !empty($_POST['spouse_age']) ? intval($_POST['spouse_age']) : null;
    $spouse_gender = !empty($_POST['spouse_gender']) ? $_POST['spouse_gender'] : null;
    $spouse_cnic = trim($_POST['spouse_cnic'] ?? '');
    $spouse_phone = trim($_POST['spouse_phone'] ?? '');

    if (empty($mr_number) || empty($first_name) || empty($gender)) {
        $error = "MR Number, First Name, and Gender are required fields.";
    }
    else {
        try {
            $stmt = $conn->prepare("INSERT INTO patients (mr_number, first_name, last_name, patient_age, date_of_birth, blood_group, gender, marital_status, gravida, para, abortions, years_married, cnic, phone, address, email, spouse_name, spouse_age, spouse_gender, spouse_cnic, spouse_phone, referring_hospital_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                // Total 22 params: sss i ssss iiii sssss i sss i
                $stmt->bind_param("sssissssiiiisssssisssi", $mr_number, $first_name, $last_name, $patient_age, $date_of_birth, $blood_group, $gender, $marital_status, $gravida, $para, $abortions, $years_married, $cnic, $phone, $address, $email, $spouse_name, $spouse_age, $spouse_gender, $spouse_cnic, $spouse_phone, $hospital_id);
                if ($stmt->execute()) {
                    $new_id = $conn->insert_id;
                    header("Location: patients_view.php?id=" . $new_id . "&msg=created");
                    exit;
                }
                else {
                    $error = "Database Error: " . $stmt->error;
                }
            }
            else {
                $error = "Failed to prepare statement.";
            }
        }
        catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // Duplicate entry
                $error = "MR Number '$mr_number' is already registered. Please use a unique MR Number.";
            }
            else {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    
    <div class="mb-6 flex items-center justify-between">
        <a href="patients.php" class="text-sm text-gray-500 hover:text-teal-600 font-medium flex items-center gap-1">
            <i class="fa-solid fa-arrow-left"></i> Back to Registry
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-8 py-5 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
            <div class="w-10 h-10 bg-teal-100 text-teal-600 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-user-plus text-base"></i>
            </div>
            <div>
                <h3 class="font-black text-gray-800 text-lg">Register New Patient</h3>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Complete intake form</p>
            </div>
        </div>
        
        <div class="p-6 md:p-8">
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo esc($error); ?>
                </div>
            <?php
endif; ?>

            <form method="POST" x-data="{ 
                marital: '<?php echo esc($_POST['marital_status'] ?? 'Single'); ?>', 
                gender: '<?php echo esc($_POST['gender'] ?? 'Female'); ?>' 
            }">
                <!-- MR Number -->
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-1">MR Number *</label>
                    <div class="flex gap-2">
                        <input type="text" name="mr_number" id="mr_number" value="<?php echo esc($_POST['mr_number'] ?? $auto_mr); ?>" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 font-mono bg-gray-50" required>
                        <button type="button" onclick="document.getElementById('mr_number').value = 'IVF-' + new Date().toISOString().slice(2,10).replace(/-/g,'') + '-' + Math.floor(1000 + Math.random() * 9000)" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg text-sm shrink-0 transition-colors" title="Generate New Auto MR">
                            <i class="fa-solid fa-rotate-right"></i> Generate
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Leave as auto-generated or replace with external referring hospital MR Number.</p>
                </div>

                <!-- Section: Primary Patient -->
                <div class="bg-teal-50 border border-teal-100 rounded-xl p-5 mb-6">
                    <h4 class="font-bold text-teal-800 text-sm mb-4"><i class="fa-solid fa-user mr-1"></i> Primary Patient Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">First Name *</label>
                            <input type="text" name="first_name" value="<?php echo esc($_POST['first_name'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Last Name</label>
                            <input type="text" name="last_name" value="<?php echo esc($_POST['last_name'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Gender *</label>
                            <select name="gender" x-model="gender" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white" required>
                                <option value="Female">Female</option>
                                <option value="Male">Male</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Age</label>
                            <input type="number" name="patient_age" value="<?php echo esc($_POST['patient_age'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white" min="1" max="120" placeholder="Years">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Date of Birth</label>
                            <input type="date" name="date_of_birth" value="<?php echo esc($_POST['date_of_birth'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Blood Group</label>
                            <select name="blood_group" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white">
                                <option value="">-- Unknown --</option>
                                <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg): ?>
                                    <option value="<?php echo $bg; ?>" <?php echo(isset($_POST['blood_group']) && $_POST['blood_group'] == $bg) ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                                <?php
endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                            <input type="text" name="phone" value="<?php echo esc($_POST['phone'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white" placeholder="03XX-XXXXXXX">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">CNIC / ID Number</label>
                            <input type="text" name="cnic" value="<?php echo esc($_POST['cnic'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white font-mono text-sm" placeholder="XXXXX-XXXXXXX-X">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                            <input type="email" name="email" value="<?php echo esc($_POST['email'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white" placeholder="patient@example.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                            <input type="text" name="address" value="<?php echo esc($_POST['address'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white" placeholder="City, Area">
                        </div>
                    </div>
                </div>

                <!-- Section: Marital Status & Obstetric History -->
                <div class="bg-purple-50 border border-purple-100 rounded-xl p-5 mb-6">
                    <h4 class="font-bold text-purple-800 text-sm mb-4"><i class="fa-solid fa-ring mr-1"></i> Marital & Obstetric History</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Marital Status</label>
                            <select name="marital_status" x-model="marital" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white">
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Widowed">Widowed</option>
                            </select>
                        </div>
                        <div x-show="marital === 'Married'">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Years Married</label>
                            <input type="number" name="years_married" value="<?php echo esc($_POST['years_married'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white" min="0" placeholder="Years">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mt-4" x-show="gender === 'Female'">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Gravida (G)</label>
                            <input type="number" name="gravida" id="gravida_input" value="<?php echo esc($_POST['gravida'] ?? '0'); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white text-center font-bold" min="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Para (P)</label>
                            <input type="number" name="para" id="para_input" value="<?php echo esc($_POST['para'] ?? '0'); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white text-center font-bold" min="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Abortions (A)</label>
                            <input type="number" name="abortions" id="abortions_input" value="<?php echo esc($_POST['abortions'] ?? '0'); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white text-center font-bold" min="0">
                        </div>
                    </div>
                </div>

                <!-- Section: Spouse Details -->
                <div class="bg-pink-50 border border-pink-100 rounded-xl p-5 mb-6" x-show="marital === 'Married'">
                    <h4 class="font-bold text-pink-800 text-sm mb-4"><i class="fa-solid fa-heart mr-1"></i> Spouse / Partner Details</h4>
                    <p class="text-xs text-pink-600 mb-4">This links the spouse's tests (Semen Analysis, Lab Results) under the same patient file for 360° fertility tracking.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Spouse Full Name</label>
                            <input type="text" name="spouse_name" value="<?php echo esc($_POST['spouse_name'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Spouse Gender</label>
                            <select name="spouse_gender" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 bg-white">
                                <option value="">-- Select --</option>
                                <option value="Male" <?php echo(isset($_POST['spouse_gender']) && $_POST['spouse_gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo(isset($_POST['spouse_gender']) && $_POST['spouse_gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo(isset($_POST['spouse_gender']) && $_POST['spouse_gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Spouse Age</label>
                            <input type="number" name="spouse_age" value="<?php echo esc($_POST['spouse_age'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 bg-white" min="1" max="120" placeholder="Years">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Spouse Contact Number</label>
                            <input type="text" name="spouse_phone" value="<?php echo esc($_POST['spouse_phone'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 bg-white" placeholder="03XX-XXXXXXX">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Spouse CNIC / ID</label>
                            <input type="text" name="spouse_cnic" value="<?php echo esc($_POST['spouse_cnic'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 bg-white font-mono text-sm" placeholder="XXXXX-XXXXXXX-X">
                        </div>
                    </div>
                </div>

                <!-- Referring Hospital -->
                <div class="border-t border-gray-100 pt-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Referring Clinic / Place of Consult</label>
                    <select name="hospital_id" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white">
                        <option value="">Direct to IVF Experts (Default)</option>
                        <?php foreach ($hospitals as $h): ?>
                            <option value="<?php echo $h['id']; ?>" <?php echo(isset($_POST['hospital_id']) && $_POST['hospital_id'] == $h['id']) ? 'selected' : ''; ?>>
                                <?php echo esc($h['name']); ?>
                            </option>
                        <?php
endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">If the patient is registered under another hospital's MR, select the hospital to link them correctly.</p>
                </div>

                <div class="mt-8 flex items-center justify-between gap-4 border-t border-gray-50 pt-6">
                    <a href="patients.php" class="text-sm text-gray-500 hover:text-gray-700 font-bold flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-arrow-left text-xs"></i> Back to Registry
                    </a>
                    <button type="submit"
                            class="bg-teal-600 hover:bg-teal-700 text-white font-black py-3.5 px-8 rounded-2xl shadow-xl shadow-teal-100 transition-all active:scale-95 flex items-center gap-2.5">
                        <i class="fa-solid fa-user-check"></i> Register & Open Patient File
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
