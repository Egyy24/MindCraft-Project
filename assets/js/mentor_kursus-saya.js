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

    // Search functionality
    const searchInput = document.getElementById('searchCourse');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const coursesGrid = document.querySelector('.courses-grid');
    
    let courses = []; // Will be populated from PHP data
    let filteredCourses = [];

    // Initialize courses data from window.coursesData
    if (window.coursesData) {
        courses = window.coursesData;
        filteredCourses = [...courses];
        renderCourses();
    }

    // Search input handler
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            filterCourses(searchTerm, categoryFilter?.value, statusFilter?.value);
        });
    }

    // Category filter handler
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            const searchTerm = searchInput?.value.toLowerCase().trim() || '';
            filterCourses(searchTerm, this.value, statusFilter?.value);
        });
    }

    // Status filter handler
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const searchTerm = searchInput?.value.toLowerCase().trim() || '';
            filterCourses(searchTerm, categoryFilter?.value, this.value);
        });
    }

    // Filter courses function
    function filterCourses(searchTerm, category, status) {
        filteredCourses = courses.filter(course => {
            const matchesSearch = !searchTerm || 
                course.title.toLowerCase().includes(searchTerm) ||
                course.category.toLowerCase().includes(searchTerm);
                
            const matchesCategory = !category || category === 'all' || 
                course.category === category;
                
            const matchesStatus = !status || status === 'all' || 
                course.status.toLowerCase() === status.toLowerCase();

            return matchesSearch && matchesCategory && matchesStatus;
        });

        renderCourses();
    }

    // Render courses function
    function renderCourses() {
        if (!coursesGrid) return;

        if (filteredCourses.length === 0) {
            coursesGrid.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">📚</div>
                    <h3>Tidak ada kursus ditemukan</h3>
                    <p>Coba ubah filter pencarian atau buat kursus baru</p>
                    <a href="/MindCraft-Project/views/mentor/buat-kursus-baru.php" class="btn-create-course">
                        ➕ Buat Kursus Baru
                    </a>
                </div>
            `;
            return;
        }

        coursesGrid.innerHTML = filteredCourses.map((course, index) => `
            <div class="course-card fade-in-up" style="animation-delay: ${index * 0.1}s;">
                <div class="course-header">
                    <h3 class="course-title">${escapeHtml(course.title)}</h3>
                    <span class="course-status status-${course.status.toLowerCase()}">
                        ${course.status}
                    </span>
                </div>
                
                <div class="course-stats">
                    <div class="stat-item">
                        <div class="stat-label">Mentee</div>
                        <div class="stat-value">${course.mentees || 0}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Modul</div>
                        <div class="stat-value">${course.modules || 0}</div>
                    </div>
                </div>
                
                <div class="course-metrics">
                    <div class="metric-item">
                        <span class="metric-label">Rating:</span>
                        <span class="metric-value">${course.rating || '0'}/5</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Pendapatan:</span>
                        <span class="metric-value">${formatCurrency(course.earnings || 0)}</span>
                    </div>
                </div>
                
                <div class="course-chart">
                    <div class="chart-placeholder">
                        <div class="chart-bars">
                            ${generateChartBars()}
                        </div>
                    </div>
                </div>
                
                <div class="course-actions">
                    <button class="btn btn-edit" onclick="editCourse(${course.id})">
                        ✏️ Edit
                    </button>
                </div>
            </div>
        `).join('');
    }

    // Generate random chart bars for visual appeal
    function generateChartBars() {
        let bars = '';
        for (let i = 0; i < 6; i++) {
            const height = Math.random() * 70 + 20; // Random height between 20-90%
            bars += `<div class="chart-bar" style="height: ${height}%;"></div>`;
        }
        return bars;
    }

    // Format currency
    function formatCurrency(amount) {
        if (amount >= 1000000) {
            return `Rp ${(amount / 1000000).toFixed(1)}jt`;
        } else if (amount >= 1000) {
            return `Rp ${(amount / 1000).toFixed(0)}k`;
        } else {
            return `Rp ${amount.toLocaleString('id-ID')}`;
        }
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Loading state
    function showLoadingState() {
        if (!coursesGrid) return;

        coursesGrid.innerHTML = Array(6).fill().map(() => `
            <div class="loading-card">
                <div class="loading-line"></div>
                <div class="loading-line"></div>
                <div class="loading-line"></div>
                <div class="loading-line"></div>
            </div>
        `).join('');
    }

    // Enhanced hover effects for course cards
    document.addEventListener('mouseenter', function(e) {
        if (e.target.closest('.course-card')) {
            const card = e.target.closest('.course-card');
            card.style.transform = 'translateY(-4px)';
            card.style.boxShadow = '0 8px 25px rgba(58, 89, 209, 0.15)';
        }
    }, true);

    document.addEventListener('mouseleave', function(e) {
        if (e.target.closest('.course-card')) {
            const card = e.target.closest('.course-card');
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
        }
    }, true);

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

    // Course management functions
    window.editCourse = function(courseId) {
        // Show loading state on button
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ Memuat...';
        button.disabled = true;

        // Simulate loading
        setTimeout(() => {
            // Navigate to edit page
            window.location.href = `/MindCraft-Project/views/mentor/edit-course.php?id=${courseId}`;
        }, 500);
    };

    window.deleteCourse = function(courseId) {
        if (confirm('Apakah Anda yakin ingin menghapus kursus ini? Tindakan ini tidak dapat dibatalkan.')) {
            // Show loading notification
            showNotification('Menghapus kursus...', 'info');
            
            // Simulate delete process
            setTimeout(() => {
                // Remove course from data
                courses = courses.filter(course => course.id !== courseId);
                filteredCourses = filteredCourses.filter(course => course.id !== courseId);
                
                // Re-render courses
                renderCourses();
                
                showNotification('Kursus berhasil dihapus!', 'success');
            }, 1500);
        }
    };

    window.duplicateCourse = function(courseId) {
        const course = courses.find(c => c.id === courseId);
        if (!course) return;

        showNotification('Menduplikasi kursus...', 'info');
        
        // Simulate duplication
        setTimeout(() => {
            const duplicatedCourse = {
                ...course,
                id: Date.now(), // Generate new ID
                title: course.title + ' (Copy)',
                status: 'Draft',
                mentees: 0,
                earnings: 0
            };
            
            courses.unshift(duplicatedCourse);
            filterCourses(
                searchInput?.value.toLowerCase().trim() || '',
                categoryFilter?.value,
                statusFilter?.value
            );
            
            showNotification('Kursus berhasil diduplikasi!', 'success');
        }, 1500);
    };

    window.toggleCourseStatus = function(courseId) {
        const course = courses.find(c => c.id === courseId);
        if (!course) return;

        const newStatus = course.status === 'Published' ? 'Draft' : 'Published';
        showNotification(`Mengubah status ke ${newStatus}...`, 'info');
        
        setTimeout(() => {
            course.status = newStatus;
            filterCourses(
                searchInput?.value.toLowerCase().trim() || '',
                categoryFilter?.value,
                statusFilter?.value
            );
            
            showNotification(`Kursus berhasil diubah ke ${newStatus}!`, 'success');
        }, 1000);
    };

    // Statistics update functionality
    function updateCourseStats() {
        const totalCourses = courses.length;
        const publishedCourses = courses.filter(c => c.status === 'Published').length;
        const draftCourses = courses.filter(c => c.status === 'Draft').length;
        const totalMentees = courses.reduce((sum, c) => sum + (c.mentees || 0), 0);
        const totalEarnings = courses.reduce((sum, c) => sum + (c.earnings || 0), 0);

        // Update stats display if elements exist
        const statsElements = {
            totalCourses: document.querySelector('.stat-total-courses'),
            publishedCourses: document.querySelector('.stat-published'),
            draftCourses: document.querySelector('.stat-draft'),
            totalMentees: document.querySelector('.stat-mentees'),
            totalEarnings: document.querySelector('.stat-earnings')
        };

        if (statsElements.totalCourses) {
            statsElements.totalCourses.textContent = totalCourses;
        }
        if (statsElements.publishedCourses) {
            statsElements.publishedCourses.textContent = publishedCourses;
        }
        if (statsElements.draftCourses) {
            statsElements.draftCourses.textContent = draftCourses;
        }
        if (statsElements.totalMentees) {
            statsElements.totalMentees.textContent = totalMentees;
        }
        if (statsElements.totalEarnings) {
            statsElements.totalEarnings.textContent = formatCurrency(totalEarnings);
        }
    }

    // Bulk actions functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const bulkActionSelect = document.getElementById('bulkAction');
    const applyBulkActionBtn = document.getElementById('applyBulkAction');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const courseCheckboxes = document.querySelectorAll('.course-checkbox');
            courseCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionState();
        });
    }

    function updateBulkActionState() {
        const selectedCourses = document.querySelectorAll('.course-checkbox:checked');
        const hasSelection = selectedCourses.length > 0;
        
        if (bulkActionSelect && applyBulkActionBtn) {
            bulkActionSelect.disabled = !hasSelection;
            applyBulkActionBtn.disabled = !hasSelection;
        }
        
        // Update select all checkbox state
        if (selectAllCheckbox) {
            const totalCheckboxes = document.querySelectorAll('.course-checkbox');
            if (selectedCourses.length === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
            } else if (selectedCourses.length === totalCheckboxes.length) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.indeterminate = true;
                selectAllCheckbox.checked = false;
            }
        }
    }

    // Apply bulk actions
    if (applyBulkActionBtn) {
        applyBulkActionBtn.addEventListener('click', function() {
            const selectedCourses = Array.from(document.querySelectorAll('.course-checkbox:checked'))
                .map(cb => parseInt(cb.value));
            const action = bulkActionSelect?.value;
            
            if (selectedCourses.length === 0 || !action) return;
            
            applyBulkAction(selectedCourses, action);
        });
    }

    function applyBulkAction(courseIds, action) {
        const courseCount = courseIds.length;
        let actionText = '';
        
        switch (action) {
            case 'publish':
                actionText = 'mempublikasi';
                break;
            case 'draft':
                actionText = 'mengubah ke draft';
                break;
            case 'delete':
                actionText = 'menghapus';
                if (!confirm(`Apakah Anda yakin ingin menghapus ${courseCount} kursus? Tindakan ini tidak dapat dibatalkan.`)) {
                    return;
                }
                break;
            default:
                return;
        }
        
        showNotification(`Sedang ${actionText} ${courseCount} kursus...`, 'info');
        
        setTimeout(() => {
            courseIds.forEach(courseId => {
                const courseIndex = courses.findIndex(c => c.id === courseId);
                if (courseIndex === -1) return;
                
                switch (action) {
                    case 'publish':
                        courses[courseIndex].status = 'Published';
                        break;
                    case 'draft':
                        courses[courseIndex].status = 'Draft';
                        break;
                    case 'delete':
                        courses.splice(courseIndex, 1);
                        break;
                }
            });
            
            // Update filtered courses and re-render
            filterCourses(
                searchInput?.value.toLowerCase().trim() || '',
                categoryFilter?.value,
                statusFilter?.value
            );
            
            // Reset bulk actions
            if (selectAllCheckbox) selectAllCheckbox.checked = false;
            if (bulkActionSelect) bulkActionSelect.value = '';
            updateBulkActionState();
            
            showNotification(`Berhasil ${actionText} ${courseCount} kursus!`, 'success');
        }, 1500);
    }

    // Sort functionality
    const sortSelect = document.getElementById('sortBy');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            sortCourses(this.value);
        });
    }

    function sortCourses(sortBy) {
        switch (sortBy) {
            case 'title':
                filteredCourses.sort((a, b) => a.title.localeCompare(b.title));
                break;
            case 'date':
                filteredCourses.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                break;
            case 'mentees':
                filteredCourses.sort((a, b) => (b.mentees || 0) - (a.mentees || 0));
                break;
            case 'earnings':
                filteredCourses.sort((a, b) => (b.earnings || 0) - (a.earnings || 0));
                break;
            case 'rating':
                filteredCourses.sort((a, b) => (b.rating || 0) - (a.rating || 0));
                break;
            default:
                // Default sort by creation date
                filteredCourses.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        }
        
        renderCourses();
    }

    // Export functionality
    window.exportCourseData = function() {
        showNotification('Mengekspor data kursus...', 'info');
        
        setTimeout(() => {
            const csvContent = generateCSV(courses);
            downloadCSV(csvContent, 'daftar-kursus-saya.csv');
            showNotification('Data kursus berhasil diekspor!', 'success');
        }, 1000);
    };

    function generateCSV(data) {
        const headers = ['Judul', 'Kategori', 'Status', 'Mentee', 'Modul', 'Rating', 'Pendapatan', 'Dibuat'];
        const rows = data.map(course => [
            course.title,
            course.category,
            course.status,
            course.mentees || 0,
            course.modules || 0,
            course.rating || 0,
            course.earnings || 0,
            course.created_at || ''
        ]);
        
        return [headers, ...rows]
            .map(row => row.map(field => `"${field}"`).join(','))
            .join('\n');
    }

    function downloadCSV(content, filename) {
        const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Auto-refresh functionality
    let autoRefreshInterval;
    const autoRefreshToggle = document.getElementById('autoRefresh');
    
    if (autoRefreshToggle) {
        autoRefreshToggle.addEventListener('change', function() {
            if (this.checked) {
                startAutoRefresh();
            } else {
                stopAutoRefresh();
            }
        });
    }

    function startAutoRefresh() {
        autoRefreshInterval = setInterval(() => {
            // Simulate fetching updated data
            showNotification('Memperbarui data...', 'info', 2000);
            
            // In real implementation, fetch from server
            // fetchUpdatedCourseData();
        }, 30000); // Refresh every 30 seconds
    }

    function stopAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
        }
    }

    // Course analytics preview
    window.showCourseAnalytics = function(courseId) {
        const course = courses.find(c => c.id === courseId);
        if (!course) return;
        
        // Show modal or navigate to analytics page
        showNotification(`Memuat analitik untuk "${course.title}"...`, 'info');
        
        setTimeout(() => {
            window.location.href = `/MindCraft-Project/views/mentor/analitik-detail.php?course=${courseId}`;
        }, 500);
    };

    // Initialize course statistics
    updateCourseStats();
    
    // Update stats when courses change
    const originalRenderCourses = renderCourses;
    renderCourses = function() {
        originalRenderCourses();
        updateCourseStats();
    };
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
const coursesUtils = {
    // Format numbers
    formatNumber: function(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(0) + 'K';
        }
        return num.toString();
    },
    
    // Get status color
    getStatusColor: function(status) {
        const colors = {
            'Published': '#2B992B',
            'Draft': '#F56500',
            'Archived': '#E53E3E'
        };
        return colors[status] || '#718096';
    },
    
    // Calculate course score
    calculateCourseScore: function(course) {
        const menteeScore = (course.mentees || 0) * 0.3;
        const ratingScore = (course.rating || 0) * 20;
        const earningsScore = Math.min((course.earnings || 0) / 1000000, 1) * 50;
        
        return Math.round(menteeScore + ratingScore + earningsScore);
    }
};

// Export for external use
window.coursesUtils = coursesUtils;