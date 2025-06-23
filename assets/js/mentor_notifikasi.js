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

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });

    // Notification item interactions
    const notificationItems = document.querySelectorAll('.notification-item');
    
    notificationItems.forEach(item => {
        // Add hover effect
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(4px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });

        // Handle notification click (mark as read if unread)
        item.addEventListener('click', function(e) {
            // Don't trigger if clicking on action buttons
            if (e.target.closest('.notification-actions')) {
                return;
            }

            if (this.classList.contains('unread')) {
                markAsReadAnimation(this);
            }
        });
    });

    // Action button handlers
    const markReadButtons = document.querySelectorAll('.btn-action.mark-read');
    markReadButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const notificationItem = this.closest('.notification-item');
            markAsReadAnimation(notificationItem);
            
            // Submit the form after animation
            setTimeout(() => {
                this.closest('form').submit();
            }, 300);
        });
    });

    // Delete button confirmation and animation
    const deleteButtons = document.querySelectorAll('.btn-action.delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const notificationItem = this.closest('.notification-item');
            
            // Show confirmation dialog
            showConfirmationModal(
                'Hapus Notifikasi',
                'Apakah Anda yakin ingin menghapus notifikasi ini?',
                () => {
                    // Delete animation
                    deleteNotificationAnimation(notificationItem);
                    
                    // Submit form after animation
                    setTimeout(() => {
                        this.closest('form').submit();
                    }, 400);
                }
            );
        });
    });

    // View button handler
    const viewButtons = document.querySelectorAll('.btn-action.view');
    viewButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Add loading state
            this.style.opacity = '0.6';
            this.innerHTML = '⏳';
            
            // Simulate navigation delay
            setTimeout(() => {
                window.location.href = this.href;
            }, 300);
        });
    });

    // Real-time notifications (simulate with periodic check)
    initRealTimeNotifications();

    // Keyboard shortcuts
    initKeyboardShortcuts();

    // Scroll animations
    initScrollAnimations();
});

// Mark as read animation
function markAsReadAnimation(notificationItem) {
    if (!notificationItem.classList.contains('unread')) return;
    
    notificationItem.style.transition = 'all 0.3s ease';
    notificationItem.style.background = 'linear-gradient(135deg, rgba(56, 161, 105, 0.1) 0%, rgba(56, 161, 105, 0.05) 100%)';
    notificationItem.style.borderLeftColor = '#38a169';
    
    setTimeout(() => {
        notificationItem.classList.remove('unread');
        notificationItem.classList.add('read');
        notificationItem.style.background = '';
        notificationItem.style.borderLeftColor = '';
        notificationItem.style.opacity = '0.7';
        
        // Update unread count
        updateUnreadCount(-1);
        
        // Remove mark as read button
        const markReadBtn = notificationItem.querySelector('.btn-action.mark-read');
        if (markReadBtn) {
            markReadBtn.style.opacity = '0';
            setTimeout(() => markReadBtn.remove(), 300);
        }
    }, 300);
}

// Delete notification animation
function deleteNotificationAnimation(notificationItem) {
    notificationItem.style.transition = 'all 0.4s ease';
    notificationItem.style.transform = 'translateX(-100%)';
    notificationItem.style.opacity = '0';
    notificationItem.style.maxHeight = notificationItem.offsetHeight + 'px';
    
    setTimeout(() => {
        notificationItem.style.maxHeight = '0';
        notificationItem.style.padding = '0';
        notificationItem.style.margin = '0';
    }, 200);
}

// Update unread count
function updateUnreadCount(change) {
    const unreadCountElement = document.querySelector('.unread-count');
    if (unreadCountElement) {
        const currentText = unreadCountElement.textContent;
        const currentCount = parseInt(currentText.split(' ')[0]) || 0;
        const newCount = Math.max(0, currentCount + change);
        
        unreadCountElement.textContent = newCount + ' Belum Dibaca';
        
        // Hide if zero
        if (newCount === 0) {
            unreadCountElement.style.opacity = '0';
            setTimeout(() => {
                unreadCountElement.style.display = 'none';
            }, 300);
        }
        
        // Update total count
        const totalCountElement = document.querySelector('.total-count');
        if (totalCountElement && change < 0) {
            const totalText = totalCountElement.textContent;
            const totalCount = parseInt(totalText.split(' ')[0]) || 0;
            const newTotal = Math.max(0, totalCount + change);
            totalCountElement.textContent = newTotal + ' Total';
        }
    }
}

