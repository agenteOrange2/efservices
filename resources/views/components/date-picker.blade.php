@props(['id', 'name', 'value' => null, 'required' => false, 'placeholder' => 'MM-DD-YYYY', 'class' => ''])

@php
    $id = $id ?? $name ?? 'date-picker-' . uniqid();
@endphp

<div class="relative">
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="text"
        placeholder="{{ $placeholder }}"
        value="{{ $value }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'date-picker form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm ' . $class]) }}
    />
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar los datepickers cuando el DOM esté listo
        initializeDatepickers();
        
        // Reinicializar cuando Livewire actualiza el DOM
        document.addEventListener('livewire:update', function() {
            setTimeout(initializeDatepickers, 100);
        });
        
        // Escuchar el evento personalizado que emitimos desde Livewire
        window.addEventListener('dom-updated', function() {
            setTimeout(initializeDatepickers, 100);
        });
        
        // Función para inicializar los datepickers
        function initializeDatepickers() {
            // Buscar todos los elementos con la clase date-picker que no estén inicializados
            document.querySelectorAll('.date-picker:not(.pikaday-initialized)').forEach(function(input) {
                // Si ya tiene una instancia de Pikaday, destruirla
                if (input._pikaday) {
                    input._pikaday.destroy();
                }
                
                // Crear instancia de Pikaday
                if (typeof Pikaday !== 'undefined') {
                    const picker = new Pikaday({
                        field: input,
                        format: 'MM-DD-YYYY',
                        bound: true,
                        reposition: true,
                        defaultDate: false,
                        setDefaultDate: false,
                        toString: function(date) {
                            if (!date) return '';
                            const month = (date.getMonth() + 1).toString().padStart(2, '0');
                            const day = date.getDate().toString().padStart(2, '0');
                            const year = date.getFullYear();
                            return `${month}-${day}-${year}`;
                        },
                        parse: function(dateString) {
                            if (!dateString) return null;
                            const parts = dateString.split('-');
                            if (parts.length === 3) {
                                return new Date(parts[2], parts[0] - 1, parts[1]);
                            }
                            return null;
                        },
                        onSelect: function() {
                            // Disparar evento de cambio para Livewire
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                            
                            // Mostrar el botón de limpiar
                            const clearButton = input.parentNode.querySelector('.clear-date-btn');
                            if (clearButton) {
                                clearButton.style.display = 'flex';
                            }
                        }
                    });
                    
                    // Guardar la instancia en el elemento
                    input._pikaday = picker;
                    
                    // Marcar como inicializado
                    input.classList.add('pikaday-initialized');
                    
                    // Agregar botón de limpiar si no existe
                    let clearButton = input.parentNode.querySelector('.clear-date-btn');
                    if (!clearButton) {
                        clearButton = document.createElement('button');
                        clearButton.type = 'button';
                        clearButton.className = 'clear-date-btn absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600';
                        clearButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>';
                        clearButton.style.display = input.value ? 'flex' : 'none';
                        
                        // Agregar evento para limpiar el campo
                        clearButton.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            picker.setDate(null);
                            input.value = '';
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                            clearButton.style.display = 'none';
                        });
                        
                        // Agregar el botón al contenedor
                        input.parentNode.appendChild(clearButton);
                    }
                    
                    // Mostrar/ocultar el botón de limpiar según haya valor o no
                    input.addEventListener('input', function() {
                        if (clearButton) {
                            clearButton.style.display = input.value ? 'flex' : 'none';
                        }
                    });
                }
            });
        }
        
        // Observar cambios en el DOM para detectar nuevos inputs
        const observer = new MutationObserver(function(mutations) {
            let hasNewInputs = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    mutation.addedNodes.forEach(function(node) {
                        // Verificar si el nodo es un elemento y tiene la clase date-picker
                        if (node.nodeType === 1 && node.classList && node.classList.contains('date-picker') && !node.classList.contains('pikaday-initialized')) {
                            hasNewInputs = true;
                        } 
                        // Verificar si el nodo contiene elementos con la clase date-picker
                        else if (node.nodeType === 1 && node.querySelectorAll) {
                            const newInputs = node.querySelectorAll('.date-picker:not(.pikaday-initialized)');
                            if (newInputs.length > 0) {
                                hasNewInputs = true;
                            }
                        }
                    });
                }
            });
            
            if (hasNewInputs) {
                setTimeout(initializeDatepickers, 100);
            }
        });
        
        // Iniciar la observación del contenedor principal
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });
</script>
