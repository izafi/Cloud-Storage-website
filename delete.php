<?php
require_once 'db.php';
requireLogin();

header('Content-Type: application/json');

if (!isset($_POST['file_id'])) {
    echo json_encode(['success' => false, 'message' => 'No file specified']);
    exit;
}

$file_id = $_POST['file_id'];

try {
    // Get file info
    $stmt = $pdo->prepare("
        SELECT * FROM files 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$file_id, $_SESSION['user_id']]);
    $file = $stmt->fetch();

    if (!$file) {
        echo json_encode(['success' => false, 'message' => 'File not found']);
        exit;
    }

    // Delete file from disk
    if (file_exists($file['file_path'])) {
        unlink($file['file_path']);
    }

    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
    $stmt->execute([$file_id]);

    echo json_encode(['success' => true, 'message' => 'File deleted successfully']);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Delete failed']);
}
?>