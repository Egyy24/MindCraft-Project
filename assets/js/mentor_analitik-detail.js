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

    // Initialize Weekly Activity Chart
    const weeklyChartCanvas = document.getElementById('weeklyActivityChart');
    if (weeklyChartCanvas && typeof Chart !== 'undefined') {
        const ctx = weeklyChartCanvas.getContext('2d');
        
        // Get data from PHP or use default
        const chartData = window.detailData || {
            weeklyActivity: [12, 18, 15, 22, 25, 20, 19],
            weekLabels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']
        };
        
        const weeklyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.weekLabels,
                datasets: [{
                    label: 'Mentee Aktif',
                    data: chartData.weeklyActivity,
                    borderColor: '#3A59D1',
                    backgroundColor: 'rgba(58, 89, 209, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3A59D1',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
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
                                return 'Mentee Aktif: ' + context.parsed.y;
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
                        max: Math.max(...chartData.weeklyActivity) + 5,
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
                    duration: 1500,
                    easing: 'easeOutQuart'
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });

        // Store chart instance globally for updates
        window.weeklyChart = weeklyChart;
    }

    // Initialize Course Engagement Chart
    const engagementChartCanvas = document.getElementById('courseEngagementChart');
    if (engagementChartCanvas && typeof Chart !== 'undefined') {
        const ctx = engagementChartCanvas.getContext('2d');
        
        // Get course engagement data
        const courseData = window.detailData?.courseEngagement || [
            {course_name: 'Kerajian Anyaman untuk Pemula', engagement: 85, completion: 72},
            {course_name: 'Pengenalan Web Development', engagement: 78, completion: 65},
            {course_name: 'Strategi Pemasaran Digital', engagement: 92, completion: 88}
        ];
        
        const courseNames = courseData.map(item => item.course_name.length > 20 ? 
            item.course_name.substring(0, 20) + '...' : item.course_name);
        const engagementData = courseData.map(item => item.engagement);
        const completionData = courseData.map(item => item.completion);
        
        const engagementChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: courseNames,
                datasets: [
                    {
                        label: 'Engagement Rate',
                        data: engagementData,
                        backgroundColor: '#90C7F8',
                        borderColor: '#90C7F8',
                        borderWidth: 0,
                        borderRadius: 4,
                        borderSkipped: false,
                        maxBarThickness: 30
                    },
                    {
                        label: 'Completion Rate',
                        data: completionData,
                        backgroundColor: '#3A59D1',
                        borderColor: '#3A59D1',
                        borderWidth: 0,
                        borderRadius: 4,
                        borderSkipped: false,
                        maxBarThickness: 30
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: '#718096',
                            font: {
                                family: 'Inter',
                                size: 12,
                                weight: '500'
                            },
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'rect'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(58, 89, 209, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#3305BC',
                        borderWidth: 1,
                        cornerRadius: 8,
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
                                return courseData[context[0].dataIndex].course_name;
                            },
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + '%';
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
                                size: 11,
                                weight: '400'
                            },
                            padding: 8,
                            maxRotation: 45
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
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
                            stepSize: 20,
                            padding: 8,
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeOutQuart'
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });

        // Store chart instance globally for updates
        window.engagementChart = engagementChart;
    }

    // Animate progress bars
    function animateProgressBars() {
        const progressBars = document.querySelectorAll('.progress-fill');
        
        progressBars.forEach((bar, index) => {
            const targetWidth = bar.style.width;
            bar.style.width = '0%';
            
            setTimeout(() => {
                bar.style.transition = 'width 1s ease-out';
                bar.style.width = targetWidth;
            }, index * 100 + 500);
        });
    }

    // Animate counter numbers in overview cards
    function animateCounters() {
        const counters = document.querySelectorAll('.card-number');
        
        counters.forEach((counter, index) => {
            setTimeout(() => {
                const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
                let current = 0;
                const increment = target / 50;
                const isPercentage = counter.textContent.includes('%');
                
                if (target > 0) {
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        
                        if (isPercentage) {
                            counter.textContent = Math.floor(current) + '%';
                        } else if (counter.textContent.includes('min')) {
                            counter.textContent = Math.floor(current) + ' min';
                        } else {
                            counter.textContent = Math.floor(current);
                        }
                    }, 20);
                }
            }, index * 150);
        });
    }

    // Enhanced fade-in animation
    function initFadeInAnimations() {
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
    }

    // Initialize animations
    setTimeout(() => {
        initFadeInAnimations();
        animateCounters();
        animateProgressBars();
    }, 300);

    // Filter change handlers
    const courseSelect = document.getElementById('courseSelect');
    const periodSelect = document.getElementById('periodSelect');

    if (courseSelect) {
        courseSelect.addEventListener('change', function() {
            updateDetailData(this.value, periodSelect ? periodSelect.value : '30');
        });
    }

    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            updateDetailData(courseSelect ? courseSelect.value : 'all', this.value);
        });
    }

    // Action button handlers
    const actionButtons = document.querySelectorAll('.action-btn');
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const btnText = this.textContent.trim();
            
            // Add loading state
            const originalText = this.innerHTML;
            this.style.opacity = '0.6';
            this.style.pointerEvents = 'none';
            this.innerHTML = '<span class="btn-icon">⏳</span> Memproses...';
            
            // Simulate action
            setTimeout(() => {
                this.style.opacity = '1';
                this.style.pointerEvents = 'auto';
                this.innerHTML = originalText;
                
                // Show success feedback
                showNotification('Aksi berhasil: ' + btnText, 'success');
            }, 2000);
        });
    });

    // Enhanced hover effects for cards
    document.querySelectorAll('.overview-card, .chart-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
            this.style.boxShadow = '0 8px 25px rgba(58, 89, 209, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
        });
    });

    // Table row interactions
    document.querySelectorAll('.progress-table tr').forEach(row => {
        if (row.querySelector('td')) { // Skip header row
            row.addEventListener('click', function() {
                // Remove previous selections
                document.querySelectorAll('.progress-table tr').forEach(r => {
                    r.classList.remove('selected');
                });
                
                // Add selection to clicked row
                this.classList.add('selected');
                
                // You could add more functionality here like showing detail modal
                const menteeName = this.querySelector('.mentee-name')?.textContent;
                if (menteeName) {
                    console.log('Selected mentee:', menteeName);
                }
            });
        }
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

    // Chart resize handler
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (typeof Chart !== 'undefined') {
                if (window.weeklyChart) window.weeklyChart.resize();
                if (window.engagementChart) window.engagementChart.resize();
            }
        }, 250);
    });
});

