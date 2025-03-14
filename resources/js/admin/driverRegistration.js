// resources/js/driverRegistration.js
document.addEventListener("alpine:init", () => {
    Alpine.data("driverForm", function (config) {
      return {
        activeTab: config.initialTab || "general",
        submissionType: "partial",
        dateError: "",
        hasWorkHistory: config.hasWorkHistory || false,
        workHistories: config.workHistories || [],
        fromDate: config.fromDate || "",
        toDate: config.toDate || "",
        livedThreeYears: config.livedThreeYears || false,
        previousAddresses: config.previousAddresses || [],
        isAddressValid: false,
        totalYears: 0,
        isSubmitting: false,
        showAutoSaveMessage: false,
        userDriverId: config.userDriverId || null,
        autoSaveMessage: "",
        termsAccepted: config.termsAccepted || false,
        hasTwicCard: config.hasTwicCard || false,
        applyingPosition: config.applyingPosition || "",
        showOtherPosition: false,
        referralSource: config.referralSource || "",
        showEmployeeReferral: false,
        showOtherReferral: false,
        eligibleToWork: config.eligibleToWork || "",
        openSections: {
          address: true,
          driver: false,
          application: false,
        },
        
        // Initialize component
        init() {
          // Address initialization
          this.calculateTotal();
          this.$watch("toDate", () => this.validateAndCalculateDates());
          this.$watch("fromDate", () => this.calculateTotal());
          this.$watch("toDate", () => this.calculateTotal());
          this.$watch("previousAddresses", () => this.calculateTotal(), {
            deep: true,
          });
          this.$watch("livedThreeYears", (value) => {
            if (value) {
              this.totalYears = 3;
              this.isAddressValid = true;
            } else {
              this.calculateTotal();
            }
          });
  
          // Position and referral watchers
          this.showOtherPosition = this.applyingPosition === "other";
          this.$watch("applyingPosition", (value) => {
            this.showOtherPosition = value === "other";
          });
          
          this.showEmployeeReferral = this.referralSource === "employee_referral";
          this.showOtherReferral = this.referralSource === "other";
          this.$watch("referralSource", (value) => {
            this.showEmployeeReferral = value === "employee_referral";
            this.showOtherReferral = value === "other";
          });
          
          // Configurar el envío del formulario para capturar el evento submit
          const form = document.getElementById("driverForm");
          if (form) {
            form.addEventListener("submit", (e) => {
              e.preventDefault();
              this.saveAndFinish();
            });
          }
        },
        
        // Address calculation methods
        calculateDuration(from, to) {
          if (!from) return 0;
          
          const fromDate = new Date(from);
          const toDate = to ? new Date(to) : new Date();
          
          // Validación básica
          if (isNaN(fromDate.getTime()) || isNaN(toDate.getTime())) {
            return 0;
          }
          
          if (toDate < fromDate) return 0;
          
          // Cálculo preciso de años
          let years = toDate.getFullYear() - fromDate.getFullYear();
          const months = toDate.getMonth() - fromDate.getMonth();
          const days = toDate.getDate() - fromDate.getDate();
          
          // Ajustar si no ha pasado un año completo
          if (months < 0 || (months === 0 && days < 0)) {
            years--;
          }
          
          return Math.max(0, years);
        },
        
        calculateTotal() {
          let currentYears = this.calculateDuration(this.fromDate, this.toDate);
          let total = currentYears;
          
          // Si ya tiene 3 años en la dirección actual, no necesitamos más
          if (currentYears >= 3) {
            this.livedThreeYears = true;
            this.isAddressValid = true;
            this.totalYears = 3;
            return;
          }
          
          // Sumamos años de direcciones adicionales
          if (this.previousAddresses && this.previousAddresses.length > 0) {
            this.previousAddresses.forEach(addr => {
              if (addr.from_date && addr.to_date) {
                const years = this.calculateDuration(addr.from_date, addr.to_date);
                total += years;
              }
            });
          }
          
          this.totalYears = Math.min(total, 3); // Limitamos a 3 para claridad
          this.isAddressValid = total >= 3;
          
          // Si ya tenemos 3 años con las direcciones adicionales, marcamos como válido
          if (total >= 3 && !this.livedThreeYears) {
            this.isAddressValid = true;
          }
        },
        
        validateAndCalculateDates() {
          this.dateError = "";
          
          if (!this.fromDate) {
            return;
          }
          
          const fromD = new Date(this.fromDate);
          const toD = this.toDate ? new Date(this.toDate) : new Date();
          
          // Validar formato de fechas
          if (isNaN(fromD.getTime()) || (this.toDate && isNaN(toD.getTime()))) {
            this.dateError = "Formato de fecha inválido";
            return;
          }
          
          // Validar que la fecha final sea posterior a la inicial
          if (this.toDate && toD < fromD) {
            this.dateError = "La fecha final debe ser posterior a la fecha inicial";
            return;
          }
          
          // Calcular años en esta dirección
          const years = this.calculateDuration(this.fromDate, this.toDate || null);
          
          // Si ha vivido 3+ años, marcarlo automáticamente
          if (years >= 3) {
            this.livedThreeYears = true;
            this.totalYears = 3;
            this.isAddressValid = true;
          } else {
            this.livedThreeYears = false;
            this.calculateTotal();
          }
        },
        
        // Address management methods
        addAddress() {
          // No permitir agregar si ya se alcanzaron los 3 años
          if (this.totalYears >= 3 || this.livedThreeYears) {
            return;
          }
          
          // Crear nueva dirección con ID único
          const newAddress = {
            id: Date.now(), // Usamos timestamp como ID único
            address_line1: "",
            address_line2: "",
            city: "",
            state: "",
            zip_code: "",
            from_date: "",
            to_date: ""
          };
          
          this.previousAddresses.push(newAddress);
          
          // Recalcular después de un breve delay para dar tiempo a que Alpine actualice el DOM
          setTimeout(() => {
            this.calculateTotal();
          }, 100);
        },
        
        removeAddress(index) {
          if (index >= 0 && index < this.previousAddresses.length) {
            this.previousAddresses.splice(index, 1);
            
            // Recalcular total después de eliminar
            setTimeout(() => {
              this.calculateTotal();
            }, 100);
          }
        },
        
        // Work history methods
        addWorkHistory() {
          this.workHistories.push({
            previous_company: "",
            start_date: "",
            end_date: "",
            location: "",
            position: "",
            reason_for_leaving: "",
            reference_contact: "",
          });
        },
        
        removeWorkHistory(index) {
          this.workHistories.splice(index, 1);
        },
        
        // Navigation methods
        isFirstTab() {
          return this.activeTab === "general";
        },
        
        isLastTab() {
          return this.activeTab === "accident";
        },
        
        getPreviousTab() {
          const tabs = [
            "general",
            "licenses",
            "medical",
            "training",
            "traffic",
            "accident",
          ];
          const currentIndex = tabs.indexOf(this.activeTab);
          if (currentIndex > 0) {
            return tabs[currentIndex - 1];
          }
          return "general";
        },
        
        getNextTab() {
          const tabs = [
            "general",
            "licenses",
            "medical",
            "training",
            "traffic",
            "accident",
          ];
          const currentIndex = tabs.indexOf(this.activeTab);
          if (currentIndex < tabs.length - 1) {
            return tabs[currentIndex + 1];
          }
          return "accident";
        },
        
        // Section toggle methods
        toggleSection(section) {
          this.openSections[section] = !this.openSections[section];
        },
        
        // Validation methods
        validateCurrentStep() {
          // Limpiar errores anteriores
          this.clearValidationErrors();
          
          let isValid = true;
          
          switch (this.activeTab) {
            case "general":
              // Validación específica para la pestaña general
              const requiredFields = [
                "name", "email", "phone", "last_name", "date_of_birth",
                "address_line1", "city", "state", "zip_code", "from_date"
              ];
              
              // Si es envío completo, validar todos los campos
              if (this.submissionType === 'complete') {
                requiredFields.push("applying_position", "applying_location");
              }
              
              // Comprobar campos obligatorios
              requiredFields.forEach(field => {
                const element = document.querySelector(`[name="${field}"]`);
                if (!element || !element.value.trim()) {
                  this.showFieldError(field, "Este campo es obligatorio");
                  isValid = false;
                }
              });
              
              // Validar email
              const emailElement = document.querySelector('[name="email"]');
              if (emailElement && emailElement.value && !this.isValidEmail(emailElement.value)) {
                this.showFieldError("email", "Por favor ingrese un email válido");
                isValid = false;
              }
              
              // Validar que se cumpla el requisito de 3 años (si no marcó la casilla)
              if (!this.livedThreeYears && this.totalYears < 3) {
                this.showGeneralError("Las direcciones deben cubrir al menos 3 años de historial");
                isValid = false;
              }
              
              // Validar fechas de la dirección
              if (this.fromDate && this.toDate && new Date(this.toDate) < new Date(this.fromDate)) {
                this.dateError = "La fecha final debe ser posterior a la fecha inicial";
                isValid = false;
              }
              
              break;
              
            case "licenses":
              // Validación básica para pestañas adicionales
              // Puedes implementar validaciones específicas para cada pestaña
              break;
              
            // Añadir validaciones para otras pestañas si es necesario...
          }
          
          // Si no es válido, desplazar a los errores
          if (!isValid) {
            const firstError = document.querySelector(".border-red-500, .text-red-500");
            if (firstError) {
              firstError.scrollIntoView({
                behavior: "smooth",
                block: "center",
              });
              
              // Si es un campo, intentar enfocarlo
              if (firstError.nodeName === 'INPUT' || firstError.nodeName === 'SELECT' || firstError.nodeName === 'TEXTAREA') {
                firstError.focus();
              }
            }
          }
          
          return isValid;
        },
        
        isValidEmail(email) {
          const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          return re.test(email);
        },
        
        showFieldError(fieldName, message) {
          const element = document.querySelector(`[name="${fieldName}"]`);
          if (!element) return;
          element.classList.add("border-red-500");
          const errorMsg = document.createElement("p");
          errorMsg.className = "text-red-500 text-sm mt-1 error-message";
          errorMsg.textContent = message;
          element.parentNode.insertBefore(errorMsg, element.nextSibling);
        },
        
        showGeneralError(message) {
          const errorContainer = document.createElement("div");
          errorContainer.className = "bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded my-3 error-message";
          errorContainer.innerHTML = `<p>${message}</p>`;
          
          // Insertar al principio del formulario
          const form = document.getElementById('driverForm');
          if (form) {
            form.insertBefore(errorContainer, form.firstChild);
          }
        },
        
        clearValidationErrors() {
          document
            .querySelectorAll(".error-message")
            .forEach((el) => el.remove());
          document
            .querySelectorAll(".border-red-500")
            .forEach((el) => el.classList.remove("border-red-500"));
        },
        
        handleValidationErrors(errors) {
          this.clearValidationErrors();
          for (const field in errors) {
            const element = document.querySelector(`[name="${field}"]`);
            if (element) {
              element.classList.add("border-red-500");
              const errorMsg = document.createElement("p");
              errorMsg.className = "text-red-500 text-sm mt-1 error-message";
              errorMsg.textContent = errors[field][0];
              element.parentNode.insertBefore(errorMsg, element.nextSibling);
            }
          }
          
          // Desplazamiento al primer error
          const firstError = document.querySelector(".border-red-500");
          if (firstError) {
            firstError.scrollIntoView({
              behavior: "smooth",
              block: "center",
            });
            firstError.focus();
          }
        },
        
        // Form submission methods
        // moveToNextTab() {
        //   this.submissionType = 'complete'; // Establecer como completo para validación completa
          
        //   if (this.validateCurrentStep()) {
        //     // Guardar antes de avanzar
        //     this.saveCurrentTab().then(success => {
        //       if (success) {
        //         this.activeTab = this.getNextTab(); 
        //         window.scrollTo(0, 0);
        //       }
        //     });
        //   } else {
        //     // Mostrar mensaje de error
        //     this.autoSaveMessage = "Por favor complete todos los campos obligatorios antes de continuar";
        //     this.showAutoSaveMessage = true;
        //     setTimeout(() => { this.showAutoSaveMessage = false; }, 3000);
        //   }
        // },

        moveToNextTab() {
            this.submissionType = 'complete'; // Establecer como completo para validación completa
            if (this.validateCurrentStep()) {
                // Simplemente cambiar de pestaña sin guardar
                this.activeTab = this.getNextTab();
                window.scrollTo(0, 0);
            } else {
                // Mostrar mensaje de error
                this.autoSaveMessage = "Por favor complete todos los campos obligatorios antes de continuar";
                this.showAutoSaveMessage = true;
                setTimeout(() => { this.showAutoSaveMessage = false; }, 3000);
            }
        },
        
        saveCurrentTab() {
          return new Promise(resolve => {
            if (this.isSubmitting) {
              resolve(false);
              return;
            }
            
            // Validar primero
            if (!this.validateCurrentStep()) {
              resolve(false);
              return;
            }
            
            this.isSubmitting = true;
            this.autoSaveMessage = "Guardando...";
            this.showAutoSaveMessage = true;
            
            // Preparar el formulario
            const form = document.getElementById('driverForm');
            const formData = new FormData(form);
            
            // Configurar datos importantes
            formData.set('active_tab', this.activeTab);
            formData.set('submission_type', 'partial');
            
            // Corregir valores booleanos y direcciones
            this.fixBooleanValues(formData);
            this.fixAddressesBeforeSubmit(formData);
            
            fetch(form.action, {
              method: 'POST',
              body: formData,
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
              }
            })
            .then(response => {
              if (response.redirected) {
                window.location.href = response.url;
                resolve(false);
                return null;
              }
              
              if (!response.ok) {
                return response.json().then(errorData => {
                  throw new Error(errorData.message || 'Error en respuesta del servidor');
                });
              }
              
              return response.json();
            })
            .then(data => {
              if (data === null) return; // Redirección ya manejada
              
              if (data.success) {
                // Si tenemos un ID nuevo, actualizarlo
                if (data.data && data.data.id && !this.userDriverId) {
                  this.userDriverId = data.data.id;
                }
                
                // Mostrar mensaje de éxito
                this.autoSaveMessage = "Pestaña guardada correctamente";
                this.showAutoSaveMessage = true;
                setTimeout(() => { this.showAutoSaveMessage = false; }, 2000);
                
                resolve(true);
              } else {
                // Mostrar errores si los hay
                if (data.errors) {
                  this.handleValidationErrors(data.errors);
                }
                
                this.autoSaveMessage = data.message || "Error al guardar los datos";
                this.showAutoSaveMessage = true;
                resolve(false);
              }
            })
            .catch(error => {
              console.error('Error:', error);
              this.autoSaveMessage = `Error: ${error.message}`;
              this.showAutoSaveMessage = true;
              resolve(false);
            })
            .finally(() => {
              this.isSubmitting = false;
            });
          });
        },
        
        saveAndFinish() {
            // Establecer como parcial para permitir guardado desde cualquier pestaña
            this.submissionType = 'partial';
            // Validar solo la pestaña actual
            if (!this.validateCurrentStep()) {
                return false;
            }
            
            this.isSubmitting = true;
            this.autoSaveMessage = "Guardando...";
            this.showAutoSaveMessage = true;
            
            // Obtener el formulario y preparar los datos
            const form = document.getElementById('driverForm');
            const formData = new FormData(form);
            
            // Configurar para envío parcial
            formData.set('active_tab', this.activeTab);
            formData.set('submission_type', 'partial');
            
            // Corregir formatos de datos
            this.fixBooleanValues(formData);
            this.fixAddressesBeforeSubmit(formData);
            
            // Enviar la solicitud
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                } else {
                    return response.json().then(errorData => {
                        throw new Error(errorData.message || 'Error al guardar los datos');
                    });
                }
            })
            .then(data => {
                if (data.success) {
                    // Redirigir a la página de edición si se proporcionó una URL
                    if (data.data && data.data.redirect_url) {
                        window.location.href = data.data.redirect_url;
                    } else if (data.data && data.data.id) {
                        // Si solo tenemos el ID, construir URL de edición
                        const carrierId = window.location.pathname.split('/')[3]; // Asumiendo URL como /admin/carrier/{slug}/drivers/create
                        const editUrl = `/admin/carrier/${carrierId}/drivers/${data.data.id}/edit`;
                        window.location.href = editUrl;
                    } else {
                        // Mostrar mensaje de éxito
                        this.autoSaveMessage = "Datos guardados correctamente";
                        this.showAutoSaveMessage = true;
                        setTimeout(() => { this.showAutoSaveMessage = false; }, 3000);
                    }
                } else if (data.errors) {
                    this.handleValidationErrors(data.errors);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.autoSaveMessage = `Error: ${error.message}`;
                this.showAutoSaveMessage = true;
            })
            .finally(() => {
                this.isSubmitting = false;
            });
        },
        
        // Helper methods
        fixBooleanValues(formData) {
          // Lista de campos que deben ser booleanos
          const booleanFields = [
            'lived_three_years',
            'terms_accepted',
            'has_twic_card',
            'eligible_to_work',
            'can_speak_english',
            'has_work_history',
            'has_attended_training_school',
            'has_traffic_convictions',
            'has_accidents'
          ];
          
          // Corregir cada campo según el tab activo
          switch(this.activeTab) {
            case 'general':
              formData.set('lived_three_years', this.livedThreeYears ? '1' : '0');
              formData.set('terms_accepted', this.termsAccepted ? '1' : '0');
              formData.set('has_twic_card', this.hasTwicCard ? '1' : '0');
              break;
            case 'licenses':
              // Manejar licencias y sus valores booleanos
              if (formData.has('licenses')) {
                // Esto es más complejo y depende de la estructura exacta
              }
              break;
            case 'training':
              if (formData.has('has_attended_training_school')) {
                // Convertir on/off a 1/0
                const val = formData.get('has_attended_training_school');
                formData.set('has_attended_training_school', val === 'on' ? '1' : '0');
              }
              break;
            case 'traffic':
              if (formData.has('has_traffic_convictions')) {
                const val = formData.get('has_traffic_convictions');
                formData.set('has_traffic_convictions', val === 'on' ? '1' : '0');
              }
              break;
            case 'accident':
              if (formData.has('has_accidents')) {
                const val = formData.get('has_accidents');
                formData.set('has_accidents', val === 'on' ? '1' : '0');
              }
              break;
          }
          
          // Recorrer todos los campos booleanos que pudieran estar
          booleanFields.forEach(field => {
            if (formData.has(field)) {
              const val = formData.get(field);
              if (val === 'on' || val === 'off') {
                formData.set(field, val === 'on' ? '1' : '0');
              }
            }
          });
        },
        
        fixAddressesBeforeSubmit(formData) {
          // Asegurarnos que los campos de dirección principal están presentes
          formData.set('lived_three_years', this.livedThreeYears ? '1' : '0');
          
          // Solo procesar direcciones adicionales si no ha vivido tres años en la dirección actual
          if (!this.livedThreeYears && this.previousAddresses.length > 0) {
            // Eliminar entradas vacías
            const validAddresses = this.previousAddresses.filter(addr => 
              addr.address_line1 && addr.city && addr.state && 
              addr.zip_code && addr.from_date && addr.to_date
            );
            
            // Actualizar formData con direcciones válidas
            validAddresses.forEach((addr, index) => {
              formData.set(`previous_addresses[${index}][address_line1]`, addr.address_line1);
              formData.set(`previous_addresses[${index}][address_line2]`, addr.address_line2 || '');
              formData.set(`previous_addresses[${index}][city]`, addr.city);
              formData.set(`previous_addresses[${index}][state]`, addr.state);
              formData.set(`previous_addresses[${index}][zip_code]`, addr.zip_code);
              formData.set(`previous_addresses[${index}][from_date]`, addr.from_date);
              formData.set(`previous_addresses[${index}][to_date]`, addr.to_date);
            });
          } else {
            // Si vive 3+ años, eliminar cualquier entrada previa
            for (const key of Array.from(formData.keys())) {
              if (key.startsWith('previous_addresses')) {
                formData.delete(key);
              }
            }
          }
        },
        
        validatePreviousAddresses() {
          let isValid = true;
          
          if (!this.livedThreeYears && this.totalYears < 3) {
            // Comprobar que cada dirección previa tenga los campos obligatorios
            this.previousAddresses.forEach((addr, index) => {
              const requiredFields = ['address_line1', 'city', 'state', 'zip_code', 'from_date', 'to_date'];
              requiredFields.forEach(field => {
                if (!addr[field]) {
                  // Marcar error
                  isValid = false;
                }
              });
              
              // Validar fechas
              if (addr.from_date && addr.to_date) {
                const fromDate = new Date(addr.from_date);
                const toDate = new Date(addr.to_date);
                if (toDate < fromDate) {
                  isValid = false;
                }
              }
            });
          }
          
          return isValid;
        }
      };
    });
  });
  
  // Función para manejar la previsualización de imágenes
  function imagePreview() {
    return {
      previewUrl: null,
      hasImage: false,
      handleFileChange(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        // Validar tipo de archivo
        if (!file.type.startsWith('image/')) {
          alert('Por favor seleccione un archivo de imagen');
          e.target.value = '';
          return;
        }
        
        // Crear URL de previsualización
        this.previewUrl = URL.createObjectURL(file);
        this.hasImage = true;
      },
      removeImage() {
        // Limpiar input file
        const input = document.getElementById('photo');
        if (input) {
          input.value = '';
        }
        
        // Restaurar imagen por defecto
        this.previewUrl = null;
        this.hasImage = false;
      }
    };
  }