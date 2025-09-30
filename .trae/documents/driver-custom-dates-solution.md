# Solución para Fechas Personalizadas en Documentos de Drivers

## 1. Análisis del Problema

### Situación Actual
- El sistema actual genera documentos PDF con fechas automáticas (`created_at` y `updated_at`)
- Los drivers se registran con fechas del momento actual
- El dueño necesita registrar drivers históricos con fechas específicas
- Los documentos deben mostrar las fechas reales de cuando el driver comenzó a trabajar
- No se puede afectar la funcionalidad existente de `livewire/driver/steps`

### Requerimientos
1. Permitir fechas personalizadas solo en el admin
2. Mantener funcionalidad actual para drivers externos
3. Agregar `created_at` y `updated_at` en documentos PDF
4. No modificar estructura de base de datos existente
5. Compatibilidad con drivers nuevos y históricos

## 2. Propuesta de Solución

### 2.1 Campos Adicionales sin Modificar BD

Agregar campos virtuales en el admin para fechas personalizadas:

```php
// En DriverRegistrationManager.php (Admin)
public $custom_registration_date = null;
public $custom_completion_date = null;
public $use_custom_dates = false;
```

### 2.2 Modificación en Admin Steps

#### A. Agregar campos en General Info Step (Admin)
```php
// En DriverGeneralInfoStep.php (Admin)
public $custom_registration_date;
public $custom_completion_date;
public $use_custom_dates = false;

protected function rules()
{
    $rules = parent::rules();
    
    if ($this->use_custom_dates) {
        $rules['custom_registration_date'] = 'required|date|before_or_equal:today';
        $rules['custom_completion_date'] = 'nullable|date|after_or_equal:custom_registration_date';
    }
    
    return $rules;
}
```

#### B. Modificar Certification Step (Admin)
```php
// En DriverCertificationStep.php (Admin)
private function getDocumentDates($userDriverDetail)
{
    // Verificar si hay fechas personalizadas en la sesión o propiedades
    $customDates = session('driver_custom_dates_' . $userDriverDetail->id);
    
    if ($customDates && $customDates['use_custom_dates']) {
        return [
            'created_at' => Carbon::parse($customDates['registration_date']),
            'updated_at' => $customDates['completion_date'] ? 
                Carbon::parse($customDates['completion_date']) : 
                Carbon::parse($customDates['registration_date'])
        ];
    }
    
    // Usar fechas reales del modelo
    return [
        'created_at' => $userDriverDetail->created_at,
        'updated_at' => $userDriverDetail->updated_at
    ];
}
```

### 2.3 Almacenamiento Temporal de Fechas

#### Opción 1: Sesión (Recomendada)
```php
// Guardar fechas personalizadas en sesión
public function saveCustomDates()
{
    if ($this->use_custom_dates) {
        session([
            'driver_custom_dates_' . $this->driverId => [
                'use_custom_dates' => true,
                'registration_date' => $this->custom_registration_date,
                'completion_date' => $this->custom_completion_date
            ]
        ]);
    }
}
```

#### Opción 2: Campo JSON en UserDriverDetail
```php
// Agregar campo metadata JSON (sin migración nueva)
// Usar campo existente o agregar uno nuevo
public function setCustomDatesAttribute($value)
{
    $metadata = json_decode($this->attributes['metadata'] ?? '{}', true);
    $metadata['custom_dates'] = $value;
    $this->attributes['metadata'] = json_encode($metadata);
}
```

### 2.4 Modificación en Generación de PDFs

```php
// En DriverCertificationStep.php
private function generateApplicationPDFs(UserDriverDetail $userDriverDetail)
{
    // Obtener fechas (personalizadas o reales)
    $documentDates = $this->getDocumentDates($userDriverDetail);
    
    // Preparar datos para PDFs
    $pdfData = [
        'userDriverDetail' => $userDriverDetail,
        'signaturePath' => $signaturePath,
        'title' => $step['title'],
        'created_at' => $documentDates['created_at']->format('d/m/Y'),
        'updated_at' => $documentDates['updated_at']->format('d/m/Y'),
        'creation_date' => $documentDates['created_at']->format('d/m/Y'),
        'last_update' => $documentDates['updated_at']->format('d/m/Y')
    ];
    
    // Generar PDFs con fechas personalizadas
    foreach ($steps as $step) {
        $pdf = App::make('dompdf.wrapper')->loadView($step['view'], $pdfData);
        // ... resto del código
    }
}
```

## 3. Implementación Paso a Paso

