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
    initializeFormSteps();
    initializeAmountInput();
    initializePaymentMethods();
    initializeConfirmation();
    initializeModals();
    initializeFormValidation();
    initializeAnimations();

    /**
     * Initialize Form Steps Navigation
     */
    function initializeFormSteps() {
        const steps = document.querySelectorAll('.form-step');
        const nextButtons = document.querySelectorAll('.next-step');
        const prevButtons = document.querySelectorAll('.prev-step');
        
        let currentStep = 1;
        const totalSteps = steps.length;

        // Next step handlers
        nextButtons.forEach(button => {
            button.addEventListener('click', function() {
                const nextStepId = this.getAttribute('data-next');
                if (validateCurrentStep(currentStep)) {
                    goToStep(nextStepId);
                }
            });
        });

        // Previous step handlers
        prevButtons.forEach(button => {
            button.addEventListener('click', function() {
                const prevStepId = this.getAttribute('data-prev');
                goToStep(prevStepId);
            });
        });

        function goToStep(stepId) {
            // Hide all steps
            steps.forEach(step => {
                step.classList.remove('active');
            });

            // Show target step
            const targetStep = document.getElementById(stepId);
            if (targetStep) {
                targetStep.classList.add('active');
                currentStep = parseInt(stepId.replace('step', ''));
                
                // Update progress if needed
                updateStepProgress();
                
                // Scroll to top
                targetStep.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function validateCurrentStep(step) {
            switch (step) {
                case 1:
                    return validateAmount();
                case 2:
                    return validatePaymentMethod();
                case 3:
                    return validateConfirmation();
                default:
                    return true;
            }
        }

        function updateStepProgress() {
            // Update form progress indicator if exists
            const progressBars = document.querySelectorAll('.step-progress');
            progressBars.forEach(bar => {
                const progress = (currentStep / totalSteps) * 100;
                bar.style.width = progress + '%';
            });
        }
    }

    /**
     * Initialize Amount Input
     */
    function initializeAmountInput() {
        const amountField = document.getElementById('amount');
        const quickAmountButtons = document.querySelectorAll('.quick-amount-btn');
        
        if (!amountField) return;

        // Format number input
        amountField.addEventListener('input', function() {
            let value = this.value.replace(/[^\d]/g, '');
            if (value) {
                value = parseInt(value);
                this.value = formatNumber(value);
                updateAmountValidation(value);
            } else {
                this.value = '';
                clearAmountValidation();
            }
        });

        // Quick amount buttons
        quickAmountButtons.forEach(button => {
            button.addEventListener('click', function() {
                const amount = parseInt(this.getAttribute('data-amount'));
                
                // Update active button
                quickAmountButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Set amount
                amountField.value = formatNumber(amount);
                updateAmountValidation(amount);
                
                // Animate input
                amountField.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    amountField.style.transform = 'scale(1)';
                }, 200);
            });
        });

        // Validate on blur
        amountField.addEventListener('blur', function() {
            const value = parseFloat(this.value.replace(/[^\d]/g, ''));
            if (value) {
                updateAmountValidation(value);
            }
        });
    }

    /**
     * Initialize Payment Methods
     */
    function initializePaymentMethods() {
        const methodOptions = document.querySelectorAll('input[name="withdrawal_method"]');
        const bankAccountsSection = document.getElementById('bank_accounts');
        const ewalletAccountsSection = document.getElementById('ewallet_accounts');
        const accountOptions = document.querySelectorAll('input[name="account_info"]');
        
        // Method selection handlers
        methodOptions.forEach(option => {
            option.addEventListener('change', function() {
                if (this.checked) {
                    handleMethodChange(this.value);
                    updateMethodValidation();
                }
            });
        });

        // Account selection handlers
        accountOptions.forEach(option => {
            option.addEventListener('change', function() {
                if (this.checked) {
                    updateAccountValidation();
                }
            });
        });

        function handleMethodChange(method) {
            // Hide all account sections
            if (bankAccountsSection) bankAccountsSection.style.display = 'none';
            if (ewalletAccountsSection) ewalletAccountsSection.style.display = 'none';
            
            // Show relevant section
            if (method === 'bank_transfer' && bankAccountsSection) {
                bankAccountsSection.style.display = 'block';
                animateSlideDown(bankAccountsSection);
            } else if (['gopay', 'dana', 'ovo'].includes(method) && ewalletAccountsSection) {
                ewalletAccountsSection.style.display = 'block';
                animateSlideDown(ewalletAccountsSection);
                
                // Filter ewallet options based on selected method
                filterEwalletOptions(method);
            }
            
            // Clear account selection
            accountOptions.forEach(option => {
                option.checked = false;
            });
        }

        function filterEwalletOptions(selectedMethod) {
            const ewalletOptions = ewalletAccountsSection.querySelectorAll('input[name="account_info"]');
            
            ewalletOptions.forEach(option => {
                const optionType = option.getAttribute('data-type');
                const optionContainer = option.closest('.account-option');
                
                if (optionType === selectedMethod) {
                    optionContainer.style.display = 'block';
                } else {
                    optionContainer.style.display = 'none';
                }
            });
        }
    }

    /**
     * Initialize Confirmation
     */
    function initializeConfirmation() {
        const confirmAmount = document.getElementById('confirmAmount');
        const confirmMethod = document.getElementById('confirmMethod');
        const confirmAccount = document.getElementById('confirmAccount');
        const confirmTime = document.getElementById('confirmTime');
        const confirmTotal = document.getElementById('confirmTotal');
        const agreeTerms = document.getElementById('agreeTerms');
        const confirmData = document.getElementById('confirmData');
        const submitBtn = document.querySelector('.submit-btn');

        // Update confirmation when step 3 is reached
        const step3 = document.getElementById('step3');
        if (step3) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        if (step3.classList.contains('active')) {
                            updateConfirmationSummary();
                        }
                    }
                });
            });
            
            observer.observe(step3, { attributes: true });
        }

        // Terms validation
        [agreeTerms, confirmData].forEach(checkbox => {
            if (checkbox) {
                checkbox.addEventListener('change', function() {
                    updateSubmitButton();
                });
            }
        });

        function updateConfirmationSummary() {
            const amount = getSelectedAmount();
            const method = getSelectedMethod();
            const account = getSelectedAccount();
            
            if (confirmAmount) confirmAmount.textContent = formatCurrency(amount);
            if (confirmMethod) confirmMethod.textContent = getMethodDisplayName(method);
            if (confirmAccount) confirmAccount.textContent = account || '-';
            if (confirmTime) confirmTime.textContent = getProcessingTime(method);
            if (confirmTotal) confirmTotal.textContent = formatCurrency(amount);
        }

        function updateSubmitButton() {
            const termsChecked = agreeTerms ? agreeTerms.checked : false;
            const dataChecked = confirmData ? confirmData.checked : false;
            
            if (submitBtn) {
                submitBtn.disabled = !(termsChecked && dataChecked);
            }
        }
    }

    /**
     * Initialize Modals
     */
    function initializeModals() {
        const bankAccountModal = document.getElementById('bankAccountModal');
        const ewalletModal = document.getElementById('ewalletModal');
        const addBankAccountBtn = document.getElementById('addBankAccount');
        const addEwalletAccountBtn = document.getElementById('addEwalletAccount');
        const modalCloses = document.querySelectorAll('.modal-close');
        const addBankForm = document.getElementById('addBankForm');
        const addEwalletForm = document.getElementById('addEwalletForm');

        // Open modals
        if (addBankAccountBtn) {
            addBankAccountBtn.addEventListener('click', function() {
                showModal(bankAccountModal);
            });
        }

        if (addEwalletAccountBtn) {
            addEwalletAccountBtn.addEventListener('click', function() {
                showModal(ewalletModal);
            });
        }

        // Close modals
        modalCloses.forEach(closeBtn => {
            closeBtn.addEventListener('click', function() {
                closeModal();
            });
        });

        // Close modal on backdrop click
        [bankAccountModal, ewalletModal].forEach(modal => {
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeModal();
                    }
                });
            }
        });

        // Form submissions
        if (addBankForm) {
            addBankForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleAddBankAccount();
            });
        }

        if (addEwalletForm) {
            addEwalletForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleAddEwalletAccount();
            });
        }

        function showModal(modal) {
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
            [bankAccountModal, ewalletModal].forEach(modal => {
                if (modal && modal.style.display === 'flex') {
                    modal.style.opacity = '0';
                    setTimeout(() => {
                        modal.style.display = 'none';
                        document.body.style.overflow = '';
                        modal.style.transition = '';
                    }, 300);
                }
            });
        }

        function handleAddBankAccount() {
            const bankName = document.getElementById('bankName').value;
            const accountNumber = document.getElementById('accountNumber').value;
            const accountName = document.getElementById('accountName').value;

            if (!bankName || !accountNumber || !accountName) {
                showNotification('Lengkapi semua field', 'error');
                return;
            }

            // Simulate adding account
            showNotification('Rekening bank berhasil ditambahkan', 'success');
            addBankForm.reset();
            closeModal();
            
            // Add to account list (simplified)
            addNewBankAccountToList(bankName, accountNumber, accountName);
        }

        function handleAddEwalletAccount() {
            const ewalletType = document.getElementById('ewalletType').value;
            const phoneNumber = document.getElementById('phoneNumber').value;
            const ewalletName = document.getElementById('ewalletName').value;

            if (!ewalletType || !phoneNumber || !ewalletName) {
                showNotification('Lengkapi semua field', 'error');
                return;
            }

            // Validate phone number format
            if (!isValidPhoneNumber(phoneNumber)) {
                showNotification('Format nomor handphone tidak valid', 'error');
                return;
            }

            // Simulate adding account
            showNotification('Akun e-wallet berhasil ditambahkan', 'success');
            addEwalletForm.reset();
            closeModal();
            
            // Add to account list (simplified)
            addNewEwalletAccountToList(ewalletType, phoneNumber, ewalletName);
        }
    }

    /**
     * Initialize Form Validation
     */
    function initializeFormValidation() {
        const form = document.getElementById('withdrawalForm');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    showNotification('Lengkapi semua field yang diperlukan', 'error');
                    return;
                }
                
                // Show loading
                showLoadingOverlay();
            });
        }
    }

    /**
     * Initialize Animations
     */
    function initializeAnimations() {
        // Animate balance cards
        const balanceCards = document.querySelectorAll('.balance-card');
        balanceCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 150);
        });

        // Animate form sections
        setTimeout(() => {
            const formSection = document.querySelector('.withdrawal-form-section');
            if (formSection) {
                formSection.style.opacity = '0';
                formSection.style.transform = 'translateY(30px)';
                formSection.style.transition = 'all 0.8s ease';
                
                setTimeout(() => {
                    formSection.style.opacity = '1';
                    formSection.style.transform = 'translateY(0)';
                }, 100);
            }
        }, 500);

        // Animate info cards
        setTimeout(() => {
            const infoCards = document.querySelectorAll('.info-card');
            infoCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        }, 1000);
    }

    /**
     * Validation Functions
     */
    function validateAmount() {
        const amountField = document.getElementById('amount');
        if (!amountField) return false;
        
        const amount = parseFloat(amountField.value.replace(/[^\d]/g, ''));
        const availableBalance = window.withdrawalData?.availableBalance || 0;
        const minimumPayout = window.withdrawalData?.minimumPayout || 100000;
        const maximumPayout = window.withdrawalData?.maximumPayout || 10000000;
        
        if (!amount || isNaN(amount)) {
            showFieldError(amountField, 'Masukkan jumlah penarikan');
            return false;
        }
        
        if (amount < minimumPayout) {
            showFieldError(amountField, `Minimum penarikan ${formatCurrency(minimumPayout)}`);
            return false;
        }
        
        if (amount > maximumPayout) {
            showFieldError(amountField, `Maksimum penarikan ${formatCurrency(maximumPayout)}`);
            return false;
        }
        
        if (amount > availableBalance) {
            showFieldError(amountField, 'Jumlah melebihi saldo tersedia');
            return false;
        }
        
        clearFieldError(amountField);
        return true;
    }

    function validatePaymentMethod() {
        const selectedMethod = document.querySelector('input[name="withdrawal_method"]:checked');
        const selectedAccount = document.querySelector('input[name="account_info"]:checked');
        
        if (!selectedMethod) {
            showNotification('Pilih metode penarikan', 'error');
            return false;
        }
        
        if (!selectedAccount) {
            showNotification('Pilih akun tujuan', 'error');
            return false;
        }
        
        return true;
    }

    function validateConfirmation() {
        const agreeTerms = document.getElementById('agreeTerms');
        const confirmData = document.getElementById('confirmData');
        
        if (!agreeTerms || !agreeTerms.checked) {
            showNotification('Setujui syarat dan ketentuan', 'error');
            return false;
        }
        
        if (!confirmData || !confirmData.checked) {
            showNotification('Konfirmasi keakuratan data', 'error');
            return false;
        }
        
        return true;
    }

    function validateForm() {
        return validateAmount() && validatePaymentMethod() && validateConfirmation();
    }

    /**
     * Helper Functions
     */
    function getSelectedAmount() {
        const amountField = document.getElementById('amount');
        if (!amountField) return 0;
        
        const amount = parseFloat(amountField.value.replace(/[^\d]/g, ''));
        return isNaN(amount) ? 0 : amount;
    }

    function getSelectedMethod() {
        const selectedMethod = document.querySelector('input[name="withdrawal_method"]:checked');
        return selectedMethod ? selectedMethod.value : '';
    }

    function getSelectedAccount() {
        const selectedAccount = document.querySelector('input[name="account_info"]:checked');
        return selectedAccount ? selectedAccount.value : '';
    }

    function getMethodDisplayName(method) {
        const names = {
            bank_transfer: '🏦 Transfer Bank',
            gopay: '💚 GoPay',
            dana: '💙 DANA',
            ovo: '💜 OVO',
            shopeepay: '🧡 ShopeePay'
        };
        return names[method] || method;
    }

    function getProcessingTime(method) {
        const processingTimes = window.withdrawalData?.processingTimes || {
            bank_transfer: "1-2 hari kerja",
            gopay: "Instan",
            dana: "Instan",
            ovo: "Instan",
            shopeepay: "Instan"
        };
        return processingTimes[method] || "1-2 hari kerja";
    }

    function formatNumber(num) {
        return num.toLocaleString('id-ID');
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

    function isValidPhoneNumber(phone) {
        // Indonesian phone number validation
        const phoneRegex = /^(\+62|62|0)[0-9]{9,13}$/;
        return phoneRegex.test(phone.replace(/[\s\-]/g, ''));
    }

    function updateAmountValidation(amount) {
        const amountField = document.getElementById('amount');
        const availableBalance = window.withdrawalData?.availableBalance || 0;
        const minimumPayout = window.withdrawalData?.minimumPayout || 100000;
        const maximumPayout = window.withdrawalData?.maximumPayout || 10000000;
        
        // Clear previous validation
        clearFieldError(amountField);
        
        // Validate and show feedback
        if (amount < minimumPayout) {
            showFieldError(amountField, `Minimum ${formatCurrency(minimumPayout)}`);
        } else if (amount > maximumPayout) {
            showFieldError(amountField, `Maksimum ${formatCurrency(maximumPayout)}`);
        } else if (amount > availableBalance) {
            showFieldError(amountField, 'Melebihi saldo tersedia');
        } else {
            showFieldSuccess(amountField);
        }
    }

    function clearAmountValidation() {
        const amountField = document.getElementById('amount');
        clearFieldError(amountField);
    }

    function updateMethodValidation() {
        // Add visual feedback for method selection
        const methodCards = document.querySelectorAll('.method-card');
        methodCards.forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                card.style.borderColor = 'var(--primary-blue)';
                card.style.background = 'rgba(58, 89, 209, 0.05)';
            }
        });
    }

    function updateAccountValidation() {
        // Add visual feedback for account selection
        const accountCards = document.querySelectorAll('.account-card');
        accountCards.forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                card.style.borderColor = 'var(--primary-blue)';
                card.style.background = 'rgba(58, 89, 209, 0.05)';
            }
        });
    }

    function showFieldError(field, message) {
        clearFieldError(field);
        
        field.style.borderColor = 'var(--danger-red)';
        field.style.boxShadow = '0 0 0 3px rgba(229, 62, 62, 0.1)';
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.style.cssText = `
            color: var(--danger-red);
            font-size: 12px;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        `;
        errorDiv.innerHTML = `⚠️ ${message}`;
        
        field.parentNode.appendChild(errorDiv);
    }

    function showFieldSuccess(field) {
        clearFieldError(field);
        
        field.style.borderColor = 'var(--success-green)';
        field.style.boxShadow = '0 0 0 3px rgba(43, 153, 43, 0.1)';
        
        const successDiv = document.createElement('div');
        successDiv.className = 'field-success';
        successDiv.style.cssText = `
            color: var(--success-green);
            font-size: 12px;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        `;
        successDiv.innerHTML = `✅ Jumlah valid`;
        
        field.parentNode.appendChild(successDiv);
    }

    function clearFieldError(field) {
        field.style.borderColor = '';
        field.style.boxShadow = '';
        
        const errorDiv = field.parentNode.querySelector('.field-error');
        const successDiv = field.parentNode.querySelector('.field-success');
        
        if (errorDiv) errorDiv.remove();
        if (successDiv) successDiv.remove();
    }

    function animateSlideDown(element) {
        element.style.height = '0';
        element.style.overflow = 'hidden';
        element.style.transition = 'height 0.3s ease';
        
        setTimeout(() => {
            element.style.height = 'auto';
            const height = element.scrollHeight + 'px';
            element.style.height = '0';
            
            setTimeout(() => {
                element.style.height = height;
                
                setTimeout(() => {
                    element.style.height = 'auto';
                    element.style.overflow = 'visible';
                }, 300);
            }, 10);
        }, 10);
    }

    function addNewBankAccountToList(bankName, accountNumber, accountName) {
        const bankAccountsSection = document.getElementById('bank_accounts');
        if (!bankAccountsSection) return;
        
        const accountOptions = bankAccountsSection.querySelector('.account-options');
        if (!accountOptions) return;
        
        const newId = Date.now();
        const newAccountHtml = `
            <div class="account-option">
                <input type="radio" name="account_info" 
                       value="${bankName} - ${accountNumber}" 
                       id="bank_${newId}">
                <label for="bank_${newId}" class="account-card">
                    <div class="account-info">
                        <div class="account-bank">${bankName}</div>
                        <div class="account-number">${accountNumber}</div>
                        <div class="account-name">${accountName}</div>
                    </div>
                    <div class="unverified-badge">⏳ Belum Verifikasi</div>
                </label>
            </div>
        `;
        
        accountOptions.insertAdjacentHTML('beforeend', newAccountHtml);
        
        // Animate new item
        const newItem = accountOptions.lastElementChild;
        newItem.style.opacity = '0';
        newItem.style.transform = 'translateY(-10px)';
        
        setTimeout(() => {
            newItem.style.transition = 'all 0.3s ease';
            newItem.style.opacity = '1';
            newItem.style.transform = 'translateY(0)';
        }, 100);
    }

    function addNewEwalletAccountToList(ewalletType, phoneNumber, ewalletName) {
        const ewalletAccountsSection = document.getElementById('ewallet_accounts');
        if (!ewalletAccountsSection) return;
        
        const accountOptions = ewalletAccountsSection.querySelector('.account-options');
        if (!accountOptions) return;
        
        const typeNames = {
            gopay: 'GoPay',
            dana: 'DANA',
            ovo: 'OVO',
            shopeepay: 'ShopeePay'
        };
        
        const newId = Date.now();
        const displayName = typeNames[ewalletType] || ewalletType;
        
        const newAccountHtml = `
            <div class="account-option">
                <input type="radio" name="account_info" 
                       value="${displayName} - ${phoneNumber}" 
                       id="ewallet_${newId}"
                       data-type="${ewalletType}">
                <label for="ewallet_${newId}" class="account-card">
                    <div class="account-info">
                        <div class="account-bank">${displayName}</div>
                        <div class="account-number">${phoneNumber}</div>
                    </div>
                    <div class="unverified-badge">⏳ Belum Verifikasi</div>
                </label>
            </div>
        `;
        
        accountOptions.insertAdjacentHTML('beforeend', newAccountHtml);
        
        // Animate new item
        const newItem = accountOptions.lastElementChild;
        newItem.style.opacity = '0';
        newItem.style.transform = 'translateY(-10px)';
        
        setTimeout(() => {
            newItem.style.transition = 'all 0.3s ease';
            newItem.style.opacity = '1';
            newItem.style.transform = 'translateY(0)';
        }, 100);
    }

    function showLoadingOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = 'flex';
            overlay.style.opacity = '0';
            
            setTimeout(() => {
                overlay.style.transition = 'opacity 0.3s ease';
                overlay.style.opacity = '1';
            }, 10);
        }
    }

    function hideLoadingOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.opacity = '0';
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 300);
        }
    }

    // Handle responsive behavior
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

    // Close loading overlay on page unload (fallback)
    window.addEventListener('beforeunload', function() {
        hideLoadingOverlay();
    });

    // Enhanced hover effects for interactive elements
    document.querySelectorAll('.balance-card, .method-card, .account-card, .info-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            if (!this.style.transform.includes('translateY')) {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 8px 20px rgba(58, 89, 209, 0.15)';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            if (this.style.transform.includes('translateY(-2px)')) {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '';
            }
        });
    });

    // Auto-save form data to sessionStorage (optional)
    const form = document.getElementById('withdrawalForm');
    if (form) {
        // Save form data on input
        form.addEventListener('input', debounce(function() {
            saveFormData();
        }, 500));
        
        // Load saved form data
        loadFormData();
    }

    function saveFormData() {
        try {
            const formData = {
                amount: document.getElementById('amount')?.value || '',
                method: document.querySelector('input[name="withdrawal_method"]:checked')?.value || '',
                account: document.querySelector('input[name="account_info"]:checked')?.value || '',
                description: document.getElementById('description')?.value || ''
            };
            
            sessionStorage.setItem('withdrawalFormData', JSON.stringify(formData));
        } catch (e) {
            console.log('Could not save form data');
        }
    }

    function loadFormData() {
        try {
            const savedData = sessionStorage.getItem('withdrawalFormData');
            if (savedData) {
                const formData = JSON.parse(savedData);
                
                if (formData.amount) {
                    const amountField = document.getElementById('amount');
                    if (amountField) amountField.value = formData.amount;
                }
                
                if (formData.description) {
                    const descField = document.getElementById('description');
                    if (descField) descField.value = formData.description;
                }
                
                // Note: Radio buttons would need more complex restoration
            }
        } catch (e) {
            console.log('Could not load saved form data');
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

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Escape to close modals
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal[style*="flex"]');
            openModals.forEach(modal => {
                modal.querySelector('.modal-close')?.click();
            });
        }
        
        // Enter to proceed to next step (if not in textarea)
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
            const activeStep = document.querySelector('.form-step.active');
            if (activeStep) {
                const nextBtn = activeStep.querySelector('.next-step');
                if (nextBtn && !nextBtn.disabled) {
                    e.preventDefault();
                    nextBtn.click();
                }
            }
        }
    });

    // Success animation for form completion
    if (window.location.search.includes('success=1')) {
        const successSection = document.querySelector('.success-section');
        if (successSection) {
            successSection.style.opacity = '0';
            successSection.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                successSection.style.transition = 'all 0.8s ease';
                successSection.style.opacity = '1';
                successSection.style.transform = 'scale(1)';
            }, 300);
            
            // Clear saved form data on success
            sessionStorage.removeItem('withdrawalFormData');
        }
    }
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

    validateAmount: function(amount, balance, min = 100000, max = 10000000) {
        if (!amount || isNaN(amount)) {
            return { valid: false, message: 'Masukkan jumlah yang valid' };
        }
        
        if (amount < min) {
            return { valid: false, message: `Minimum penarikan ${this.formatCurrency(min)}` };
        }
        
        if (amount > max) {
            return { valid: false, message: `Maksimum penarikan ${this.formatCurrency(max)}` };
        }
        
        if (amount > balance) {
            return { valid: false, message: 'Jumlah melebihi saldo tersedia' };
        }
        
        return { valid: true, message: 'Valid' };
    },

    getProcessingTime: function(method) {
        const times = {
            bank_transfer: '1-2 hari kerja',
            gopay: 'Instan',
            dana: 'Instan',
            ovo: 'Instan',
            shopeepay: 'Instan'
        };
        
        return times[method] || '1-2 hari kerja';
    },

    calculateFees: function(amount, method) {
        // Currently all methods are free
        return {
            adminFee: 0,
            platformFee: 0,
            totalFees: 0,
            netAmount: amount
        };
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