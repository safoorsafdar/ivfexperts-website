<?php
/**
 * Log a document download and return a unique tracking code.
 * Gracefully returns null if the table doesn't exist yet.
 */
function log_document_download($conn, $type, $doc_id)
{
    try {
        // Check table exists before attempting insert
        $chk = $conn->query("SHOW TABLES LIKE 'document_download_logs'");
        if (!$chk || $chk->num_rows === 0) {
            return null;
        }

        $patient_id = $_SESSION['portal_patient_id'] ?? null;
        $admin_id   = $_SESSION['admin_id'] ?? null;
        $ip         = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ua         = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $stmt = $conn->prepare("INSERT INTO document_download_logs (document_type, document_id, patient_id, admin_id, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) return null;
        $stmt->bind_param("siiiss", $type, $doc_id, $patient_id, $admin_id, $ip, $ua);

        if ($stmt->execute()) {
            $insert_id    = $conn->insert_id;
            $tracking_code = date('ymd') . str_pad($insert_id % 1000000, 6, '0', STR_PAD_LEFT);

            $update = $conn->prepare("UPDATE document_download_logs SET tracking_code = ? WHERE id = ?");
            if ($update) {
                $update->bind_param("si", $tracking_code, $insert_id);
                $update->execute();
            }
            return $tracking_code;
        }
    } catch (Throwable $e) {
        // Don't crash the print page if logging fails
    }

    return null;
}
?>
