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

    // Initialize earnings chart
    const chartCanvas = document.getElementById('earningsChart');
    if (chartCanvas && typeof Chart !== 'undefined') {
        const ctx = chartCanvas.getContext('2d');
        
        // Get data from PHP or use default
        const chartData = window.earningsData || {
            monthlyEarnings: [850000, 920000, 1150000, 780000, 1020000, 1350000, 1680000, 1240000, 1430000, 1150000, 1580000, 1720000],
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
        };
        
        const earningsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Pendapatan',
                    data: chartData.monthlyEarnings,
                    backgroundColor: '#90C7F8',
                    borderColor: '#3A59D1',
                    borderWidth: 0,
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 40
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
                                return 'Pendapatan: ' + formatCurrency(context.parsed.y);
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
                            padding: 8,
                            callback: function(value) {
                                return formatCurrency(value);
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
        window.earningsChart = earningsChart;
    }

    // Filter change handlers
    const courseSelect = document.getElementById('courseSelect');
    const periodSelect = document.getElementById('periodSelect');

    if (courseSelect) {
        courseSelect.addEventListener('change', function() {
            updateEarningsData(this.value, periodSelect ? periodSelect.value : '30');
        });
    }

    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            updateEarningsData(courseSelect ? courseSelect.value : 'all', this.value);
        });
    }

    // Counter animations for earnings amounts
    function animateEarningsCounters() {
        const counters = document.querySelectorAll('.summary-amount');
        
        counters.forEach((counter, index) => {
            setTimeout(() => {
                const originalText = counter.textContent;
                const target = parseFloat(originalText.replace(/[^\d]/g, ''));
                let current = 0;
                const increment = target / 60;
                
                if (target > 0) {
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        
                        // Format based on original content
                        if (originalText.includes('.')) {
                            counter.textContent = formatCurrency(Math.floor(current));
                        } else if (originalText.includes('Rp')) {
                            counter.textContent = formatCurrency(Math.floor(current));
                        } else {
                            counter.textContent = Math.floor(current);
                        }
                    }, 25);
                }
            }, index * 200);
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
        animateEarningsCounters();
    }, 300);

    // Enhanced hover effects for cards
    document.querySelectorAll('.summary-card, .chart-section, .earnings-table-section').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
            this.style.boxShadow = '0 8px 25px rgba(58, 89, 209, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
        });
    });

    // Withdrawal functionality
    const withdrawBtn = document.getElementById('withdrawBtn');
    const withdrawalHistoryBtn = document.getElementById('withdrawalHistoryBtn');

    if (withdrawBtn) {
        withdrawBtn.addEventListener('click', function() {
            handleWithdrawal();
        });
    }

    if (withdrawalHistoryBtn) {
        withdrawalHistoryBtn.addEventListener('click', function() {
            showWithdrawalHistory();
        });
    }

    // Handle withdrawal process
    function handleWithdrawal() {
        const availableBalance = parseFloat(document.querySelector('.withdrawal-balance').textContent.replace(/[^\d]/g, ''));
        const minimumWithdrawal = 100000; // Rp 100,000

        if (availableBalance < minimumWithdrawal) {
            showNotification('Minimum penarikan adalah ' + formatCurrency(minimumWithdrawal), 'warning');
            return;
        }

        // Show withdrawal modal or process
        const amount = prompt(`Masukkan jumlah yang ingin ditarik (Minimum: ${formatCurrency(minimumWithdrawal)}, Maksimum: ${formatCurrency(availableBalance)}):`);
        
        if (amount === null) return; // User cancelled

        const withdrawalAmount = parseFloat(amount.replace(/[^\d]/g, ''));

        if (isNaN(withdrawalAmount) || withdrawalAmount < minimumWithdrawal) {
            showNotification('Jumlah penarikan tidak valid atau kurang dari minimum', 'error');
            return;
        }

        if (withdrawalAmount > availableBalance) {
            showNotification('Jumlah penarikan melebihi saldo tersedia', 'error');
            return;
        }

        // Show loading state
        withdrawBtn.disabled = true;
        withdrawBtn.classList.add('loading');
        withdrawBtn.textContent = 'Memproses...';

        // Simulate withdrawal process
        setTimeout(() => {
            // Reset button state
            withdrawBtn.disabled = false;
            withdrawBtn.classList.remove('loading');
            withdrawBtn.innerHTML = '💳 Tarik Dana';

            // Update balance
            const newBalance = availableBalance - withdrawalAmount;
            document.querySelector('.withdrawal-balance').textContent = formatCurrency(newBalance);

            showNotification(`Penarikan ${formatCurrency(withdrawalAmount)} berhasil diproses! Dana akan masuk ke rekening dalam 1-2 hari kerja.`, 'success');
            
            // Add to withdrawal history (in real app, this would update the database)
            addWithdrawalRecord(withdrawalAmount);
        }, 2000);
    }

    // Show withdrawal history
    function showWithdrawalHistory() {
        setTimeout(() => {
            // In real implementation, this would navigate to withdrawal history page
            window.location.href = '/MindCraft-Project/views/mentor/riwayat-penarikan.php';
        }, 1000);
    }

    // Add withdrawal record to table
    function addWithdrawalRecord(amount) {
        const tableBody = document.querySelector('.earnings-table tbody');
        if (!tableBody) return;

        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <div class="transaction-info">
                    <div class="transaction-type">Penarikan Dana</div>
                    <div class="transaction-course">Transfer ke rekening</div>
                </div>
            </td>
            <td>
                <div class="amount-cell">
                    <div class="amount-gross">-${formatCurrency(amount)}</div>
                    <div class="amount-fee">Biaya admin: Gratis</div>
                </div>
            </td>
            <td><span class="status-badge status-pending">Pending</span></td>
            <td><span class="payout-badge payout-pending">Processing</span></td>
            <td class="date-cell">${new Date().toLocaleDateString('id-ID')}</td>
        `;
        
        // Add to top of table
        tableBody.insertBefore(newRow, tableBody.firstChild);
        
        // Animate new row
        newRow.style.opacity = '0';
        newRow.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            newRow.style.transition = 'all 0.3s ease';
            newRow.style.opacity = '1';
            newRow.style.transform = 'translateY(0)';
        }, 100);
    }

    // Format currency function
    function formatCurrency(amount) {
        if (typeof amount !== 'number') {
            amount = parseFloat(amount) || 0;
        }
        
        if (amount >= 1000000) {
            return 'Rp ' + (amount / 1000000).toFixed(1) + ' jt';
        } else if (amount >= 1000) {
            return 'Rp ' + (amount / 1000).toFixed(0) + 'k';
        } else {
            return 'Rp ' + amount.toLocaleString('id-ID');
        }
    }

    // Export earnings data
    window.exportEarningsData = function() {
        showNotification('Mengekspor data pendapatan...', 'info');
        
        setTimeout(() => {
            const csvContent = generateEarningsCSV();
            downloadCSV(csvContent, 'laporan-pendapatan-' + new Date().toISOString().split('T')[0] + '.csv');
            showNotification('Data pendapatan berhasil diekspor!', 'success');
        }, 1000);
    };

    function generateEarningsCSV() {
        const headers = ['Tanggal', 'Jenis Transaksi', 'Kursus', 'Jumlah Kotor', 'Jumlah Bersih', 'Status', 'Status Payout'];
        
        // Get data from earnings table
        const rows = [];
        const tableRows = document.querySelectorAll('.earnings-table tbody tr');
        
        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 5) {
                const transactionType = cells[0].querySelector('.transaction-type')?.textContent || '';
                const course = cells[0].querySelector('.transaction-course')?.textContent || '';
                const grossAmount = cells[1].querySelector('.amount-gross')?.textContent || '';
                const netAmount = cells[1].querySelector('.amount-net')?.textContent || grossAmount;
                const status = cells[2].querySelector('.status-badge')?.textContent || '';
                const payoutStatus = cells[3].querySelector('.payout-badge')?.textContent || '';
                const date = cells[4].textContent || '';
                
                rows.push([date, transactionType, course, grossAmount, netAmount, status, payoutStatus]);
            }
        });
        
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

    // Update earnings data based on filters
    function updateEarningsData(courseFilter, periodFilter) {
        // Show loading state
        showLoadingState();
        
        // In real implementation, this would make an AJAX call
        setTimeout(() => {
            const newData = generateMockEarningsData(courseFilter, periodFilter);
            updateChartsAndMetrics(newData);
            hideLoadingState();
        }, 1000);
    }

    // Generate mock data based on filters
    function generateMockEarningsData(courseFilter, periodFilter) {
        const baseData = {
            monthlyEarnings: [850000, 920000, 1150000, 780000, 1020000, 1350000, 1680000, 1240000, 1430000, 1150000, 1580000, 1720000],
            totalEarnings: 12450000,
            courseSales: 186,
            avgPerCourse: 66789
        };
        
        const multiplier = courseFilter === 'all' ? 1 : 0.7;
        const periodMultiplier = periodFilter === '7' ? 0.2 : periodFilter === '30' ? 1 : periodFilter === '90' ? 2.5 : 4;
        
        return {
            monthlyEarnings: baseData.monthlyEarnings.map(val => Math.floor(val * multiplier * periodMultiplier)),
            totalEarnings: Math.floor(baseData.totalEarnings * multiplier * periodMultiplier),
            courseSales: Math.floor(baseData.courseSales * multiplier * periodMultiplier),
            avgPerCourse: Math.floor(baseData.avgPerCourse * multiplier)
        };
    }

    // Update charts and metrics with new data
    function updateChartsAndMetrics(newData) {
        // Update earnings chart
        if (window.earningsChart) {
            window.earningsChart.data.datasets[0].data = newData.monthlyEarnings;
            window.earningsChart.update('active');
        }
        
        // Update summary cards
        const summaryElements = document.querySelectorAll('.summary-amount');
        if (summaryElements.length >= 3) {
            animateNumberChange(summaryElements[0], newData.totalEarnings);
            animateNumberChange(summaryElements[1], newData.courseSales);
            animateNumberChange(summaryElements[2], newData.avgPerCourse);
        }
    }

    // Show loading state
    function showLoadingState() {
        const cards = document.querySelectorAll('.summary-card, .chart-section');
        cards.forEach(card => {
            card.style.opacity = '0.6';
            card.style.pointerEvents = 'none';
        });
    }

    // Hide loading state
    function hideLoadingState() {
        const cards = document.querySelectorAll('.summary-card, .chart-section');
        cards.forEach(card => {
            card.style.opacity = '1';
            card.style.pointerEvents = 'auto';
        });
    }

    // Animate number changes
    function animateNumberChange(element, newValue) {
        if (!element) return;
        
        const currentValue = parseFloat(element.textContent.replace(/[^\d]/g, '')) || 0;
        const difference = newValue - currentValue;
        const steps = 30;
        const stepValue = difference / steps;
        let currentStep = 0;

        const animation = setInterval(() => {
            currentStep++;
            const displayValue = currentValue + (stepValue * currentStep);
            
            if (currentStep >= steps) {
                clearInterval(animation);
                if (element.textContent.includes('Rp')) {
                    element.textContent = formatCurrency(newValue);
                } else {
                    element.textContent = Math.floor(newValue);
                }
            } else {
                if (element.textContent.includes('Rp')) {
                    element.textContent = formatCurrency(Math.floor(displayValue));
                } else {
                    element.textContent = Math.floor(displayValue);
                }
            }
        }, 16); // 60fps
    }

    // Table sorting functionality
    const sortableHeaders = document.querySelectorAll('.earnings-table th[data-sort]');
    sortableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            sortTable(this.dataset.sort);
        });
    });

    function sortTable(column) {
        const table = document.querySelector('.earnings-table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            let aVal, bVal;
            
            switch (column) {
                case 'date':
                    aVal = new Date(a.cells[4].textContent);
                    bVal = new Date(b.cells[4].textContent);
                    break;
                case 'amount':
                    aVal = parseFloat(a.cells[1].querySelector('.amount-gross').textContent.replace(/[^\d]/g, ''));
                    bVal = parseFloat(b.cells[1].querySelector('.amount-gross').textContent.replace(/[^\d]/g, ''));
                    break;
                case 'type':
                    aVal = a.cells[0].querySelector('.transaction-type').textContent;
                    bVal = b.cells[0].querySelector('.transaction-type').textContent;
                    break;
                default:
                    return 0;
            }
            
            return aVal > bVal ? -1 : aVal < bVal ? 1 : 0;
        });
        
        // Clear and re-append sorted rows
        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));
        
        showNotification(`Tabel diurutkan berdasarkan ${column}`, 'info', 2000);
    }

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
            if (typeof Chart !== 'undefined' && window.earningsChart) {
                window.earningsChart.resize();
            }
        }, 250);
    });

    // Detail link functionality
    const detailLink = document.querySelector('.detail-link');
    if (detailLink) {
        detailLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            setTimeout(() => {
                window.location.href = '/MindCraft-Project/views/mentor/pendapatan-detail.php';
            }, 1000);
        });
    }

    // Auto-refresh earnings data
    let autoRefreshInterval;
    function startAutoRefresh() {
        autoRefreshInterval = setInterval(() => {
            // In real implementation, fetch updated earnings data
            console.log('Auto-refreshing earnings data...');
            showNotification('Memperbarui data pendapatan...', 'info', 2000);
        }, 60000); // Refresh every minute
    }

    function stopAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
        }
    }

    // Start auto-refresh by default
    startAutoRefresh();

    // Stop auto-refresh when page becomes hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
        }
    });
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
const earningsUtils = {
    // Format currency with proper Indonesian format
    formatCurrency: function(amount) {
        if (typeof amount !== 'number') {
            amount = parseFloat(amount) || 0;
        }
        
        if (amount >= 1000000000) {
            return 'Rp ' + (amount / 1000000000).toFixed(1) + ' M';
        } else if (amount >= 1000000) {
            return 'Rp ' + (amount / 1000000).toFixed(1) + ' jt';
        } else if (amount >= 1000) {
            return 'Rp ' + (amount / 1000).toFixed(0) + 'k';
        } else {
            return 'Rp ' + amount.toLocaleString('id-ID');
        }
    },
    
    // Calculate earnings statistics
    calculateStats: function(earningsData) {
        const total = earningsData.reduce((sum, item) => sum + item.net_amount, 0);
        const average = total / earningsData.length;
        const highest = Math.max(...earningsData.map(item => item.net_amount));
        const lowest = Math.min(...earningsData.map(item => item.net_amount));
        
        return { total, average, highest, lowest };
    },
    
    // Get earnings trend
    getTrend: function(currentMonth, previousMonth) {
        if (previousMonth === 0) return 0;
        return ((currentMonth - previousMonth) / previousMonth * 100).toFixed(1);
    }
};

// Export for external use
window.earningsUtils = earningsUtils;