// Function to update detail data based on filters
function updateDetailData(courseFilter, periodFilter) {
    // Show loading state
    showLoadingState();
    
    // In real implementation, this would make an AJAX call
    setTimeout(() => {
        // Simulate data update
        const newData = generateMockDetailData(courseFilter, periodFilter);
        updateChartsAndMetrics(newData);
        hideLoadingState();
    }, 1000);
}

// Generate mock data based on filters
function generateMockDetailData(courseFilter, periodFilter) {
    const baseData = {
        weeklyActivity: [12, 18, 15, 22, 25, 20, 19],
        totalMentees: 96,
        activeMentees: 78
    };
    
    const multiplier = courseFilter === 'all' ? 1 : 0.7;
    const periodMultiplier = periodFilter === '7' ? 0.3 : periodFilter === '30' ? 1 : 1.5;
    
    return {
        weeklyActivity: baseData.weeklyActivity.map(val => Math.floor(val * multiplier * periodMultiplier)),
        totalMentees: Math.floor(baseData.totalMentees * multiplier),
        activeMentees: Math.floor(baseData.activeMentees * multiplier * periodMultiplier)
    };
}

// Update charts and metrics with new data
function updateChartsAndMetrics(newData) {
    // Update weekly chart
    if (window.weeklyChart) {
        window.weeklyChart.data.datasets[0].data = newData.weeklyActivity;
        window.weeklyChart.options.scales.y.max = Math.max(...newData.weeklyActivity) + 5;
        window.weeklyChart.update('active');
    }
    
    // Update overview cards
    const totalMenteesCard = document.querySelector('.overview-card:nth-child(1) .card-number');
    const activeMenteesCard = document.querySelector('.overview-card:nth-child(2) .card-number');
    
    if (totalMenteesCard) animateNumberChange(totalMenteesCard, newData.totalMentees);
    if (activeMenteesCard) animateNumberChange(activeMenteesCard, newData.activeMentees);
}

// Show loading state
function showLoadingState() {
    const cards = document.querySelectorAll('.overview-card, .chart-card');
    cards.forEach(card => {
        card.style.opacity = '0.6';
        card.style.pointerEvents = 'none';
    });
}

// Hide loading state
function hideLoadingState() {
    const cards = document.querySelectorAll('.overview-card, .chart-card');
    cards.forEach(card => {
        card.style.opacity = '1';
        card.style.pointerEvents = 'auto';
    });
}

// Animate number changes
function animateNumberChange(element, newValue) {
    if (!element) return;
    
    const currentValue = parseInt(element.textContent.replace(/[^\d]/g, '')) || 0;
    const difference = newValue - currentValue;
    const steps = 30;
    const stepValue = difference / steps;
    let currentStep = 0;

    const animation = setInterval(() => {
        currentStep++;
        const displayValue = currentValue + (stepValue * currentStep);
        
        if (currentStep >= steps) {
            clearInterval(animation);
            element.textContent = newValue;
        } else {
            element.textContent = Math.floor(displayValue);
        }
    }, 16); // 60fps
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${type === 'success' ? '#2B992B' : '#3A59D1'};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        z-index: 1001;
        animation: slideInRight 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
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
    
    .progress-table tr.selected {
        background: rgba(58, 89, 209, 0.1) !important;
        border-left: 3px solid #3A59D1;
    }
    
    .progress-table tr:not(:first-child) {
        cursor: pointer;
        transition: all 0.2s ease;
    }
`;
document.head.appendChild(notificationStyles);

// Export functions for external use
window.detailUtils = {
    updateDetailData,
    animateNumberChange,
    showNotification
};