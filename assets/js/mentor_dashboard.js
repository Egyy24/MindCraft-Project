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

    // Chart.js - Registration Chart
    const chartCanvas = document.getElementById('registrationChart');
    if (chartCanvas && typeof Chart !== 'undefined') {
        const ctx = chartCanvas.getContext('2d');
        
        // Get data from PHP (passed via window.dashboardData)
        const chartData = window.dashboardData || {
            monthlyRegistrations: [10, 20, 25, 22, 28, 24, 30],
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul']
        };
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Pendaftaran',
                    data: chartData.monthlyRegistrations,
                    backgroundColor: '#3A59D1',
                    borderColor: '#3305BC',
                    borderWidth: 0,
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(58, 89, 209, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#3305BC',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false,
                        titleFont: {
                            family: 'Inter',
                            size: 13,
                            weight: '500'
                        },
                        bodyFont: {
                            family: 'Inter',
                            size: 12,
                            weight: '400'
                        },
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                return 'Pendaftaran: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#718096',
                            font: {
                                family: 'Inter',
                                size: 12,
                                weight: '400'
                            },
                            padding: 8
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: Math.max(...chartData.monthlyRegistrations) + 5,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.06)',
                            drawBorder: false,
                            lineWidth: 1
                        },
                        ticks: {
                            color: '#718096',
                            font: {
                                family: 'Inter',
                                size: 12,
                                weight: '400'
                            },
                            stepSize: 5,
                            padding: 8
                        }
                    }
                },
                animation: {
                    duration: 1200,
                    easing: 'easeOutQuart'
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }

    // Counter animations for stat numbers with stagger effect
    function animateCounters() {
        const counters = document.querySelectorAll('.stat-number, .summary-value');
        
        counters.forEach((counter, index) => {
            setTimeout(() => {
                const originalText = counter.textContent;
                const target = parseFloat(originalText.replace(/[^\d.]/g, ''));
                let current = 0;
                const increment = target / 80; // Slower animation for better effect
                const isDecimal = originalText.includes('.');
                
                if (target > 0) {
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        
                        // Format based on original content
                        if (originalText.includes('%')) {
                            counter.textContent = Math.floor(current) + '%';
                        } else if (originalText.includes('Jam')) {
                            counter.textContent = Math.floor(current) + ' Jam';
                        } else if (originalText.includes('jt')) {
                            counter.textContent = 'Rp ' + current.toFixed(1) + ' jt';
                        } else if (isDecimal) {
                            counter.textContent = current.toFixed(1);
                        } else {
                            counter.textContent = Math.floor(current);
                        }
                    }, 20); // Smooth 50fps animation
                }
            }, index * 100); // Stagger animation by 100ms
        });
    }

    // Enhanced fade-in animation
    function initFadeInAnimations() {
        const elements = document.querySelectorAll('.fade-in-up');
        
        elements.forEach((element, index) => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                element.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 150); // Stagger by 150ms
        });
    }

    // Initialize animations
    setTimeout(() => {
        initFadeInAnimations();
        animateCounters();
    }, 300);

    // Enhanced hover effects for cards
    document.querySelectorAll('.stat-card, .activity-card, .chart-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
            this.style.boxShadow = '0 8px 25px rgba(58, 89, 209, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
        });
    });

    // Summary bar hover effects
    document.querySelectorAll('.summary-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.background = '#f8fafc';
            this.style.transform = 'scale(1.02)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.background = 'white';
            this.style.transform = 'scale(1)';
        });
    });

    // Smooth scroll for sidebar navigation with active state management
    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        link.addEventListener('click', function(e) {
            // Remove active class from all links
            document.querySelectorAll('.sidebar-menu a').forEach(el => {
                el.classList.remove('active');
            });
            
            // Add active class to clicked link
            this.classList.add('active');
        });
    });

    // Enhanced responsive sidebar for mobile
    function handleResize() {
        if (sidebar) {
            if (window.innerWidth <= 768) {
                sidebar.classList.add('mobile');
            } else {
                sidebar.classList.remove('mobile', 'open');
            }
        }
    }

    // Initialize and handle window resize
    handleResize();
    window.addEventListener('resize', handleResize);

    // Badge animation on scroll
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px'
    };

    const badgeObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const badges = entry.target.querySelectorAll('.stat-badge');
                badges.forEach((badge, index) => {
                    setTimeout(() => {
                        badge.style.animation = 'pulse 0.6s ease-in-out';
                    }, index * 200);
                });
            }
        });
    }, observerOptions);

    // Observe stats grid for badge animation
    const statsGrid = document.querySelector('.stats-grid');
    if (statsGrid) {
        badgeObserver.observe(statsGrid);
    }

    // Chart resize handler with debounce
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (typeof Chart !== 'undefined' && Chart.instances && Chart.instances.length > 0) {
                Chart.instances.forEach(chart => {
                    chart.resize();
                });
            }
        }, 250);
    });

    // Add loading skeleton effect (optional enhancement)
    function addLoadingEffect() {
        const cards = document.querySelectorAll('.stat-card, .summary-item');
        cards.forEach(card => {
            const originalBg = card.style.background;
            card.style.background = 'linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%)';
            card.style.backgroundSize = '200% 100%';
            card.style.animation = 'loading 1.5s infinite';
            
            // Store original background
            card.dataset.originalBg = originalBg;
        });

        // Remove loading effect after content loads
        setTimeout(() => {
            cards.forEach(card => {
                card.style.background = card.dataset.originalBg || 'white';
                card.style.animation = 'none';
            });
        }, 1000);
    }

    // Initialize loading effect
    addLoadingEffect();
});

