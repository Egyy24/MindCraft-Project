let currentCourseId = null;
let currentTab = 'overview';
let studentsData = [];
let reviewsData = [];
let analyticsData = {};

// DOM Elements
const mobileMenuToggle = document.getElementById('mobileMenuToggle');
const sidebar = document.getElementById('sidebar');
const tabButtons = document.querySelectorAll('.tab-button');
const tabContents = document.querySelectorAll('.tab-content');

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeViewKursus();
});

function initializeViewKursus() {
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
    initializeTabs();
    initializeMobileMenu();
    initializeSearch();
    initializeCharts();
    initializeKeyboardShortcuts();
    
    // Load data from window object if available
    if (window.courseData) {
        displayCourseData(window.courseData);
    }
    
    if (window.studentsData) {
        studentsData = window.studentsData;
        displayStudentsData(studentsData);
    }
    
    if (window.reviewsData) {
        reviewsData = window.reviewsData;
        displayReviewsData(reviewsData);
    }
    
    console.log('View kursus page initialized for course ID:', currentCourseId);
}

/**
 * Initialize tab functionality
 */
function initializeTabs() {
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabId = button.dataset.tab;
            switchTab(tabId);
        });
    });
    
    // Set default active tab
    switchTab('overview');
}

/**
 * Switch between tabs
 */
function switchTab(tabId) {
    // Update button states
    tabButtons.forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tabId);
    });
    
    // Update content visibility
    tabContents.forEach(content => {
        content.classList.toggle('active', content.id === `${tabId}Tab`);
    });
    
    currentTab = tabId;
    
    // Load tab-specific data
    switch(tabId) {
        case 'students':
            refreshStudentsData();
            break;
        case 'reviews':
            refreshReviewsData();
            break;
        case 'analytics':
            refreshAnalyticsData();
            break;
        case 'settings':
            loadCourseSettings();
            break;
    }
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
 * Initialize search functionality
 */
function initializeSearch() {
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('input', debounce((e) => {
            filterStudents(e.target.value);
        }, 300));
    }
}

/**
 * Initialize charts
 */
function initializeCharts() {
    // Initialize performance chart in sidebar
    initializePerformanceChart();
    
    // Initialize analytics charts
    initializeAnalyticsCharts();
}

/**
 * Display course data
 */
function displayCourseData(data) {
    // Update course title in header
    const headerTitle = document.querySelector('.content-header h1');
    if (headerTitle) {
        headerTitle.textContent = data.title;
    }
    
    // Update course status
    const statusElement = document.querySelector('.course-status');
    if (statusElement) {
        statusElement.className = `course-status status-${data.status.toLowerCase()}`;
        statusElement.innerHTML = `<span>${getStatusIcon(data.status)}</span> ${data.status}`;
    }
    
    // Update course cover
    const coverElement = document.querySelector('.course-cover');
    if (coverElement && data.cover_image) {
        coverElement.innerHTML = `<img src="${data.cover_image}" alt="${data.title}" style="width: 100%; height: 100%; object-fit: cover;">`;
    }
    
    // Update course info
    const titleElement = document.querySelector('.course-title');
    if (titleElement) {
        titleElement.textContent = data.title;
    }
    
    const descriptionElement = document.querySelector('.course-description');
    if (descriptionElement) {
        descriptionElement.textContent = data.description;
    }
    
    // Update course meta
    updateCourseMeta(data);
    
    // Update stats if available
    if (window.courseStats) {
        updateCourseStats(window.courseStats);
    }
}

/**
 * Update course meta information
 */
function updateCourseMeta(data) {
    const metaContainer = document.querySelector('.course-meta');
    if (!metaContainer) return;
    
    metaContainer.innerHTML = `
        <div class="meta-item">
            <span>📚</span>
            <span>${data.category}</span>
        </div>
        <div class="meta-item">
            <span>📊</span>
            <span>${data.difficulty}</span>
        </div>
        <div class="meta-item">
            <span>💰</span>
            <span>${formatCurrency(data.price)}</span>
        </div>
        <div class="meta-item">
            <span>🕒</span>
            <span>${data.duration_hours || 0} jam</span>
        </div>
        <div class="meta-item">
            <span>📅</span>
            <span>Dibuat ${formatDate(data.created_at)}</span>
        </div>
    `;
}

