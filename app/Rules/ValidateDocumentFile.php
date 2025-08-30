<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ValidateDocumentFile implements ValidationRule
{
    private array $allowedMimes;
    private array $maxSizes;
    private int $defaultMaxSize;

    public function __construct()
    {
        $this->allowedMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
            'image/gif',
            'text/plain'
        ];

        $this->maxSizes = [
            'application/pdf' => 20971520, // 20MB para PDFs
            'application/msword' => 10485760, // 10MB para DOC
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 10485760, // 10MB para DOCX
            'application/vnd.ms-excel' => 5242880, // 5MB para XLS
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 5242880, // 5MB para XLSX
            'image/jpeg' => 5242880, // 5MB para imágenes
            'image/png' => 5242880,
            'image/gif' => 2097152, // 2MB para GIF
            'text/plain' => 1048576 // 1MB para TXT
        ];

        $this->defaultMaxSize = 10485760; // 10MB por defecto
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('The file must be a valid uploaded file.');
            return;
        }

        // Validar que el archivo se subió correctamente
        if (!$value->isValid()) {
            $fail('The file upload failed. Please try again.');
            return;
        }

        // Validar tipo MIME real del archivo
        $fileMime = $value->getMimeType();
        if (!in_array($fileMime, $this->allowedMimes)) {
            $fail('The file type is not allowed. Only PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, GIF, and TXT files are permitted.');
            return;
        }

        // Validar tamaño específico por tipo de archivo
        $fileSize = $value->getSize();
        $maxSize = $this->maxSizes[$fileMime] ?? $this->defaultMaxSize;
        
        if ($fileSize > $maxSize) {
            $maxSizeMB = round($maxSize / 1048576, 1);
            $fail("The file size exceeds the maximum allowed size of {$maxSizeMB}MB for this file type.");
            return;
        }

        // Validar nombre del archivo
        $fileName = $value->getClientOriginalName();
        if (strlen($fileName) > 255) {
            $fail('The file name is too long. Maximum 255 characters allowed.');
            return;
        }

        // Validar caracteres especiales en el nombre
        $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
        if (!preg_match('/^[a-zA-Z0-9._\-\s()]+$/', $fileNameWithoutExtension)) {
            $fail('The file name contains invalid characters. Only letters, numbers, spaces, dots, hyphens, underscores, and parentheses are allowed.');
            return;
        }

        // Validar que el archivo no esté vacío
        if ($fileSize === 0) {
            $fail('The file cannot be empty.');
            return;
        }

        // Validación adicional para PDFs
        if ($fileMime === 'application/pdf') {
            $this->validatePdfFile($value, $fail);
        }

        // Validación adicional para imágenes
        if (str_starts_with($fileMime, 'image/')) {
            $this->validateImageFile($value, $fail);
        }
    }

    /**
     * Validaciones específicas para archivos PDF
     */
    private function validatePdfFile(UploadedFile $file, Closure $fail): void
    {
        // Verificar que el archivo realmente sea un PDF leyendo su header
        $handle = fopen($file->getPathname(), 'rb');
        if ($handle) {
            $header = fread($handle, 4);
            fclose($handle);
            
            if ($header !== '%PDF') {
                $fail('The file appears to be corrupted or is not a valid PDF.');
                return;
            }
        }
    }

    /**
     * Validaciones específicas para archivos de imagen
     */
    private function validateImageFile(UploadedFile $file, Closure $fail): void
    {
        // Verificar dimensiones de la imagen
        $imageInfo = getimagesize($file->getPathname());
        if ($imageInfo === false) {
            $fail('The image file is corrupted or invalid.');
            return;
        }

        [$width, $height] = $imageInfo;
        
        // Límites máximos de dimensiones (ajustables según necesidades)
        $maxWidth = 4096;
        $maxHeight = 4096;
        
        if ($width > $maxWidth || $height > $maxHeight) {
            $fail("The image dimensions are too large. Maximum allowed: {$maxWidth}x{$maxHeight} pixels.");
            return;
        }

        // Verificar que las dimensiones mínimas sean razonables
        if ($width < 10 || $height < 10) {
            $fail('The image dimensions are too small. Minimum: 10x10 pixels.');
            return;
        }
    }

    /**
     * Get allowed MIME types
     */
    public function getAllowedMimes(): array
    {
        return $this->allowedMimes;
    }

    /**
     * Get max sizes configuration
     */
    public function getMaxSizes(): array
    {
        return $this->maxSizes;
    }
}