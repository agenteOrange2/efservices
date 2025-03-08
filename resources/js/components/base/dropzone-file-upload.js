// Archivo: dropzone-file-upload.js
import Dropzone from 'dropzone';

function initializeDropzone() {
    document.querySelectorAll("[data-hs-file-upload]").forEach(container => {
        const uploadUrl = container.getAttribute('data-hs-file-upload-url');
        const dropzoneOptions = {
            url: uploadUrl,
            autoProcessQueue: true,
            paramName: 'document',
            maxFilesize: 2,
            acceptedFiles: '.jpg,.png,.pdf,.csv,.xls,.zip',
            addRemoveLinks: true,
            dictDefaultMessage: 'Drag & Drop files here or click to upload',
            init: function () {
                this.on('success', (file, response) => {
                    console.log('File uploaded successfully:', response);
                    alert('File uploaded successfully!');
                    location.reload();
                });
                this.on('error', (file, errorMessage) => {
                    console.error('Error uploading file:', errorMessage);
                    alert('Error uploading file!');
                });
            },
        };
        new Dropzone(container, dropzoneOptions);
    });
}

window.initializeDropzone = initializeDropzone; // Agregar al objeto global
