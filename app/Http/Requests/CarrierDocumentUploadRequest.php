<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class CarrierDocumentUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('user_carrier');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'document_type_id' => [
                'required',
                'integer',
                'exists:document_types,id,status,active'
            ],
            'file' => [
                'required',
                File::types(['pdf', 'jpg', 'jpeg', 'png', 'tiff', 'bmp'])
                    ->min(1) // 1KB mínimo
                    ->max(10 * 1024) // 10MB máximo
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\-\.\'",:;!?()]+$/' // Caracteres seguros para descripción
            ],
            'expiration_date' => [
                'nullable',
                'date',
                'after:today',
                'before:' . now()->addYears(10)->format('Y-m-d') // Máximo 10 años en el futuro
            ],
            'is_default' => [
                'boolean'
            ],
            'replace_existing' => [
                'boolean'
            ],
            'existing_media_id' => [
                'nullable',
                'integer',
                'exists:media,id',
                'required_if:replace_existing,true'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'document_type_id.required' => 'Please select a document type.',
            'document_type_id.exists' => 'Selected document type is not valid or inactive.',
            
            'file.required' => 'Please select a file to upload.',
            'file.mimes' => 'File must be a PDF, JPG, JPEG, PNG, TIFF, or BMP.',
            'file.max' => 'File size cannot exceed 10MB.',
            'file.min' => 'File is too small. Please select a valid document.',
            
            'description.max' => 'Description cannot exceed 500 characters.',
            'description.regex' => 'Description contains invalid characters.',
            
            'expiration_date.date' => 'Please enter a valid expiration date.',
            'expiration_date.after' => 'Expiration date must be in the future.',
            'expiration_date.before' => 'Expiration date cannot be more than 10 years in the future.',
            
            'existing_media_id.required_if' => 'Please specify which document to replace.',
            'existing_media_id.exists' => 'Selected document to replace does not exist.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'document_type_id' => 'document type',
            'file' => 'document file',
            'description' => 'description',
            'expiration_date' => 'expiration date',
            'is_default' => 'default document',
            'replace_existing' => 'replace existing',
            'existing_media_id' => 'existing document'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'description' => $this->description ? trim($this->description) : null,
            'is_default' => $this->boolean('is_default'),
            'replace_existing' => $this->boolean('replace_existing'),
            'existing_media_id' => $this->existing_media_id ? (int) $this->existing_media_id : null
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar que el archivo no esté corrupto
            if ($this->hasFile('file')) {
                $file = $this->file('file');
                
                // Verificar que el archivo se subió correctamente
                if (!$file->isValid()) {
                    $validator->errors()->add('file', 'The uploaded file is corrupted or invalid.');
                    return;
                }
                
                // Validaciones adicionales por tipo de archivo
                $this->validateFileContent($validator, $file);
                
                // Validar nombre de archivo
                $this->validateFileName($validator, $file);
            }
            
            // Validar permisos del usuario para el tipo de documento
            $this->validateDocumentTypePermissions($validator);
        });
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Illuminate\Support\Facades\Log::warning('Document upload validation failed', [
            'user_id' => auth()->id(),
            'carrier_id' => $this->getCarrierId(),
            'errors' => $validator->errors()->toArray(),
            'file_info' => $this->getFileInfo()
        ]);

        parent::failedValidation($validator);
    }

    /**
     * Get processed data for document upload.
     */
    public function getProcessedData(): array
    {
        $validated = $this->validated();
        
        return [
            'document_type_id' => $validated['document_type_id'],
            'file' => $validated['file'],
            'description' => $validated['description'],
            'expiration_date' => $validated['expiration_date'],
            'is_default' => $validated['is_default'] ?? false,
            'replace_existing' => $validated['replace_existing'] ?? false,
            'existing_media_id' => $validated['existing_media_id'],
            'uploaded_by' => auth()->id(),
            'upload_ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ];
    }

    /**
     * Validate file content based on file type.
     */
    private function validateFileContent($validator, $file)
    {
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        
        // Validar que la extensión coincida con el MIME type
        $validMimeTypes = [
            'pdf' => ['application/pdf'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'tiff' => ['image/tiff'],
            'bmp' => ['image/bmp', 'image/x-ms-bmp']
        ];
        
        if (isset($validMimeTypes[$extension])) {
            if (!in_array($mimeType, $validMimeTypes[$extension])) {
                $validator->errors()->add('file', 'File type does not match its content. Please upload a valid document.');
                return;
            }
        }
        
        // Validaciones específicas para PDFs
        if ($extension === 'pdf') {
            $this->validatePdfContent($validator, $file);
        }
        
        // Validaciones específicas para imágenes
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'tiff', 'bmp'])) {
            $this->validateImageContent($validator, $file);
        }
    }

    /**
     * Validate PDF content.
     */
    private function validatePdfContent($validator, $file)
    {
        try {
            // Verificar que el PDF no esté corrupto leyendo los primeros bytes
            $handle = fopen($file->getPathname(), 'rb');
            $header = fread($handle, 8);
            fclose($handle);
            
            if (!str_starts_with($header, '%PDF-')) {
                $validator->errors()->add('file', 'The PDF file appears to be corrupted.');
            }
        } catch (\Exception $e) {
            $validator->errors()->add('file', 'Unable to validate PDF file.');
        }
    }

    /**
     * Validate image content.
     */
    private function validateImageContent($validator, $file)
    {
        try {
            $imageInfo = getimagesize($file->getPathname());
            
            if ($imageInfo === false) {
                $validator->errors()->add('file', 'The image file appears to be corrupted.');
                return;
            }
            
            // Validar dimensiones mínimas (para legibilidad)
            if ($imageInfo[0] < 200 || $imageInfo[1] < 200) {
                $validator->errors()->add('file', 'Image must be at least 200x200 pixels for document clarity.');
            }
            
            // Validar dimensiones máximas (para rendimiento)
            if ($imageInfo[0] > 8000 || $imageInfo[1] > 8000) {
                $validator->errors()->add('file', 'Image dimensions are too large. Maximum 8000x8000 pixels.');
            }
        } catch (\Exception $e) {
            $validator->errors()->add('file', 'Unable to validate image file.');
        }
    }

    /**
     * Validate file name.
     */
    private function validateFileName($validator, $file)
    {
        $fileName = $file->getClientOriginalName();
        
        // Validar longitud del nombre
        if (strlen($fileName) > 255) {
            $validator->errors()->add('file', 'File name is too long. Maximum 255 characters.');
        }
        
        // Validar caracteres peligrosos
        if (preg_match('/[<>:"|\*\?\\\/<>]/', $fileName)) {
            $validator->errors()->add('file', 'File name contains invalid characters.');
        }
        
        // Validar que no sea un nombre reservado
        $reservedNames = ['CON', 'PRN', 'AUX', 'NUL', 'COM1', 'COM2', 'COM3', 'COM4', 'COM5', 'COM6', 'COM7', 'COM8', 'COM9', 'LPT1', 'LPT2', 'LPT3', 'LPT4', 'LPT5', 'LPT6', 'LPT7', 'LPT8', 'LPT9'];
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        
        if (in_array(strtoupper($baseName), $reservedNames)) {
            $validator->errors()->add('file', 'File name is reserved. Please rename the file.');
        }
    }

    /**
     * Validate document type permissions.
     */
    private function validateDocumentTypePermissions($validator)
    {
        if (!$this->document_type_id) {
            return;
        }
        
        $user = auth()->user();
        $carrierId = $this->getCarrierId();
        
        if (!$carrierId) {
            $validator->errors()->add('document_type_id', 'Unable to determine carrier for document upload.');
            return;
        }
        
        // Aquí se pueden agregar validaciones específicas de permisos
        // por ejemplo, ciertos tipos de documentos solo pueden ser subidos por ciertos roles
    }

    /**
     * Get carrier ID from route or user.
     */
    private function getCarrierId(): ?int
    {
        // Intentar obtener del parámetro de ruta
        $carrierSlug = $this->route('carrierSlug');
        if ($carrierSlug) {
            $carrier = \App\Models\Carrier::where('slug', $carrierSlug)->first();
            return $carrier ? $carrier->id : null;
        }
        
        // Obtener del usuario autenticado
        $user = auth()->user();
        return $user && $user->carrierDetails ? $user->carrierDetails->carrier_id : null;
    }

    /**
     * Get file information for logging.
     */
    private function getFileInfo(): array
    {
        if (!$this->hasFile('file')) {
            return [];
        }
        
        $file = $this->file('file');
        
        return [
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension()
        ];
    }
}