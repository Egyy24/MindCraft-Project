let autoSaveInterval;
let unsavedChanges = false;
let originalFormData = {};
let currentCourseId = null;

// DOM Elements
const form = document.getElementById('editCourseForm');
const autoSaveIndicator = document.querySelector('.auto-save-indicator');
const mobileMenuToggle = document.getElementById('mobileMenuToggle');
const sidebar = document.getElementById('sidebar');

document.addEventListener('DOMContentLoaded', function() {
    initializeEditCourse();
});

function initializeEditCourse() {
    // Extract course ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    currentCourseId = urlParams.get('id');
    
    if (!currentCourseId) {
        showNotification('ID kursus tidak ditemukan', 'error');
        setTimeout(() => {
            window.location.href = '/MindCraft-Project/views/mentor/kursus-saya.php';
        }, 2000);
        return;
    }

    // Initialize components
    initializeFormHandling();
    initializeFileUpload();
    initializeTagsInput();
    initializeLearningObjectives();
    initializeMobileMenu();
    initializeFormValidation();
    
    // Load course data
    loadCourseData();
    
    // Setup auto-save
    setupAutoSave();
    
    // Store original form data
    storeOriginalFormData();
    
    // Setup before unload warning
    setupUnloadWarning();
    
    console.log('Edit course page initialized for course ID:', currentCourseId);
}

/**
 * Load existing course data
 */
async function loadCourseData() {
    try {
        showLoadingState(true);
        
        // Simulate API call - replace with actual endpoint
        const response = await fetch(`/MindCraft-Project/api/courses/${currentCourseId}`);
        
        if (!response.ok) {
            throw new Error('Failed to load course data');
        }
        
        const courseData = await response.json();
        populateFormWithData(courseData);
        
    } catch (error) {
        console.error('Error loading course data:', error);
        
        // Use mock data for demo
        const mockData = getMockCourseData();
        populateFormWithData(mockData);
        
        showNotification('Menggunakan data demo', 'info');
    } finally {
        showLoadingState(false);
    }
}

/**
 * Populate form with course data
 */
function populateFormWithData(data) {
    // Basic information
    document.getElementById('courseTitle').value = data.title || '';
    document.getElementById('courseCategory').value = data.category || '';
    document.getElementById('courseDifficulty').value = data.difficulty || 'Pemula';
    document.getElementById('courseDescription').value = data.description || '';
    document.getElementById('coursePrice').value = data.price || '';
    
    // Course settings
    document.getElementById('isPremium').checked = data.is_premium || false;
    document.getElementById('allowReviews').checked = data.allow_reviews !== false;
    document.getElementById('sendNotifications').checked = data.send_notifications !== false;
    document.getElementById('autoCertificate').checked = data.auto_certificate || false;
    
    // Requirements and objectives
    document.getElementById('courseRequirements').value = data.requirements || '';
    document.getElementById('targetAudience').value = data.target_audience || '';
    
    // Learning objectives
    if (data.learning_objectives && data.learning_objectives.length > 0) {
        populateLearningObjectives(data.learning_objectives);
    }
    
    // Tags
    if (data.tags && data.tags.length > 0) {
        populateTags(data.tags);
    }
    
    // Cover image
    if (data.cover_image) {
        displayCurrentImage(data.cover_image);
    }
    
    // Update preview
    updateCoursePreview();
    
    // Store as original data
    storeOriginalFormData();
    
    console.log('Form populated with course data');
}

/**
 * Initialize form handling
 */
function initializeFormHandling() {
    if (!form) return;
    
    // Form submission
    form.addEventListener('submit', handleFormSubmit);
    
    // Track form changes
    form.addEventListener('input', handleFormChange);
    form.addEventListener('change', handleFormChange);
    
    // Real-time preview updates
    const previewFields = ['courseTitle', 'courseCategory', 'courseDifficulty', 'courseDescription', 'coursePrice'];
    previewFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', updateCoursePreview);
        }
    });
}

/**
 * Handle form submission
 */
