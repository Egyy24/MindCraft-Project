document.addEventListener('DOMContentLoaded', function() {
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

    // Initialize profile features
    initTabSwitching();
    initFormValidation();
    initAvatarUpload();
    initPasswordStrength();
    initFormAutoSave();
    initSocialMediaValidation();
    initProfileAnimations();
});

// Initialize tab switching
function initTabSwitching() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // Remove active class from all buttons and panes
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Add active class to clicked button and corresponding pane
            this.classList.add('active');
            const targetPane = document.getElementById(targetTab + '-tab');
            if (targetPane) {
                targetPane.classList.add('active');
            }
            
            // Save active tab to localStorage
            localStorage.setItem('activeProfileTab', targetTab);
            
            // Animate tab change
            animateTabChange(targetPane);
        });
    });
    
    // Restore active tab from localStorage
    const savedTab = localStorage.getItem('activeProfileTab');
    if (savedTab) {
        const savedTabButton = document.querySelector(`[data-tab="${savedTab}"]`);
        if (savedTabButton) {
            savedTabButton.click();
        }
    }
}

// Animate tab change
function animateTabChange(targetPane) {
    if (!targetPane) return;
    
    targetPane.style.opacity = '0';
    targetPane.style.transform = 'translateY(10px)';
    
    setTimeout(() => {
        targetPane.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        targetPane.style.opacity = '1';
        targetPane.style.transform = 'translateY(0)';
    }, 50);
}

// Initialize form validation
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
        
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            } else {
                showFormSubmitLoading(this);
            }
        });
    });
}

// Validate individual field
function validateField(field) {
    const value = field.value.trim();
    const fieldGroup = field.closest('.form-group');
    let isValid = true;
    let errorMessage = '';
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'Field ini wajib diisi';
    }
    
    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = 'Format email tidak valid';
        }
    }
    
    // URL validation
    if (field.type === 'url' && value) {
        try {
            new URL(value);
        } catch {
            isValid = false;
            errorMessage = 'Format URL tidak valid';
        }
    }
    
    // Phone validation
    if (field.type === 'tel' && value) {
        const phoneRegex = /^[\d\s\-\+\(\)]+$/;
        if (!phoneRegex.test(value) || value.length < 10) {
            isValid = false;
            errorMessage = 'Format nomor telepon tidak valid';
        }
    }
    
    // Password validation
    if (field.type === 'password' && field.name !== 'current_password' && value) {
        if (value.length < 6) {
            isValid = false;
            errorMessage = 'Password minimal 6 karakter';
        }
    }
    
    // Confirm password validation
    if (field.name === 'confirm_password' && value) {
        const newPassword = form.querySelector('input[name="new_password"]');
        if (newPassword && value !== newPassword.value) {
            isValid = false;
            errorMessage = 'Konfirmasi password tidak sesuai';
        }
    }
    
    // Update field appearance
    if (isValid) {
        fieldGroup.classList.remove('error');
        fieldGroup.classList.add('success');
    } else {
        fieldGroup.classList.remove('success');
        fieldGroup.classList.add('error');
        showFieldError(fieldGroup, errorMessage);
    }
    
    return isValid;
}

// Show field error
function showFieldError(fieldGroup, message) {
    // Remove existing error message
    const existingError = fieldGroup.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error message
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;
    fieldGroup.appendChild(errorElement);
}

// Clear field error
function clearFieldError(field) {
    const fieldGroup = field.closest('.form-group');
    const errorElement = fieldGroup.querySelector('.field-error');
    
    if (errorElement) {
        errorElement.remove();
    }
    
    fieldGroup.classList.remove('error');
}

// Validate entire form
function validateForm(form) {
    const inputs = form.querySelectorAll('input, textarea, select');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    if (!isValid) {
        showToast('Mohon perbaiki kesalahan pada form', 'error');
        
        // Focus on first error field
        const firstError = form.querySelector('.form-group.error input, .form-group.error textarea');
        if (firstError) {
            firstError.focus();
        }
    }
    
    return isValid;
}

// Show form submit loading
function showFormSubmitLoading(form) {
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = '⏳ Menyimpan...';
        submitButton.disabled = true;
        
        // Store original text for restoration if needed
        submitButton.dataset.originalText = originalText;
    }
}

// Initialize avatar upload
function initAvatarUpload() {
    const avatarUploadBtn = document.querySelector('.avatar-upload-btn');
    
    if (avatarUploadBtn) {
        avatarUploadBtn.addEventListener('click', function() {
            // Create file input
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = 'image/*';
            fileInput.style.display = 'none';
            
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    handleAvatarUpload(file);
                }
            });
            
            document.body.appendChild(fileInput);
            fileInput.click();
            document.body.removeChild(fileInput);
        });
    }
}

