@props([
    'name' => 'profile_photo',        // Nombre del campo input
    'id' => 'profile_photo_input',   // ID del input
    'currentPhotoUrl' => null,       // URL de la foto actual
    'defaultPhotoUrl' => asset('build/default_profile.png'), // URL de la foto predeterminada
    'deleteUrl' => null,             // URL para eliminar la foto
])

<div x-data="{
        photoPreview: null,
        originalPhoto: '{{ $currentPhotoUrl ?? '' }}',
        defaultPhoto: '{{ $defaultPhotoUrl ?? '' }}',
        deleteUrl: '{{ $deleteUrl ?? '' }}',
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
            if (!this.deleteUrl) {
                console.warn('Delete URL is not defined.');
                return;
            }
            fetch(this.deleteUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Content-Type': 'application/json',
                },
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to delete the photo.');
                }
                return response.json();
            })
            .then(data => {
                this.originalPhoto = this.defaultPhoto;
                this.photoPreview = null;
                console.log('Photo deleted successfully.');
            })
            .catch(error => console.error('Error:', error));
        }
    }"
    class="flex items-center">
    <!-- Foto de perfil -->
    <div class="relative flex h-24 w-24 items-center justify-center rounded-full border border-primary/10 bg-primary/5">
        <template x-if="photoPreview">
            <img :src="photoPreview" alt="Preview" class="h-full w-full rounded-full object-cover">
        </template>
        <template x-if="!photoPreview && originalPhoto">
            <img :src="originalPhoto" alt="Original Photo" class="h-full w-full rounded-full object-cover">
        </template>
        <template x-if="!photoPreview && !originalPhoto">
            <img :src="defaultPhoto" alt="Default Photo" class="h-full w-full rounded-full object-cover">
        </template>
        <label for="{{ $id }}" class="box absolute bottom-0 right-0 flex h-7 w-7 items-center justify-center rounded-full cursor-pointer">
            <x-base.lucide class="h-3.5 w-3.5 stroke-[1.3] text-slate-500" icon="Pencil" />
        </label>
    </div>

    <!-- Input para subir archivo -->
    <input type="file" name="{{ $name }}" id="{{ $id }}" class="hidden" accept="image/*" @change="updatePhotoPreview">

    <!-- Botón para eliminar imagen -->
    <x-base.button class="ml-8 mr-2 h-8 pl-3.5 pr-4" variant="outline-secondary" size="sm" @click.prevent="clearPhoto">
        <x-base.lucide class="mr-1.5 h-3.5 w-3.5 stroke-[1.3]" icon="Trash2" />
        Remove
    </x-base.button>
</div>