// Utility functions for dynamic updates
function formatNumber(num, type) {
    if (typeof num !== 'number') {
        num = parseFloat(num) || 0;
    }
    
    switch (type) {
        case 'currency':
            return 'Rp ' + (num / 1000000).toFixed(1) + ' jt';
        case 'percentage':
            return Math.floor(num) + '%';
        case 'rating':
            return num.toFixed(1);
        case 'hours':
            return Math.floor(num) + ' Jam';
        default:
            return Math.floor(num).toString();
    }
}

// Function to update dashboard data dynamically with smooth transitions
function updateDashboardData(newData) {
    try {
        // Update stat cards with animation
        if (newData.totalCourses !== undefined) {
            const element = document.querySelector('.stat-card:nth-child(1) .stat-number');
            if (element) animateNumberChange(element, newData.totalCourses);
        }
        if (newData.totalMentees !== undefined) {
            const element = document.querySelector('.stat-card:nth-child(2) .stat-number');
            if (element) animateNumberChange(element, newData.totalMentees);
        }
        if (newData.averageRating !== undefined) {
            const element = document.querySelector('.stat-card:nth-child(3) .stat-number');
            if (element) animateNumberChange(element, newData.averageRating, 'rating');
        }

        // Update summary bar with stagger effect
        const summaryItems = document.querySelectorAll('.summary-value');
        const summaryData = [
            { value: newData.completionRate, format: 'percentage' },
            { value: newData.videoHours, format: 'hours' },
            { value: newData.moduleCount, format: 'default' },
            { value: newData.totalReviews, format: 'default' },
            { value: newData.totalEarnings, format: 'currency' }
        ];

        summaryData.forEach((data, index) => {
            if (data.value !== undefined && summaryItems[index]) {
                setTimeout(() => {
                    animateNumberChange(summaryItems[index], data.value, data.format);
                }, index * 100);
            }
        });

        // Update chart with smooth transition
        if (newData.monthlyRegistrations && typeof Chart !== 'undefined' && Chart.instances && Chart.instances.length > 0) {
            const chart = Chart.instances[0];
            chart.data.datasets[0].data = newData.monthlyRegistrations;
            chart.update('active');
        }
    } catch (error) {
        console.error('Error updating dashboard data:', error);
    }
}

// Helper function to animate number changes
function animateNumberChange(element, newValue, format) {
    if (!element) return;
    
    try {
        const currentValue = parseFloat(element.textContent.replace(/[^\d.]/g, '')) || 0;
        const difference = newValue - currentValue;
        const steps = 30;
        const stepValue = difference / steps;
        let currentStep = 0;

        const animation = setInterval(() => {
            currentStep++;
            const displayValue = currentValue + (stepValue * currentStep);
            
            if (currentStep >= steps) {
                clearInterval(animation);
                element.textContent = formatNumber(newValue, format);
            } else {
                element.textContent = formatNumber(displayValue, format);
            }
        }, 16); // 60fps
    } catch (error) {
        console.error('Error animating number change:', error);
        element.textContent = formatNumber(newValue, format);
    }
}

// CSS animations
const additionalStyles = document.createElement('style');
additionalStyles.textContent = `
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    
    .stat-badge {
        transition: all 0.3s ease;
    }
    
    .summary-item {
        transition: all 0.3s ease;
    }
    
    .activity-item {
        transition: all 0.2s ease;
    }
    
    .fade-in-up {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.8s ease, transform 0.8s ease;
    }
`;
document.head.appendChild(additionalStyles);