// Handle avatar upload
function handleAvatarUpload(file) {
    // Validate file size (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
        showToast('Ukuran file maksimal 2MB', 'error');
        return;
    }
    
    // Validate file type
    if (!file.type.startsWith('image/')) {
        showToast('File harus berupa gambar', 'error');
        return;
    }
    
    // Show loading state
    const avatarPlaceholder = document.querySelector('.avatar-placeholder');
    const uploadBtn = document.querySelector('.avatar-upload-btn');
    
    if (avatarPlaceholder && uploadBtn) {
        avatarPlaceholder.style.opacity = '0.6';
        uploadBtn.innerHTML = '⏳';
        uploadBtn.disabled = true;
    }
    
    // Create preview
    const reader = new FileReader();
    reader.onload = function(e) {
        if (avatarPlaceholder) {
            avatarPlaceholder.style.backgroundImage = `url(${e.target.result})`;
            avatarPlaceholder.style.backgroundSize = 'cover';
            avatarPlaceholder.style.backgroundPosition = 'center';
            avatarPlaceholder.innerHTML = '';
            avatarPlaceholder.style.opacity = '1';
        }
        
        if (uploadBtn) {
            uploadBtn.innerHTML = '✓';
            uploadBtn.disabled = false;
            
            setTimeout(() => {
                uploadBtn.innerHTML = '📷';
            }, 2000);
        }
        
        showToast('Avatar berhasil diupload', 'success');
    };
    
    reader.readAsDataURL(file);
    
    // In real implementation, you would upload to server here
    // uploadAvatarToServer(file);
}

// Initialize password strength indicator
function initPasswordStrength() {
    const newPasswordInput = document.querySelector('input[name="new_password"]');
    
    if (newPasswordInput) {
        // Create strength indicator
        const strengthIndicator = document.createElement('div');
        strengthIndicator.className = 'password-strength';
        strengthIndicator.innerHTML = `
            <div class="strength-bar">
                <div class="strength-fill"></div>
            </div>
            <div class="strength-text">Kekuatan password</div>
        `;
        
        // Add styles
        const strengthStyle = document.createElement('style');
        strengthStyle.textContent = `
            .password-strength {
                margin-top: 8px;
            }
            .strength-bar {
                width: 100%;
                height: 4px;
                background: #e2e8f0;
                border-radius: 2px;
                overflow: hidden;
                margin-bottom: 4px;
            }
            .strength-fill {
                height: 100%;
                background: #e53e3e;
                transition: all 0.3s ease;
                width: 0%;
            }
            .strength-text {
                font-size: 12px;
                color: #718096;
            }
            .strength-weak .strength-fill { width: 25%; background: #e53e3e; }
            .strength-fair .strength-fill { width: 50%; background: #d69e2e; }
            .strength-good .strength-fill { width: 75%; background: #3182ce; }
            .strength-strong .strength-fill { width: 100%; background: #38a169; }
        `;
        
        document.head.appendChild(strengthStyle);
        newPasswordInput.parentNode.appendChild(strengthIndicator);
        
        // Update strength on input
        newPasswordInput.addEventListener('input', function() {
            updatePasswordStrength(this.value, strengthIndicator);
        });
    }
}

// Update password strength
function updatePasswordStrength(password, indicator) {
    const strength = calculatePasswordStrength(password);
    const strengthFill = indicator.querySelector('.strength-fill');
    const strengthText = indicator.querySelector('.strength-text');
    
    // Remove all strength classes
    indicator.classList.remove('strength-weak', 'strength-fair', 'strength-good', 'strength-strong');
    
    if (password.length === 0) {
        strengthText.textContent = 'Kekuatan password';
        return;
    }
    
    switch (strength) {
        case 1:
            indicator.classList.add('strength-weak');
            strengthText.textContent = 'Lemah';
            break;
        case 2:
            indicator.classList.add('strength-fair');
            strengthText.textContent = 'Cukup';
            break;
        case 3:
            indicator.classList.add('strength-good');
            strengthText.textContent = 'Baik';
            break;
        case 4:
            indicator.classList.add('strength-strong');
            strengthText.textContent = 'Kuat';
            break;
    }
}

// Calculate password strength
function calculatePasswordStrength(password) {
    let score = 0;
    
    if (password.length >= 6) score++;
    if (password.length >= 8) score++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
    if (/\d/.test(password)) score++;
    if (/[^a-zA-Z\d]/.test(password)) score++;
    
    return Math.min(Math.floor(score / 1.25), 4);
}

// Initialize form auto-save
function initFormAutoSave() {
    const profileForm = document.querySelector('.profile-form');
    
    if (profileForm) {
        const inputs = profileForm.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            input.addEventListener('input', debounce(() => {
                autoSaveForm(profileForm);
            }, 2000));
        });
    }
}

// Auto-save form data
function autoSaveForm(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    // Save to localStorage
    localStorage.setItem('profileFormDraft', JSON.stringify(data));
    
    // Show save indicator
    showAutoSaveIndicator();
}

