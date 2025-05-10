// Inicialización de Select2
document.addEventListener('DOMContentLoaded', function() {
    // Verificar que jQuery esté disponible
    if (typeof jQuery === 'undefined') {
        console.error('jQuery no está disponible para Select2');
        return;
    }

    // Verificar que Select2 esté disponible
    if (typeof jQuery.fn.select2 === 'undefined') {
        console.error('Select2 no está disponible');
        return;
    }

    // Inicializar Select2 para makes
    $('#make').select2({
        theme: 'bootstrap4',
        width: '100%',
        dropdownParent: document.body,
        placeholder: 'Search or add make...',
        allowClear: true,
        ajax: {
            url: document.getElementById('make').dataset.searchUrl,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(function(item) {
                        return {
                            id: item.name,
                            text: item.name
                        };
                    })
                };
            },
            cache: true
        },
        tags: true,
        createTag: function(params) {
            const term = $.trim(params.term);
            if (term === '') return null;

            return {
                id: term,
                text: term,
                isNew: true
            };
        }
    }).on('select2:select', function(e) {
        if (e.params.data.isNew) {
            // Si es una nueva marca, crear en el servidor
            $.ajax({
                url: document.getElementById('make').dataset.createUrl,
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                data: JSON.stringify({ name: e.params.data.text }),
                success: function(data) {
                    if (data.success) {
                        Toastify({
                            text: 'Make created successfully',
                            duration: 3000,
                            gravity: 'bottom',
                            position: 'right',
                            className: 'bg-success'
                        }).showToast();
                    }
                }
            });
        }
    });

    // Inicializar Select2 para types
    $('#type').select2({
        theme: 'bootstrap4',
        width: '100%',
        dropdownParent: document.body,
        placeholder: 'Search or add type...',
        allowClear: true,
        ajax: {
            url: document.getElementById('type').dataset.searchUrl,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(function(item) {
                        return {
                            id: item.name,
                            text: item.name
                        };
                    })
                };
            },
            cache: true
        },
        tags: true,
        createTag: function(params) {
            const term = $.trim(params.term);
            if (term === '') return null;

            return {
                id: term,
                text: term,
                isNew: true
            };
        }
    }).on('select2:select', function(e) {
        if (e.params.data.isNew) {
            // Si es un nuevo tipo, crear en el servidor
            $.ajax({
                url: document.getElementById('type').dataset.createUrl,
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                data: JSON.stringify({ name: e.params.data.text }),
                success: function(data) {
                    if (data.success) {
                        Toastify({
                            text: 'Type created successfully',
                            duration: 3000,
                            gravity: 'bottom',
                            position: 'right',
                            className: 'bg-success'
                        }).showToast();
                    }
                }
            });
        }
    });

    // Si hay valores antiguos, establecerlos
    const oldMake = document.getElementById('make').dataset.oldValue;
    if (oldMake) {
        const makeOption = new Option(oldMake, oldMake, true, true);
        $('#make').append(makeOption).trigger('change');
    }

    const oldType = document.getElementById('type').dataset.oldValue;
    if (oldType) {
        const typeOption = new Option(oldType, oldType, true, true);
        $('#type').append(typeOption).trigger('change');
    }
});
