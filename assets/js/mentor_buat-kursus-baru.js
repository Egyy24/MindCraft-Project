document.addEventListener("DOMContentLoaded", function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (mobileMenuToggle && sidebar) {
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && sidebar) {
            if (!sidebar.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        }
    });

    // Form elements
    const form = document.getElementById('createCourseForm');
    const fileInput = document.getElementById('coverImage');
    const fileUpload = document.querySelector('.file-upload');
    const filePreview = document.querySelector('.file-preview');
    const fileRemove = document.querySelector('.file-remove');
    const priceInput = document.getElementById('price');
    const freemiumCheckbox = document.getElementById('freemium');

    // File upload handling dengan image preview
    if (fileInput && fileUpload) {
        // Click to upload
        fileUpload.addEventListener('click', function(e) {
            if (e.target !== fileInput) {
                fileInput.click();
            }
        });

        // File input change dengan preview - FIXED
        fileInput.addEventListener('change', function(e) {
            console.log('File input changed:', e.target.files); // Debug
            const file = e.target.files[0];
            if (file) {
                console.log('File selected:', file.name, file.type); // Debug
                handleFileSelect(file);
            } else {
                console.log('No file selected'); // Debug
                hideImagePreview();
            }
        });

        // Drag and drop dengan preview
        fileUpload.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        fileUpload.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });

        fileUpload.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                // Set ke file input juga
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
                
                handleFileSelect(file);
            }
        });
    } else {
        console.error('File input atau file upload area tidak ditemukan!');
    }

    // Price input formatting
    if (priceInput) {
        priceInput.addEventListener('input', function() {
            let value = this.value.replace(/[^\d]/g, '');
            if (value) {
                // Format number with thousand separators
                value = parseInt(value).toLocaleString('id-ID');
            }
            this.value = value;
        });

        // Disable price input when freemium is checked
        if (freemiumCheckbox) {
            freemiumCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    priceInput.value = '';
                    priceInput.disabled = true;
                    priceInput.placeholder = 'Gratis dengan konten premium berbayar';
                } else {
                    priceInput.disabled = false;
                    priceInput.placeholder = 'Masukkan harga kursus';
                }
            });
        }
    }

    // Form validation
    const validators = {
        title: {
            required: true,
            minLength: 5,
            maxLength: 100
        },
        category: {
            required: true
        },
        difficulty: {
            required: true
        },
        description: {
            required: true,
            minLength: 20,
            maxLength: 1000
        },
        price: {
            required: function() {
                return !freemiumCheckbox?.checked;
            },
            min: 0
        }
    };

    // Validate field
    function validateField(field, value) {
        const rules = validators[field.name];
        if (!rules) return true;

        const errors = [];

        // Required validation
        if (rules.required) {
            const isRequired = typeof rules.required === 'function' ? rules.required() : rules.required;
            if (isRequired && (!value || value.trim() === '')) {
                errors.push('Field ini wajib diisi');
            }
        }

        // Skip other validations if field is empty and not required
        if (!value || value.trim() === '') {
            showFieldError(field, errors);
            return errors.length === 0;
        }

        // Length validations
        if (rules.minLength && value.length < rules.minLength) {
            errors.push(`Minimal ${rules.minLength} karakter`);
        }

        if (rules.maxLength && value.length > rules.maxLength) {
            errors.push(`Maksimal ${rules.maxLength} karakter`);
        }

        // Number validations
        if (rules.min !== undefined) {
            const numValue = parseFloat(value.replace(/[^\d]/g, ''));
            if (numValue < rules.min) {
                errors.push(`Nilai minimal ${rules.min}`);
            }
        }

        showFieldError(field, errors);
        return errors.length === 0;
    }

    // Show field error
    function showFieldError(field, errors) {
        const formGroup = field.closest('.form-group');
        if (!formGroup) return;

        // Remove existing error
        formGroup.classList.remove('error', 'success');
        const existingError = formGroup.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }

        if (errors.length > 0) {
            // Add error state
            formGroup.classList.add('error');
            
            // Add error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = '⚠️ ' + errors[0];
            field.parentNode.appendChild(errorDiv);
        } else if (field.value.trim() !== '') {
            // Add success state
            formGroup.classList.add('success');
        }
    }

    // Real-time validation
    const formFields = form?.querySelectorAll('input, textarea, select');
    formFields?.forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this, this.value);
        });

        field.addEventListener('input', function() {
            // Remove error state while typing
            const formGroup = this.closest('.form-group');
            if (formGroup?.classList.contains('error')) {
                formGroup.classList.remove('error');
                const errorMessage = formGroup.querySelector('.error-message');
                if (errorMessage) {
                    errorMessage.remove();
                }
            }
        });
    });

    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate all fields
            let isValid = true;
            const formData = new FormData(this);
            
            formFields.forEach(field => {
                if (!validateField(field, field.value)) {
                    isValid = false;
                }
            });

            // Check if cover image is uploaded (optional but recommended)
            if (!fileInput?.files.length) {
                showNotification('Disarankan untuk mengunggah gambar cover kursus', 'warning');
            }

            if (isValid) {
                submitForm(formData);
            } else {
                showNotification('Mohon periksa kembali form Anda', 'error');
                
                // Focus on first error field
                const firstError = form.querySelector('.form-group.error input, .form-group.error textarea, .form-group.error select');
                if (firstError) {
                    firstError.focus();
                }
            }
        });
    }

    // Submit form
    function submitForm(formData) {
        const submitButtons = document.querySelectorAll('.btn');
        const primaryButton = document.querySelector('.btn-primary');
        
        // Show loading state
        submitButtons.forEach(btn => {
            btn.disabled = true;
        });
        
        if (primaryButton) {
            primaryButton.classList.add('loading');
            primaryButton.textContent = 'Menyimpan...';
        }

        // Get form action from button clicked
        const action = document.activeElement.dataset.action || 'draft';
        formData.append('action', action);

        // Submit form to server (PHP will handle this)
        form.submit();
    }

    // Clear all validations
    function clearAllValidations() {
        const formGroups = form?.querySelectorAll('.form-group');
        formGroups?.forEach(group => {
            group.classList.remove('error', 'success');
            const errorMessage = group.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.remove();
            }
        });
    }

    // Auto-save draft functionality
    let autoSaveTimeout;
    const autoSaveDelay = 30000; // 30 seconds

    function autoSave() {
        if (!form) return;
        
        const formData = new FormData(form);
        formData.append('action', 'auto_save');
        
        // Simple validation for auto-save
        const title = formData.get('title');
        if (!title || title.trim().length < 3) return;

        // Show auto-save indicator
        showNotification('Draft otomatis tersimpan 💾', 'info', 2000);
        
        // Save to localStorage for draft functionality
        const draftData = Object.fromEntries(formData);
        courseUtils.saveToStorage(draftData);
    }

    // Track form changes for auto-save
    formFields?.forEach(field => {
        field.addEventListener('input', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(autoSave, autoSaveDelay);
        });
    });

    // Character counter for description
    const descriptionField = document.getElementById('description');
    if (descriptionField) {
        const maxLength = 1000;
        const counter = document.getElementById('descriptionCount');
        
        function updateCounter() {
            const length = descriptionField.value.length;
            if (counter) {
                counter.textContent = length;
                
                if (length > maxLength * 0.9) {
                    counter.style.color = '#F56500';
                } else if (length > maxLength * 0.8) {
                    counter.style.color = '#E53E3E';
                } else {
                    counter.style.color = '#718096';
                }
            }
        }
        
        descriptionField.addEventListener('input', updateCounter);
        updateCounter(); // Initial count
    }

    // Responsive handling
    function handleResize() {
        if (sidebar) {
            if (window.innerWidth <= 768) {
                sidebar.classList.add('mobile');
            } else {
                sidebar.classList.remove('mobile', 'open');
            }
        }
    }

    handleResize();
    window.addEventListener('resize', handleResize);

    // Initialize animations
    setTimeout(() => {
        const elements = document.querySelectorAll('.fade-in-up');
        elements.forEach((element, index) => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }, 100);
});

