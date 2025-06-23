document.addEventListener('DOMContentLoaded', function() {
    // Initialize basic functionality
    initializeBasicFunctionality();
    
    // Add keyboard support
    addKeyboardSupport();
});

/**
 * Initialize basic logout functionality
 */
function initializeBasicFunctionality() {
    // Remove any existing fancy animations
    const card = document.querySelector('.logout-card');
    if (card) {
        card.style.transform = 'none';
        card.style.transition = 'none';
    }
    
    // Remove floating shapes animations
    const shapes = document.querySelectorAll('.floating-shape');
    shapes.forEach(shape => {
        shape.style.animation = 'none';
    });
}

/**
 * Handle logout confirmation
 */
function confirmLogout() {
    // Simple confirmation
    if (confirm('Apakah Anda yakin ingin logout?')) {
        processLogout();
    }
}

/**
 * Process logout
 */
function processLogout() {
    // Show simple loading
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.disabled = true;
        logoutBtn.textContent = 'Sedang logout...';
    }
    
    // Set form value and submit
    document.getElementById('confirmLogoutInput').value = 'yes';
    document.getElementById('logoutForm').submit();
}

/**
 * Handle cancel logout
 */
function cancelLogout() {
    // Simple redirect without animation
    window.location.href = '/MindCraft-Project/views/mentor/dashboard.php';
}

/**
 * Add basic keyboard support
 */
function addKeyboardSupport() {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            // Cancel on Escape
            cancelLogout();
        }
    });
}