// Page Transitions
document.addEventListener('DOMContentLoaded', function() {
    // Fade in animation
    document.body.style.opacity = '0';
    document.body.style.transition = 'opacity 0.5s ease';
    
    setTimeout(() => {
        document.body.style.opacity = '1';
    }, 100);
    
    // Initialize components
    initUpload();
    initFileActions();
    initModal();
    initAnimations();
});

// Animate elements on scroll
function initAnimations() {
    const animateElements = document.querySelectorAll('.animate-text, .animate-text-delay');
    
    animateElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'all 0.6s ease';
        
        setTimeout(() => {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 200);
    });
}

// Upload System
function initUpload() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const uploadForm = document.querySelector('.upload-form');
    const uploadBtn = document.getElementById('uploadBtn');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    
    if (!uploadArea) return;
    
    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.style.borderColor = '#4361ee';
        uploadArea.style.backgroundColor = '#f0f4ff';
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.style.borderColor = '';
        uploadArea.style.backgroundColor = '';
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.style.borderColor = '';
        uploadArea.style.backgroundColor = '';
        
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            showToast(`Selected ${e.dataTransfer.files.length} file(s)`, 'info');
        }
    });
    
    // File input change
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) {
            showToast(`Selected ${fileInput.files.length} file(s)`, 'info');
        }
    });
    
    // Form submission with AJAX
    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!fileInput.files.length) {
            showToast('Please select files to upload', 'error');
            return;
        }
        
        const formData = new FormData(uploadForm);
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        uploadProgress.style.display = 'block';
        
        try {
            const xhr = new XMLHttpRequest();
            
            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progressBar.style.width = percentComplete + '%';
                    progressText.textContent = Math.round(percentComplete) + '%';
                }
            };
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response.success) {
                        showToast(response.message, 'success');
                        
                        // Add new file card to grid
                        addFileCard({
                            id: response.file_id,
                            file_name: response.file_name,
                            file_size: response.file_size,
                            file_type: response.file_type,
                            file_path: response.file_path,
                            uploaded_at: new Date().toISOString()
                        });
                        
                        // Reset form
                        uploadForm.reset();
                        uploadProgress.style.display = 'none';
                        progressBar.style.width = '0%';
                        progressText.textContent = '0%';
                    } else {
                        showToast(response.message, 'error');
                    }
                } else {
                    showToast('Upload failed. Please try again.', 'error');
                }
                
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Files';
            };
            
            xhr.onerror = function() {
                showToast('Network error. Please check your connection.', 'error');
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Files';
                uploadProgress.style.display = 'none';
            };
            
            xhr.open('POST', 'upload.php');
            xhr.send(formData);
            
        } catch (error) {
            console.error('Upload error:', error);
            showToast('Upload failed. Please try again.', 'error');
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Files';
            uploadProgress.style.display = 'none';
        }
    });
}

// File Actions
function initFileActions() {
    // Delete files
    document.addEventListener('click', async (e) => {
        if (e.target.closest('.btn-delete')) {
            const button = e.target.closest('.btn-delete');
            const fileId = button.dataset.fileId;
            const fileCard = button.closest('.file-card');
            
            if (confirm('Are you sure you want to delete this file?')) {
                try {
                    const response = await fetch('delete.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `file_id=${fileId}`
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Animate removal
                        fileCard.style.transform = 'translateX(100px)';
                        fileCard.style.opacity = '0';
                        
                        setTimeout(() => {
                            fileCard.remove();
                            updateFileCount();
                            showToast(result.message, 'success');
                        }, 300);
                    } else {
                        showToast(result.message, 'error');
                    }
                } catch (error) {
                    showToast('Delete failed. Please try again.', 'error');
                }
            }
        }
        
        // Preview files
        if (e.target.closest('.btn-preview')) {
            const button = e.target.closest('.btn-preview');
            const filePath = button.dataset.file;
            const fileType = button.dataset.type || 'image';
            
            openPreviewModal(filePath, fileType);
        }
    });
}

