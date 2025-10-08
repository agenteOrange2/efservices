# Gestión de Transiciones de Estado de Conductores

## 1. Problema Identificado

Cuando un conductor cambia de tipo (owner_operator → third_party_driver → company_driver), surgen desafíos críticos:
- **Preservación de historial**: Mantener registros de vehículos previamente asociados
- **Integridad referencial**: Evitar pérdida de datos en relaciones existentes
- **Asignaciones activas**: Gestionar transiciones sin interrumpir operaciones
- **Auditoría**: Rastrear cambios de estado para compliance y análisis

## 2. Arquitectura de Solución Propuesta

### 2.1 Tabla de Historial de Estados

```sql
CREATE TABLE driver_state_history (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    driver_id BIGINT UNSIGNED NOT NULL,
    previous_state ENUM('owner_operator', 'third_party_driver', 'company_driver') NULL,
    new_state ENUM('owner_operator', 'third_party_driver', 'company_driver') NOT NULL,
    transition_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reason TEXT NULL,
    metadata JSON NULL, -- Datos adicionales del cambio
    created_by BIGINT UNSIGNED NULL, -- Usuario que realizó el cambio
    
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_driver_transitions (driver_id, transition_date),
    INDEX idx_state_changes (new_state, transition_date)
);
```

### 2.2 Tabla de Asociaciones Históricas de Vehículos

```sql
CREATE TABLE driver_vehicle_history (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    driver_id BIGINT UNSIGNED NOT NULL,
    vehicle_id BIGINT UNSIGNED NOT NULL,
    relationship_type ENUM('owner_operator', 'third_party_driver') NOT NULL,
    start_date TIMESTAMP NOT NULL,
    end_date TIMESTAMP NULL,
    status ENUM('active', 'inactive', 'transferred') DEFAULT 'active',
    transition_reason TEXT NULL,
    
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    
    INDEX idx_driver_vehicle_period (driver_id, start_date, end_date),
    INDEX idx_active_relationships (driver_id, status)
);
```

## 3. Estrategias de Gestión por Tipo de Transición

### 3.1 Owner Operator → Company Driver

**Escenario**: Conductor con vehículo propio se convierte en empleado de la empresa.

**Acciones Requeridas**:
1. **Preservar Propiedad del Vehículo**:
   ```php
   // El vehículo permanece en la tabla vehicles
   // Se actualiza owner_operator_details.status = 'inactive'
   // Se mantiene la relación histórica
   ```

2. **Crear Nuevo Registro Company Driver**:
   ```php
   CompanyDriverDetail::create([
       'driver_id' => $driver->id,
       'employee_id' => $newEmployeeId,
       'hire_date' => now(),
       'status' => 'active'
   ]);
   ```

3. **Registrar Transición**:
   ```php
   DriverStateHistory::create([
       'driver_id' => $driver->id,
       'previous_state' => 'owner_operator',
       'new_state' => 'company_driver',
       'reason' => 'Hired as company employee',
       'metadata' => json_encode([
           'previous_vehicles' => $driver->ownedVehicles->pluck('id'),
           'transition_type' => 'employment_change'
       ])
   ]);
   ```

### 3.2 Third Party Driver → Company Driver

**Escenario**: Conductor de empresa tercera se une directamente a la empresa.

**Acciones Requeridas**:
1. **Finalizar Relación Third Party**:
   ```php
   $thirdPartyDetail = $driver->thirdPartyDetail;
   $thirdPartyDetail->update([
       'end_date' => now(),
       'status' => 'terminated',
       'termination_reason' => 'Hired by carrier'
   ]);
   ```

2. **Transferir Conocimiento de Vehículos**:
   ```php
   // Los vehículos third party no se transfieren
   // Se mantiene solo el historial de asociación
   DriverVehicleHistory::where('driver_id', $driver->id)
       ->where('status', 'active')
       ->update([
           'end_date' => now(),
           'status' => 'transferred',
           'transition_reason' => 'Driver hired by carrier'
       ]);
   ```

### 3.3 Company Driver → Owner Operator

**Escenario**: Empleado adquiere vehículo propio y se independiza.

**Acciones Requeridas**:
1. **Finalizar Empleo**:
   ```php
   $companyDetail = $driver->companyDriverDetail;
   $companyDetail->update([
       'termination_date' => now(),
       'status' => 'terminated',
       'termination_reason' => 'Became owner operator'
   ]);
   ```

2. **Registrar Nuevo Vehículo**:
   ```php
   $vehicle = Vehicle::create($vehicleData);
   
   OwnerOperatorDetail::create([
       'driver_id' => $driver->id,
       'vehicle_id' => $vehicle->id,
       'ownership_start_date' => now()
   ]);
   ```

