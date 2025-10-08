# Especificación Técnica: Modificación de driver_application_details

## 1. Resumen del Proyecto

Modificación de la tabla `driver_application_details` para mejorar la flexibilidad del campo `applying_position` y establecer relaciones apropiadas con las tablas de detalles específicos por tipo de conductor y vehículos.

## 2. Cambios en Base de Datos

### 2.1 Modificaciones a la Tabla driver_application_details

**Cambios Propuestos:**

| Campo Actual | Tipo Actual | Campo Nuevo | Tipo Nuevo | Descripción |
|--------------|-------------|-------------|------------|--------------|
| applying_position | string (enum-like) | applying_position | text | Campo de entrada libre para posición |
| - | - | owner_operator_detail_id | unsignedBigInteger nullable | FK a owner_operator_details |
| - | - | third_party_detail_id | unsignedBigInteger nullable | FK a third_party_details |
| vehicle_id | foreignId nullable | vehicle_id | foreignId nullable | Mantener referencia a vehicles |

### 2.2 Nueva Migración Requerida

```sql
-- Migración: modify_driver_application_details_table.php
SCHEMA::table('driver_application_details', function (Blueprint $table) {
    // Cambiar applying_position de string a text para entrada libre
    $table->text('applying_position')->change();
    
    // Agregar referencias a tablas de detalles
    $table->unsignedBigInteger('owner_operator_detail_id')->nullable()->after('vehicle_id');
    $table->unsignedBigInteger('third_party_detail_id')->nullable()->after('owner_operator_detail_id');
    
    // Crear foreign keys
    $table->foreign('owner_operator_detail_id')->references('id')->on('owner_operator_details')->nullOnDelete();
    $table->foreign('third_party_detail_id')->references('id')->on('third_party_details')->nullOnDelete();
});
```

## 3. Modificaciones en Modelos

### 3.1 Modelo DriverApplicationDetail

**Campos a Agregar en $fillable:**
```php
'owner_operator_detail_id',
'third_party_detail_id',
```

**Nuevas Relaciones:**
```php
/**
 * Relación con detalles de Owner Operator
 */
public function ownerOperatorDetail(): BelongsTo
{
    return $this->belongsTo(OwnerOperatorDetail::class, 'owner_operator_detail_id');
}

/**
 * Relación con detalles de Third Party
 */
public function thirdPartyDetail(): BelongsTo
{
    return $this->belongsTo(ThirdPartyDetail::class, 'third_party_detail_id');
}

/**
 * Determinar el tipo de conductor basado en las relaciones
 */
public function getDriverTypeAttribute(): string
{
    if ($this->owner_operator_detail_id) {
        return 'owner_operator';
    } elseif ($this->third_party_detail_id) {
        return 'third_party_driver';
    }
    return 'company_driver';
}
```

### 3.2 Actualización de Modelos Relacionados

**OwnerOperatorDetail:**
```php
public function driverApplicationDetail(): HasOne
{
    return $this->hasOne(DriverApplicationDetail::class, 'owner_operator_detail_id');
}
```

**ThirdPartyDetail:**
```php
public function driverApplicationDetail(): HasOne
{
    return $this->hasOne(DriverApplicationDetail::class, 'third_party_detail_id');
}
```

## 4. Separación de Vistas

### 4.1 Estructura de Vistas Propuesta

**Vista Principal:** `application-step.blade.php`
- Selector de tipo de conductor (radio buttons o dropdown)
- Campo libre para `applying_position`
- Carga dinámica de secciones específicas

**Vistas Específicas por Tipo:**

1. **`partials/owner-operator-section.blade.php`**
   - Información del propietario-operador
   - Detalles del vehículo propio
   - Campos específicos de owner operator

2. **`partials/third-party-section.blade.php`**
   - Información de la empresa tercera
   - Detalles de contacto
   - Campos específicos de third party

3. **`partials/company-driver-section.blade.php`**
   - Información básica del conductor
   - Preferencias de asignación
   - Sin detalles de vehículo propio

### 4.2 Lógica de Vista Condicional

```blade
<!-- En application-step.blade.php -->
<div class="driver-type-selection">
    <label>Tipo de Conductor:</label>
    <select wire:model="driverType" wire:change="loadDriverTypeSection">
        <option value="">Seleccionar...</option>
        <option value="owner_operator">Propietario-Operador</option>
        <option value="third_party_driver">Conductor Tercero</option>
        <option value="company_driver">Conductor de Empresa</option>
    </select>
</div>

<div class="applying-position">
    <label>Posición a la que Aplica:</label>
    <textarea wire:model="applying_position" placeholder="Describa la posición específica..."></textarea>
</div>

@if($driverType === 'owner_operator')
    @include('livewire.driver.steps.partials.owner-operator-section')
@elseif($driverType === 'third_party_driver')
    @include('livewire.driver.steps.partials.third-party-section')
@elseif($driverType === 'company_driver')
    @include('livewire.driver.steps.partials.company-driver-section')
@endif
```

