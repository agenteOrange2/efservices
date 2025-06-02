/**
 * Update the specified resource in storage.
 */
public function update(Request $request, DriverTesting $driverTesting)
{
    // Log para debug
    Log::info('Driver Testing Update - Request data', ['files' => $request->driver_testing_files]);
    $request->validate([
        'test_type' => 'required|string',
        'location' => 'required|string',
        'test_date' => 'required|date',
        'status' => 'required|string',
        'test_result' => 'required|string',
        'administered_by' => 'required|string',
        'requester_name' => 'nullable|string',
        'scheduled_time' => 'nullable|date_format:Y-m-d\TH:i',
        'notes' => 'nullable|string',
        'bill_to' => 'required|string',
    ]);
    
    // Actualizar los campos básicos
    $driverTesting->fill($request->all());
    $driverTesting->updated_by = Auth::id();
    $driverTesting->save();

    // Procesar archivos adjuntos si existen
    if ($request->has('driver_testing_files') && !empty($request->driver_testing_files)) {
        $filesData = json_decode($request->driver_testing_files, true);

        Log::info('Driver Testing Update - Files data decoded', ['filesData' => $filesData]);

        if (is_array($filesData) && count($filesData) > 0) {
            $driverId = $driverTesting->userDriverDetail->id;
            $testingId = $driverTesting->getKey();

            // Crear el directorio de destino si no existe
            $destinationDir = "public/driver/{$driverId}/testing/{$testingId}";

            if (!Storage::exists($destinationDir)) {
                Storage::makeDirectory($destinationDir);
            }

            foreach ($filesData as $fileData) {
                // Si el archivo tiene un path temporal, es un archivo recién subido
                if (isset($fileData['path']) && !empty($fileData['path'])) {
                    // Obtener el archivo temporal del disco temporal
                    $tempPath = storage_path('app/' . $fileData['path']);

                    Log::info('Driver Testing Update - Processing file', [
                        'tempPath' => $tempPath,
                        'originalName' => $fileData['original_name']
                    ]);

                    if (file_exists($tempPath)) {
                        // Verificar si el archivo ya existe para evitar duplicados
                        $fileExists = $driverTesting->getMedia('document_attachments')
                            ->where('name', $fileData['original_name'])
                            ->first();

                        if (!$fileExists) {
                            // Guardar en la colección de media para este registro
                            $driverTesting->addMedia($tempPath)
                                ->usingName($fileData['original_name'])
                                ->preservingOriginal()
                                ->toMediaCollection('document_attachments');

                            // Log de éxito
                            Log::info('Driver Testing Update - File successfully added to media collection', [
                                'testing_id' => $testingId,
                                'filename' => $fileData['original_name']
                            ]);
                        } else {
                            Log::info('Driver Testing Update - File already exists, skipping', [
                                'filename' => $fileData['original_name']
                            ]);
                        }
                    } else {
                        // Log de error si el archivo no existe
                        Log::error('Driver Testing Update - Temp file does not exist', [
                            'tempPath' => $tempPath,
                            'filename' => $fileData['original_name']
                        ]);
                    }
                } else {
                    Log::warning('Driver Testing Update - File data missing path', ['fileData' => $fileData]);
                }
            }
        } else {
            Log::warning('Driver Testing Update - No valid files data found in JSON');
        }
    } else {
        Log::info('Driver Testing Update - No files to process');
    }

    // Verificar si ha cambiado el estatus para notificar
    if ($request->status != 'pending' && $request->status != $driverTesting->getOriginal('status')) {
        // Status changed, might want to notify relevant parties here
        // TODO: Implementar notificación por cambio de estatus
    }
    
    // Regenerar el PDF con la información actualizada
    $pdf = $this->generatePDF($driverTesting);
    $testingId = $driverTesting->getKey();
    $pdfPath = storage_path('app/public/driver_testings/driver_testing_' . $testingId . '.pdf');
    
    // Asegurar que el directorio existe
    $directory = storage_path('app/public/driver_testings');
    if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
    }
    
    // Guardar el PDF
    file_put_contents($pdfPath, $pdf->output());
    
    // Eliminar PDF anterior si existe
    $driverTesting->clearMediaCollection('drug_test_pdf');
    
    // Adjuntar el nuevo PDF a la biblioteca de medios en la colección correcta
    $driverTesting->addMedia($pdfPath)
        ->toMediaCollection('drug_test_pdf');
    
    // Enviar email al conductor con el PDF adjunto actualizado
    $this->sendEmailToDriver($driverTesting);
    
    return redirect()->route('admin.driver-testings.show', ['driverTesting' => $driverTesting->getKey()])
        ->with('success', 'Drug test updated successfully. PDF has been regenerated and emailed to the driver.');
}