/**
 * Update course statistics
 */
function updateCourseStats(stats) {
    const defaultStats = {
        total_students: 0,
        active_students: 0,
        completion_rate: 0,
        average_rating: 0,
        total_reviews: 0,
        total_earnings: 0
    };
    
    const courseStats = { ...defaultStats, ...stats };
    
    // Update individual stat values
    updateStatValue('total-students', courseStats.total_students);
    updateStatValue('active-students', courseStats.active_students);
    updateStatValue('completion-rate', `${courseStats.completion_rate}%`);
    updateStatValue('average-rating', `${courseStats.average_rating}/5`);
    updateStatValue('total-reviews', courseStats.total_reviews);
    updateStatValue('total-earnings', formatCurrency(courseStats.total_earnings));
}

/**
 * Update individual stat value
 */
function updateStatValue(statId, value) {
    const element = document.querySelector(`[data-stat="${statId}"]`);
    if (element) {
        element.textContent = value;
    }
}

/**
 * Display students data
 */
function displayStudentsData(students) {
    const container = document.querySelector('.students-list');
    if (!container) return;
    
    if (students.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                <div style="font-size: 3rem; margin-bottom: 1rem;">👥</div>
                <h3>Belum ada siswa terdaftar</h3>
                <p>Promosikan kursus Anda untuk menarik lebih banyak siswa</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = students.map(student => `
        <div class="student-item" data-student-id="${student.id}">
            <div class="student-avatar">
                ${student.name.charAt(0).toUpperCase()}
            </div>
            <div class="student-info">
                <div class="student-name">${student.name}</div>
                <div class="student-meta">
                    Bergabung ${formatDate(student.enrolled_at)} • 
                    Terakhir aktif ${formatRelativeTime(student.last_activity)}
                </div>
            </div>
            <div class="student-progress">
                <div class="progress-percentage">${student.progress}%</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: ${student.progress}%"></div>
                </div>
            </div>
        </div>
    `).join('');
}

/**
 * Filter students based on search query
 */
function filterStudents(query) {
    const filteredStudents = studentsData.filter(student =>
        student.name.toLowerCase().includes(query.toLowerCase()) ||
        student.email.toLowerCase().includes(query.toLowerCase())
    );
    
    displayStudentsData(filteredStudents);
}

/**
 * Refresh students data
 */
function refreshStudentsData() {
    if (currentTab === 'students' && window.studentsData) {
        studentsData = window.studentsData;
        displayStudentsData(studentsData);
    }
}

/**
 * Display reviews data
 */
function displayReviewsData(data) {
    updateReviewsSummary(data.summary);
    displayReviewsList(data.reviews);
}

/**
 * Update reviews summary
 */
function updateReviewsSummary(summary) {
    const avgRating = document.querySelector('.avg-rating');
    const ratingStars = document.querySelector('.rating-stars');
    const totalReviews = document.querySelector('.total-reviews');
    
    if (avgRating) avgRating.textContent = summary.average_rating.toFixed(1);
    if (ratingStars) ratingStars.innerHTML = generateStars(summary.average_rating);
    if (totalReviews) totalReviews.textContent = `${summary.total_reviews} ulasan`;
    
    // Update rating breakdown
    const ratingBreakdown = document.querySelector('.rating-breakdown');
    if (ratingBreakdown) {
        ratingBreakdown.innerHTML = [5, 4, 3, 2, 1].map(rating => {
            const count = summary.rating_breakdown[rating] || 0;
            const percentage = summary.total_reviews > 0 ? (count / summary.total_reviews * 100) : 0;
            
            return `
                <div class="rating-row">
                    <div class="rating-label">${rating}</div>
                    <div class="rating-bar">
                        <div class="rating-fill" style="width: ${percentage}%"></div>
                    </div>
                    <div class="rating-count">${count}</div>
                </div>
            `;
        }).join('');
    }
}

/**
 * Display reviews list
 */
function displayReviewsList(reviews) {
    const container = document.querySelector('.reviews-list');
    if (!container) return;
    
    if (reviews.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                <div style="font-size: 3rem; margin-bottom: 1rem;">⭐</div>
                <h3>Belum ada ulasan</h3>
                <p>Ulasan akan muncul setelah siswa menyelesaikan kursus</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = reviews.map(review => `
        <div class="review-item" data-review-id="${review.id}">
            <div class="review-header">
                <div class="reviewer-info">
                    <div class="reviewer-avatar">
                        ${review.student_name.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <div class="reviewer-name">${review.student_name}</div>
                        <div class="review-rating">${generateStars(review.rating)}</div>
                    </div>
                </div>
                <div class="review-date">${formatDate(review.created_at)}</div>
            </div>
            <div class="review-text">${review.review_text}</div>
            <div class="review-actions">
                <button class="btn btn-secondary btn-sm" onclick="replyToReview(${review.id})">
                    💬 Balas
                </button>
                <button class="btn btn-secondary btn-sm" onclick="reportReview(${review.id})">
                    🚩 Laporkan
                </button>
            </div>
        </div>
    `).join('');
}

/**
 * Refresh reviews data
 */
function refreshReviewsData() {
    if (currentTab === 'reviews' && window.reviewsData) {
        reviewsData = window.reviewsData;
        displayReviewsData(reviewsData);
    }
}

/**
 * Refresh analytics data
 */
function refreshAnalyticsData() {
    if (currentTab === 'analytics') {
        console.log('Refreshing analytics data...');
        // This would load fresh analytics data
    }
}

/**
 * Initialize performance chart in sidebar
 */
function initializePerformanceChart() {
    const chartContainer = document.querySelector('.chart-bars');
    if (!chartContainer) return;
    
    // Generate sample bars
    const barCount = 7;
    const bars = [];
    
    for (let i = 0; i < barCount; i++) {
        const height = Math.random() * 80 + 20; // Random height between 20-100%
        bars.push(`<div class="chart-bar" style="height: ${height}%;"></div>`);
    }
    
    chartContainer.innerHTML = bars.join('');
}

/**
 * Initialize analytics charts
 */
function initializeAnalyticsCharts() {
    const chartContainers = document.querySelectorAll('.analytics-chart');
    
    chartContainers.forEach(container => {
        container.innerHTML = `
            <div style="text-align: center;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📊</div>
                <div>Chart akan dimuat di sini</div>
                <div style="font-size: 0.8rem; color: var(--text-light); margin-top: 0.5rem;">
                    Integrasi dengan Chart.js atau library lainnya
                </div>
            </div>
        `;
    });
}

/**
 * Load course settings
 */
function loadCourseSettings() {
    console.log('Loading course settings...');
}

/**
 * Course management functions
 */
function editCourse() {
    window.location.href = `/MindCraft-Project/views/mentor/edit-course.php?id=${currentCourseId}`;
}

function toggleCourseStatus() {
    const currentStatus = document.querySelector('.course-status').textContent.trim();
    const newStatus = currentStatus.includes('Published') ? 'Draft' : 'Published';
    
    if (confirm(`Ubah status kursus menjadi ${newStatus}?`)) {
        updateCourseStatus(newStatus);
    }
}

async function updateCourseStatus(status) {
    try {
        showLoadingState('course-actions', true);
        
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Update UI
        const statusElement = document.querySelector('.course-status');
        statusElement.className = `course-status status-${status.toLowerCase()}`;
        statusElement.innerHTML = `<span>${getStatusIcon(status)}</span> ${status}`;
        
        showNotification(`Status kursus berhasil diubah menjadi ${status}`, 'success');
        
    } catch (error) {
        console.error('Error updating course status:', error);
        showNotification('Gagal mengubah status kursus', 'error');
    } finally {
        showLoadingState('course-actions', false);
    }
}

function duplicateCourse() {
    if (confirm('Duplikasi kursus ini? Kursus baru akan dibuat dengan status Draft.')) {
        window.location.href = `/MindCraft-Project/views/mentor/buat-kursus-baru.php?duplicate=${currentCourseId}`;
    }
}

function deleteCourse() {
    const courseName = document.querySelector('.course-title').textContent;
    
    if (confirm(`Hapus kursus "${courseName}"? Tindakan ini tidak dapat dibatalkan.`)) {
        if (confirm('Apakah Anda yakin? Semua data siswa dan progress akan hilang.')) {
            performDeleteCourse();
        }
    }
}

async function performDeleteCourse() {
    try {
        showLoadingState('course-actions', true);
        
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        showNotification('Kursus berhasil dihapus', 'success');
        
        setTimeout(() => {
            window.location.href = '/MindCraft-Project/views/mentor/kursus-saya.php';
        }, 2000);
        
    } catch (error) {
        console.error('Error deleting course:', error);
        showNotification('Gagal menghapus kursus', 'error');
    } finally {
        showLoadingState('course-actions', false);
    }
}

/**
 * Review management functions
 */
function replyToReview(reviewId) {
    const modal = createModal('Balas Ulasan', `
        <form id="replyForm" onsubmit="submitReply(event, ${reviewId})">
            <div class="form-group">
                <label class="form-label">Balasan Anda:</label>
                <textarea class="form-control" name="reply" rows="4" placeholder="Tulis balasan untuk ulasan ini..." required></textarea>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Kirim Balasan</button>
            </div>
        </form>
    `);
    
    showModal(modal);
}

function submitReply(event, reviewId) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const reply = formData.get('reply');
    
    // Simulate API call
    console.log('Submitting reply for review', reviewId, ':', reply);
    
    showNotification('Balasan berhasil dikirim', 'success');
    closeModal();
    
    // Refresh reviews
    setTimeout(() => {
        refreshReviewsData();
    }, 1000);
}

function reportReview(reviewId) {
    if (confirm('Laporkan ulasan ini sebagai tidak pantas?')) {
        console.log('Reporting review:', reviewId);
        showNotification('Ulasan telah dilaporkan', 'info');
    }
}

/**
 * Utility functions
 */
function formatCurrency(amount) {
    if (amount === 0) return 'Gratis';
    
    const num = parseInt(amount) || 0;
    if (num >= 1000000) {
        return 'Rp ' + (num / 1000000).toFixed(1) + 'jt';
    } else if (num >= 1000) {
        return 'Rp ' + (num / 1000).toFixed(0) + 'k';
    } else {
        return 'Rp ' + num.toLocaleString('id-ID');
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatRelativeTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) return 'Hari ini';
    if (diffDays === 1) return 'Kemarin';
    if (diffDays < 7) return `${diffDays} hari lalu`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} minggu lalu`;
    return `${Math.floor(diffDays / 30)} bulan lalu`;
}

function generateStars(rating) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
    
    return '★'.repeat(fullStars) + 
           (hasHalfStar ? '☆' : '') + 
           '☆'.repeat(emptyStars);
}

function getStatusIcon(status) {
    switch (status.toLowerCase()) {
        case 'published': return '🟢';
        case 'draft': return '🟡';
        case 'archived': return '🔴';
        default: return '⚪';
    }
}

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

function showLoadingState(containerId, show) {
    const container = document.getElementById(containerId) || document.querySelector(`.${containerId}`);
    if (!container) return;
    
    if (show) {
        container.classList.add('loading');
    } else {
        container.classList.remove('loading');
    }
}

/**
 * Modal functions
 */
function createModal(title, content) {
    return `
        <div class="modal" id="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">${title}</h3>
                    <button class="modal-close" onclick="closeModal()">&times;</button>
                </div>
                <div class="modal-body">
                    ${content}
                </div>
            </div>
        </div>
    `;
}

function showModal(modalHtml) {
    // Remove existing modal
    const existingModal = document.getElementById('modal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add new modal
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    const modal = document.getElementById('modal');
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
    
    // Close on background click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });
}

function closeModal() {
    const modal = document.getElementById('modal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.remove();
        }, 300);
    }
}

/**
 * Notification function
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span>${type === 'error' ? '❌' : type === 'success' ? '✅' : 'ℹ️'}</span>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Auto remove
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 4000);
}

/**
 * Navigation functions
 */
function goBackToCourses() {
    window.location.href = '/MindCraft-Project/views/mentor/kursus-saya.php';
}

function viewCoursePublic() {
    // Open course public view in new tab
    window.open(`/MindCraft-Project/views/course-detail.php?id=${currentCourseId}`, '_blank');
}

function exportCourseData() {
    showNotification('Menyiapkan data untuk export...', 'info');
    
    setTimeout(() => {
        // Simulate export
        const data = {
            course: window.courseData || {},
            students: window.studentsData || [],
            reviews: window.reviewsData || {},
            analytics: {}
        };
        
        const dataStr = JSON.stringify(data, null, 2);
        const dataBlob = new Blob([dataStr], { type: 'application/json' });
        const url = URL.createObjectURL(dataBlob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = `course-${currentCourseId}-data-${new Date().toISOString().split('T')[0]}.json`;
        link.style.display = 'none';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        URL.revokeObjectURL(url);
        
        showNotification('Data berhasil diexport!', 'success');
    }, 1500);
}

/**
 * Settings functions
 */
function saveSettings() {
    const form = document.getElementById('settingsForm');
    if (!form) return;
    
    const formData = new FormData(form);
    const settings = {};
    
    for (let [key, value] of formData.entries()) {
        settings[key] = value;
    }
    
    console.log('Saving settings:', settings);
    
    showNotification('Pengaturan berhasil disimpan!', 'success');
}

/**
 * Student management functions
 */
function exportStudentsList() {
    const students = window.studentsData || [];
    const csvContent = [
        ['Nama', 'Email', 'Tanggal Bergabung', 'Progress', 'Terakhir Aktif'],
        ...students.map(student => [
            student.name,
            student.email,
            formatDate(student.enrolled_at),
            `${student.progress}%`,
            formatDate(student.last_activity)
        ])
    ];
    
    const csvString = csvContent.map(row => 
        row.map(field => `"${field}"`).join(',')
    ).join('\n');
    
    const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `students-course-${currentCourseId}-${new Date().toISOString().split('T')[0]}.csv`;
    link.style.display = 'none';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showNotification('Daftar siswa berhasil diexport!', 'success');
}

function sendMessage() {
    const modal = createModal('Kirim Pesan ke Semua Siswa', `
        <form id="messageForm" onsubmit="submitMessage(event)">
            <div class="form-group">
                <label class="form-label">Subjek:</label>
                <input type="text" class="form-control" name="subject" placeholder="Contoh: Update Materi Baru" required>
            </div>
            <div class="form-group">
                <label class="form-label">Pesan:</label>
                <textarea class="form-control" name="message" rows="5" placeholder="Tulis pesan untuk semua siswa di kursus ini..." required></textarea>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Kirim Pesan</button>
            </div>
        </form>
    `);
    
    showModal(modal);
}

function submitMessage(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const messageData = {
        subject: formData.get('subject'),
        message: formData.get('message'),
        course_id: currentCourseId
    };
    
    console.log('Sending message:', messageData);
    
    showNotification('Pesan berhasil dikirim ke semua siswa!', 'success');
    closeModal();
}

/**
 * Keyboard shortcuts
 */
function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
        // Only trigger if not typing in input/textarea
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            return;
        }
        
        switch (e.key) {
            case '1':
                switchTab('overview');
                break;
            case '2':
                switchTab('students');
                break;
            case '3':
                switchTab('reviews');
                break;
            case '4':
                switchTab('analytics');
                break;
            case '5':
                switchTab('settings');
                break;
            case 'e':
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    editCourse();
                }
                break;
            case 'Escape':
                closeModal();
                break;
        }
    });
}

// Auto-refresh data every 5 minutes
setInterval(() => {
    if (document.visibilityState === 'visible') {
        switch (currentTab) {
            case 'students':
                refreshStudentsData();
                break;
            case 'reviews':
                refreshReviewsData();
                break;
            case 'analytics':
                refreshAnalyticsData();
                break;
        }
    }
}, 5 * 60 * 1000);

// Performance optimization
function lazyLoadImages() {
    const images = document.querySelectorAll('img[data-src]');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for older browsers
        images.forEach(img => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        });
    }
}

// Initialize lazy loading when page loads
document.addEventListener('DOMContentLoaded', lazyLoadImages);

// Add form group styling for modal forms
const formGroupStyle = `
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        display: block;
        font-weight: 500;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }
    
    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        font-size: 0.95rem;
        font-family: inherit;
        transition: var(--transition);
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px rgba(58, 89, 209, 0.1);
    }
`;

// Add styles to head if not already present
if (!document.querySelector('#modal-styles')) {
    const styleEl = document.createElement('style');
    styleEl.id = 'modal-styles';
    styleEl.textContent = formGroupStyle;
    document.head.appendChild(styleEl);
}