## 5. Modificaciones en Componente Livewire

### 5.1 ApplicationStep.php - Nuevas Propiedades

```php
public $driverType = '';
public $applying_position = '';
public $owner_operator_detail_id = null;
public $third_party_detail_id = null;
```

### 5.2 Nuevos Métodos

```php
public function loadDriverTypeSection()
{
    // Limpiar campos cuando cambia el tipo
    $this->resetDriverSpecificFields();
    
    // Cargar datos específicos si existen
    $this->loadExistingDriverDetails();
}

private function resetDriverSpecificFields()
{
    $this->owner_operator_detail_id = null;
    $this->third_party_detail_id = null;
    // Limpiar otros campos específicos
}

private function loadExistingDriverDetails()
{
    if ($this->driverType === 'owner_operator') {
        // Cargar detalles de owner operator si existen
    } elseif ($this->driverType === 'third_party_driver') {
        // Cargar detalles de third party si existen
    }
}
```

## 6. Validaciones Actualizadas

### 6.1 Reglas de Validación Dinámicas

```php
protected function rules()
{
    $rules = [
        'applying_position' => 'required|string|max:1000',
        'driverType' => 'required|in:owner_operator,third_party_driver,company_driver',
    ];
    
    // Validaciones específicas por tipo
    if ($this->driverType === 'owner_operator') {
        $rules = array_merge($rules, $this->ownerOperatorRules());
    } elseif ($this->driverType === 'third_party_driver') {
        $rules = array_merge($rules, $this->thirdPartyRules());
    }
    
    return $rules;
}
```

## 7. Proceso de Guardado Modificado

### 7.1 Lógica de Guardado por Tipo

```php
public function save()
{
    $this->validate();
    
    DB::transaction(function () {
        // Crear/actualizar detalles específicos primero
        $detailId = $this->saveDriverSpecificDetails();
        
        // Guardar application detail con referencia apropiada
        $applicationDetail = DriverApplicationDetail::updateOrCreate(
            ['driver_application_id' => $this->application->id],
            [
                'applying_position' => $this->applying_position,
                'vehicle_id' => $this->selected_vehicle_id,
                'owner_operator_detail_id' => $this->driverType === 'owner_operator' ? $detailId : null,
                'third_party_detail_id' => $this->driverType === 'third_party_driver' ? $detailId : null,
                // otros campos...
            ]
        );
    });
}

private function saveDriverSpecificDetails()
{
    if ($this->driverType === 'owner_operator') {
        return $this->saveOwnerOperatorDetails();
    } elseif ($this->driverType === 'third_party_driver') {
        return $this->saveThirdPartyDetails();
    }
    
    return null;
}
```

## 8. Consideraciones de Migración

### 8.1 Migración de Datos Existentes

```php
// Script de migración de datos
DriverApplicationDetail::chunk(100, function ($details) {
    foreach ($details as $detail) {
        // Migrar datos basados en applying_position actual
        if ($detail->applying_position === 'owner_operator') {
            // Crear owner_operator_detail y vincular
        } elseif ($detail->applying_position === 'third_party_driver') {
            // Crear third_party_detail y vincular
        }
    }
});
```

## 9. Pruebas Requeridas

### 9.1 Pruebas de Unidad
- Validación de relaciones en modelos
- Lógica de guardado por tipo de conductor
- Migración de datos existentes

### 9.2 Pruebas de Integración
- Flujo completo de aplicación por tipo
- Carga dinámica de secciones
- Validaciones condicionales

### 9.3 Pruebas de UI
- Cambio dinámico de secciones
- Validación de formularios
- Guardado correcto de datos

## 10. Cronograma de Implementación

1. **Fase 1:** Modificaciones de base de datos y modelos
2. **Fase 2:** Separación de vistas y componentes
3. **Fase 3:** Actualización de lógica Livewire
4. **Fase 4:** Migración de datos existentes
5. **Fase 5:** Pruebas y validación

## 11. Riesgos y Consideraciones

- **Compatibilidad:** Asegurar que los datos existentes se migren correctamente
- **Performance:** Las nuevas relaciones pueden impactar consultas existentes
- **UX:** La separación de vistas debe mantener una experiencia fluida
- **Validación:** Las reglas dinámicas deben cubrir todos los casos de uso