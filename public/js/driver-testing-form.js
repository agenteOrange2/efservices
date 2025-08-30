/**
 * Driver Testing Form Management
 * Centralizes form logic for create and edit views
 */
class DriverTestingForm {
    constructor(options = {}) {
        this.isEditMode = options.isEditMode || false;
        this.currentDriverId = options.currentDriverId || null;
        this.uploadedFiles = [];
        
        this.initializeElements();
        this.bindEvents();
        this.initializeLivewire();
        
        if (this.isEditMode && this.carrierSelect.value) {
            this.loadDrivers(this.carrierSelect.value, () => {
                this.selectCurrentDriver();
            });
        }
    }

    init() {
        // Method for backward compatibility
        // All initialization is already done in constructor
        console.log('DriverTestingForm initialized');
    }

    initializeElements() {
        // Form elements
        this.form = document.getElementById(this.isEditMode ? 'edit-test-form' : 'create-test-form');
        this.carrierSelect = document.getElementById('carrier_id');
        this.driverSelect = document.getElementById('user_driver_detail_id');
        this.carrierIdHidden = document.getElementById('carrier_id_hidden');
        this.userDriverDetailIdHidden = document.getElementById('user_driver_detail_id_hidden');
        
        // Driver detail elements
        this.driverDetailCard = document.getElementById('driver-detail-card');
        this.driverName = document.getElementById('driver-name');
        this.driverEmail = document.getElementById('driver-email');
        this.driverPhone = document.getElementById('driver-phone');
        this.driverLicense = document.getElementById('driver-license');
        this.driverLicenseClass = document.getElementById('driver-license-class');
        this.driverLicenseExpiration = document.getElementById('driver-license-expiration');
        
        // Other reason elements
        this.otherReasonCheckbox = document.getElementById('is_other_reason_test');
        this.otherReasonContainer = document.getElementById('other_reason_container');
        
        // File upload elements
        this.filesInput = document.getElementById('driver_testing_files_input');
        
        // Initialize files input
        if (this.filesInput) {
            this.filesInput.value = JSON.stringify(this.uploadedFiles);
        }
    }

    bindEvents() {
        // Carrier selection
        if (this.carrierSelect) {
            this.carrierSelect.addEventListener('change', (e) => {
                this.loadDrivers(e.target.value);
            });
        }

        // Driver selection
        if (this.driverSelect) {
            this.driverSelect.addEventListener('change', (e) => {
                this.showDriverDetails();
                if (this.userDriverDetailIdHidden) {
                    this.userDriverDetailIdHidden.value = e.target.value;
                }
            });
        }

        // Form submission
        if (this.form) {
            this.form.addEventListener('submit', (e) => {
                if (!this.validateForm()) {
                    e.preventDefault();
                    return;
                }
                
                // Show form submission loading
                this.showFormLoading();
            });
        }

        // Other reason toggle
        if (this.otherReasonCheckbox) {
            this.otherReasonCheckbox.addEventListener('change', () => {
                this.toggleOtherReasonField();
            });
            // Initialize state
            this.toggleOtherReasonField();
        }
    }

