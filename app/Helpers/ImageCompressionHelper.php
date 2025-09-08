<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class ImageCompressionHelper
{
    /**
     * Compress an uploaded image file
     *
     * @param UploadedFile $file
     * @param int $maxWidth Maximum width in pixels (default: 1200)
     * @param int $maxHeight Maximum height in pixels (default: 1200)
     * @param int $quality JPEG quality 1-100 (default: 85)
     * @return string|false Returns the compressed image path or false on failure
     */
    public static function compressImage(UploadedFile $file, $maxWidth = 1200, $maxHeight = 1200, $quality = 85)
    {
        try {
            // Validate file type
            $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($extension, $allowedTypes)) {
                Log::error('Invalid file type for compression: ' . $extension);
                return false;
            }

            // Create image instance
            $image = Image::make($file->getRealPath());
            
            // Get original dimensions
            $originalWidth = $image->width();
            $originalHeight = $image->height();
            
            Log::info('Original image dimensions', [
                'width' => $originalWidth,
                'height' => $originalHeight,
                'size' => $file->getSize()
            ]);
            
            // Calculate new dimensions while maintaining aspect ratio
            if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
                $image->resize($maxWidth, $maxHeight, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize(); // Prevent upsizing
                });
            }
            
            // Generate temporary file path
            $tempPath = tempnam(sys_get_temp_dir(), 'compressed_image_');
            
            // Save compressed image based on original format
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $image->save($tempPath, $quality, 'jpg');
                    break;
                case 'png':
                    // For PNG, convert quality (0-100) to compression level (0-9)
                    $compressionLevel = round((100 - $quality) / 11.11);
                    $image->save($tempPath, $compressionLevel, 'png');
                    break;
                case 'webp':
                    $image->save($tempPath, $quality, 'webp');
                    break;
            }
            
            // Log compression results
            $compressedSize = filesize($tempPath);
            $compressionRatio = round((($file->getSize() - $compressedSize) / $file->getSize()) * 100, 2);
            
            Log::info('Image compression completed', [
                'original_size' => $file->getSize(),
                'compressed_size' => $compressedSize,
                'compression_ratio' => $compressionRatio . '%',
                'new_dimensions' => $image->width() . 'x' . $image->height()
            ]);
            
            return $tempPath;
            
        } catch (\Exception $e) {
            Log::error('Image compression failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get file size in human readable format
     *
     * @param int $bytes
     * @return string
     */
    public static function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Check if image needs compression based on file size
     *
     * @param UploadedFile $file
     * @param int $maxSizeKB Maximum size in KB (default: 1024KB = 1MB)
     * @return bool
     */
    public static function needsCompression(UploadedFile $file, $maxSizeKB = 1024)
    {
        return ($file->getSize() /