export default function imagePreview(inputId, currentPhotoUrl = null) {
    return {
        photoPreview: null,
        originalPhoto: currentPhotoUrl,
        updatePhotoPreview(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.photoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        clearPhoto() {
            this.photoPreview = null;
            document.getElementById(inputId).value = "";
        },
        resetToOriginalPhoto() {
            this.photoPreview = null;
            this.originalPhoto = currentPhotoUrl;
            document.getElementById(inputId).value = "";
        },
    };
}
