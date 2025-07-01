import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

window.addEventListener('DOMContentLoaded', () => {
    const elements = document.querySelectorAll('.editor');
    
    if (elements.length) {
        elements.forEach(element => {
            ClassicEditor.create(element)
                .then(editor => {
                    // Configuración adicional si es necesaria
                    console.log('CKEditor Classic inicializado', editor);
                })
                .catch(error => {
                    console.error('Error al inicializar CKEditor:', error);
                });
        });
    }
});