// Show auto-save indicator
function showAutoSaveIndicator() {
    // Remove existing indicator
    const existingIndicator = document.querySelector('.autosave-indicator');
    if (existingIndicator) {
        existingIndicator.remove();
    }
    
    // Create new indicator
    const indicator = document.createElement('div');
    indicator.className = 'autosave-indicator';
    indicator.textContent = '💾 Draft tersimpan';
    
    // Add styles
    const indicatorStyle = document.createElement('style');
    indicatorStyle.textContent = `
        .autosave-indicator {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #38a169;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            animation: slideInUp 0.3s ease;
            z-index: 1000;
        }
        @keyframes slideInUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    `;
    
    document.head.appendChild(indicatorStyle);
    document.body.appendChild(indicator);
    
    // Remove after 3 seconds
    setTimeout(() => {
        indicator.style.opacity = '0';
        indicator.style.transform = 'translateY(100%)';
        setTimeout(() => {
            indicator.remove();
            document.head.removeChild(indicatorStyle);
        }, 300);
    }, 3000);
}

// Initialize social media validation
function initSocialMediaValidation() {
    const socialInputs = document.querySelectorAll('input[name="website"], input[name="linkedin"], input[name="instagram"], input[name="youtube"]');
    
    socialInputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateSocialMediaUrl(this);
        });
    });
}

// Validate social media URLs
function validateSocialMediaUrl(input) {
    const value = input.value.trim();
    const fieldGroup = input.closest('.form-group');
    
    if (!value) return;
    
    let isValid = true;
    let errorMessage = '';
    
    try {
        const url = new URL(value);
        
        switch (input.name) {
            case 'linkedin':
                if (!url.hostname.includes('linkedin.com')) {
                    isValid = false;
                    errorMessage = 'URL LinkedIn tidak valid';
                }
                break;
            case 'instagram':
                if (!url.hostname.includes('instagram.com')) {
                    isValid = false;
                    errorMessage = 'URL Instagram tidak valid';
                }
                break;
            case 'youtube':
                if (!url.hostname.includes('youtube.com') && !url.hostname.includes('youtu.be')) {
                    isValid = false;
                    errorMessage = 'URL YouTube tidak valid';
                }
                break;
        }
    } catch {
        isValid = false;
        errorMessage = 'Format URL tidak valid';
    }
    
    if (isValid) {
        fieldGroup.classList.remove('error');
        fieldGroup.classList.add('success');
    } else {
        fieldGroup.classList.remove('success');
        fieldGroup.classList.add('error');
        showFieldError(fieldGroup, errorMessage);
    }
}

// Initialize profile animations
function initProfileAnimations() {
    // Animate stats on page load
    animateStats();
    
    // Animate form sections
    animateFormSections();
}

// Animate statistics
function animateStats() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    statNumbers.forEach((stat, index) => {
        const finalValue = stat.textContent;
        const numericValue = parseInt(finalValue.replace(/[^\d]/g, ''));
        
        if (numericValue > 0) {
            stat.textContent = '0';
            
            setTimeout(() => {
                animateCounter(stat, 0, numericValue, finalValue);
            }, index * 200);
        }
    });
}

// Animate counter
function animateCounter(element, start, end, finalText) {
    const duration = 1000;
    const stepTime = 50;
    const steps = duration / stepTime;
    const increment = (end - start) / steps;
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= end) {
            element.textContent = finalText;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, stepTime);
}

// Animate form sections
function animateFormSections() {
    const sections = document.querySelectorAll('.form-section');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });
    
    sections.forEach((section, index) => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(20px)';
        section.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
        observer.observe(section);
    });
}

// Utility function: debounce
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Show toast notifications
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    
    // Toast styles
    const toastStyle = document.createElement('style');
    toastStyle.textContent = `
        .toast {
            position: fixed;
            top: 90px;
            right: 24px;
            padding: 16px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 1001;
            animation: slideInRight 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            max-width: 300px;
        }
        .toast.success { background: #38a169; }
        .toast.error { background: #e53e3e; }
        .toast.info { background: #3A59D1; }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    
    document.head.appendChild(toastStyle);
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(toast);
            document.head.removeChild(toastStyle);
        }, 300);
    }, 3000);
}

// Responsive handling
function handleResize() {
    const sidebar = document.getElementById('sidebar');
    
    if (window.innerWidth <= 768) {
        if (sidebar) {
            sidebar.classList.add('mobile');
        }
    } else {
        if (sidebar) {
            sidebar.classList.remove('mobile', 'open');
        }
    }
}

handleResize();
window.addEventListener('resize', handleResize);

// Export functions for global use
window.profileUtils = {
    validateField,
    validateForm,
    showToast,
    animateCounter,
    handleAvatarUpload
};