// Show confirmation modal
function showConfirmationModal(title, message, onConfirm) {
    // Create modal HTML
    const modal = document.createElement('div');
    modal.className = 'confirmation-modal';
    modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-dialog">
                <div class="modal-header">
                    <h3>${title}</h3>
                </div>
                <div class="modal-body">
                    <p>${message}</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-cancel">Batal</button>
                    <button class="btn btn-confirm">Ya, Hapus</button>
                </div>
            </div>
        </div>
    `;
    
    // Add styles
    const style = document.createElement('style');
    style.textContent = `
        .confirmation-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-dialog {
            background: white;
            border-radius: 12px;
            max-width: 400px;
            width: 90%;
            animation: modalSlideIn 0.3s ease;
        }
        .modal-header {
            padding: 20px 24px 0;
        }
        .modal-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
        }
        .modal-body {
            padding: 16px 24px 20px;
        }
        .modal-body p {
            color: #718096;
            line-height: 1.5;
        }
        .modal-footer {
            padding: 0 24px 24px;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        .btn-cancel {
            padding: 10px 16px;
            background: #e2e8f0;
            color: #4a5568;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }
        .btn-confirm {
            padding: 10px 16px;
            background: #e53e3e;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }
        @keyframes modalSlideIn {
            from { opacity: 0; transform: scale(0.9) translateY(-20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
    `;
    
    document.head.appendChild(style);
    document.body.appendChild(modal);
    
    // Handle actions
    const cancelBtn = modal.querySelector('.btn-cancel');
    const confirmBtn = modal.querySelector('.btn-confirm');
    const overlay = modal.querySelector('.modal-overlay');
    
    function closeModal() {
        modal.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(modal);
            document.head.removeChild(style);
        }, 300);
    }
    
    cancelBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal();
    });
    
    confirmBtn.addEventListener('click', () => {
        onConfirm();
        closeModal();
    });
    
    // ESC key to close
    document.addEventListener('keydown', function escKeyHandler(e) {
        if (e.key === 'Escape') {
            closeModal();
            document.removeEventListener('keydown', escKeyHandler);
        }
    });
}

// Real-time notifications simulation
function initRealTimeNotifications() {
    // Check for new notifications every 30 seconds
    setInterval(async () => {
        try {
            // In real implementation, this would be an API call
            // const response = await fetch('/api/notifications/check-new');
            // const data = await response.json();
            
            // Simulate random new notification
            if (Math.random() < 0.1) { // 10% chance every 30 seconds
                showNewNotificationToast();
            }
        } catch (error) {
            console.log('Failed to check for new notifications:', error);
        }
    }, 30000);
}

// Show new notification toast
function showNewNotificationToast() {
    const toast = document.createElement('div');
    toast.className = 'notification-toast';
    toast.innerHTML = `
        <div class="toast-icon">🔔</div>
        <div class="toast-content">
            <div class="toast-title">Notifikasi Baru</div>
            <div class="toast-message">Anda memiliki notifikasi baru</div>
        </div>
        <button class="toast-close">&times;</button>
    `;
    
    // Add toast styles
    const toastStyle = document.createElement('style');
    toastStyle.textContent = `
        .notification-toast {
            position: fixed;
            top: 90px;
            right: 24px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            max-width: 350px;
            z-index: 1001;
            animation: toastSlideIn 0.3s ease;
            border-left: 4px solid #3A59D1;
        }
        .toast-icon {
            font-size: 20px;
            flex-shrink: 0;
        }
        .toast-content {
            flex: 1;
        }
        .toast-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 2px;
        }
        .toast-message {
            font-size: 14px;
            color: #718096;
        }
        .toast-close {
            background: none;
            border: none;
            font-size: 20px;
            color: #a0aec0;
            cursor: pointer;
            padding: 4px;
            flex-shrink: 0;
        }
        @keyframes toastSlideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    
    document.head.appendChild(toastStyle);
    document.body.appendChild(toast);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(toast);
            document.head.removeChild(toastStyle);
        }, 300);
    }, 5000);
    
    // Close button
    toast.querySelector('.toast-close').addEventListener('click', () => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(toast);
            document.head.removeChild(toastStyle);
        }, 300);
    });
    
    // Update unread count
    updateUnreadCount(1);
}

// Keyboard shortcuts
function initKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
        // Only activate shortcuts when not typing in inputs
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            return;
        }
        
        switch (e.key) {
            case 'r':
                // R key to refresh notifications
                e.preventDefault();
                window.location.reload();
                break;
                
            case 'a':
                // A key to mark all as read
                e.preventDefault();
                const markAllButton = document.querySelector('button[name="mark_all_read"]');
                if (markAllButton) {
                    markAllButton.click();
                }
                break;
                
            case 'Escape':
                // ESC to close mobile sidebar
                if (window.innerWidth <= 768) {
                    const sidebar = document.getElementById('sidebar');
                    if (sidebar) {
                        sidebar.classList.remove('open');
                    }
                }
                break;
        }
    });
}

// Scroll animations
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe notification items for scroll animation
    const notificationItems = document.querySelectorAll('.notification-item');
    notificationItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        item.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
        observer.observe(item);
    });
}

// Filter change with smooth transition
function applyFilters() {
    const container = document.querySelector('.notifications-container');
    
    // Add loading state
    container.style.opacity = '0.6';
    container.style.pointerEvents = 'none';
    
    // Get filter values
    const typeFilter = document.getElementById('typeFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    // Build URL with parameters
    const params = new URLSearchParams();
    if (typeFilter !== 'all') params.set('type', typeFilter);
    if (statusFilter !== 'all') params.set('status', statusFilter);
    
    // Navigate with smooth transition
    setTimeout(() => {
        window.location.search = params.toString();
    }, 300);
}

// Responsive handling
function handleResize() {
    const sidebar = document.getElementById('sidebar');
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

// Export functions for external use
window.notificationUtils = {
    markAsReadAnimation,
    deleteNotificationAnimation,
    updateUnreadCount,
    showConfirmationModal,
    showNewNotificationToast
};