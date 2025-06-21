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

    // Initialize charts
    initializeCharts();
    
    // Initialize filters
    initializeFilters();
    
    // Initialize search
    initializeSearch();
    
    // Initialize table sorting
    initializeTableSorting();
    
    // Initialize animations
    initializeAnimations();
    
    // Initialize export functions
    initializeExportFunctions();

    /**
     * Initialize Charts
     */
    function initializeCharts() {
        // Daily Earnings Chart
        const dailyChartCanvas = document.getElementById('dailyEarningsChart');
        if (dailyChartCanvas && typeof Chart !== 'undefined') {
            const ctx = dailyChartCanvas.getContext('2d');
            
            const earningsData = window.earningsDetailData || {};
            const dailyData = earningsData.dailyData || [];
            
            // Prepare chart data
            const labels = dailyData.map(item => {
                const date = new Date(item.date);
                return date.toLocaleDateString('id-ID', { month: 'short', day: 'numeric' });
            });
            
            const earningsValues = dailyData.map(item => parseFloat(item.daily_earnings) || 0);
            const transactionValues = dailyData.map(item => parseInt(item.daily_transactions) || 0);
            
            window.dailyEarningsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pendapatan',
                        data: earningsValues,
                        borderColor: '#3A59D1',
                        backgroundColor: 'rgba(58, 89, 209, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#3A59D1',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }, {
                        label: 'Transaksi',
                        data: transactionValues,
                        borderColor: '#90C7F8',
                        backgroundColor: 'rgba(144, 199, 248, 0.1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#90C7F8',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        yAxisID: 'y1',
                        hidden: true
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
                            displayColors: true,
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
                                    if (context.datasetIndex === 0) {
                                        return 'Pendapatan: ' + formatCurrency(context.parsed.y);
                                    } else {
                                        return 'Transaksi: ' + context.parsed.y + ' transaksi';
                                    }
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
                                maxTicksLimit: 8
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.06)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#718096',
                                font: {
                                    family: 'Inter',
                                    size: 11,
                                    weight: '400'
                                },
                                callback: function(value) {
                                    return formatCurrency(value);
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false
                            },
                            ticks: {
                                color: '#718096',
                                font: {
                                    family: 'Inter',
                                    size: 11,
                                    weight: '400'
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
        }

        // Course Performance Chart
        const courseChartCanvas = document.getElementById('coursePerformanceChart');
        if (courseChartCanvas && typeof Chart !== 'undefined') {
            const ctx = courseChartCanvas.getContext('2d');
            
            const courseData = earningsData.courseBreakdown || [];
            const courseLabels = courseData.map(item => {
                const title = item.course_name || 'Unknown';
                return title.length > 20 ? title.substring(0, 20) + '...' : title;
            });
            const courseEarnings = courseData.map(item => parseFloat(item.total_earnings) || 0);
            const courseTransactions = courseData.map(item => parseInt(item.transaction_count) || 0);
            
            window.coursePerformanceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: courseLabels,
                    datasets: [{
                        label: 'Pendapatan',
                        data: courseEarnings,
                        backgroundColor: '#3A59D1',
                        borderColor: '#3305BC',
                        borderWidth: 1,
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 40
                    }, {
                        label: 'Transaksi',
                        data: courseTransactions,
                        backgroundColor: '#90C7F8',
                        borderColor: '#3A59D1',
                        borderWidth: 1,
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 40,
                        yAxisID: 'y1'
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
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    if (context.datasetIndex === 0) {
                                        return 'Pendapatan: ' + formatCurrency(context.parsed.y);
                                    } else {
                                        return 'Transaksi: ' + context.parsed.y + ' transaksi';
                                    }
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
                                    size: 10,
                                    weight: '400'
                                },
                                maxRotation: 45
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.06)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#718096',
                                font: {
                                    family: 'Inter',
                                    size: 10,
                                    weight: '400'
                                },
                                callback: function(value) {
                                    return formatCurrency(value);
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false
                            },
                            ticks: {
                                color: '#718096',
                                font: {
                                    family: 'Inter',
                                    size: 10,
                                    weight: '400'
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart'
                    }
                }
            });
        }

        // Chart toggle functionality
        const chartToggles = document.querySelectorAll('.chart-toggle');
        chartToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const chartType = this.getAttribute('data-chart');
                
                // Update active state
                chartToggles.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Toggle chart datasets
                if (window.dailyEarningsChart) {
                    if (chartType === 'earnings') {
                        window.dailyEarningsChart.data.datasets[0].hidden = false;
                        window.dailyEarningsChart.data.datasets[1].hidden = true;
                    } else {
                        window.dailyEarningsChart.data.datasets[0].hidden = true;
                        window.dailyEarningsChart.data.datasets[1].hidden = false;
                    }
                    window.dailyEarningsChart.update();
                }
            });
        });
    }

    /**
     * Initialize Filters
     */
    function initializeFilters() {
        const courseSelect = document.getElementById('courseSelect');
        const periodSelect = document.getElementById('periodSelect');
        const startDate = document.getElementById('startDate');
        const endDate = document.getElementById('endDate');
        const applyFilters = document.getElementById('applyFilters');
        const resetFilters = document.getElementById('resetFilters');
        const dateRangeGroup = document.getElementById('dateRangeGroup');
        const dateRangeGroupEnd = document.getElementById('dateRangeGroupEnd');

        // Show/hide date range inputs
        if (periodSelect) {
            periodSelect.addEventListener('change', function() {
                if (this.value === 'custom') {
                    dateRangeGroup.style.display = 'flex';
                    dateRangeGroupEnd.style.display = 'flex';
                } else {
                    dateRangeGroup.style.display = 'none';
                    dateRangeGroupEnd.style.display = 'none';
                }
            });

            // Trigger on initial load
            if (periodSelect.value === 'custom') {
                dateRangeGroup.style.display = 'flex';
                dateRangeGroupEnd.style.display = 'flex';
            }
        }

        // Apply filters
        if (applyFilters) {
            applyFilters.addEventListener('click', function() {
                const filters = {
                    course: courseSelect ? courseSelect.value : 'all',
                    period: periodSelect ? periodSelect.value : '30',
                    start_date: startDate ? startDate.value : '',
                    end_date: endDate ? endDate.value : ''
                };

                // Build query string
                const queryParams = new URLSearchParams();
                Object.keys(filters).forEach(key => {
                    if (filters[key]) {
                        queryParams.append(key, filters[key]);
                    }
                });

                // Reload page with filters
                window.location.href = window.location.pathname + '?' + queryParams.toString();
            });
        }

        // Reset filters
        if (resetFilters) {
            resetFilters.addEventListener('click', function() {
                window.location.href = window.location.pathname;
            });
        }

        // Auto-apply on select change (for better UX)
        [courseSelect, periodSelect].forEach(select => {
            if (select) {
                select.addEventListener('change', function() {
                    // Add visual feedback
                    this.style.borderColor = '#3A59D1';
                    setTimeout(() => {
                        this.style.borderColor = '';
                    }, 300);
                });
            }
        });
    }

    /**
     * Initialize Search
     */
    function initializeSearch() {
        const searchInput = document.getElementById('transactionSearch');
        const tableBody = document.getElementById('transactionsTableBody');

        if (searchInput && tableBody) {
            let searchTimeout;

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.toLowerCase().trim();

                searchTimeout = setTimeout(() => {
                    filterTransactions(query, tableBody);
                }, 300);
            });
        }
    }

    /**
     * Filter Transactions
     */
    function filterTransactions(query, tableBody) {
        const rows = tableBody.querySelectorAll('tr.transaction-row');
        let visibleCount = 0;

        rows.forEach(row => {
            if (!query) {
                row.style.display = '';
                visibleCount++;
                return;
            }

            const text = row.textContent.toLowerCase();
            if (text.includes(query)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Show/hide no results message
        let noResultsRow = tableBody.querySelector('.no-results-row');
        if (visibleCount === 0 && query) {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-results-row';
                noResultsRow.innerHTML = `
                    <td colspan="8" style="text-align: center; padding: 40px; color: #718096;">
                        <div style="font-size: 48px; margin-bottom: 16px;">🔍</div>
                        <div style="font-weight: 500; margin-bottom: 8px;">Tidak ada hasil ditemukan</div>
                        <div style="font-size: 13px;">Coba dengan kata kunci yang berbeda</div>
                    </td>
                `;
                tableBody.appendChild(noResultsRow);
            }
        } else if (noResultsRow) {
            noResultsRow.remove();
        }
    }

    /**
     * Initialize Table Sorting
     */
    function initializeTableSorting() {
        const sortableHeaders = document.querySelectorAll('th[data-sort]');
        
        sortableHeaders.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                const sortBy = this.getAttribute('data-sort');
                const table = this.closest('table');
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr:not(.no-results-row)'));

                // Determine sort direction
                const isAscending = !this.classList.contains('sort-desc');
                
                // Clear previous sort indicators
                sortableHeaders.forEach(h => {
                    h.classList.remove('sort-asc', 'sort-desc');
                });
                
                // Add sort indicator
                this.classList.add(isAscending ? 'sort-asc' : 'sort-desc');

                // Sort rows
                rows.sort((a, b) => {
                    let aVal, bVal;
                    
                    switch (sortBy) {
                        case 'date':
                            aVal = new Date(a.cells[0].textContent);
                            bVal = new Date(b.cells[0].textContent);
                            break;
                        case 'amount':
                            aVal = parseFloat(a.cells[3].textContent.replace(/[^\d]/g, ''));
                            bVal = parseFloat(b.cells[3].textContent.replace(/[^\d]/g, ''));
                            break;
                        case 'net':
                            aVal = parseFloat(a.cells[5].textContent.replace(/[^\d]/g, ''));
                            bVal = parseFloat(b.cells[5].textContent.replace(/[^\d]/g, ''));
                            break;
                        case 'transactions':
                            aVal = parseInt(a.cells[1].textContent);
                            bVal = parseInt(b.cells[1].textContent);
                            break;
                        case 'earnings':
                            aVal = parseFloat(a.cells[2].textContent.replace(/[^\d]/g, ''));
                            bVal = parseFloat(b.cells[2].textContent.replace(/[^\d]/g, ''));
                            break;
                        case 'average':
                            aVal = parseFloat(a.cells[3].textContent.replace(/[^\d]/g, ''));
                            bVal = parseFloat(b.cells[3].textContent.replace(/[^\d]/g, ''));
                            break;
                        default:
                            aVal = a.cells[0].textContent;
                            bVal = b.cells[0].textContent;
                    }
                    
                    if (isAscending) {
                        return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
                    } else {
                        return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
                    }
                });

                // Clear and re-append sorted rows
                tbody.innerHTML = '';
                rows.forEach(row => tbody.appendChild(row));
                
                // Add visual feedback
                animateTableSort();
            });
        });
    }

    /**
     * Animate table sort
     */
    function animateTableSort() {
        const rows = document.querySelectorAll('.transaction-row, .breakdown-table tr');
        rows.forEach((row, index) => {
            row.style.opacity = '0.5';
            row.style.transform = 'translateX(-10px)';
            
            setTimeout(() => {
                row.style.transition = 'all 0.3s ease';
                row.style.opacity = '1';
                row.style.transform = 'translateX(0)';
                
                setTimeout(() => {
                    row.style.transition = '';
                }, 300);
            }, index * 20);
        });
    }

    /**
     * Initialize Animations
     */
    function initializeAnimations() {
        // Animate stat cards on load
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Animate stat values
        setTimeout(() => {
            animateStatValues();
        }, 500);

        // Animate contribution bars
        setTimeout(() => {
            animateContributionBars();
        }, 800);

        // Animate insights
        setTimeout(() => {
            animateInsights();
        }, 1000);
    }

    /**
     * Animate stat values
     */
    function animateStatValues() {
        const statValues = document.querySelectorAll('.stat-value');
        
        statValues.forEach((element, index) => {
            setTimeout(() => {
                const text = element.textContent;
                const isNumeric = /[\d,.]/.test(text);
                
                if (isNumeric) {
                    const target = parseFloat(text.replace(/[^\d]/g, ''));
                    if (target > 0) {
                        animateNumberChange(element, 0, target, text);
                    }
                }
            }, index * 200);
        });
    }

    /**
     * Animate contribution bars
     */
    function animateContributionBars() {
        const bars = document.querySelectorAll('.contribution-fill');
        
        bars.forEach((bar, index) => {
            const width = bar.style.width;
            bar.style.width = '0%';
            
            setTimeout(() => {
                bar.style.transition = 'width 1s ease';
                bar.style.width = width;
            }, index * 100);
        });
    }

    /**
     * Animate insights
     */
    function animateInsights() {
        const insights = document.querySelectorAll('.insight-card');
        
        insights.forEach((insight, index) => {
            insight.style.opacity = '0';
            insight.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                insight.style.transition = 'all 0.6s ease';
                insight.style.opacity = '1';
                insight.style.transform = 'translateY(0)';
            }, index * 150);
        });
    }

    /**
     * Initialize Export Functions
     */
    function initializeExportFunctions() {
        const exportBreakdown = document.getElementById('exportBreakdown');
        const exportTransactions = document.getElementById('exportTransactions');

        if (exportBreakdown) {
            exportBreakdown.addEventListener('click', function() {
                exportBreakdownData();
            });
        }

        if (exportTransactions) {
            exportTransactions.addEventListener('click', function() {
                exportTransactionData();
            });
        }
    }

    /**
     * Export breakdown data
     */
    function exportBreakdownData() {
        showNotification('Mengekspor data breakdown kursus...', 'info');
        
        setTimeout(() => {
            const csvContent = generateBreakdownCSV();
            downloadCSV(csvContent, 'breakdown-kursus-' + new Date().toISOString().split('T')[0] + '.csv');
            showNotification('Data breakdown berhasil diekspor!', 'success');
        }, 1000);
    }

    /**
     * Export transaction data
     */
    function exportTransactionData() {
        showNotification('Mengekspor data transaksi detail...', 'info');
        
        setTimeout(() => {
            const csvContent = generateTransactionCSV();
            downloadCSV(csvContent, 'transaksi-detail-' + new Date().toISOString().split('T')[0] + '.csv');
            showNotification('Data transaksi berhasil diekspor!', 'success');
        }, 1000);
    }

    /**
     * Generate breakdown CSV
     */
    function generateBreakdownCSV() {
        const headers = ['Nama Kursus', 'Jumlah Transaksi', 'Total Pendapatan', 'Rata-rata per Transaksi', 'Kontribusi (%)'];
        
        const rows = [];
        const tableRows = document.querySelectorAll('.breakdown-table tbody tr');
        
        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 5) {
                const courseName = cells[0].querySelector('.course-name')?.textContent || '';
                const transactions = cells[1].textContent.trim();
                const earnings = cells[2].textContent.trim();
                const average = cells[3].textContent.trim();
                const contribution = cells[4].querySelector('.contribution-percentage')?.textContent || '';
                
                rows.push([courseName, transactions, earnings, average, contribution]);
            }
        });
        
        return [headers, ...rows]
            .map(row => row.map(field => `"${field}"`).join(','))
            .join('\n');
    }

    /**
     * Generate transaction CSV
     */
    function generateTransactionCSV() {
        const headers = ['Tanggal', 'Jenis Transaksi', 'Student/Mentee', 'Jumlah Kotor', 'Fee Platform', 'Jumlah Bersih', 'Status', 'Status Payout'];
        
        const rows = [];
        const tableRows = document.querySelectorAll('.transactions-table tbody tr.transaction-row');
        
        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 8) {
                const date = cells[0].textContent.trim();
                const type = cells[1].querySelector('.type-badge')?.textContent || '';
                const student = cells[2].querySelector('.student-name')?.textContent || cells[2].textContent.trim();
                const gross = cells[3].textContent.trim();
                const fee = cells[4].textContent.trim();
                const net = cells[5].textContent.trim();
                const status = cells[6].textContent.trim();
                const payoutStatus = cells[7].textContent.trim();
                
                rows.push([date, type, student, gross, fee, net, status, payoutStatus]);
            }
        });
        
        return [headers, ...rows]
            .map(row => row.map(field => `"${field}"`).join(','))
            .join('\n');
    }

    /**
     * Download CSV file
     */
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

    /**
     * Animate number changes
     */
    function animateNumberChange(element, start, end, originalText) {
        const duration = 1500;
        const steps = 60;
        const stepValue = (end - start) / steps;
        let currentStep = 0;
        let current = start;

        const animation = setInterval(() => {
            currentStep++;
            current += stepValue;
            
            if (currentStep >= steps) {
                clearInterval(animation);
                element.textContent = originalText;
            } else {
                if (originalText.includes('Rp')) {
                    element.textContent = formatCurrency(Math.floor(current));
                } else {
                    element.textContent = Math.floor(current);
                }
            }
        }, duration / steps);
    }

    /**
     * Format currency
     */
    function formatCurrency(amount) {
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
    }

    /**
     * Responsive handling
     */
    function handleResize() {
        if (sidebar) {
            if (window.innerWidth <= 768) {
                sidebar.classList.add('mobile');
            } else {
                sidebar.classList.remove('mobile', 'open');
            }
        }

        // Resize charts
        if (typeof Chart !== 'undefined') {
            if (window.dailyEarningsChart) {
                window.dailyEarningsChart.resize();
            }
            if (window.coursePerformanceChart) {
                window.coursePerformanceChart.resize();
            }
        }
    }

    handleResize();
    window.addEventListener('resize', handleResize);

    // Chart resize handler with debounce
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (typeof Chart !== 'undefined') {
                if (window.dailyEarningsChart) {
                    window.dailyEarningsChart.resize();
                }
                if (window.coursePerformanceChart) {
                    window.coursePerformanceChart.resize();
                }
            }
        }, 250);
    });

    // Enhanced hover effects
    document.querySelectorAll('.stat-card, .chart-section, .breakdown-section, .transactions-detail-section').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
            this.style.boxShadow = '0 8px 25px rgba(58, 89, 209, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
        });
    });

    // Add sort indicators to headers
    const style = document.createElement('style');
    style.textContent = `
        th[data-sort]:after {
            content: '';
            margin-left: 8px;
            opacity: 0.5;
        }
        th[data-sort].sort-asc:after {
            content: '↑';
            opacity: 1;
        }
        th[data-sort].sort-desc:after {
            content: '↓';
            opacity: 1;
        }
        th[data-sort]:hover {
            background: #f1f5f9 !important;
        }
    `;
    document.head.appendChild(style);

    // Auto-refresh data functionality
    let autoRefreshInterval;
    
    function startAutoRefresh() {
        autoRefreshInterval = setInterval(() => {
            console.log('Auto-refreshing earnings detail data...');
            // In real implementation, this would fetch updated data
            showNotification('Memperbarui data pendapatan...', 'info', 2000);
        }, 300000); // Refresh every 5 minutes
    }

    function stopAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
        }
    }

    // Start auto-refresh
    startAutoRefresh();

    // Stop auto-refresh when page becomes hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
        }
    });

    // Advanced filtering with URL state management
    function updateURLWithFilters(filters) {
        const url = new URL(window.location);
        Object.keys(filters).forEach(key => {
            if (filters[key]) {
                url.searchParams.set(key, filters[key]);
            } else {
                url.searchParams.delete(key);
            }
        });
        window.history.pushState({}, '', url);
    }

    // Load filters from URL on page load
    function loadFiltersFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        const courseSelect = document.getElementById('courseSelect');
        const periodSelect = document.getElementById('periodSelect');
        const startDate = document.getElementById('startDate');
        const endDate = document.getElementById('endDate');

        if (courseSelect && urlParams.has('course')) {
            courseSelect.value = urlParams.get('course');
        }
        if (periodSelect && urlParams.has('period')) {
            periodSelect.value = urlParams.get('period');
        }
        if (startDate && urlParams.has('start_date')) {
            startDate.value = urlParams.get('start_date');
        }
        if (endDate && urlParams.has('end_date')) {
            endDate.value = urlParams.get('end_date');
        }
    }

    // Load filters on page load
    loadFiltersFromURL();
});

// Global utility functions
window.earningsDetailUtils = {
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

    exportData: function(data, filename) {
        const csvContent = data.map(row => 
            row.map(field => `"${field}"`).join(',')
        ).join('\n');
        
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    },

    formatDate: function(date, format = 'dd/mm/yyyy') {
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        
        switch (format) {
            case 'dd/mm/yyyy':
                return `${day}/${month}/${year}`;
            case 'yyyy-mm-dd':
                return `${year}-${month}-${day}`;
            case 'readable':
                return d.toLocaleDateString('id-ID', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
            default:
                return d.toLocaleDateString('id-ID');
        }
    }
};

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

// Add notification animations
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