// Handle file selection dengan image preview - FIXED VERSION
function handleFileSelect(file) {
    console.log('handleFileSelect called with:', file); // Debug
    
    if (!file) {
        console.log('No file provided to handleFileSelect');
        return;
    }

    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    console.log('File type:', file.type); // Debug
    
    if (!allowedTypes.includes(file.type)) {
        showNotification('Hanya file gambar (JPEG, PNG, GIF, WebP) yang diizinkan', 'error');
        return;
    }

    // Validate file size (max 5MB)
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        showNotification('Ukuran file tidak boleh lebih dari 5MB', 'error');
        return;
    }

    console.log('File validation passed, showing loading...'); // Debug
    
    // Show loading state
    showImageLoading();

    // Create FileReader untuk preview
    const reader = new FileReader();
    
    reader.onload = function(e) {
        console.log('FileReader onload triggered'); // Debug
        
        // Create image object untuk mendapatkan dimensions
        const img = new Image();
        
        img.onload = function() {
            console.log('Image loaded, dimensions:', this.naturalWidth, 'x', this.naturalHeight); // Debug
            
            // Validate dimensions
            const minWidth = 300;
            const minHeight = 200;
            if (this.naturalWidth < minWidth || this.naturalHeight < minHeight) {
                showNotification(`Resolusi minimum ${minWidth}×${minHeight} pixels`, 'error');
                hideImagePreview();
                return;
            }
            
            console.log('Showing image preview...'); // Debug
            showImagePreview(file, e.target.result, {
                width: this.naturalWidth,
                height: this.naturalHeight
            });
        };
        
        img.onerror = function() {
            console.error('Error loading image for dimensions');
            showNotification('Gagal memuat gambar', 'error');
            hideImagePreview();
        };
        
        img.src = e.target.result;
    };
    
    reader.onerror = function() {
        console.error('FileReader error');
        showNotification('Gagal membaca file gambar', 'error');
        hideImagePreview();
    };
    
    console.log('Starting FileReader...'); // Debug
    reader.readAsDataURL(file);
}