## 4. Lógica de Negocio para Transiciones

### 4.1 Servicio de Transición de Estado

```php
class DriverStateTransitionService
{
    public function transitionDriverState(
        Driver $driver, 
        string $newState, 
        array $transitionData = []
    ): bool {
        DB::transaction(function () use ($driver, $newState, $transitionData) {
            $currentState = $this->getCurrentDriverState($driver);
            
            // Validar transición permitida
            $this->validateTransition($currentState, $newState);
            
            // Ejecutar lógica específica de transición
            $this->executeTransitionLogic($driver, $currentState, $newState, $transitionData);
            
            // Registrar en historial
            $this->recordStateTransition($driver, $currentState, $newState, $transitionData);
            
            // Actualizar estado actual del conductor
            $this->updateDriverCurrentState($driver, $newState);
        });
        
        return true;
    }
    
    private function executeTransitionLogic(
        Driver $driver, 
        string $currentState, 
        string $newState, 
        array $data
    ): void {
        $transitionKey = "{$currentState}_to_{$newState}";
        
        switch ($transitionKey) {
            case 'owner_operator_to_company_driver':
                $this->handleOwnerToCompanyTransition($driver, $data);
                break;
                
            case 'third_party_driver_to_company_driver':
                $this->handleThirdPartyToCompanyTransition($driver, $data);
                break;
                
            case 'company_driver_to_owner_operator':
                $this->handleCompanyToOwnerTransition($driver, $data);
                break;
                
            // Más casos según necesidades
        }
    }
}
```

### 4.2 Validaciones de Transición

```php
private function validateTransition(string $currentState, string $newState): void
{
    $allowedTransitions = [
        'owner_operator' => ['company_driver', 'third_party_driver'],
        'third_party_driver' => ['company_driver', 'owner_operator'],
        'company_driver' => ['owner_operator', 'third_party_driver']
    ];
    
    if (!in_array($newState, $allowedTransitions[$currentState] ?? [])) {
        throw new InvalidTransitionException(
            "Transition from {$currentState} to {$newState} is not allowed"
        );
    }
    
    // Validaciones adicionales específicas
    $this->validateTransitionRequirements($currentState, $newState);
}
```

## 5. Gestión de Asignaciones Activas

### 5.1 Impacto en Asignaciones Existentes

```php
class AssignmentTransitionHandler
{
    public function handleActiveAssignments(Driver $driver, string $newState): void
    {
        $activeAssignments = VehicleDriverAssignment::where('driver_id', $driver->id)
            ->where('status', 'active')
            ->get();
            
        foreach ($activeAssignments as $assignment) {
            switch ($newState) {
                case 'company_driver':
                    // Company drivers pueden mantener asignaciones
                    $this->validateCompanyDriverAssignment($assignment);
                    break;
                    
                case 'owner_operator':
                    // Owner operators solo pueden usar sus propios vehículos
                    $this->handleOwnerOperatorAssignment($assignment, $driver);
                    break;
                    
                case 'third_party_driver':
                    // Third party drivers usan vehículos de su empresa
                    $this->handleThirdPartyAssignment($assignment, $driver);
                    break;
            }
        }
    }
    
    private function handleOwnerOperatorAssignment(
        VehicleDriverAssignment $assignment, 
        Driver $driver
    ): void {
        $ownedVehicles = $driver->ownedVehicles->pluck('id');
        
        if (!$ownedVehicles->contains($assignment->vehicle_id)) {
            // Terminar asignación de vehículo no propio
            $assignment->update([
                'status' => 'terminated',
                'end_date' => now(),
                'termination_reason' => 'Driver became owner operator - vehicle not owned'
            ]);
            
            // Crear nueva asignación con vehículo propio si existe
            if ($ownedVehicles->isNotEmpty()) {
                VehicleDriverAssignment::create([
                    'driver_id' => $driver->id,
                    'vehicle_id' => $ownedVehicles->first(),
                    'assignment_type' => 'owner_operator',
                    'start_date' => now(),
                    'status' => 'active'
                ]);
            }
        }
    }
}
```

## 6. Consultas y Reportes Históricos

### 6.1 Historial Completo de Conductor

