// Unified Date Picker Component
window.unifiedDatePicker = function(config) {
    return {
        value: config.value || '',
        isOpen: false,
        inputId: config.inputId,
        minDate: config.minDate,
        maxDate: config.maxDate,
        wireModel: config.wireModel,
        
        init() {
            this.$nextTick(() => {
                this.restoreValueFromStorage();
                this.initializePicker();
                this.setupPersistence();
            });
        },
        
        restoreValueFromStorage() {
            const storageKey = `unified_date_picker_${this.inputId}`;
            const storedValue = sessionStorage.getItem(storageKey);
            if (storedValue && !this.value) {
                this.value = storedValue;
                const input = document.getElementById(this.inputId);
                if (input) {
                    input.value = storedValue;
                }
            }
        },
        
        setupPersistence() {
            const storageKey = `unified_date_picker_${this.inputId}`;
            
            // Listen for Livewire updates and preserve value
            document.addEventListener('livewire:update', () => {
                if (this.value) {
                    sessionStorage.setItem(storageKey, this.value);
                }
            });
            
            // Watch for value changes and update storage
            this.$watch('value', (newValue) => {
                if (newValue) {
                    sessionStorage.setItem(storageKey, newValue);
                } else {
                    sessionStorage.removeItem(storageKey);
                }
            });
        },
        
        initializePicker() {
            const input = document.getElementById(this.inputId);
            if (!input) {
                console.warn('Date picker input not found:', this.inputId);
                return;
            }
            
            // Check if Pikaday is available
            if (typeof Pikaday === 'undefined') {
                console.error('Pikaday library is not loaded');
                return;
            }
            
            // Initialize Pikaday with MM/DD/YYYY format
            const picker = new Pikaday({
                field: input,
                format: 'MM/DD/YYYY',
                yearRange: [1900, new Date().getFullYear() + 10],
                showMonthAfterYear: false,
                showDaysInNextAndPreviousMonths: true,
                enableSelectionDaysInNextAndPreviousMonths: true,
                numberOfMonths: 1,
                firstDay: 0, // Sunday
                minDate: this.minDate ? new Date(this.minDate) : null,
                maxDate: this.maxDate ? new Date(this.maxDate) : null,
                onSelect: (date) => {
                    const formatted = this.formatDate(date);
                    this.value = formatted;
                    input.value = formatted;
                    
                    // Store in sessionStorage for persistence
                    const storageKey = `unified_date_picker_${this.inputId}`;
                    sessionStorage.setItem(storageKey, formatted);
                    
                    // Prevent default form submission or page reload
                    event?.preventDefault?.();
                    
                    // Use setTimeout to defer Livewire update and prevent immediate page reload
                    setTimeout(() => {
                        // Trigger Livewire update if wire:model is used
                        if (this.wireModel && window.Livewire) {
                            const component = input.closest('[wire\\:id]');
                            if (component) {
                                const livewireComponent = window.Livewire.find(component.getAttribute('wire:id'));
                                if (livewireComponent) {
                                    // Use set method without triggering immediate update
                                    livewireComponent.set(this.wireModel, formatted, false);
                                }
                            }
                        }
                        
                        // Trigger input event for wire:model with debounce
                        const inputEvent = new Event('input', { bubbles: true });
                        inputEvent.livewirePreventDefault = true;
                        input.dispatchEvent(inputEvent);
                        
                        // Dispatch custom event
                        input.dispatchEvent(new CustomEvent('date-selected', {
                            detail: { date: formatted, originalDate: date }
                        }));
                        
                        this.validateDate();
                    }, 100);
                },
                onOpen: () => {
                    this.isOpen = true;
                },
                onClose: () => {
                    this.isOpen = false;
                }
            });
            
            // Handle manual input
            input.addEventListener('input', (e) => {
                this.value = e.target.value;
                this.validateDate();
            });
            
            input.addEventListener('blur', (e) => {
                this.formatAndValidateInput();
            });
        },
        
        formatDate(date) {
            if (!date) return '';
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const year = date.getFullYear();
            return `${month}/${day}/${year}`;
        },
        
        formatAndValidateInput() {
            const input = document.getElementById(this.inputId);
            if (!input.value) return;
            
            // Try to parse and reformat the input
            try {
                const date = new Date(input.value);
                if (!isNaN(date.getTime())) {
                    const formatted = this.formatDate(date);
                    this.value = formatted;
                    input.value = formatted;
                    
                    // Use setTimeout to defer Livewire update
                    setTimeout(() => {
                        if (this.wireModel && window.Livewire) {
                            const component = input.closest('[wire\\:id]');
                            if (component) {
                                const livewireComponent = window.Livewire.find(component.getAttribute('wire:id'));
                                if (livewireComponent) {
                                    livewireComponent.set(this.wireModel, formatted, false);
                                }
                            }
                        }
                        
                        // Trigger input event for wire:model
                        const inputEvent = new Event('input', { bubbles: true });
                        inputEvent.livewirePreventDefault = true;
                        input.dispatchEvent(inputEvent);
                    }, 50);
                }
            } catch (e) {
                // Invalid date, keep original value
            }
            
            this.validateDate();
        },
        
        validateDate() {
            const input = document.getElementById(this.inputId);
            const value = input.value;
            
            // Remove previous validation classes
            input.classList.remove('border-red-500', 'border-green-500');
            
            if (!value) return;
            
            try {
                const date = new Date(value);
                if (isNaN(date.getTime())) {
                    input.classList.add('border-red-500');
                    return;
                }
                
                // Check min/max dates
                if (this.minDate && date < new Date(this.minDate)) {
                    input.classList.add('border-red-500');
                    return;
                }
                
                if (this.maxDate && date > new Date(this.maxDate)) {
                    input.classList.add('border-red-500');
                    return;
                }
                
                input.classList.add('border-green-500');
            } catch (e) {
                input.classList.add('border-red-500');
            }
        },
        
        clearDate() {
            this.value = '';
            const input = document.getElementById(this.inputId);
            input.value = '';
            input.classList.remove('border-red-500', 'border-green-500');
            
            // Remove from sessionStorage
            const storageKey = `unified_date_picker_${this.inputId}`;
            sessionStorage.removeItem(storageKey);
            
            if (this.wireModel && window.Livewire) {
                const component = input.closest('[wire\\:id]');
                if (component) {
                    const livewireComponent = window.Livewire.find(component.getAttribute('wire:id'));
                    if (livewireComponent) {
                        livewireComponent.set(this.wireModel, '');
                    }
                }
            }
            
            // Also trigger input event for wire:model
            input.dispatchEvent(new Event('input', { bubbles: true }));
            
            input.dispatchEvent(new CustomEvent('date-cleared'));
        }
    };
};