// Show loading state - FIXED VERSION
function showImageLoading() {
    console.log('showImageLoading called'); // Debug
    
    const fileUpload = document.querySelector('.file-upload');
    let imagePreviewContainer = document.querySelector('.image-preview-container');
    
    if (!imagePreviewContainer) {
        console.log('Creating container for loading state'); // Debug
        imagePreviewContainer = document.createElement('div');
        imagePreviewContainer.className = 'image-preview-container';
        fileUpload.parentNode.insertBefore(imagePreviewContainer, fileUpload.nextSibling);
    }
    
    const loadingHTML = `
        <div class="image-loading" style="display: flex; align-items: center; justify-content: center; height: 200px; background: #f8f9fa; color: #718096; font-size: 14px; border-radius: 8px;">
            <div class="loading-spinner" style="width: 24px; height: 24px; border: 2px solid #e2e8f0; border-top: 2px solid #3A59D1; border-radius: 50%; animation: spin 1s linear infinite; margin-right: 12px;"></div>
            Memuat preview gambar...
        </div>
    `;
    
    imagePreviewContainer.innerHTML = loadingHTML;
    
    fileUpload.style.display = 'none';
    imagePreviewContainer.style.display = 'block';
    imagePreviewContainer.classList.add('show');
    
    console.log('Loading state displayed'); // Debug
}

// Create image preview container jika belum ada
function createImagePreviewContainer() {
    const fileUpload = document.querySelector('.file-upload');
    const container = document.createElement('div');
    container.className = 'image-preview-container';
    fileUpload.parentNode.insertBefore(container, fileUpload.nextSibling);
}