```php
class DriverHistoryService
{
    public function getCompleteDriverHistory(int $driverId): array
    {
        return [
            'state_transitions' => $this->getStateTransitions($driverId),
            'vehicle_history' => $this->getVehicleHistory($driverId),
            'assignment_history' => $this->getAssignmentHistory($driverId),
            'current_status' => $this->getCurrentStatus($driverId)
        ];
    }
    
    private function getVehicleHistory(int $driverId): Collection
    {
        return DriverVehicleHistory::where('driver_id', $driverId)
            ->with(['vehicle', 'driver'])
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function ($history) {
                return [
                    'vehicle' => $history->vehicle->make . ' ' . $history->vehicle->model,
                    'relationship' => $history->relationship_type,
                    'period' => $history->start_date->format('Y-m-d') . ' - ' . 
                               ($history->end_date ? $history->end_date->format('Y-m-d') : 'Present'),
                    'status' => $history->status
                ];
            });
    }
}
```

### 6.2 Reportes de Transiciones

```php
public function getTransitionReport(Carbon $startDate, Carbon $endDate): array
{
    $transitions = DriverStateHistory::whereBetween('transition_date', [$startDate, $endDate])
        ->with('driver')
        ->get()
        ->groupBy(['previous_state', 'new_state']);
        
    return [
        'summary' => $this->generateTransitionSummary($transitions),
        'details' => $transitions,
        'trends' => $this->analyzeTransitionTrends($transitions)
    ];
}
```

## 7. Consideraciones de Implementación

### 7.1 Migración de Datos Existentes

```php
// Migración para poblar historial existente
public function populateExistingHistory(): void
{
    Driver::chunk(100, function ($drivers) {
        foreach ($drivers as $driver) {
            $currentState = $this->determineCurrentState($driver);
            
            DriverStateHistory::create([
                'driver_id' => $driver->id,
                'previous_state' => null, // Estado inicial
                'new_state' => $currentState,
                'transition_date' => $driver->created_at,
                'reason' => 'Initial state from migration'
            ]);
            
            // Poblar historial de vehículos
            $this->populateVehicleHistory($driver);
        }
    });
}
```

### 7.2 Eventos y Notificaciones

```php
class DriverStateTransitioned
{
    public function __construct(
        public Driver $driver,
        public string $previousState,
        public string $newState,
        public array $metadata = []
    ) {}
}

// Listener para notificaciones
class NotifyStateTransition
{
    public function handle(DriverStateTransitioned $event): void
    {
        // Notificar a administradores
        // Actualizar sistemas externos
        // Generar reportes automáticos
    }
}
```

## 8. Beneficios de la Solución

### 8.1 Integridad de Datos
- **Preservación completa** del historial de relaciones
- **Trazabilidad total** de cambios de estado
- **Consistencia referencial** mantenida en todas las transiciones

### 8.2 Flexibilidad Operacional
- **Transiciones fluidas** sin pérdida de información
- **Reversibilidad** de cambios cuando sea necesario
- **Adaptabilidad** a nuevos tipos de conductor en el futuro

### 8.3 Compliance y Auditoría
- **Registro completo** de todos los cambios
- **Razones documentadas** para cada transición
- **Reportes históricos** para análisis y compliance

## 9. Casos de Uso Específicos

### 9.1 Conductor con Múltiples Vehículos

**Escenario**: Owner operator con 2 vehículos propios cambia a company driver.

**Solución**:
```php
// Los vehículos permanecen en su propiedad
// Se crean registros históricos para cada vehículo
// Se permite reactivación futura como owner operator

foreach ($driver->ownedVehicles as $vehicle) {
    DriverVehicleHistory::create([
        'driver_id' => $driver->id,
        'vehicle_id' => $vehicle->id,
        'relationship_type' => 'owner_operator',
        'start_date' => $vehicle->ownership_start_date,
        'end_date' => now(),
        'status' => 'inactive',
        'transition_reason' => 'Driver became company employee'
    ]);
}
```

### 9.2 Reactivación de Estado Anterior

**Escenario**: Company driver quiere volver a ser owner operator con sus vehículos anteriores.

**Solución**:
```php
public function reactivatePreviousState(Driver $driver, string $targetState): bool
{
    $previousHistory = DriverVehicleHistory::where('driver_id', $driver->id)
        ->where('relationship_type', $targetState)
        ->where('status', 'inactive')
        ->get();
        
    foreach ($previousHistory as $history) {
        // Reactivar relación con vehículo
        $history->update([
            'status' => 'active',
            'start_date' => now() // Nueva fecha de inicio
        ]);
    }
    
    return true;
}
```

## 10. Conclusión

Esta arquitectura proporciona una solución robusta para manejar las transiciones de estado de conductores, preservando la integridad de los datos históricos mientras permite flexibilidad operacional. La implementación gradual y la migración cuidadosa de datos existentes aseguran una transición suave al nuevo sistema.