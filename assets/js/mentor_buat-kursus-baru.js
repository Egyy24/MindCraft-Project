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

    // File upload handling
    if (fileInput && fileUpload) {
        // Click to upload
        fileUpload.addEventListener('click', function(e) {
            if (e.target !== fileInput) {
                fileInput.click();
            }
        });

        // File input change
        fileInput.addEventListener('change', function(e) {
            handleFileSelect(e.target.files[0]);
        });

        // Drag and drop
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
                handleFileSelect(files[0]);
            }
        });

        // File remove
        if (fileRemove) {
            fileRemove.addEventListener('click', function(e) {
                e.stopPropagation();
                removeFile();
            });
        }
    }

    // Handle file selection
    function handleFileSelect(file) {
        if (!file) return;

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
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

        // Show file preview
        showFilePreview(file);
    }

    // Show file preview
    function showFilePreview(file) {
        if (!filePreview) return;

        const fileName = document.querySelector('.file-name');
        const fileSize = document.querySelector('.file-size');

        if (fileName) fileName.textContent = file.name;
        if (fileSize) fileSize.textContent = formatFileSize(file.size);

        filePreview.classList.add('show');
        fileUpload.style.display = 'none';
    }

    // Remove file
    function removeFile() {
        if (fileInput) fileInput.value = '';
        if (filePreview) filePreview.classList.remove('show');
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

        // Simulate API call
        setTimeout(() => {
            // Reset loading state
            submitButtons.forEach(btn => {
                btn.disabled = false;
            });
            
            if (primaryButton) {
                primaryButton.classList.remove('loading');
                primaryButton.textContent = 'Publikasikan';
            }

            // Show success message
            if (action === 'publish') {
                showNotification('Kursus berhasil dipublikasikan! 🎉', 'success');
            } else if (action === 'preview') {
                showNotification('Membuka pratinjau kursus...', 'info');
                // Simulate opening preview
                setTimeout(() => {
                    window.open('#', '_blank');
                }, 1000);
            } else {
                showNotification('Draft kursus berhasil disimpan! 💾', 'success');
            }

            // Reset form after successful submission
            if (action === 'publish') {
                setTimeout(() => {
                    if (confirm('Kursus telah dipublikasikan. Ingin membuat kursus baru?')) {
                        form.reset();
                        removeFile();
                        clearAllValidations();
                    }
                }, 2000);
            }
        }, 2000);
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
        
        console.log('Auto-saving draft...', Object.fromEntries(formData));
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
        
        // Create counter element
        const counter = document.createElement('div');
        counter.style.cssText = `
            font-size: 12px;
            color: #718096;
            text-align: right;
            margin-top: 4px;
        `;
        
        descriptionField.parentNode.appendChild(counter);
        
        function updateCounter() {
            const length = descriptionField.value.length;
            counter.textContent = `${length}/${maxLength}`;
            
            if (length > maxLength * 0.9) {
                counter.style.color = '#F56500';
            } else if (length > maxLength * 0.8) {
                counter.style.color = '#E53E3E';
            } else {
                counter.style.color = '#718096';
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

// Add notification animations to CSS
const notificationStyles = document.createElement('style');
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
    
    // Save to localStorage
    saveToStorage: function(data) {
        try {
            localStorage.setItem('course_draft', JSON.stringify({
                ...data,
                timestamp: new Date().toISOString()
            }));
        } catch (e) {
            console.warn('Could not save to localStorage:', e);
        }
    },
    
    // Load from localStorage
    loadFromStorage: function() {
        try {
            const saved = localStorage.getItem('course_draft');
            return saved ? JSON.parse(saved) : null;
        } catch (e) {
            console.warn('Could not load from localStorage:', e);
            return null;
        }
    },
    
    // Clear storage
    clearStorage: function() {
        try {
            localStorage.removeItem('course_draft');
        } catch (e) {
            console.warn('Could not clear localStorage:', e);
        }
    }
};

// Auto-load draft on page load
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

// Export for external use
window.courseUtils = courseUtils;