async function handleFormSubmit(e) {
    e.preventDefault();
    
    const action = e.submitter?.dataset.action || 'save';
    
    if (!validateForm()) {
        showNotification('Mohon periksa kembali data yang diisi', 'error');
        return;
    }
    
    try {
        showButtonLoading(e.submitter, true);
        
        const formData = collectFormData();
        formData.action = action;
        formData.course_id = currentCourseId;
        
        const response = await submitCourseData(formData);
        
        if (response.success) {
            unsavedChanges = false;
            storeOriginalFormData();
            
            if (action === 'publish') {
                showNotification('Kursus berhasil dipublikasi!', 'success');
                setTimeout(() => {
                    window.location.href = '/MindCraft-Project/views/mentor/view-kursus.php';
                }, 1500);
            } else {
                showNotification('Perubahan berhasil disimpan!', 'success');
                showAutoSave('Tersimpan');
            }
        } else {
            throw new Error(response.message || 'Gagal menyimpan perubahan');
        }
        
    } catch (error) {
        console.error('Form submission error:', error);
        showNotification(error.message || 'Terjadi kesalahan saat menyimpan', 'error');
    } finally {
        showButtonLoading(e.submitter, false);
    }
}

/**
 * Handle form changes
 */
function handleFormChange() {
    unsavedChanges = true;
    updateCoursePreview();
}

/**
 * Initialize file upload functionality
 */
function initializeFileUpload() {
    const uploadArea = document.querySelector('.file-upload-area');
    const fileInput = document.getElementById('coverImage');
    
    if (!uploadArea || !fileInput) return;
    
    // Click to upload
    uploadArea.addEventListener('click', () => {
        fileInput.click();
    });
    
    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });
    
    // File input change
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });
}

/**
 * Handle file selection
 */
function handleFileSelect(file) {
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        showNotification('Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.', 'error');
        return;
    }
    
    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
        showNotification('Ukuran file terlalu besar. Maksimal 5MB.', 'error');
        return;
    }
    
    // Preview image
    const reader = new FileReader();
    reader.onload = (e) => {
        displayImagePreview(e.target.result);
        unsavedChanges = true;
    };
    reader.readAsDataURL(file);
}

/**
 * Display image preview
 */
function displayImagePreview(imageSrc) {
    const uploadArea = document.querySelector('.file-upload-area');
    const currentImageDiv = document.querySelector('.current-image');
    
    if (currentImageDiv) {
        currentImageDiv.remove();
    }
    
    const imageDiv = document.createElement('div');
    imageDiv.className = 'current-image';
    imageDiv.innerHTML = `
        <img src="${imageSrc}" alt="Course cover preview">
        <div class="image-actions">
            <button type="button" class="btn btn-secondary btn-sm" onclick="changeImage()">
                Ganti Gambar
            </button>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeImage()">
                Hapus
            </button>
        </div>
    `;
    
    uploadArea.parentNode.insertBefore(imageDiv, uploadArea.nextSibling);
    updateCoursePreview();
}

/**
 * Display current image
 */
function displayCurrentImage(imagePath) {
    displayImagePreview(imagePath);
}

/**
 * Change image
 */
function changeImage() {
    const fileInput = document.getElementById('coverImage');
    fileInput.click();
}

/**
 * Remove image
 */
function removeImage() {
    const currentImageDiv = document.querySelector('.current-image');
    const fileInput = document.getElementById('coverImage');
    
    if (currentImageDiv) {
        currentImageDiv.remove();
    }
    
    if (fileInput) {
        fileInput.value = '';
    }
    
    unsavedChanges = true;
    updateCoursePreview();
}

/**
 * Initialize tags input functionality
 */
function initializeTagsInput() {
    const tagsContainer = document.querySelector('.tags-container');
    const tagInput = document.querySelector('.tag-input');
    
    if (!tagsContainer || !tagInput) return;
    
    tagInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            addTag(tagInput.value.trim());
            tagInput.value = '';
        }
        
        if (e.key === 'Backspace' && tagInput.value === '') {
            removeLastTag();
        }
    });
    
    tagInput.addEventListener('blur', () => {
        if (tagInput.value.trim()) {
            addTag(tagInput.value.trim());
            tagInput.value = '';
        }
    });
}

/**
 * Add tag
 */
function addTag(tagText) {
    if (!tagText || tagText.length < 2) return;
    
    const tagsContainer = document.querySelector('.tags-container');
    const tagInput = document.querySelector('.tag-input');
    
    // Check if tag already exists
    const existingTags = Array.from(tagsContainer.querySelectorAll('.tag-item')).map(
        tag => tag.textContent.replace('×', '').trim()
    );
    
    if (existingTags.includes(tagText)) {
        showNotification('Tag sudah ada', 'warning');
        return;
    }
    
    // Limit to 10 tags
    if (existingTags.length >= 10) {
        showNotification('Maksimal 10 tag', 'warning');
        return;
    }
    
    const tagElement = document.createElement('div');
    tagElement.className = 'tag-item';
    tagElement.innerHTML = `
        ${tagText}
        <button type="button" class="tag-remove" onclick="removeTag(this)">×</button>
    `;
    
    tagsContainer.insertBefore(tagElement, tagInput);
    unsavedChanges = true;
}

