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

    // Initialize main trend chart
    const trendChartCanvas = document.getElementById('trendChart');
    if (trendChartCanvas && typeof Chart !== 'undefined') {
        const ctx = trendChartCanvas.getContext('2d');
        
        // Get data from PHP or use default
        const chartData = window.analyticsData || {
            monthlyData: [15, 18, 25, 12, 16, 22, 28, 19, 24, 17, 20, 26],
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
        };
        
        const trendChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Pendaftaran',
                    data: chartData.monthlyData,
                    backgroundColor: '#90C7F8',
                    borderColor: '#90C7F8',
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                    maxBarThickness: 30
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
                        max: Math.max(...chartData.monthlyData) + 5,
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
        window.trendChart = trendChart;
    }

    // Counter animations for analytics numbers
    function animateAnalyticsCounters() {
        const counters = document.querySelectorAll('.analytics-number');
        
        counters.forEach((counter, index) => {
            setTimeout(() => {
                const target = parseInt(counter.textContent);
                let current = 0;
                const increment = target / 60;
                
                if (target > 0) {
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        counter.textContent = Math.floor(current);
                    }, 25);
                }
            }, index * 200); // Stagger animation
        });
    }

    // Enhanced fade-in animation
    function initFadeInAnimations() {
        const elements = document.querySelectorAll('.analytics-card, .chart-section, .detail-link');
        
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
        animateAnalyticsCounters();
    }, 300);

    // Dropdown change handlers
    const courseSelect = document.getElementById('courseSelect');
    const periodSelect = document.getElementById('periodSelect');

    if (courseSelect) {
        courseSelect.addEventListener('change', function() {
            updateAnalyticsData(this.value, periodSelect ? periodSelect.value : '30');
        });
    }

    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            updateAnalyticsData(courseSelect ? courseSelect.value : 'all', this.value);
        });
    }

    // Enhanced hover effects for cards
    document.querySelectorAll('.analytics-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
            this.style.boxShadow = '0 8px 25px rgba(58, 89, 209, 0.15)';
            
            // Pulse animation for trend badge
            const trend = this.querySelector('.analytics-trend');
            if (trend) {
                trend.style.animation = 'pulse 0.6s ease-in-out';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
        });
    });

    // Sidebar navigation active state
    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        link.addEventListener('click', function(e) {
            document.querySelectorAll('.sidebar-menu a').forEach(el => {
                el.classList.remove('active');
            });
            this.classList.add('active');
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

    // Chart resize handler
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (typeof Chart !== 'undefined' && window.trendChart) {
                window.trendChart.resize();
            }
        }, 250);
    });

    // Detail link click handler
    const detailLink = document.querySelector('.detail-link');
    if (detailLink) {
        detailLink.addEventListener('click', function(e) {
            e.preventDefault();
            // Add loading state
            this.style.opacity = '0.6';
            this.style.pointerEvents = 'none';
            
            // Simulate navigation or AJAX call
            setTimeout(() => {
                this.style.opacity = '1';
                this.style.pointerEvents = 'auto';
                
                // Navigate to detail page or open modal
                window.location.href = '/MindCraft-Project/views/mentor/analitik-detail.php';
            }, 500);
        });
    }
});

// Function to update analytics data based on filters
function updateAnalyticsData(courseFilter, periodFilter) {
    // Show loading state
    showLoadingState();
    
    // In real implementation, this would make an AJAX call
    setTimeout(() => {
        // Simulate data update
        const newData = generateMockData(courseFilter, periodFilter);
        updateChartsAndCards(newData);
        hideLoadingState();
    }, 1000);
}

// Generate mock data based on filters
function generateMockData(courseFilter, periodFilter) {
    const baseData = [15, 18, 25, 12, 16, 22, 28, 19, 24, 17, 20, 26];
    const multiplier = courseFilter === 'all' ? 1 : 0.7;
    const periodMultiplier = periodFilter === '30' ? 1 : periodFilter === '90' ? 0.8 : 0.6;
    
    return {
        monthlyData: baseData.map(val => Math.floor(val * multiplier * periodMultiplier)),
        totalRegistrations: Math.floor(78 * multiplier * periodMultiplier),
        growthPercentage: Math.floor(12 * multiplier * periodMultiplier)
    };
}

// Update charts and cards with new data
function updateChartsAndCards(newData) {
    // Update chart
    if (window.trendChart) {
        window.trendChart.data.datasets[0].data = newData.monthlyData;
        window.trendChart.options.scales.y.max = Math.max(...newData.monthlyData) + 5;
        window.trendChart.update('active');
    }
    
    // Update analytics cards
    const analyticsNumbers = document.querySelectorAll('.analytics-number');
    analyticsNumbers.forEach((number, index) => {
        animateNumberChange(number, newData.totalRegistrations);
    });
    
    // Update trend percentages
    const trendElements = document.querySelectorAll('.analytics-trend');
    trendElements.forEach(trend => {
        trend.textContent = `▲${newData.growthPercentage}%`;
    });
}

// Show loading state
function showLoadingState() {
    const cards = document.querySelectorAll('.analytics-card');
    cards.forEach(card => {
        card.style.opacity = '0.6';
        card.style.pointerEvents = 'none';
    });
    
    const chartSection = document.querySelector('.chart-section');
    if (chartSection) {
        chartSection.style.opacity = '0.6';
    }
}

// Hide loading state
function hideLoadingState() {
    const cards = document.querySelectorAll('.analytics-card');
    cards.forEach(card => {
        card.style.opacity = '1';
        card.style.pointerEvents = 'auto';
    });
    
    const chartSection = document.querySelector('.chart-section');
    if (chartSection) {
        chartSection.style.opacity = '1';
    }
}

// Animate number changes
function animateNumberChange(element, newValue) {
    if (!element) return;
    
    const currentValue = parseInt(element.textContent) || 0;
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

// Utility function for AJAX calls (for real implementation)
function fetchAnalyticsData(courseId, period) {
    return fetch('/MindCraft-Project/api/mentor/analytics', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            course_id: courseId,
            period: period
        })
    })
    .then(response => response.json())
    .catch(error => {
        console.error('Error fetching analytics data:', error);
        return null;
    });
}

// Export functions for external use
window.analyticsUtils = {
    updateAnalyticsData,
    animateNumberChange,
    fetchAnalyticsData
};