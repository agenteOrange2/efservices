// Clase para manejar selects personalizados con vanilla JS
class CustomSelect {
    constructor(element, options = {}) {
        this.element = element;
        this.options = {
            placeholder: options.placeholder || 'Selecciona una opción',
            searchUrl: options.searchUrl || '',
            createUrl: options.createUrl || '',
            csrfToken: options.csrfToken || '',
            onSelect: options.onSelect || (() => {}),
            onCreate: options.onCreate || (() => {})
        };

        this.init();
    }

    init() {
        // Crear el contenedor
        this.container = document.createElement('div');
        this.container.className = 'custom-select-container';
        this.element.parentNode.insertBefore(this.container, this.element);
        this.container.appendChild(this.element);

        // Crear el input de búsqueda
        this.searchInput = document.createElement('input');
        this.searchInput.type = 'text';
        this.searchInput.className = 'custom-select-search py-2 px-3 block w-full border-gray-200 rounded-md text-sm';
        this.searchInput.placeholder = this.options.placeholder;
        this.container.appendChild(this.searchInput);

        // Crear el dropdown
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'custom-select-dropdown hidden absolute z-50 w-full bg-white border border-gray-200 rounded-md shadow-lg mt-1';
        this.container.appendChild(this.dropdown);

        // Event listeners
        this.searchInput.addEventListener('input', this.debounce(() => this.search(), 250));
        this.searchInput.addEventListener('focus', () => this.showDropdown());
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.hideDropdown();
            }
        });
    }

    async search() {
        const query = this.searchInput.value;
        if (!query) {
            this.hideDropdown();
            return;
        }

        try {
            const response = await fetch(`${this.options.searchUrl}?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            this.dropdown.innerHTML = '';
            
            // Mostrar resultados existentes
            data.forEach(item => {
                const option = document.createElement('div');
                option.className = 'custom-select-option p-2 hover:bg-gray-100 cursor-pointer';
                option.textContent = item.name;
                option.addEventListener('click', () => this.selectOption(item));
                this.dropdown.appendChild(option);
            });

            // Opción para crear nuevo
            if (query && !data.some(item => item.name.toLowerCase() === query.toLowerCase())) {
                const createOption = document.createElement('div');
                createOption.className = 'custom-select-create p-2 hover:bg-primary hover:text-white cursor-pointer';
                createOption.textContent = `Crear "${query}"`;
                createOption.addEventListener('click', () => this.createOption(query));
                this.dropdown.appendChild(createOption);
            }

            this.showDropdown();
        } catch (error) {
            console.error('Error searching:', error);
        }
    }

    async createOption(name) {
        try {
            const response = await fetch(this.options.createUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.options.csrfToken
                },
                body: JSON.stringify({ name })
            });

            const data = await response.json();
            
            if (data.success) {
                this.selectOption(data.item);
                this.options.onCreate(data.item);
                
                // Mostrar notificación
                if (window.Toastify) {
                    window.Toastify({
                        text: `${name} creado exitosamente`,
                        duration: 3000,
                        gravity: 'bottom',
                        position: 'right',
                        className: 'bg-success'
                    }).showToast();
                }
            }
        } catch (error) {
            console.error('Error creating option:', error);
        }
    }

    selectOption(item) {
        this.searchInput.value = item.name;
        this.element.value = item.name;
        this.hideDropdown();
        this.options.onSelect(item);
    }

    showDropdown() {
        if (this.dropdown.children.length > 0) {
            this.dropdown.classList.remove('hidden');
        }
    }

    hideDropdown() {
        this.dropdown.classList.add('hidden');
    }

    debounce(func, wait) {
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
}

// Exportar la clase globalmente
window.CustomSelect = CustomSelect;
