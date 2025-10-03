## 1. Architecture design

```mermaid
graph TD
    A[User Browser] --> B[Laravel Frontend Views]
    B --> C[MedicalRecordsController]
    C --> D[DriverMedicalQualification Model]
    C --> E[Spatie Media Library]
    C --> F[Notification System]
    D --> G[MySQL Database]
    E --> H[File Storage]
    F --> I[Email Service]

    subgraph "Frontend Layer"
        B
    end

    subgraph "Controller Layer"
        C
    end

    subgraph "Model & Service Layer"
        D
        E
        F
    end

    subgraph "Data Layer"
        G
        H
    end

    subgraph "External Services"
        I
    end
```

## 2. Technology Description

- Frontend: Laravel Blade Templates + TailwindCSS + Lucide Icons + Alpine.js
- Backend: Laravel 10 + Spatie Media Library + Laravel Notifications
- Database: MySQL (existing structure)
- File Storage: Local Storage (public disk)
- Authentication: Laravel Sanctum (existing)

## 3. Route definitions

| Route | Purpose |
|-------|----------|
| /admin/medical-records | Lista principal de registros médicos con filtros y paginación |
| /admin/medical-records/create | Formulario para crear nuevo registro médico |
| /admin/medical-records/{id} | Vista detallada de un registro médico específico |
| /admin/medical-records/{id}/edit | Formulario para editar registro médico existente |
| /admin/medical-records/{id}/documents | Gestión de documentos del registro médico |
| /admin/medical-records/dashboard | Dashboard de vencimientos y alertas |
| /admin/medical-records/export | Exportación de datos a Excel/PDF |

## 4. API definitions

### 4.1 Core API

**Listar registros médicos**
```
GET /admin/medical-records
```

Request Parameters:
| Param Name | Param Type | isRequired | Description |
|------------|------------|------------|-------------|
| search_term | string | false | Término de búsqueda para filtrar registros |
| status | string | false | Filtro por estado (active, expired, pending) |
| driver_id | integer | false | ID del conductor para filtrar |
| expiration_from | date | false | Fecha inicio para filtro de vencimiento |
| expiration_to | date | false | Fecha fin para filtro de vencimiento |

Response:
| Param Name | Param Type | Description |
|------------|------------|-------------|
| data | array | Lista paginada de registros médicos |
| pagination | object | Información de paginación |
| statistics | object | Contadores de estado |

**Crear registro médico**
```
POST /admin/medical-records
```

Request:
| Param Name | Param Type | isRequired | Description |
|------------|------------|------------|-------------|
| user_driver_detail_id | integer | true | ID del conductor |
| medical_record_number | string | true | Número único del registro médico |
| medical_examiner_name | string | true | Nombre del médico examinador |
| medical_center_name | string | true | Nombre del centro médico |
| examination_date | date | true | Fecha del examen médico |
| expiration_date | date | true | Fecha de vencimiento del certificado |
| status | string | true | Estado del registro (active, expired, pending) |

Response:
| Param Name | Param Type | Description |
|------------|------------|-------------|
| success | boolean | Estado de la operación |
| data | object | Datos del registro creado |
| message | string | Mensaje de confirmación |

**Subir documentos**
```
POST /admin/medical-records/{id}/upload-documents
```

Request:
| Param Name | Param Type | isRequired | Description |
|------------|------------|------------|-------------|
| documents | file[] | true | Archivos de documentos médicos |
| collection | string | true | Tipo de documento (medical_certificate, test_results, additional) |

Response:
| Param Name | Param Type | Description |
|------------|------------|-------------|
| success | boolean | Estado de la operación |
| uploaded_files | array | Lista de archivos subidos |

## 5. Server architecture diagram

```mermaid
graph TD
    A[HTTP Request] --> B[Route Middleware]
    B --> C[MedicalRecordsController]
    C --> D[Request Validation]
    D --> E[Business Logic Layer]
    E --> F[Model Layer]
    F --> G[Database Operations]
    E --> H[Media Library Service]
    H --> I[File Storage]
    E --> J[Notification Service]
    J --> K[Email Queue]

    subgraph "Controller Layer"
        C
        D
    end

    subgraph "Service Layer"
        E
        H
        J
    end

    subgraph "Data Layer"
        F
        G
        I
    end

    subgraph "Queue Layer"
        K
    end
```

## 6. Data model

### 6.1 Data model definition