    async loadDrivers(carrierId, callback = null) {
        if (!carrierId) {
            this.clearDriverSelect();
            return;
        }

        // Show loading state
        this.showDriversLoading();

        try {
            const response = await fetch(`/api/active-drivers-by-carrier/${carrierId}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();

            // The API returns drivers directly as an array
            if (Array.isArray(data)) {
                this.populateDriverSelect(data);
                if (callback) callback();
                this.showSuccess('Drivers loaded successfully');
            } else {
                console.error('Error loading drivers: Invalid response format');
                this.showError('Error loading drivers. Please try again.');
            }
        } catch (error) {
            console.error('Network error loading drivers:', error);
            this.showError('Network error. Please check your connection.');
        } finally {
            this.hideDriversLoading();
        }
    }

    clearDriverSelect() {
        if (this.driverSelect) {
            this.driverSelect.innerHTML = '<option value="">Select a driver...</option>';
            this.hideDriverDetails();
        }
    }

    populateDriverSelect(drivers) {
        if (!this.driverSelect) return;

        this.driverSelect.innerHTML = '<option value="">Select a driver...</option>';
        
        drivers.forEach(driver => {
            const option = document.createElement('option');
            option.value = driver.id;
            
            // Handle UserDriverDetail structure with user relationship
            const user = driver.user || {};
            const fullName = this.formatDriverName(driver);
            option.textContent = fullName;
            
            // Get license info from licenses array (most recent active license)
            const license = driver.licenses && driver.licenses.length > 0 ? driver.licenses[0] : {};
            
            // Format license expiration date
            let licenseExpiration = license.expiration_date || '';
            if (licenseExpiration) {
                const expDate = new Date(licenseExpiration);
                licenseExpiration = expDate.toLocaleDateString();
            }
            
            // Set data attributes for driver details (matching createold.blade.php structure)
            option.setAttribute('data-full-name', fullName);
            option.setAttribute('data-name', fullName);
            option.setAttribute('data-email', user.email || '');
            option.setAttribute('data-phone', driver.phone || '');
            option.setAttribute('data-license', license.license_number || '');
            option.setAttribute('data-license-class', license.license_class || '');
            option.setAttribute('data-license-expiration', licenseExpiration);
            option.setAttribute('data-first-name', user.name || '');
            option.setAttribute('data-middle-name', user.middle_name || '');
            option.setAttribute('data-last-name', user.last_name || '');
            
            this.driverSelect.appendChild(option);
        });
    }

    selectCurrentDriver() {
        if (!this.currentDriverId || !this.driverSelect) return;

        const option = this.driverSelect.querySelector(`option[value="${this.currentDriverId}"]`);
        if (option) {
            this.driverSelect.value = this.currentDriverId;
            this.showDriverDetails();
        }
    }

    showDriverDetails() {
        const selectedOption = this.driverSelect?.options[this.driverSelect.selectedIndex];
        
        if (!selectedOption || !selectedOption.value) {
            this.hideDriverDetails();
            return;
        }

        try {
            const driverData = this.extractDriverData(selectedOption);
            this.updateDriverDisplay(driverData);
            this.showDriverCard();
        } catch (error) {
            console.error('Error displaying driver details:', error);
        }
    }

    extractDriverData(option) {
        return {
            id: option.value,
            name: option.getAttribute('data-full-name') || option.getAttribute('data-name') || option.textContent,
            email: option.getAttribute('data-email') || 'N/A',
            phone: option.getAttribute('data-phone') || 'N/A',
            licenseNumber: option.getAttribute('data-license') || 'N/A',
            licenseClass: option.getAttribute('data-license-class') || 'N/A',
            licenseExpiration: option.getAttribute('data-license-expiration') || 'N/A',
            firstName: option.getAttribute('data-first-name') || '',
            middleName: option.getAttribute('data-middle-name') || '',
            lastName: option.getAttribute('data-last-name') || ''
        };
    }

    updateDriverDisplay(driverData) {
        if (!this.driverName || !this.driverEmail || !this.driverPhone) {
            console.warn('Driver display elements not found');
            return;
        }

        const formattedName = this.formatDriverName(driverData);
        this.driverName.innerHTML = formattedName;
        this.driverEmail.textContent = driverData.email || 'N/A';
        this.driverPhone.textContent = driverData.phone || 'N/A';
        
        // Update license information if elements exist
        if (this.driverLicense) {
            this.driverLicense.textContent = driverData.licenseNumber || 'N/A';
        }
        if (this.driverLicenseClass) {
            this.driverLicenseClass.textContent = driverData.licenseClass || 'N/A';
        }
        if (this.driverLicenseExpiration) {
            this.driverLicenseExpiration.textContent = driverData.licenseExpiration || 'N/A';
        }
    }

    formatDriverName(data) {
        // If data already has full_name, use it
        if (data.full_name && data.full_name.trim()) {
            return data.full_name.trim();
        }
        
        // If data has user object (from UserDriverDetail API response)
        if (data.user) {
            const parts = [
                data.user.name,
                data.user.middle_name,
                data.user.last_name
            ].filter(part => part && part.trim());
            
            return parts.length > 0 ? parts.join(' ') : 'N/A';
        }
        
        // If data has direct name properties with HTML formatting (for display)
        const parts = [
            data.firstName,
            data.middleName ? `<span class="text-gray-700">${data.middleName}</span>` : '',
            data.lastName ? `<span class="font-semibold">${data.lastName}</span>` : ''
        ].filter(Boolean);
        
        return parts.length > 0 ? parts.join(' ') : (data.name || 'N/A');
    }

    showDriverCard() {
        if (this.driverDetailCard) {
            this.driverDetailCard.classList.remove('hidden');
        }
    }

    hideDriverDetails() {
        if (this.driverDetailCard) {
            this.driverDetailCard.classList.add('hidden');
        }
    }

    toggleOtherReasonField() {
        if (!this.otherReasonContainer) return;
        
        const isVisible = this.otherReasonCheckbox?.checked;
        this.otherReasonContainer.style.display = isVisible ? 'block' : 'none';
    }

    validateForm() {
        const carrierId = this.carrierSelect?.value;
        const driverId = this.driverSelect?.value;

        if (!carrierId) {
            this.showError('Please select a carrier');
            return false;
        }

        if (!driverId) {
            this.showError('Please select a driver');
            return false;
        }

        // Update hidden fields
        if (this.carrierIdHidden) this.carrierIdHidden.value = carrierId;
        if (this.userDriverDetailIdHidden) this.userDriverDetailIdHidden.value = driverId;

        // Validate administered by field for edit mode
        if (this.isEditMode) {
            const administeredBySelect = document.getElementById('administered_by_select');
            if (administeredBySelect?.value === 'other') {
                const otherValue = document.getElementById('administered_by_other')?.value?.trim();
                if (!otherValue) {
                    this.showError('Please specify who administered the test');
                    return false;
                }
                const administeredByHidden = document.getElementById('administered_by');
                if (administeredByHidden) administeredByHidden.value = otherValue;
            }
        }

        return true;
    }

    initializeLivewire() {
        window.addEventListener('livewire:initialized', () => {
            console.log('Livewire initialized - registering file upload listeners');

            Livewire.on('fileUploaded', (eventData) => {
                const data = eventData[0];
                if (data.modelName === 'driver_testing_files') {
                    this.handleFileUploaded(data);
                }
            });

            Livewire.on('fileRemoved', (eventData) => {
                const data = eventData[0];
                if (data.modelName === 'driver_testing_files') {
                    this.handleFileRemoved(data);
                }
            });
        });
    }

    handleFileUploaded(data) {
        this.uploadedFiles.push({
            path: data.tempPath,
            original_name: data.originalName,
            mime_type: data.mimeType,
            size: data.size
        });
        
        this.updateFilesInput();
        this.showSuccess(`File "${data.originalName}" uploaded successfully`);
        console.log('File uploaded:', data.originalName);
    }

    handleFileRemoved(data) {
        const fileId = data.fileId;
        if (fileId.startsWith('temp_')) {
            // Remove the last added file for temporary files
            this.uploadedFiles.pop();
        }
        
        this.updateFilesInput();
        this.showSuccess('File removed successfully');
        console.log('File removed:', fileId);
    }

    updateFilesInput() {
        if (this.filesInput) {
            this.filesInput.value = JSON.stringify(this.uploadedFiles);
        }
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
        
        // Set colors based on type
        const colors = {
            success: 'bg-green-500 text-white',
            error: 'bg-red-500 text-white',
            info: 'bg-blue-500 text-white',
            warning: 'bg-yellow-500 text-black'
        };
        
        notification.className += ` ${colors[type] || colors.info}`;
        notification.textContent = message;
        
        // Add to DOM
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    showDriversLoading() {
        if (this.driverSelect) {
            this.driverSelect.disabled = true;
            this.driverSelect.innerHTML = '<option value="">Loading drivers...</option>';
            
            // Add loading spinner to carrier select
            this.addLoadingSpinner(this.carrierSelect);
        }
    }

    hideDriversLoading() {
        if (this.driverSelect) {
            this.driverSelect.disabled = false;
            this.removeLoadingSpinner(this.carrierSelect);
        }
    }

    showFormLoading() {
        const submitButton = this.form?.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            const originalText = submitButton.textContent;
            submitButton.setAttribute('data-original-text', originalText);
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
            `;
        }
    }

    addLoadingSpinner(element) {
        if (!element || element.querySelector('.loading-spinner')) return;
        
        const spinner = document.createElement('div');
        spinner.className = 'loading-spinner absolute right-2 top-1/2 transform -translate-y-1/2';
        spinner.innerHTML = `
            <svg class="animate-spin h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        `;
        
        // Make parent relative if not already
        const parentStyle = window.getComputedStyle(element.parentElement);
        if (parentStyle.position === 'static') {
            element.parentElement.style.position = 'relative';
        }
        
        element.parentElement.appendChild(spinner);
    }

    removeLoadingSpinner(element) {
        if (!element) return;
        
        const spinner = element.parentElement?.querySelector('.loading-spinner');
        if (spinner) {
            spinner.remove();
        }
    }
}

// Export for use in views
window.DriverTestingForm = DriverTestingForm;