// Add new file card to grid
function addFileCard(fileData) {
    const filesGrid = document.getElementById('filesGrid');
    if (!filesGrid) return;
    
    const emptyState = document.querySelector('.empty-state');
    if (emptyState) {
        emptyState.remove();
    }
    
    // Format file size
    const formatFileSize = (bytes) => {
        if (bytes >= 1073741824) {
            return (bytes / 1073741824).toFixed(2) + ' GB';
        } else if (bytes >= 1048576) {
            return (bytes / 1048576).toFixed(2) + ' MB';
        } else if (bytes >= 1024) {
            return (bytes / 1024).toFixed(2) + ' KB';
        } else {
            return bytes + ' bytes';
        }
    };
    
    // Get file icon
    const getFileIcon = (fileType) => {
        if (fileType.startsWith('image/')) {
            return 'fas fa-file-image';
        } else if (fileType.startsWith('video/')) {
            return 'fas fa-file-video';
        } else if (fileType.startsWith('audio/')) {
            return 'fas fa-file-audio';
        } else if (fileType === 'application/pdf') {
            return 'fas fa-file-pdf';
        } else if (fileType.includes('zip') || fileType.includes('rar')) {
            return 'fas fa-file-archive';
        } else if (fileType.startsWith('text/')) {
            return 'fas fa-file-alt';
        } else {
            return 'fas fa-file';
        }
    };
    
    const fileCard = document.createElement('div');
    fileCard.className = 'file-card';
    fileCard.dataset.fileId = fileData.id;
    fileCard.style.opacity = '0';
    fileCard.style.transform = 'translateY(20px)';
    
    const fileSize = formatFileSize(fileData.file_size);
    const uploadDate = new Date(fileData.uploaded_at).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    const fileIcon = getFileIcon(fileData.file_type);
    
    fileCard.innerHTML = `
        <div class="file-icon">
            <i class="${fileIcon}"></i>
        </div>
        <div class="file-info">
            <h4 class="file-name" title="${fileData.file_name}">
                ${fileData.file_name}
            </h4>
            <p class="file-meta">
                <span class="file-size">${fileSize}</span>
                <span class="file-date">${uploadDate}</span>
            </p>
        </div>
        <div class="file-actions">
            ${fileData.file_type.startsWith('image/') || fileData.file_type.startsWith('video/') ? `
                <button class="btn-action btn-preview" 
                        data-file="${fileData.file_path}"
                        data-type="${fileData.file_type.startsWith('video/') ? 'video' : 'image'}">
                    <i class="fas ${fileData.file_type.startsWith('video/') ? 'fa-play' : 'fa-eye'}"></i>
                </button>
            ` : ''}
            <a href="download.php?id=${fileData.id}" class="btn-action btn-download">
                <i class="fas fa-download"></i>
            </a>
            <button class="btn-action btn-delete" data-file-id="${fileData.id}">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    filesGrid.prepend(fileCard);
    
    // Animate in
    setTimeout(() => {
        fileCard.style.opacity = '1';
        fileCard.style.transform = 'translateY(0)';
        fileCard.style.transition = 'all 0.3s ease';
    }, 10);
    
    // Re-attach event listeners
    const previewBtn = fileCard.querySelector('.btn-preview');
    if (previewBtn) {
        previewBtn.addEventListener('click', () => {
            openPreviewModal(
                previewBtn.dataset.file,
                previewBtn.dataset.type
            );
        });
    }
    
    updateFileCount();
}

// Update file count
function updateFileCount() {
    const fileCount = document.querySelector('.file-count span');
    const filesGrid = document.getElementById('filesGrid');
    
    if (fileCount && filesGrid) {
        const count = filesGrid.children.length;
        fileCount.textContent = `${count} file${count !== 1 ? 's' : ''}`;
    }
}

// Modal System
function initModal() {
    const modal = document.getElementById('previewModal');
    const modalClose = document.getElementById('modalClose');
    
    if (modalClose) {
        modalClose.addEventListener('click', () => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
    }
    
    // Close modal on ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
    
    // Close modal on outside click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
}

// Open preview modal
function openPreviewModal(filePath, fileType) {
    const modal = document.getElementById('previewModal');
    const modalBody = document.getElementById('modalBody');
    
    if (!modal || !modalBody) return;
    
    modalBody.innerHTML = '';
    
    if (fileType === 'image') {
        const img = document.createElement('img');
        img.src = filePath;
        img.alt = 'Preview';
        modalBody.appendChild(img);
    } else if (fileType === 'video') {
        const video = document.createElement('video');
        video.src = filePath;
        video.controls = true;
        video.autoplay = true;
        video.style.maxWidth = '100%';
        modalBody.appendChild(video);
    } else {
        modalBody.innerHTML = `<p>Preview not available for this file type.</p>`;
    }
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// Toast Notifications
function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    if (!toast) return;
    
    // Clear any existing timeout
    if (toast.timeoutId) {
        clearTimeout(toast.timeoutId);
    }
    
    // Set content and style
    toast.innerHTML = `
        <i class="fas ${getToastIcon(type)}"></i>
        <span>${message}</span>
    `;
    
    toast.className = 'toast';
    toast.classList.add(`toast-${type}`);
    
    // Show toast
    toast.style.display = 'flex';
    
    // Hide after 3 seconds
    toast.timeoutId = setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

function getToastIcon(type) {
    switch(type) {
        case 'success': return 'fa-check-circle';
        case 'error': return 'fa-exclamation-circle';
        case 'warning': return 'fa-exclamation-triangle';
        case 'info': return 'fa-info-circle';
        default: return 'fa-info-circle';
    }
}

// Error alert animation
const errorAlert = document.getElementById('errorAlert');
if (errorAlert) {
    setTimeout(() => {
        errorAlert.style.opacity = '0';
        errorAlert.style.transform = 'translateX(20px)';
        errorAlert.style.transition = 'all 0.3s ease';
        
        setTimeout(() => {
            errorAlert.remove();
        }, 300);
    }, 5000);
}