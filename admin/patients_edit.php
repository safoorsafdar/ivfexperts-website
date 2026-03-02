<?php
$pageTitle = "Edit Patient Details";
require_once __DIR__ . '/includes/auth.php';

$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($patient_id <= 0) {
    header("Location: patients.php");
    exit;
}

$error = '';
$success = '';

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
    $spouse_name = trim($_POST['spouse_name']);
    $spouse_age = !empty($_POST['spouse_age']) ? intval($_POST['spouse_age']) : null;
    $spouse_gender = !empty($_POST['spouse_gender']) ? $_POST['spouse_gender'] : null;
    $spouse_cnic = trim($_POST['spouse_cnic'] ?? '');
    $spouse_phone = trim($_POST['spouse_phone'] ?? '');

    if (empty($mr_number) || empty($first_name) || empty($gender)) {
        $error = "MR Number, First Name, and Gender are required fields.";
    }
    else {
        try {
            $stmt = $conn->prepare("UPDATE patients SET mr_number=?, first_name=?, last_name=?, patient_age=?, date_of_birth=?, blood_group=?, gender=?, marital_status=?, gravida=?, para=?, abortions=?, years_married=?, cnic=?, phone=?, address=?, email=?, spouse_name=?, spouse_age=?, spouse_gender=?, spouse_cnic=?, spouse_phone=?, referring_hospital_id=? WHERE id=?");
            if ($stmt) {
                // Total 23 params: sss i ssss iiii sssss i sss ii
                $stmt->bind_param("sssissssiiiisssssisssii",
                    $mr_number, $first_name, $last_name, $patient_age, $date_of_birth, $blood_group,
                    $gender, $marital_status, $gravida, $para, $abortions, $years_married,
                    $cnic, $phone, $address, $email, $spouse_name, $spouse_age, $spouse_gender,
                    $spouse_cnic, $spouse_phone, $hospital_id, $patient_id
                );
                if ($stmt->execute()) {
                    header("Location: patients_view.php?id=" . $patient_id . "&msg=updated");
                    exit;
                }
                else {
                    $error = "Database Error: " . $stmt->error;
                }
            }
        }
        catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = "MR Number is already registered to another patient.";
            }
            else {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

// Fetch Patient Details
$patient = null;
try {
    $stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $patient = $res->fetch_assoc();
    }
}
catch (Exception $e) {
}

if (!$patient) {
    die("Patient not found.");
}

include __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <a href="patients_view.php?id=<?php echo $patient_id; ?>" class="text-sm text-gray-500 hover:text-teal-600 font-medium flex items-center gap-1">
            <i class="fa-solid fa-arrow-left"></i> Back to Profile
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h3 class="font-bold text-gray-800">Edit Patient Details</h3>
        </div>
        
        <div class="p-6 md:p-8">
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo esc($error); ?>
                </div>
            <?php
endif; ?>

            <form method="POST">
                <!-- MR Number -->
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-1">MR Number *</label>
                    <input type="text" name="mr_number" value="<?php echo esc($_POST['mr_number'] ?? $patient['mr_number']); ?>" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 font-mono bg-gray-50" required>
                </div>

                <!-- Section: Primary Patient -->
                <div class="bg-teal-50 border border-teal-100 rounded-xl p-5 mb-6">
                    <h4 class="font-bold text-teal-800 text-sm mb-4"><i class="fa-solid fa-user mr-1"></i> Primary Patient Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">First Name *</label>
                            <input type="text" name="first_name" value="<?php echo esc($_POST['first_name'] ?? $patient['first_name']); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Last Name</label>
                            <input type="text" name="last_name" value="<?php echo esc($_POST['last_name'] ?? $patient['last_name']); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Gender *</label>
                            <?php $g = $_POST['gender'] ?? $patient['gender']; ?>
                            <select name="gender" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white" required>
                                <option value="Female" <?php echo($g == 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Male" <?php echo($g == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Other" <?php echo($g == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Age</label>
                            <input type="number" name="patient_age" value="<?php echo esc($_POST['patient_age'] ?? $patient['patient_age'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white" min="1" max="120" placeholder="Years">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Date of Birth</label>
                            <input type="date" name="date_of_birth" value="<?php echo esc($_POST['date_of_birth'] ?? $patient['date_of_birth'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Blood Group</label>
                            <?php $bg_val = $_POST['blood_group'] ?? $patient['blood_group'] ?? ''; ?>
                            <select name="blood_group" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white">
                                <option value="">-- Unknown --</option>
                                <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg): ?>
                                    <option value="<?php echo $bg; ?>" <?php echo($bg_val == $bg) ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                                <?php
endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                            <input type="text" name="phone" value="<?php echo esc($_POST['phone'] ?? $patient['phone']); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white" placeholder="03XX-XXXXXXX">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">CNIC / ID Number</label>
                            <input type="text" name="cnic" value="<?php echo esc($_POST['cnic'] ?? $patient['cnic']); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white font-mono text-sm" placeholder="XXXXX-XXXXXXX-X">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                            <input type="email" name="email" value="<?php echo esc($_POST['email'] ?? $patient['email'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white" placeholder="patient@example.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                            <input type="text" name="address" value="<?php echo esc($_POST['address'] ?? $patient['address'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white" placeholder="City, Area">
                        </div>
                    </div>
                </div>

                <!-- Section: Spouse Details -->
                <div class="bg-pink-50 border border-pink-100 rounded-xl p-5 mb-6">
                    <h4 class="font-bold text-pink-800 text-sm mb-4"><i class="fa-solid fa-heart mr-1"></i> Spouse / Partner Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Spouse Full Name</label>
                            <input type="text" name="spouse_name" value="<?php echo esc($_POST['spouse_name'] ?? $patient['spouse_name']); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Spouse Gender</label>
                            <?php $sg = $_POST['spouse_gender'] ?? $patient['spouse_gender'] ?? ''; ?>
                            <select name="spouse_gender" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 bg-white">
                                <option value="">-- Select --</option>
                                <option value="Male" <?php echo($sg == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo($sg == 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo($sg == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Spouse Age</label>
                            <input type="number" name="spouse_age" value="<?php echo esc($_POST['spouse_age'] ?? $patient['spouse_age'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 bg-white" min="1" max="120" placeholder="Years">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Spouse Contact Number</label>
                            <input type="text" name="spouse_phone" value="<?php echo esc($_POST['spouse_phone'] ?? $patient['spouse_phone'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 bg-white" placeholder="03XX-XXXXXXX">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Spouse CNIC / ID</label>
                            <input type="text" name="spouse_cnic" value="<?php echo esc($_POST['spouse_cnic'] ?? $patient['spouse_cnic'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 bg-white font-mono text-sm" placeholder="XXXXX-XXXXXXX-X">
                        </div>
                    </div>
                </div>

                <!-- Referring Hospital -->
                <div class="border-t border-gray-100 pt-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Referring Clinic / Place of Consult</label>
                    <select name="hospital_id" class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 bg-white">
                        <option value="">Direct to IVF Experts (Default)</option>
                        <?php
$h_id = $_POST['hospital_id'] ?? $patient['referring_hospital_id'];
foreach ($hospitals as $h): ?>
                            <option value="<?php echo $h['id']; ?>" <?php echo($h_id == $h['id']) ? 'selected' : ''; ?>>
                                <?php echo esc($h['name']); ?>
                            </option>
                        <?php
endforeach; ?>
                    </select>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-8 rounded-lg shadow-md hover:shadow-lg transition-all focus:outline-none focus:ring-2 focus:ring-teal-500 flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> Update Patient
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
