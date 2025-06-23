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

    // Initialize components
    initializeChart();
    initializeFilters();
    initializeSearch();
    initializeTableSorting();
    initializeModalHandlers();
    initializeWithdrawalActions();
    initializeAnimations();
    initializeExportFunction();

    /**
     * Initialize Withdrawal Chart
     */
    function initializeChart() {
        const chartCanvas = document.getElementById('withdrawalChart');
        if (chartCanvas && typeof Chart !== 'undefined') {
            const ctx = chartCanvas.getContext('2d');
            
            const withdrawalData = window.withdrawalHistoryData || {};
            const monthlyData = withdrawalData.monthlyData || [];
            
            const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            
            window.withdrawalChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Penarikan Dana',
                        data: monthlyData,
                        backgroundColor: '#3A59D1',
                        borderColor: '#3305BC',
                        borderWidth: 0,
                        borderRadius: 8,
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
                                    return 'Penarikan: ' + formatCurrency(context.parsed.y);
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
        }
    }

    /**
     * Initialize Filters
     */
    function initializeFilters() {
        const statusSelect = document.getElementById('statusSelect');
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
                    status: statusSelect ? statusSelect.value : 'all',
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

                // Show loading
                showLoadingState();

                // Reload page with filters
                setTimeout(() => {
                    window.location.href = window.location.pathname + '?' + queryParams.toString();
                }, 500);
            });
        }

        // Reset filters
        if (resetFilters) {
            resetFilters.addEventListener('click', function() {
                showLoadingState();
                setTimeout(() => {
                    window.location.href = window.location.pathname;
                }, 500);
            });
        }

        // Auto-apply visual feedback on select change
        [statusSelect, periodSelect].forEach(select => {
            if (select) {
                select.addEventListener('change', function() {
                    this.style.borderColor = '#3A59D1';
                    this.style.boxShadow = '0 0 0 3px rgba(58, 89, 209, 0.1)';
                    setTimeout(() => {
                        this.style.borderColor = '';
                        this.style.boxShadow = '';
                    }, 300);
                });
            }
        });
    }

    /**
     * Initialize Search
     */
    function initializeSearch() {
        const searchInput = document.getElementById('historySearch');
        const tableBody = document.getElementById('historyTableBody');

        if (searchInput && tableBody) {
            let searchTimeout;

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.toLowerCase().trim();

                searchTimeout = setTimeout(() => {
                    filterWithdrawals(query, tableBody);
                }, 300);
            });
        }
    }

    /**
     * Filter withdrawals in table
     */
    function filterWithdrawals(query, tableBody) {
        const rows = tableBody.querySelectorAll('tr.withdrawal-row');
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
                    <td colspan="7" style="text-align: center; padding: 40px; color: #718096;">
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

        // Update pagination info
        updatePaginationInfo(visibleCount);
    }

    /**
     * Initialize Table Sorting
     */
    function initializeTableSorting() {
        const sortableHeaders = document.querySelectorAll('.history-table th[data-sort]');
        
        sortableHeaders.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                const sortBy = this.getAttribute('data-sort');
                const table = this.closest('table');
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr.withdrawal-row'));

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
                            aVal = new Date(a.cells[0].querySelector('.date-main').textContent + ' ' + a.cells[0].querySelector('.time-small').textContent);
                            bVal = new Date(b.cells[0].querySelector('.date-main').textContent + ' ' + b.cells[0].querySelector('.time-small').textContent);
                            break;
                        case 'amount':
                            aVal = parseFloat(a.cells[3].querySelector('.amount-value').textContent.replace(/[^\d]/g, ''));
                            bVal = parseFloat(b.cells[3].querySelector('.amount-value').textContent.replace(/[^\d]/g, ''));
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
                showNotification('Tabel diurutkan berdasarkan ' + sortBy, 'info', 2000);
            });
        });
    }

    /**
     * Initialize Modal Handlers
     */
    function initializeModalHandlers() {
        const modal = document.getElementById('withdrawalModal');
        const modalCloses = document.querySelectorAll('.modal-close');

        // Close modal handlers
        modalCloses.forEach(closeBtn => {
            closeBtn.addEventListener('click', function() {
                closeModal();
            });
        });

        // Close modal on backdrop click
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && modal.style.display !== 'none') {
                closeModal();
            }
        });
    }

    /**
     * Initialize Withdrawal Actions
     */
    function initializeWithdrawalActions() {
        // New withdrawal button
        const newWithdrawalBtn = document.getElementById('newWithdrawalBtn');
        if (newWithdrawalBtn) {
            newWithdrawalBtn.addEventListener('click', function() {
                initiateNewWithdrawal();
            });
        }

        // Detail buttons
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-detail')) {
                const withdrawalId = e.target.getAttribute('data-id');
                showWithdrawalDetail(withdrawalId);
            }
            
            if (e.target.classList.contains('btn-cancel')) {
                const withdrawalId = e.target.getAttribute('data-id');
                cancelWithdrawal(withdrawalId);
            }
            
            if (e.target.classList.contains('btn-receipt')) {
                const withdrawalId = e.target.getAttribute('data-id');
                downloadReceipt(withdrawalId);
            }
        });

        // Method card selection
        const methodCards = document.querySelectorAll('.method-card');
        methodCards.forEach(card => {
            card.addEventListener('click', function() {
                methodCards.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                
                const method = this.querySelector('h4').textContent;
                showNotification(`Metode ${method} dipilih sebagai favorit`, 'success', 3000);
            });
        });
    }

    /**
     * Initialize Animations
     */
    function initializeAnimations() {
        // Animate stat cards
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

        // Animate method cards
        setTimeout(() => {
            animateMethodCards();
        }, 800);

        // Animate table rows
        setTimeout(() => {
            animateTableRows();
        }, 1000);
    }

    /**
     * Initialize Export Function
     */
    function initializeExportFunction() {
        const exportBtn = document.getElementById('exportHistory');
        if (exportBtn) {
            exportBtn.addEventListener('click', function() {
                exportWithdrawalHistory();
            });
        }
    }

    /**
     * Show withdrawal detail modal
     */
    function showWithdrawalDetail(withdrawalId) {
        const withdrawal = findWithdrawalById(withdrawalId);
        if (!withdrawal) {
            showNotification('Data penarikan tidak ditemukan', 'error');
            return;
        }

        const modalBody = document.querySelector('#withdrawalModal .modal-body');
        modalBody.innerHTML = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="font-size: 12px; color: #718096; text-transform: uppercase; font-weight: 600;">ID Referensi</label>
                    <div style="font-family: monospace; background: #f8fafc; padding: 8px; border-radius: 4px; margin-top: 4px;">${withdrawal.reference_id}</div>
                </div>
                <div>
                    <label style="font-size: 12px; color: #718096; text-transform: uppercase; font-weight: 600;">Status</label>
                    <div style="margin-top: 4px;">
                        <span class="status-badge ${getStatusBadgeClass(withdrawal.status)}">${withdrawal.status}</span>
                        <span class="payout-badge ${getPayoutBadgeClass(withdrawal.payout_status)}" style="margin-left: 8px;">${withdrawal.payout_status}</span>
                    </div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="font-size: 12px; color: #718096; text-transform: uppercase; font-weight: 600;">Jumlah</label>
                    <div style="font-size: 18px; font-weight: 600; color: #E53E3E; margin-top: 4px;">${formatCurrency(Math.abs(withdrawal.net_amount))}</div>
                </div>
                <div>
                    <label style="font-size: 12px; color: #718096; text-transform: uppercase; font-weight: 600;">Metode</label>
                    <div style="margin-top: 4px; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 20px;">${getMethodIcon(withdrawal.withdrawal_method)}</span>
                        <span>${withdrawal.method_name}</span>
                    </div>
                </div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="font-size: 12px; color: #718096; text-transform: uppercase; font-weight: 600;">Akun Tujuan</label>
                <div style="background: #f8fafc; padding: 12px; border-radius: 8px; margin-top: 4px; font-family: monospace;">${withdrawal.withdrawal_account}</div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="font-size: 12px; color: #718096; text-transform: uppercase; font-weight: 600;">Tanggal Permintaan</label>
                    <div style="margin-top: 4px;">${formatDateTime(withdrawal.created_at)}</div>
                </div>
                <div>
                    <label style="font-size: 12px; color: #718096; text-transform: uppercase; font-weight: 600;">Tanggal Selesai</label>
                    <div style="margin-top: 4px;">${withdrawal.payout_date ? formatDateTime(withdrawal.payout_date) : 'Belum selesai'}</div>
                </div>
            </div>
            
            ${withdrawal.description ? `
                <div style="margin-bottom: 20px;">
                    <label style="font-size: 12px; color: #718096; text-transform: uppercase; font-weight: 600;">Deskripsi</label>
                    <div style="margin-top: 4px; color: #4A5568;">${withdrawal.description}</div>
                </div>
            ` : ''}
            
            <div style="border-top: 1px solid #E2E8F0; padding-top: 16px; font-size: 12px; color: #718096;">
                <strong>Catatan:</strong> Penarikan dana akan diproses dalam 1-2 hari kerja untuk transfer bank dan instan untuk e-wallet.
            </div>
        `;

        showModal();
    }

    /**
     * Cancel withdrawal
     */
    function cancelWithdrawal(withdrawalId) {
        if (!confirm('Apakah Anda yakin ingin membatalkan penarikan ini?')) {
            return;
        }

        // Show loading on button
        const cancelBtn = document.querySelector(`[data-id="${withdrawalId}"].btn-cancel`);
        if (cancelBtn) {
            cancelBtn.disabled = true;
            cancelBtn.innerHTML = '⏳';
        }

        // Simulate API call
        setTimeout(() => {
            showNotification('Penarikan berhasil dibatalkan', 'success');
            
            // Update row status
            const row = cancelBtn?.closest('tr');
            if (row) {
                const statusCell = row.querySelector('.status-group');
                if (statusCell) {
                    statusCell.innerHTML = `
                        <span class="status-badge status-cancelled">Cancelled</span>
                        <span class="payout-badge payout-hold">Hold</span>
                    `;
                }
                
                // Remove cancel button
                cancelBtn.remove();
            }
            
            // Update stats
            updateQuickStats();
            
        }, 1500);
    }

    /**
     * Download receipt
     */
    function downloadReceipt(withdrawalId) {
        const withdrawal = findWithdrawalById(withdrawalId);
        if (!withdrawal) {
            showNotification('Data penarikan tidak ditemukan', 'error');
            return;
        }

        showNotification('Mengunduh bukti penarikan...', 'info');
        
        // Simulate download
        setTimeout(() => {
            generateReceiptPDF(withdrawal);
            showNotification('Bukti penarikan berhasil diunduh', 'success');
        }, 1000);
    }

    /**
     * Initiate new withdrawal
     */
    function initiateNewWithdrawal() {
        const availableBalance = window.withdrawalHistoryData?.availableBalance || 0;
        const minimumWithdrawal = 100000;

        if (availableBalance < minimumWithdrawal) {
            showNotification(`Saldo minimum untuk penarikan adalah ${formatCurrency(minimumWithdrawal)}`, 'warning');
            return;
        }

        // Redirect to withdrawal form or show modal
        setTimeout(() => {
            // In real implementation, this would redirect to withdrawal form
            window.location.href = '/MindCraft-Project/views/mentor/tarik-dana.php';
        }, 1000);
    }

    /**
     * Export withdrawal history
     */
    function exportWithdrawalHistory() {
        showNotification('Mengekspor riwayat penarikan...', 'info');
        
        setTimeout(() => {
            const csvContent = generateWithdrawalCSV();
            downloadCSV(csvContent, 'riwayat-penarikan-' + new Date().toISOString().split('T')[0] + '.csv');
            showNotification('Riwayat penarikan berhasil diekspor!', 'success');
        }, 1000);
    }

    /**
     * Generate withdrawal CSV
     */
    function generateWithdrawalCSV() {
        const headers = ['Tanggal', 'Metode', 'Akun Tujuan', 'Jumlah', 'Status', 'Status Payout', 'Referensi'];
        
        const rows = [];
        const tableRows = document.querySelectorAll('.history-table tbody tr.withdrawal-row');
        
        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 6) {
                const date = cells[0].querySelector('.date-main')?.textContent + ' ' + cells[0].querySelector('.time-small')?.textContent;
                const method = cells[1].querySelector('.method-name')?.textContent || '';
                const account = cells[2].querySelector('.account-number')?.textContent || '';
                const amount = cells[3].querySelector('.amount-value')?.textContent || '';
                const status = cells[4].querySelector('.status-badge')?.textContent || '';
                const payoutStatus = cells[4].querySelector('.payout-badge')?.textContent || '';
                const reference = cells[5].querySelector('.reference-id')?.textContent || '';
                
                rows.push([date, method, account, amount, status, payoutStatus, reference]);
            }
        });
        
        return [headers, ...rows]
            .map(row => row.map(field => `"${field}"`).join(','))
            .join('\n');
    }

    /**
     * Utility Functions
     */
    function findWithdrawalById(id) {
        const withdrawals = window.withdrawalHistoryData?.withdrawals || [];
        return withdrawals.find(w => w.id == id);
    }

    function getStatusBadgeClass(status) {
        const classes = {
            'completed': 'status-completed',
            'pending': 'status-pending',
            'cancelled': 'status-cancelled'
        };
        return classes[status] || 'status-pending';
    }

    function getPayoutBadgeClass(status) {
        const classes = {
            'paid': 'payout-paid',
            'pending': 'payout-pending',
            'hold': 'payout-hold'
        };
        return classes[status] || 'payout-pending';
    }

    function getMethodIcon(method) {
        const icons = {
            'bank_transfer': '🏦',
            'gopay': '💚',
            'ovo': '💜',
            'dana': '💙',
            'shopeepay': '🧡'
        };
        return icons[method] || '🏦';
    }

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

    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function showModal() {
        const modal = document.getElementById('withdrawalModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Animate modal
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.style.transition = 'opacity 0.3s ease';
                modal.style.opacity = '1';
            }, 10);
        }
    }

    function closeModal() {
        const modal = document.getElementById('withdrawalModal');
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                modal.style.transition = '';
            }, 300);
        }
    }

    function showLoadingState() {
        const cards = document.querySelectorAll('.stat-card, .chart-section, .history-section');
        cards.forEach(card => {
            card.style.opacity = '0.6';
            card.style.pointerEvents = 'none';
        });
    }

    function hideLoadingState() {
        const cards = document.querySelectorAll('.stat-card, .chart-section, .history-section');
        cards.forEach(card => {
            card.style.opacity = '1';
            card.style.pointerEvents = 'auto';
        });
    }

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

    function animateMethodCards() {
        const methodCards = document.querySelectorAll('.method-card');
        
        methodCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 150);
        });
    }

    function animateTableRows() {
        const rows = document.querySelectorAll('.withdrawal-row');
        
        rows.forEach((row, index) => {
            if (index < 5) { // Only animate first 5 rows
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    row.style.transition = 'all 0.6s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateX(0)';
                }, index * 100);
            }
        });
    }

    function animateTableSort() {
        const rows = document.querySelectorAll('.withdrawal-row');
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

    function updateQuickStats() {
        // Update stats based on current table data
        const rows = document.querySelectorAll('.withdrawal-row:not([style*="display: none"])');
        const pendingCount = document.querySelectorAll('.payout-pending').length;
        const completedCount = document.querySelectorAll('.payout-paid').length;
        
        // Update pending count
        const pendingCard = document.querySelector('.stat-card.pending .stat-value');
        if (pendingCard) {
            pendingCard.textContent = pendingCount;
        }
        
        // Update completed count
        const completedCard = document.querySelector('.stat-card.completed .stat-value');
        if (completedCard) {
            completedCard.textContent = completedCount;
        }
    }

    function updatePaginationInfo(visibleCount) {
        const paginationInfo = document.querySelector('.pagination-info');
        if (paginationInfo) {
            const totalCount = window.withdrawalHistoryData?.summary?.total_withdrawals || 0;
            paginationInfo.textContent = `Menampilkan ${visibleCount} dari ${totalCount} transaksi`;
        }
    }

    function generateReceiptPDF(withdrawal) {
        // Simulate PDF generation
        const receiptData = `
            BUKTI PENARIKAN DANA
            MindCraft Platform
            
            ID Referensi: ${withdrawal.reference_id}
            Tanggal: ${formatDateTime(withdrawal.created_at)}
            Jumlah: ${formatCurrency(Math.abs(withdrawal.net_amount))}
            Metode: ${withdrawal.method_name}
            Akun: ${withdrawal.withdrawal_account}
            Status: ${withdrawal.status}
            
            Terima kasih atas kepercayaan Anda.
        `;
        
        const blob = new Blob([receiptData], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `bukti-penarikan-${withdrawal.reference_id}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
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

    // Responsive handling
    function handleResize() {
        if (sidebar) {
            if (window.innerWidth <= 768) {
                sidebar.classList.add('mobile');
            } else {
                sidebar.classList.remove('mobile', 'open');
            }
        }

        // Resize chart
        if (typeof Chart !== 'undefined' && window.withdrawalChart) {
            window.withdrawalChart.resize();
        }
    }

    handleResize();
    window.addEventListener('resize', handleResize);

    // Chart resize handler with debounce
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (typeof Chart !== 'undefined' && window.withdrawalChart) {
                window.withdrawalChart.resize();
            }
        }, 250);
    });

    // Enhanced hover effects
    document.querySelectorAll('.stat-card, .chart-section, .methods-overview, .history-section').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 8px 20px rgba(58, 89, 209, 0.15)';
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

    // Auto-refresh functionality
    let autoRefreshInterval;
    
    function startAutoRefresh() {
        autoRefreshInterval = setInterval(() => {
            console.log('Auto-refreshing withdrawal data...');
            // In real implementation, this would fetch updated data
            showNotification('Memperbarui data penarikan...', 'info', 2000);
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

    // Load filters from URL on page load
    function loadFiltersFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        const statusSelect = document.getElementById('statusSelect');
        const periodSelect = document.getElementById('periodSelect');
        const startDate = document.getElementById('startDate');
        const endDate = document.getElementById('endDate');

        if (statusSelect && urlParams.has('status')) {
            statusSelect.value = urlParams.get('status');
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
window.withdrawalUtils = {
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
    },

    validateWithdrawalAmount: function(amount, balance) {
        const minAmount = 100000;
        const maxAmount = 10000000;
        
        if (amount < minAmount) {
            return { valid: false, message: `Minimum penarikan Rp ${minAmount.toLocaleString('id-ID')}` };
        }
        
        if (amount > maxAmount) {
            return { valid: false, message: `Maksimum penarikan Rp ${maxAmount.toLocaleString('id-ID')}` };
        }
        
        if (amount > balance) {
            return { valid: false, message: 'Jumlah melebihi saldo tersedia' };
        }
        
        return { valid: true, message: 'Valid' };
    },

    getWithdrawalEstimate: function(method) {
        const estimates = {
            'bank_transfer': '1-2 hari kerja',
            'gopay': 'Instan',
            'ovo': 'Instan',
            'dana': 'Instan',
            'shopeepay': 'Instan'
        };
        
        return estimates[method] || '1-2 hari kerja';
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