### Paso 1: Modificar Vista del Admin General Info
```blade
{{-- En admin/driver/steps/driver-general-info-step.blade.php --}}
<div class="mt-6 p-4 bg-blue-50 rounded-lg">
    <h4 class="font-semibold text-blue-800 mb-3">Fechas Personalizadas (Opcional)</h4>
    
    <div class="mb-4">
        <label class="flex items-center">
            <input type="checkbox" wire:model="use_custom_dates" class="mr-2">
            <span class="text-sm">Usar fechas personalizadas para este driver</span>
        </label>
    </div>
    
    @if($use_custom_dates)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha de Registro *
                </label>
                <input type="date" 
                       wire:model="custom_registration_date" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                @error('custom_registration_date') 
                    <span class="text-red-500 text-xs">{{ $message }}</span> 
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha de Finalización
                </label>
                <input type="date" 
                       wire:model="custom_completion_date" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                @error('custom_completion_date') 
                    <span class="text-red-500 text-xs">{{ $message }}</span> 
                @enderror
            </div>
        </div>
        
        <p class="text-xs text-gray-600 mt-2">
            Estas fechas se usarán en los documentos PDF generados.
        </p>
    @endif
</div>
```

### Paso 2: Actualizar Vistas PDF
```blade
{{-- En todas las vistas PDF (pdf/driver/*.blade.php) --}}
<div class="document-footer">
    <p><strong>Fecha de Creación:</strong> {{ $created_at }}</p>
    <p><strong>Última Actualización:</strong> {{ $updated_at }}</p>
</div>
```

### Paso 3: Middleware de Compatibilidad
```php
// Crear trait para manejar fechas
trait HandlesCustomDates
{
    protected function getEffectiveDates($driverId)
    {
        // Verificar si es admin y hay fechas personalizadas
        if (request()->is('admin/*')) {
            $customDates = session('driver_custom_dates_' . $driverId);
            if ($customDates && $customDates['use_custom_dates']) {
                return $customDates;
            }
        }
        
        // Usar fechas del modelo
        $driver = UserDriverDetail::find($driverId);
        return [
            'use_custom_dates' => false,
            'registration_date' => $driver->created_at,
            'completion_date' => $driver->updated_at
        ];
    }
}
```

## 4. Consideraciones de Compatibilidad

### 4.1 No Afectar Driver Steps Externos
- Los cambios solo aplican a rutas `/admin/*`
- Los steps en `livewire/driver/steps` permanecen intactos
- Usar detección de contexto para aplicar lógica personalizada

### 4.2 Retrocompatibilidad
- Drivers existentes funcionan sin cambios
- Fechas personalizadas son opcionales
- Fallback a fechas reales si no hay personalizadas

### 4.3 Validaciones
```php
protected function validateCustomDates()
{
    if ($this->use_custom_dates) {
        $this->validate([
            'custom_registration_date' => [
                'required',
                'date',
                'before_or_equal:today',
                'after:2020-01-01' // Fecha mínima razonable
            ],
            'custom_completion_date' => [
                'nullable',
                'date',
                'after_or_equal:custom_registration_date',
                'before_or_equal:today'
            ]
        ]);
    }
}
```

## 5. Flujo de Implementación

### Para Drivers Nuevos (Admin)
1. Admin marca "Usar fechas personalizadas"
2. Ingresa fecha de registro y opcionalmente fecha de finalización
3. Fechas se guardan en sesión durante el proceso
4. Al generar PDFs, se usan las fechas personalizadas

### Para Drivers Históricos (Admin)
1. Admin edita driver existente
2. Activa fechas personalizadas
3. Ingresa fechas históricas
4. Regenera documentos con nuevas fechas

### Para Drivers Externos
1. Proceso normal sin cambios
2. Fechas automáticas del sistema
3. No se ven opciones de fechas personalizadas

## 6. Archivos a Modificar

### Backend
- `app/Livewire/Admin/Driver/DriverGeneralInfoStep.php`
- `app/Livewire/Admin/Driver/DriverCertificationStep.php`
- `app/Livewire/Admin/Driver/DriverRegistrationManager.php`

### Frontend
- `resources/views/livewire/admin/driver/steps/driver-general-info-step.blade.php`
- `resources/views/pdf/driver/*.blade.php` (todas las vistas PDF)

### Nuevos Archivos
- `app/Traits/HandlesCustomDates.php`

## 7. Ventajas de Esta Solución

✅ **No modifica base de datos existente**
✅ **No afecta funcionalidad externa**
✅ **Retrocompatible al 100%**
✅ **Fácil de implementar**
✅ **Mantenible y escalable**
✅ **Permite fechas históricas**
✅ **Documentos con fechas correctas**

Esta solución permite al dueño registrar drivers históricos con sus fechas reales sin afectar el sistema existente, manteniendo la integridad y compatibilidad del código actual.