// Show image preview - FIXED VERSION
function showImagePreview(file, imageSrc, dimensions) {
    console.log('showImagePreview called'); // Debug
    
    const fileUpload = document.querySelector('.file-upload');
    let imagePreviewContainer = document.querySelector('.image-preview-container');
    
    console.log('fileUpload found:', !!fileUpload); // Debug
    console.log('imagePreviewContainer found:', !!imagePreviewContainer); // Debug
    
    // Create container if it doesn't exist
    if (!imagePreviewContainer) {
        console.log('Creating new image preview container'); // Debug
        imagePreviewContainer = document.createElement('div');
        imagePreviewContainer.className = 'image-preview-container';
        fileUpload.parentNode.insertBefore(imagePreviewContainer, fileUpload.nextSibling);
    }
    
    // Clear any existing content
    imagePreviewContainer.innerHTML = '';
    
    // Buat preview dengan gambar yang sebenarnya
    const previewHTML = `
        <div class="image-preview-wrapper">
            <div class="preview-image-container">
                <img src="${imageSrc}" alt="Preview Cover Kursus" class="preview-image" style="width: 100%; height: 280px; object-fit: cover; display: block;">
                <div class="preview-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(45deg, rgba(0,0,0,0.5) 0%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0.5) 100%); display: flex; align-items: center; justify-content: center; gap: 16px; opacity: 0; transition: opacity 0.3s ease;">
                    <button type="button" class="preview-action-btn change-btn" style="background: rgba(255,255,255,0.95); border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500; color: #2d3748; display: flex; align-items: center; gap: 8px;">
                        <span class="btn-icon">🔄</span>
                        <span class="btn-text">Ganti</span>
                    </button>
                    <button type="button" class="preview-action-btn remove-btn" style="background: rgba(229,62,62,0.95); color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                        <span class="btn-icon">🗑️</span>
                        <span class="btn-text">Hapus</span>
                    </button>
                </div>
            </div>
            <div class="preview-info" style="padding: 16px 20px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-top: 1px solid rgba(0,0,0,0.05);">
                <div class="file-details">
                    <div class="file-name" style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 14px; color: #2d3748; margin-bottom: 12px;">
                        <span class="name-icon">📁</span>
                        ${file.name}
                    </div>
                    <div class="file-specs" style="display: flex; gap: 16px; flex-wrap: wrap;">
                        <span class="spec-item" style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #718096; background: white; padding: 6px 12px; border-radius: 20px; border: 1px solid rgba(0,0,0,0.1);">
                            <span class="spec-icon">📏</span>
                            ${dimensions.width} × ${dimensions.height}px
                        </span>
                        <span class="spec-item" style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #718096; background: white; padding: 6px 12px; border-radius: 20px; border: 1px solid rgba(0,0,0,0.1);">
                            <span class="spec-icon">💾</span>
                            ${formatFileSize(file.size)}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    imagePreviewContainer.innerHTML = previewHTML;
    
    // Add hover effect manually since CSS might not be loaded properly
    const previewContainer = imagePreviewContainer.querySelector('.preview-image-container');
    const overlay = imagePreviewContainer.querySelector('.preview-overlay');
    
    if (previewContainer && overlay) {
        previewContainer.addEventListener('mouseenter', function() {
            overlay.style.opacity = '1';
        });
        
        previewContainer.addEventListener('mouseleave', function() {
            overlay.style.opacity = '0';
        });
    }
    
    // Hide file upload area dan show preview
    fileUpload.style.display = 'none';
    imagePreviewContainer.style.display = 'block';
    imagePreviewContainer.classList.add('show');
    
    console.log('Preview container is now visible'); // Debug
    
    // Add event listeners untuk tombol
    const changeBtn = imagePreviewContainer.querySelector('.change-btn');
    const removeBtn = imagePreviewContainer.querySelector('.remove-btn');
    const fileInput = document.getElementById('coverImage');
    
    if (changeBtn) {
        changeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log('Change button clicked'); // Debug
            fileInput.click();
        });
    }
    
    if (removeBtn) {
        removeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log('Remove button clicked'); // Debug
            removeImage();
        });
    }
    
    // Show success notification
    showNotification('✅ Gambar cover berhasil dipilih!', 'success', 3000);
    
    console.log('showImagePreview completed successfully'); // Debug
}

// Remove image
function removeImage() {
    const fileInput = document.getElementById('coverImage');
    const fileUpload = document.querySelector('.file-upload');
    const imagePreviewContainer = document.querySelector('.image-preview-container');
    
    // Clear file input
    if (fileInput) fileInput.value = '';
    
    // Hide preview and show upload area
    if (imagePreviewContainer) {
        imagePreviewContainer.classList.remove('show');
        imagePreviewContainer.innerHTML = '';
    }
    
    if (fileUpload) fileUpload.style.display = 'block';
    
    showNotification('🗑️ Gambar telah dihapus', 'info', 2000);
}

// Hide image preview
function hideImagePreview() {
    const fileUpload = document.querySelector('.file-upload');
    const imagePreviewContainer = document.querySelector('.image-preview-container');
    
    if (imagePreviewContainer) {
        imagePreviewContainer.classList.remove('show');
    }
    
    if (fileUpload) fileUpload.style.display = 'block';
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Notification system
function showNotification(message, type = 'info', duration = 4000) {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => {
        notification.remove();
    });

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const colors = {
        success: '#2B992B',
        error: '#E53E3E',
        warning: '#F56500',
        info: '#3A59D1'
    };
    
    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };
    
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${colors[type]};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        z-index: 1001;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 8px;
        max-width: 400px;
        animation: slideInRight 0.3s ease;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
    `;
    
    notification.innerHTML = `${icons[type]} ${message}`;
    document.body.appendChild(notification);
    
    // Auto remove
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, duration);
}