```mermaid
erDiagram
    USER_DRIVER_DETAILS ||--o{ DRIVER_MEDICAL_QUALIFICATIONS : has
    DRIVER_MEDICAL_QUALIFICATIONS ||--o{ MEDIA : stores
    DRIVER_MEDICAL_QUALIFICATIONS ||--o{ MEDICAL_RECORD_NOTIFICATIONS : generates
    CARRIERS ||--o{ USER_DRIVER_DETAILS : employs

    USER_DRIVER_DETAILS {
        int id PK
        int user_id FK
        int carrier_id FK
        string first_name
        string last_name
        datetime created_at
        datetime updated_at
    }

    DRIVER_MEDICAL_QUALIFICATIONS {
        int id PK
        int user_driver_detail_id FK
        string medical_record_number
        string medical_examiner_name
        string medical_examiner_registry_number
        string medical_center_name
        date examination_date
        date medical_card_expiration_date
        string status
        boolean is_suspended
        date suspension_date
        boolean is_terminated
        date termination_date
        text notes
        datetime created_at
        datetime updated_at
    }

    MEDIA {
        int id PK
        string model_type
        int model_id FK
        string collection_name
        string name
        string file_name
        string mime_type
        int size
        datetime created_at
        datetime updated_at
    }

    MEDICAL_RECORD_NOTIFICATIONS {
        int id PK
        int medical_qualification_id FK
        string notification_type
        date scheduled_date
        boolean sent
        datetime sent_at
        datetime created_at
        datetime updated_at
    }

    CARRIERS {
        int id PK
        string name
        string email
        string phone
        datetime created_at
        datetime updated_at
    }
```

### 6.2 Data Definition Language

**Actualización de tabla driver_medical_qualifications**
```sql
-- Agregar campos adicionales para el sistema de medical records
ALTER TABLE driver_medical_qualifications ADD COLUMN IF NOT EXISTS medical_record_number VARCHAR(255) UNIQUE;
ALTER TABLE driver_medical_qualifications ADD COLUMN IF NOT EXISTS medical_center_name VARCHAR(255);
ALTER TABLE driver_medical_qualifications ADD COLUMN IF NOT EXISTS examination_date DATE;
ALTER TABLE driver_medical_qualifications ADD COLUMN IF NOT EXISTS status ENUM('active', 'expired', 'pending', 'suspended') DEFAULT 'active';
ALTER TABLE driver_medical_qualifications ADD COLUMN IF NOT EXISTS notes TEXT;

-- Crear índices para optimizar consultas
CREATE INDEX idx_medical_qualifications_status ON driver_medical_qualifications(status);
CREATE INDEX idx_medical_qualifications_expiration ON driver_medical_qualifications(medical_card_expiration_date);
CREATE INDEX idx_medical_qualifications_driver ON driver_medical_qualifications(user_driver_detail_id);
CREATE INDEX idx_medical_qualifications_record_number ON driver_medical_qualifications(medical_record_number);
```

**Tabla para notificaciones de registros médicos**
```sql
-- Crear tabla para gestionar notificaciones automáticas
CREATE TABLE medical_record_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    medical_qualification_id BIGINT UNSIGNED NOT NULL,
    notification_type ENUM('30_days', '60_days', '90_days', 'expired') NOT NULL,
    scheduled_date DATE NOT NULL,
    sent BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (medical_qualification_id) REFERENCES driver_medical_qualifications(id) ON DELETE CASCADE,
    INDEX idx_notifications_scheduled (scheduled_date, sent),
    INDEX idx_notifications_medical_id (medical_qualification_id)
);
```

**Configuración de colecciones de media para documentos médicos**
```sql
-- Los documentos se gestionan a través de Spatie Media Library
-- Las colecciones definidas en el modelo son:
-- 'medical_certificate' - Certificados médicos principales
-- 'test_results' - Resultados de exámenes y pruebas
-- 'additional_documents' - Documentos adicionales relacionados

-- Datos iniciales para testing
INSERT INTO driver_medical_qualifications (
    user_driver_detail_id, 
    medical_record_number, 
    medical_examiner_name, 
    medical_examiner_registry_number, 
    medical_center_name,
    examination_date,
    medical_card_expiration_date, 
    status
) VALUES 
(1, 'MED-2024-001', 'Dr. John Smith', 'REG123456', 'City Medical Center', '2024-01-15', '2026-01-15', 'active'),
(2, 'MED-2024-002', 'Dr. Sarah Johnson', 'REG789012', 'Highway Health Clinic', '2024-02-20', '2026-02-20', 'active');
```