/**
 * Remove tag
 */
function removeTag(button) {
    button.closest('.tag-item').remove();
    unsavedChanges = true;
}

/**
 * Remove last tag
 */
function removeLastTag() {
    const tags = document.querySelectorAll('.tag-item');
    if (tags.length > 0) {
        tags[tags.length - 1].remove();
        unsavedChanges = true;
    }
}

/**
 * Populate tags from data
 */
function populateTags(tags) {
    tags.forEach(tag => {
        addTag(tag);
    });
}

/**
 * Get all tags
 */
function getAllTags() {
    const tagElements = document.querySelectorAll('.tag-item');
    return Array.from(tagElements).map(tag => 
        tag.textContent.replace('×', '').trim()
    );
}

/**
 * Initialize learning objectives
 */
function initializeLearningObjectives() {
    const addObjectiveBtn = document.querySelector('.btn-add-objective');
    
    if (addObjectiveBtn) {
        addObjectiveBtn.addEventListener('click', addLearningObjective);
    }
}

/**
 * Add learning objective
 */
function addLearningObjective() {
    const objectivesList = document.querySelector('.objectives-list');
    
    const objectiveDiv = document.createElement('div');
    objectiveDiv.className = 'objective-item';
    objectiveDiv.innerHTML = `
        <span style="color: var(--primary-blue); font-weight: bold;">•</span>
        <input type="text" class="objective-input" placeholder="Contoh: Mampu membuat aplikasi web sederhana" maxlength="200">
        <button type="button" class="btn-remove-objective" onclick="removeObjective(this)">×</button>
    `;
    
    objectivesList.appendChild(objectiveDiv);
    
    // Focus on new input
    const newInput = objectiveDiv.querySelector('.objective-input');
    newInput.focus();
    
    // Add event listener for changes
    newInput.addEventListener('input', () => {
        unsavedChanges = true;
    });
    
    unsavedChanges = true;
}

/**
 * Remove learning objective
 */
function removeObjective(button) {
    button.closest('.objective-item').remove();
    unsavedChanges = true;
}

/**
 * Populate learning objectives from data
 */
function populateLearningObjectives(objectives) {
    objectives.forEach(objective => {
        addLearningObjective();
        const inputs = document.querySelectorAll('.objective-input');
        const lastInput = inputs[inputs.length - 1];
        lastInput.value = objective;
    });
}

/**
 * Get all learning objectives
 */
function getAllObjectives() {
    const inputs = document.querySelectorAll('.objective-input');
    return Array.from(inputs)
        .map(input => input.value.trim())
        .filter(value => value.length > 0);
}

/**
 * Initialize mobile menu
 */
function initializeMobileMenu() {
    if (mobileMenuToggle && sidebar) {
        mobileMenuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });
        
        // Close sidebar when clicking outside
        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    }
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const requiredFields = document.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        field.addEventListener('blur', () => validateField(field));
        field.addEventListener('input', () => clearFieldError(field));
    });
}

/**
 * Validate single field
 */
function validateField(field) {
    const value = field.value.trim();
    const fieldName = field.dataset.name || field.name || field.id;
    
    // Remove existing error
    clearFieldError(field);
    
    // Required validation
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, `${fieldName} wajib diisi`);
        return false;
    }
    
    // Specific validations
    switch (field.type) {
        case 'email':
            if (value && !isValidEmail(value)) {
                showFieldError(field, 'Format email tidak valid');
                return false;
            }
            break;
            
        case 'number':
            const min = parseFloat(field.min);
            const max = parseFloat(field.max);
            const numValue = parseFloat(value);
            
            if (value && isNaN(numValue)) {
                showFieldError(field, 'Harus berupa angka');
                return false;
            }
            
            if (!isNaN(min) && numValue < min) {
                showFieldError(field, `Minimal ${min}`);
                return false;
            }
            
            if (!isNaN(max) && numValue > max) {
                showFieldError(field, `Maksimal ${max}`);
                return false;
            }
            break;
            
        case 'url':
            if (value && !isValidUrl(value)) {
                showFieldError(field, 'Format URL tidak valid');
                return false;
            }
            break;
    }
    
    // Length validation
    const minLength = parseInt(field.minLength);
    const maxLength = parseInt(field.maxLength);
    
    if (!isNaN(minLength) && value.length < minLength) {
        showFieldError(field, `Minimal ${minLength} karakter`);
        return false;
    }
    
    if (!isNaN(maxLength) && value.length > maxLength) {
        showFieldError(field, `Maksimal ${maxLength} karakter`);
        return false;
    }
    
    return true;
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    field.classList.add('error');
    
    let errorDiv = field.parentNode.querySelector('.error-message');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        field.parentNode.appendChild(errorDiv);
    }
    
    errorDiv.innerHTML = `<span>⚠️</span> ${message}`;
}

