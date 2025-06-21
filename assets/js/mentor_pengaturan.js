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

    // Tab Navigation
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
            
            // Update URL hash without scrolling
            history.replaceState(null, null, '#' + targetTab);
        });
    });

    // Initialize tab from URL hash
    const urlHash = window.location.hash.substring(1);
    if (urlHash && document.getElementById(urlHash)) {
        // Remove all active states
        tabButtons.forEach(btn => btn.classList.remove('active'));
        tabContents.forEach(content => content.classList.remove('active'));
        
        // Activate target tab
        const targetButton = document.querySelector(`[data-tab="${urlHash}"]`);
        if (targetButton) {
            targetButton.classList.add('active');
            document.getElementById(urlHash).classList.add('active');
        }
    } else {
        // Default to first tab if no hash
        if (tabButtons.length > 0) {
            tabButtons[0].classList.add('active');
            if (tabContents.length > 0) {
                tabContents[0].classList.add('active');
            }
        }
    }

    // Profile Photo Upload
    const photoInput = document.getElementById('profilePhotoInput');
    const photoPreview = document.querySelector('.photo-preview');
    const photoPlaceholder = document.querySelector('.photo-placeholder');
    const changePhotoBtn = document.getElementById('changePhotoBtn');
    const deletePhotoBtn = document.getElementById('deletePhotoBtn');

    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                handlePhotoUpload(file);
            }
        });
    }

    if (changePhotoBtn) {
        changePhotoBtn.addEventListener('click', function() {
            photoInput?.click();
        });
    }

    if (deletePhotoBtn) {
        deletePhotoBtn.addEventListener('click', function() {
            deleteProfilePhoto();
        });
    }

    // Handle photo upload
    function handlePhotoUpload(file) {
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            showNotification('Hanya file gambar (JPEG, PNG, GIF, WebP) yang diizinkan', 'error');
            return;
        }

        // Validate file size (max 5MB)
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            showNotification('Ukuran file tidak boleh lebih dari 5MB', 'error');
            return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            showPhotoPreview(e.target.result);
            showNotification('Foto profil berhasil diubah! Jangan lupa simpan perubahan.', 'success');
        };
        reader.readAsDataURL(file);
    }

    // Show photo preview
    function showPhotoPreview(imageSrc) {
        if (photoPreview) {
            photoPreview.innerHTML = `<img src="${imageSrc}" alt="Profile Photo">`;
            
            // Enable delete button
            if (deletePhotoBtn) {
                deletePhotoBtn.style.display = 'flex';
            }
        }
    }

    // Delete profile photo
    function deleteProfilePhoto() {
        if (photoPreview && photoPlaceholder) {
            photoPreview.innerHTML = photoPlaceholder.outerHTML;
            
            // Clear file input
            if (photoInput) {
                photoInput.value = '';
            }
            
            // Hide delete button
            if (deletePhotoBtn) {
                deletePhotoBtn.style.display = 'none';
            }
            
            showNotification('Foto profil dihapus! Jangan lupa simpan perubahan.', 'info');
        }
    }

    // Notification Toggle Switches
    const toggleSwitches = document.querySelectorAll('.toggle-switch');
    
    toggleSwitches.forEach(toggle => {
        toggle.addEventListener('click', function() {
            this.classList.toggle('active');
            const checkbox = this.previousElementSibling || this.nextElementSibling;
            if (checkbox && checkbox.type === 'checkbox') {
                checkbox.checked = this.classList.contains('active');
            }
        });
    });

    // Initialize toggle states from checkboxes
    toggleSwitches.forEach(toggle => {
        const checkbox = toggle.previousElementSibling || toggle.nextElementSibling;
        if (checkbox && checkbox.type === 'checkbox' && checkbox.checked) {
            toggle.classList.add('active');
        }
    });

    // Payment Method Selection
    const paymentMethods = document.querySelectorAll('.payment-method');
    
    paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
            // Remove selected class from all methods
            paymentMethods.forEach(m => m.classList.remove('selected'));
            
            // Add selected class to clicked method
            this.classList.add('selected');
            
            // Check the radio button
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }
        });
    });

    // Form Validation
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea');
        
        inputs.forEach(input => {
            // Real-time validation
            input.addEventListener('blur', function() {
                validateField(this);
            });

            input.addEventListener('input', function() {
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
    });

    // Validate individual field
    function validateField(field) {
        const formGroup = field.closest('.form-group');
        if (!formGroup) return true;

        const value = field.value.trim();
        const fieldName = field.name || field.id;
        let isValid = true;
        let errorMessage = '';

        // Clear previous validation
        formGroup.classList.remove('error', 'success');
        const existingError = formGroup.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }

        // Email validation
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Format email tidak valid';
            }
        }

        // Required field validation
        if (field.required && !value) {
            isValid = false;
            errorMessage = 'Field ini wajib diisi';
        }

        // Password validation
        if (field.type === 'password' && value && value.length < 6) {
            isValid = false;
            errorMessage = 'Password minimal 6 karakter';
        }

        // Show validation result
        if (!isValid) {
            formGroup.classList.add('error');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = '⚠️ ' + errorMessage;
            field.parentNode.appendChild(errorDiv);
        } else if (value) {
            formGroup.classList.add('success');
        }

        return isValid;
    }

    // Form Submission
    const saveButtons = document.querySelectorAll('.btn-save');
    
    saveButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const form = this.closest('form') || document.querySelector('form');
            if (!form) return;
            
            // Validate all fields
            const inputs = form.querySelectorAll('input, textarea');
            let isFormValid = true;
            
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isFormValid = false;
                }
            });

            if (isFormValid) {
                saveSettings(form, this);
            } else {
                showNotification('Mohon periksa kembali form Anda', 'error');
                
                // Focus on first error field
                const firstError = form.querySelector('.form-group.error input, .form-group.error textarea');
                if (firstError) {
                    firstError.focus();
                }
            }
        });
    });

    // Save settings function
    function saveSettings(form, button) {
        // Show loading state
        button.disabled = true;
        button.classList.add('loading');
        const originalText = button.textContent;
        button.textContent = 'Menyimpan...';

        // Get form data
        const formData = new FormData(form);
        
        // Get current active tab
        const activeTab = document.querySelector('.tab-button.active');
        const tabType = activeTab ? activeTab.getAttribute('data-tab') : 'profil';
        
        // Add tab type to form data
        formData.append('tab_type', tabType);

        // Simulate API call
        setTimeout(() => {
            // Reset loading state
            button.disabled = false;
            button.classList.remove('loading');
            button.textContent = originalText;

            // Show success message
            showNotification('Pengaturan berhasil disimpan! ✅', 'success');
            
            // Log form data for debugging
            console.log('Settings saved:', Object.fromEntries(formData));
            
        }, 2000);
    }

    // Auto-save functionality
    let autoSaveTimeout;
    const autoSaveDelay = 30000; // 30 seconds

    function autoSave() {
        const activeTabContent = document.querySelector('.tab-content.active');
        if (!activeTabContent) return;
        
        const form = activeTabContent.querySelector('form');
        if (!form) return;
        
        const formData = new FormData(form);
        
        // Simple validation for auto-save
        const requiredFields = form.querySelectorAll('input[required], textarea[required]');
        let hasRequiredData = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                hasRequiredData = false;
            }
        });
        
        if (!hasRequiredData) return;

        // Show auto-save indicator
        showNotification('Draft otomatis tersimpan 💾', 'info', 2000);
        
        console.log('Auto-saving settings...', Object.fromEntries(formData));
    }

    // Track form changes for auto-save
    const formInputs = document.querySelectorAll('input, textarea');
    formInputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(autoSave, autoSaveDelay);
        });
    });

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

    // Password strength checker
    const passwordFields = document.querySelectorAll('input[type="password"]');
    passwordFields.forEach(field => {
        field.addEventListener('input', function() {
            checkPasswordStrength(this);
        });
    });

    function checkPasswordStrength(passwordField) {
        const password = passwordField.value;
        const strengthIndicator = passwordField.nextElementSibling;
        
        if (!strengthIndicator || !strengthIndicator.classList.contains('password-strength')) {
            return;
        }

        let strength = 0;
        let strengthText = '';
        let strengthColor = '';

        if (password.length >= 8) strength += 1;
        if (/[a-z]/.test(password)) strength += 1;
        if (/[A-Z]/.test(password)) strength += 1;
        if (/[0-9]/.test(password)) strength += 1;
        if (/[^A-Za-z0-9]/.test(password)) strength += 1;

        switch (strength) {
            case 0:
            case 1:
                strengthText = 'Sangat Lemah';
                strengthColor = '#E53E3E';
                break;
            case 2:
                strengthText = 'Lemah';
                strengthColor = '#F56500';
                break;
            case 3:
                strengthText = 'Sedang';
                strengthColor = '#D69E2E';
                break;
            case 4:
                strengthText = 'Kuat';
                strengthColor = '#38A169';
                break;
            case 5:
                strengthText = 'Sangat Kuat';
                strengthColor = '#2B992B';
                break;
        }

        strengthIndicator.textContent = strengthText;
        strengthIndicator.style.color = strengthColor;
    }

    // Confirm password validation
    const confirmPasswordField = document.getElementById('confirm_password');
    const newPasswordField = document.getElementById('new_password');

    if (confirmPasswordField && newPasswordField) {
        confirmPasswordField.addEventListener('input', function() {
            validatePasswordMatch();
        });

        newPasswordField.addEventListener('input', function() {
            validatePasswordMatch();
        });
    }

    function validatePasswordMatch() {
        if (!confirmPasswordField || !newPasswordField) return;

        const formGroup = confirmPasswordField.closest('.form-group');
        if (!formGroup) return;

        // Clear previous validation
        formGroup.classList.remove('error', 'success');
        const existingError = formGroup.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }

        const newPassword = newPasswordField.value;
        const confirmPassword = confirmPasswordField.value;

        if (confirmPassword && newPassword !== confirmPassword) {
            formGroup.classList.add('error');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = '⚠️ Password tidak cocok';
            confirmPasswordField.parentNode.appendChild(errorDiv);
        } else if (confirmPassword && newPassword === confirmPassword) {
            formGroup.classList.add('success');
        }
    }

    // Security settings handlers
    const enableTwoFactorBtn = document.getElementById('enableTwoFactor');
    const changePasswordBtn = document.getElementById('changePasswordBtn');

    if (enableTwoFactorBtn) {
        enableTwoFactorBtn.addEventListener('click', function() {
            // Simulate two-factor authentication setup
            showNotification('Fitur Two-Factor Authentication akan segera tersedia!', 'info');
        });
    }

    if (changePasswordBtn) {
        changePasswordBtn.addEventListener('click', function() {
            const passwordFields = document.querySelectorAll('#current_password, #new_password, #confirm_password');
            let allFilled = true;

            passwordFields.forEach(field => {
                if (!field.value.trim()) {
                    allFilled = false;
                    validateField(field);
                }
            });

            if (allFilled) {
                // Validate password match
                validatePasswordMatch();
                
                const formGroup = confirmPasswordField?.closest('.form-group');
                if (!formGroup?.classList.contains('error')) {
                    showNotification('Password berhasil diubah! 🔐', 'success');
                    
                    // Clear password fields
                    passwordFields.forEach(field => {
                        field.value = '';
                        const group = field.closest('.form-group');
                        if (group) {
                            group.classList.remove('error', 'success');
                        }
                    });
                }
            } else {
                showNotification('Mohon lengkapi semua field password', 'error');
            }
        });
    }

    // Payment method form submission
    const updatePaymentBtn = document.getElementById('updatePaymentBtn');
    if (updatePaymentBtn) {
        updatePaymentBtn.addEventListener('click', function() {
            const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
            
            if (selectedPayment) {
                showNotification('Metode pembayaran berhasil diperbarui! 💳', 'success');
            } else {
                showNotification('Pilih metode pembayaran terlebih dahulu', 'error');
            }
        });
    }
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
const settingsUtils = {
    // Format file size
    formatFileSize: function(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
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
    
    // Save to localStorage
    saveToStorage: function(data) {
        try {
            localStorage.setItem('mentor_settings', JSON.stringify({
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
            const saved = localStorage.getItem('mentor_settings');
            return saved ? JSON.parse(saved) : null;
        } catch (e) {
            console.warn('Could not load from localStorage:', e);
            return null;
        }
    }
};

// Export for external use
window.settingsUtils = settingsUtils;