// Add notification animations to CSS if not already present
if (!document.querySelector('#notification-styles')) {
    const notificationStyles = document.createElement('style');
    notificationStyles.id = 'notification-styles';
    notificationStyles.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(notificationStyles);
}

// Utility functions
const courseUtils = {
    // Format price
    formatPrice: function(price) {
        if (!price) return 'Gratis';
        return 'Rp ' + parseInt(price).toLocaleString('id-ID');
    },
    
    // Validate image file
    validateImage: function(file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!allowedTypes.includes(file.type)) {
            return { valid: false, message: 'Format file tidak didukung' };
        }
        
        if (file.size > maxSize) {
            return { valid: false, message: 'Ukuran file terlalu besar (max 5MB)' };
        }
        
        return { valid: true };
    },
    
    // Generate slug from title
    generateSlug: function(title) {
        return title
            .toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .trim();
    },
    
    // Save to localStorage (tidak menggunakan localStorage karena tidak didukung)
    saveToStorage: function(data) {
        try {
            // Simpan ke variabel global sementara untuk session ini
            window.courseDraft = {
                ...data,
                timestamp: new Date().toISOString()
            };
            console.log('Draft tersimpan ke memory:', window.courseDraft);
        } catch (e) {
            console.warn('Could not save draft:', e);
        }
    },
    
    // Load from storage
    loadFromStorage: function() {
        try {
            return window.courseDraft || null;
        } catch (e) {
            console.warn('Could not load draft:', e);
            return null;
        }
    },
    
    // Clear storage
    clearStorage: function() {
        try {
            delete window.courseDraft;
        } catch (e) {
            console.warn('Could not clear draft:', e);
        }
    }
};

// Enhanced image utilities
const imageUtils = {
    // Validate image dimensions
    validateDimensions: function(width, height, minWidth = 300, minHeight = 200) {
        if (width < minWidth || height < minHeight) {
            return {
                valid: false,
                message: `Resolusi minimum ${minWidth}×${minHeight} pixels`
            };
        }
        return { valid: true };
    },
    
    // Get image quality indicator
    getQualityIndicator: function(width, height) {
        const megapixels = (width * height) / 1000000;
        
        if (megapixels >= 2) return { level: 'Tinggi', color: '#2B992B' };
        if (megapixels >= 1) return { level: 'Sedang', color: '#F56500' };
        return { level: 'Rendah', color: '#E53E3E' };
    },
    
    // Generate thumbnail
    generateThumbnail: function(file, maxWidth = 150, maxHeight = 150) {
        return new Promise((resolve, reject) => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();
            
            img.onload = function() {
                const ratio = Math.min(maxWidth / this.width, maxHeight / this.height);
                canvas.width = this.width * ratio;
                canvas.height = this.height * ratio;
                
                ctx.drawImage(this, 0, 0, canvas.width, canvas.height);
                
                canvas.toBlob(resolve, 'image/jpeg', 0.8);
            };
            
            img.onerror = reject;
            img.src = URL.createObjectURL(file);
        });
    },
    
    // Compress image if too large
    compressImage: function(file, maxSizeMB = 2, quality = 0.8) {
        return new Promise((resolve, reject) => {
            if (file.size <= maxSizeMB * 1024 * 1024) {
                resolve(file);
                return;
            }
            
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();
            
            img.onload = function() {
                // Calculate new dimensions
                const ratio = Math.sqrt((maxSizeMB * 1024 * 1024) / file.size);
                canvas.width = this.width * ratio;
                canvas.height = this.height * ratio;
                
                ctx.drawImage(this, 0, 0, canvas.width, canvas.height);
                
                canvas.toBlob(resolve, file.type, quality);
            };
            
            img.onerror = reject;
            img.src = URL.createObjectURL(file);
        });
    }
};

