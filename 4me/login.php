<?php
session_start();
require_once __DIR__ . '/config/db.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    }
    else {
        $stmt = $conn->prepare("SELECT id, password_hash FROM admin_users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                if (password_verify($password, $row['password_hash'])) {
                    // Success
                    $_SESSION['admin_id'] = $row['id'];
                    $_SESSION['admin_username'] = $username;
                    header("Location: index.php");
                    exit;
                }
                else {
                    $error = "Invalid username or password.";
                }
            }
            else {
                $error = "Invalid username or password.";
            }
            $stmt->close();
        }
        else {
            $error = "Database error. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IVF Experts EMR - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        teal: { 600: '#0d9488', 700: '#0f766e', 800: '#115e59', 900: '#134e4a' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-teal-900 p-8 text-center">
            <h1 class="text-2xl font-bold text-white mb-2">IVF Experts</h1>
            <p class="text-teal-200 text-sm">Enterprise EMR & Clinic Management</p>
        </div>
        
        <div class="p-8">
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-lg text-sm mb-6 border border-red-100">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php
endif; ?>

            <form method="POST" action="">
                <div class="mb-5">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                    <input type="text" name="username" class="w-full px-4 py-3 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors" required>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" class="w-full px-4 py-3 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors" required>
                </div>

                <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-4 rounded-lg transition-colors focus:outline-none focus:ring-4 focus:ring-teal-500/30">
                    Sign In to Dashboard
                </button>
            </form>
            
            <div class="mt-8 text-center text-xs text-slate-400">
                &copy; <?php echo date('Y'); ?> IVF Experts Pakistan.<br>Strictly for authorized personnel only.
            </div>
        </div>
    </div>

</body>
</html>