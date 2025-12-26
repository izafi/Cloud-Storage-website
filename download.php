<?php
require_once 'db.php';
requireLogin();

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$file_id = $_GET['id'];

try {
    // Get file info
    $stmt = $pdo->prepare("
        SELECT * FROM files 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$file_id, $_SESSION['user_id']]);
    $file = $stmt->fetch();

    if (!$file || !file_exists($file['file_path'])) {
        die('File not found');
    }

    // Set headers for download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file['file_path']));
    
    // Clear output buffer
    ob_clean();
    flush();
    
    // Read file
    readfile($file['file_path']);
    exit;
    
} catch(PDOException $e) {
    die('Download failed: ' . $e->getMessage());
}
?>