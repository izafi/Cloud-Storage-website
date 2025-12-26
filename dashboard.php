<?php
require_once 'db.php';
requireLogin();

// Get user's files
$stmt = $pdo->prepare("
    SELECT * FROM files 
    WHERE user_id = ? 
    ORDER BY uploaded_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$files = $stmt->fetchAll();

// Get storage usage
$stmt = $pdo->prepare("SELECT SUM(file_size) as total_size FROM files WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$storage = $stmt->fetch();
$total_size = $storage['total_size'] ?: 0;
$total_size_mb = round($total_size / (1024 * 1024), 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CloudBox</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="logo">
                <i class="fas fa-cloud"></i>
                <span>CloudBox</span>
            </div>
            <div class="nav-links">
                <span class="welcome-text">
                    Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                </span>
                <a href="logout.php" class="btn btn-outline">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </nav>

        <main class="dashboard">
            <div class="dashboard-header">
                <h1>My Cloud Storage</h1>
                <div class="storage-info">
                    <div class="storage-bar">
                        <div class="storage-fill" style="width: <?php echo min(($total_size_mb / 1000) * 100, 100); ?>%"></div>
                    </div>
                    <span class="storage-text">
                        <?php echo $total_size_mb; ?> MB used of 1 GB
                    </span>
                </div>
            </div>

            <div class="upload-area" id="uploadArea">
                <form action="upload.php" method="POST" enctype="multipart/form-data" class="upload-form">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <h3>Drag & Drop or Click to Upload</h3>
                    <p>Max file size: 50MB</p>
                    <input type="file" name="file" id="fileInput" multiple 
                           accept=".jpg,.jpeg,.png,.gif,.webp,.mp4,.avi,.mkv,.pdf,.docx,.zip,.txt">
                    <label for="fileInput" class="btn btn-primary">
                        <i class="fas fa-folder-open"></i> Choose Files
                    </label>
                    <button type="submit" class="btn btn-success" id="uploadBtn">
                        <i class="fas fa-upload"></i> Upload Files
                    </button>
                </form>
                <div class="upload-progress" id="uploadProgress">
                    <div class="progress-bar" id="progressBar"></div>
                    <span class="progress-text" id="progressText">0%</span>
                </div>
            </div>

            <div class="files-header">
                <h2>Your Files</h2>
                <div class="file-count">
                    <i class="fas fa-folder"></i>
                    <span><?php echo count($files); ?> files</span>
                </div>
            </div>

            <?php if (empty($files)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>No files yet</h3>
                    <p>Upload your first file to get started</p>
                </div>
            <?php else: ?>
                <div class="files-grid" id="filesGrid">
                    <?php foreach ($files as $file): 
                        $file_icon = getFileIcon($file['file_type']);
                        $file_size = formatFileSize($file['file_size']);
                        $upload_date = date('M d, Y H:i', strtotime($file['uploaded_at']));
                    ?>
                        <div class="file-card" data-file-id="<?php echo $file['id']; ?>">
                            <div class="file-icon">
                                <i class="<?php echo $file_icon; ?>"></i>
                            </div>
                            <div class="file-info">
                                <h4 class="file-name" title="<?php echo htmlspecialchars($file['file_name']); ?>">
                                    <?php echo htmlspecialchars($file['file_name']); ?>
                                </h4>
                                <p class="file-meta">
                                    <span class="file-size"><?php echo $file_size; ?></span>
                                    <span class="file-date"><?php echo $upload_date; ?></span>
                                </p>
                            </div>
                            <div class="file-actions">
                                <?php if (strpos($file['file_type'], 'image/') === 0): ?>
                                    <button class="btn-action btn-preview" data-file="<?php echo $file['file_path']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                <?php elseif (strpos($file['file_type'], 'video/') === 0): ?>
                                    <button class="btn-action btn-preview" data-file="<?php echo $file['file_path']; ?>" data-type="video">
                                        <i class="fas fa-play"></i>
                                    </button>
                                <?php endif; ?>
                                <a href="download.php?id=<?php echo $file['id']; ?>" class="btn-action btn-download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <button class="btn-action btn-delete" data-file-id="<?php echo $file['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Preview Modal -->
    <div class="modal" id="previewModal">
        <div class="modal-content">
            <button class="modal-close" id="modalClose">&times;</button>
            <div class="modal-body" id="modalBody">
                <!-- Preview content goes here -->
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast"></div>

    <script src="script.js"></script>
</body>
</html>

<?php
// Helper functions
function getFileIcon($file_type) {
    if (strpos($file_type, 'image/') === 0) {
        return 'fas fa-file-image';
    } elseif (strpos($file_type, 'video/') === 0) {
        return 'fas fa-file-video';
    } elseif (strpos($file_type, 'audio/') === 0) {
        return 'fas fa-file-audio';
    } elseif (strpos($file_type, 'application/pdf') === 0) {
        return 'fas fa-file-pdf';
    } elseif (strpos($file_type, 'application/zip') === 0 || 
              strpos($file_type, 'application/x-rar-compressed') === 0) {
        return 'fas fa-file-archive';
    } elseif (strpos($file_type, 'text/') === 0) {
        return 'fas fa-file-alt';
    } else {
        return 'fas fa-file';
    }
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>