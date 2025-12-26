<?php
require_once 'db.php';
requireLogin();

// Create uploads directory if it doesn't exist
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Create user-specific directory
$user_dir = 'uploads/user_' . $_SESSION['user_id'];
if (!file_exists($user_dir)) {
    mkdir($user_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $max_size = 50 * 1024 * 1024; // 50MB
    
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die(json_encode(['success' => false, 'message' => 'Upload error']));
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        die(json_encode(['success' => false, 'message' => 'File too large (max 50MB)']));
    }
    
    // Generate unique filename
    $original_name = basename($file['name']);
    $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $unique_name = uniqid() . '_' . time() . '.' . $file_extension;
    $file_path = $user_dir . '/' . $unique_name;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Save to database
        $stmt = $pdo->prepare("
            INSERT INTO files (user_id, file_name, file_path, file_type, file_size) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $original_name,
            $file_path,
            $file['type'],
            $file['size']
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'File uploaded successfully',
            'file_id' => $pdo->lastInsertId(),
            'file_name' => $original_name,
            'file_size' => $file['size'],
            'file_type' => $file['type']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    }
    exit;
}

// If not POST request, redirect to dashboard
header('Location: dashboard.php');
exit;
?>