// Auto-load draft on page load (menggunakan memory storage)
document.addEventListener('DOMContentLoaded', function() {
    const savedDraft = courseUtils.loadFromStorage();
    if (savedDraft && savedDraft.title) {
        const shouldLoad = confirm(
            'Ditemukan draft kursus yang belum selesai. Ingin melanjutkan?'
        );
        
        if (shouldLoad) {
            loadDraftToForm(savedDraft);
            showNotification('Draft berhasil dimuat! 📄', 'info');
        } else {
            courseUtils.clearStorage();
        }
    }
});

// Load draft to form
function loadDraftToForm(draft) {
    const form = document.getElementById('createCourseForm');
    if (!form) return;
    
    Object.keys(draft).forEach(key => {
        if (key === 'timestamp') return;
        
        const field = form.querySelector(`[name="${key}"]`);
        if (field) {
            if (field.type === 'radio') {
                const radioField = form.querySelector(`[name="${key}"][value="${draft[key]}"]`);
                if (radioField) radioField.checked = true;
            } else if (field.type === 'checkbox') {
                field.checked = draft[key];
            } else {
                field.value = draft[key];
            }
        }
    });
}

// Enhanced form handling functions
function initializeFormFeatures() {
    // Smart form completion suggestions
    const titleField = document.getElementById('title');
    const categoryField = document.getElementById('category');
    
    if (titleField && categoryField) {
        titleField.addEventListener('blur', function() {
            const title = this.value.toLowerCase();
            
            // Auto-suggest category based on title keywords
            const categoryKeywords = {
                'programming': ['code', 'coding', 'program', 'javascript', 'python', 'web', 'app'],
                'ui-ux': ['design', 'ui', 'ux', 'interface', 'user', 'figma'],
                'bisnis': ['business', 'marketing', 'sales', 'entrepreneur', 'startup'],
                'fotografi': ['photo', 'camera', 'photography', 'picture'],
                'musik': ['music', 'song', 'instrument', 'guitar', 'piano']
            };
            
            if (!categoryField.value) {
                for (const [category, keywords] of Object.entries(categoryKeywords)) {
                    if (keywords.some(keyword => title.includes(keyword))) {
                        categoryField.value = category;
                        showNotification(`Kategori "${category}" dipilih otomatis berdasarkan judul`, 'info', 3000);
                        break;
                    }
                }
            }
        });
    }
}

// Form progress tracking
function trackFormProgress() {
    const requiredFields = document.querySelectorAll('input[required], textarea[required], select[required]');
    const progressIndicator = createProgressIndicator();
    
    function updateProgress() {
        let filledCount = 0;
        
        requiredFields.forEach(field => {
            if (field.type === 'radio') {
                const radioGroup = document.querySelectorAll(`input[name="${field.name}"]`);
                const isChecked = Array.from(radioGroup).some(radio => radio.checked);
                if (isChecked) filledCount++;
            } else if (field.value.trim() !== '') {
                filledCount++;
            }
        });
        
        const progress = (filledCount / requiredFields.length) * 100;
        updateProgressBar(progressIndicator, progress);
    }
    
    requiredFields.forEach(field => {
        field.addEventListener('input', updateProgress);
        field.addEventListener('change', updateProgress);
    });
    
    updateProgress(); // Initial update
}

function createProgressIndicator() {
    const indicator = document.createElement('div');
    indicator.style.cssText = `
        position: fixed;
        top: 60px;
        left: 240px;
        right: 0;
        height: 3px;
        background: rgba(58, 89, 209, 0.1);
        z-index: 999;
    `;
    
    const bar = document.createElement('div');
    bar.style.cssText = `
        height: 100%;
        background: var(--primary-blue);
        width: 0%;
        transition: width 0.3s ease;
    `;
    
    indicator.appendChild(bar);
    document.body.appendChild(indicator);
    
    return bar;
}

function updateProgressBar(bar, progress) {
    bar.style.width = progress + '%';
}

// Initialize enhanced features
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        initializeFormFeatures();
        trackFormProgress();
    }, 1000);
});

// Export for external use
window.courseUtils = courseUtils;
window.imageUtils = imageUtils;