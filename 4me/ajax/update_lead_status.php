<?php
require_once "../includes/auth.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lead_id = intval($_POST['lead_id'] ?? 0);
    $new_status = $_POST['new_status'] ?? '';

    $allowed_statuses = ['new', 'contacted', 'consultation_booked', 'closed'];

    if ($lead_id > 0 && in_array($new_status, $allowed_statuses)) {
        // Securely update the lead status
        $stmt = $conn->prepare("UPDATE leads SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $lead_id);

        if ($stmt->execute()) {
            // Optional: Set a session flash message here for success
            header("Location: ../leads.php");
            exit();
        }
        else {
            die("Database Error: " . $stmt->error);
        }
    }
    else {
        die("Invalid request parameters.");
    }
}
else {
    header("Location: ../leads.php");
    exit();
}
?>