/**
 * Clear field error
 */
function clearFieldError(field) {
    field.classList.remove('error');
    const errorDiv = field.parentNode.querySelector('.error-message');
    if (errorDiv) {
        errorDiv.remove();
    }
}

/**
 * Validate entire form
 */
function validateForm() {
    const requiredFields = document.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    // Custom validations
    const courseTitle = document.getElementById('courseTitle');
    if (courseTitle && courseTitle.value.trim().length < 5) {
        showFieldError(courseTitle, 'Judul kursus minimal 5 karakter');
        isValid = false;
    }
    
    const courseDescription = document.getElementById('courseDescription');
    if (courseDescription && courseDescription.value.trim().length < 20) {
        showFieldError(courseDescription, 'Deskripsi minimal 20 karakter');
        isValid = false;
    }
    
    const objectives = getAllObjectives();
    if (objectives.length === 0) {
        showNotification('Minimal 1 tujuan pembelajaran harus diisi', 'error');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Update course preview
 */
function updateCoursePreview() {
    const previewTitle = document.querySelector('.preview-details h3');
    const previewMeta = document.querySelector('.preview-meta');
    const previewDescription = document.querySelector('.preview-description');
    const previewImage = document.querySelector('.preview-image');
    
    if (!previewTitle) return;
    
    // Update title
    const title = document.getElementById('courseTitle')?.value || 'Judul Kursus';
    previewTitle.textContent = title;
    
    // Update meta
    const category = document.getElementById('courseCategory')?.value || 'Kategori';
    const difficulty = document.getElementById('courseDifficulty')?.value || 'Pemula';
    const price = document.getElementById('coursePrice')?.value || '0';
    
    if (previewMeta) {
        previewMeta.innerHTML = `
            <span>📚 ${category}</span>
            <span>📊 ${difficulty}</span>
            <span>💰 Rp ${formatCurrency(price)}</span>
        `;
    }
    
    // Update description
    const description = document.getElementById('courseDescription')?.value || 'Deskripsi kursus';
    if (previewDescription) {
        previewDescription.textContent = description.length > 100 ? 
            description.substring(0, 100) + '...' : description;
    }
    
    // Update image
    const currentImage = document.querySelector('.current-image img');
    if (previewImage && currentImage) {
        previewImage.innerHTML = `<img src="${currentImage.src}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--border-radius);">`;
    }
}

/**
 * Setup auto-save functionality
 */
function setupAutoSave() {
    // Auto-save every 30 seconds
    autoSaveInterval = setInterval(() => {
        if (unsavedChanges) {
            autoSave();
        }
    }, 30000);
}

/**
 * Auto-save function
 */
async function autoSave() {
    if (!unsavedChanges || !validateForm()) return;
    
    try {
        const formData = collectFormData();
        formData.action = 'auto_save';
        formData.course_id = currentCourseId;
        
        const response = await submitCourseData(formData);
        
        if (response.success) {
            unsavedChanges = false;
            showAutoSave('Tersimpan otomatis');
        }
        
    } catch (error) {
        console.error('Auto-save error:', error);
    }
}

/**
 * Show auto-save indicator
 */
function showAutoSave(message) {
    if (!autoSaveIndicator) return;
    
    autoSaveIndicator.textContent = message;
    autoSaveIndicator.classList.add('show');
    
    setTimeout(() => {
        autoSaveIndicator.classList.remove('show');
    }, 2000);
}

/**
 * Collect form data
 */
function collectFormData() {
    const formData = new FormData(form);
    
    // Add custom data
    formData.set('learning_objectives', JSON.stringify(getAllObjectives()));
    formData.set('tags', JSON.stringify(getAllTags()));
    
    // Convert FormData to object for easier handling
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    return data;
}

/**
 * Submit course data
 */
async function submitCourseData(data) {
    // Simulate API call - replace with actual endpoint
    try {
        const response = await fetch(`/MindCraft-Project/api/courses/${currentCourseId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        return await response.json();
    } catch (error) {
        // Return mock success for demo
        console.log('Mock save:', data);
        return { success: true, message: 'Data berhasil disimpan' };
    }
}

/**
 * Store original form data for comparison
 */
function storeOriginalFormData() {
    originalFormData = collectFormData();
    unsavedChanges = false;
}

/**
 * Setup unload warning
 */
function setupUnloadWarning() {
    window.addEventListener('beforeunload', (e) => {
        if (unsavedChanges) {
            e.preventDefault();
            e.returnValue = 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
            return e.returnValue;
        }
    });
}

/**
 * Show loading state
 */
function showLoadingState(show) {
    const form = document.getElementById('editCourseForm');
    if (!form) return;
    
    if (show) {
        form.style.opacity = '0.7';
        form.style.pointerEvents = 'none';
    } else {
        form.style.opacity = '1';
        form.style.pointerEvents = 'auto';
    }
}

/**
 * Show button loading state
 */
function showButtonLoading(button, loading) {
    if (!button) return;
    
    if (loading) {
        button.classList.add('loading');
        button.disabled = true;
    } else {
        button.classList.remove('loading');
        button.disabled = false;
    }
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 90px;
        right: 2rem;
        background: ${type === 'error' ? '#FED7D7' : type === 'success' ? '#C6F6D5' : type === 'warning' ? '#FEFCBF' : '#E6FFFA'};
        border: 1px solid ${type === 'error' ? '#E53E3E' : type === 'success' ? '#38A169' : type === 'warning' ? '#D69E2E' : '#319795'};
        color: ${type === 'error' ? '#E53E3E' : type === 'success' ? '#38A169' : type === 'warning' ? '#D69E2E' : '#319795'};
        padding: 1rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 300px;
    `;
    
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span>${type === 'error' ? '❌' : type === 'success' ? '✅' : type === 'warning' ? '⚠️' : 'ℹ️'}</span>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto remove
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 4000);
}

/**
 * Utility functions
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidUrl(url) {
    try {
        new URL(url);
        return true;
    } catch {
        return false;
    }
}

function formatCurrency(amount) {
    const num = parseInt(amount) || 0;
    return num.toLocaleString('id-ID');
}

/**
 * Mock data for demo
 */
function getMockCourseData() {
    return {
        id: currentCourseId,
        title: 'Kerajinan Anyaman untuk Pemula',
        category: 'Kerajinan',
        difficulty: 'Pemula',
        description: 'Pelajari seni anyaman tradisional Indonesia dari dasar hingga mahir. Kursus ini akan mengajarkan berbagai teknik anyaman menggunakan bahan alami seperti pandan, bambu, dan rotan.',
        price: 299000,
        is_premium: false,
        allow_reviews: true,
        send_notifications: true,
        auto_certificate: false,
        requirements: 'Tidak ada persyaratan khusus. Cocok untuk pemula yang ingin belajar kerajinan tangan.',
        target_audience: 'Pemula yang tertarik dengan kerajinan tradisional, ibu rumah tangga, dan siapa saja yang ingin mengembangkan keterampilan baru.',
        learning_objectives: [
            'Memahami sejarah dan filosofi seni anyaman Indonesia',
            'Menguasai teknik dasar anyaman dengan berbagai pola',
            'Mampu membuat produk anyaman sederhana seperti tas dan tempat pensil',
            'Memahami cara merawat dan mengawetkan hasil anyaman'
        ],
        tags: ['kerajinan', 'anyaman', 'tradisional', 'handmade', 'indonesia'],
        cover_image: '/MindCraft-Project/assets/images/courses/anyaman-cover.jpg'
    };
}

/**
 * Navigation functions
 */
function goBackToCourses() {
    if (unsavedChanges) {
        if (confirm('Ada perubahan yang belum disimpan. Yakin ingin kembali?')) {
            window.location.href = '/MindCraft-Project/views/mentor/kursus-saya.php';
        }
    } else {
        window.location.href = '/MindCraft-Project/views/mentor/kursus-saya.php';
    }
}

function previewCourse() {
    // Open course preview in new tab
    window.open(`/MindCraft-Project/views/course-preview.php?id=${currentCourseId}`, '_blank');
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (autoSaveInterval) {
        clearInterval(autoSaveInterval);
    }
});