{{-- Ejemplo de uso correcto del componente unified-image-upload --}}
{{-- Este archivo muestra cómo usar el componente con los props necesarios para el proceso de dos pasos --}}

@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Ejemplo de Uso: Unified Image Upload</h1>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Subir Documentos de Conductor</h2>
        
        {{-- Ejemplo 1: Subir documento de licencia --}}
        <div class="mb-8">
            <h3 class="text-md font-medium mb-3">Licencia de Conducir</h3>
            <x-unified-image-upload
                :model-type="'driver_detail'"
                :model-id="$driverDetail->id ?? 1"
                collection="license_documents"
                :multiple="false"
                :max-size="5"
                accept="image/*"
                label="Subir Imagen de Licencia"
                help-text="Sube una imagen clara de tu licencia de conducir"
                :custom-properties="['document_type' => 'license_front']"
                class="w-full"
            />
        </div>
        
        {{-- Ejemplo 2: Subir múltiples documentos médicos --}}
        <div class="mb-8">
            <h3 class="text