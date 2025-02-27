<div class="bg-white p-4">
    <h3 class="text-lg font-semibold mb-4">Resumen de Registro</h3>
    
    <div class="mb-6">
      <h4 class="text-md font-medium text-gray-700 mb-2 border-b pb-1">Información Personal</h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <span class="text-sm text-gray-500">Nombre Completo:</span>
          <p class="font-medium" x-text="formatName()"></p>
        </div>
        <div>
          <span class="text-sm text-gray-500">Email:</span>
          <p class="font-medium" x-text="document.querySelector('[name=email]').value || 'No proporcionado'"></p>
        </div>
        <div>
          <span class="text-sm text-gray-500">Teléfono:</span>
          <p class="font-medium" x-text="document.querySelector('[name=phone]').value || 'No proporcionado'"></p>
        </div>
        <div>
          <span class="text-sm text-gray-500">Fecha de Nacimiento:</span>
          <p class="font-medium" x-text="formatDate(document.querySelector('[name=date_of_birth]').value)"></p>
        </div>
      </div>
    </div>
    
    <!-- Secciones similares para cada grupo de datos -->
    
    <div class="flex justify-between mt-8">
      <x-base.button type="button" @click="activeTab = 'accident'" variant="secondary">
        <i class="fas fa-arrow-left mr-1"></i> Volver a Accidentes
      </x-base.button>
      <x-base.button type="submit" @click="submissionType = 'complete'" variant="primary">
        Confirmar y Enviar <i class="fas fa-check ml-1"></i>
      </x-base.button>
    </div>
  </div>