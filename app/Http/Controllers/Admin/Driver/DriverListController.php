<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Carrier;
use Illuminate\Http\Request;

class DriverListController extends Controller
{
    /**
     * Display a listing of approved drivers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->input('search', '');
        $carrierFilter = $request->input('carrier', '');
        $perPage = $request->input('per_page', 10);

        // Base query for approved drivers
        $query = UserDriverDetail::with(['user', 'carrier', 'application'])
            ->whereHas('application', function($q) {
                $q->where('status', DriverApplication::STATUS_APPROVED);
            })
            ->orderBy('created_at', 'desc');

        // Apply search filter if provided
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                })
                ->orWhere('last_name', 'like', '%' . $search . '%')
                ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        // Apply carrier filter if provided
        if (!empty($carrierFilter)) {
            $query->where('carrier_id', $carrierFilter);
        }

        // Get paginated results
        $drivers = $query->paginate($perPage);
        
        // Get all carriers for the filter dropdown
        $carriers = Carrier::orderBy('name')->get();

        // Calculate completion percentage for each driver
        foreach ($drivers as $driver) {
            $driver->completion_percentage = $this->calculateProfileCompleteness($driver);
        }

        return view('admin.drivers.list-driver.index', [
            'drivers' => $drivers,
            'carriers' => $carriers,
            'search' => $search,
            'carrierFilter' => $carrierFilter,
            'perPage' => $perPage
        ]);
    }

    /**
     * Calculate profile completeness percentage for a driver
     *
     * @param  \App\Models\UserDriverDetail  $driver
     * @return int
     */
    private function calculateProfileCompleteness(UserDriverDetail $driver)
    {
        $completedSteps = 0;
        $totalSteps = 6; // Total number of steps in driver registration

        // Check if basic info is complete
        if ($driver->user && $driver->user->email && $driver->phone) {
            $completedSteps++;
        }

        // Check if license info is complete
        if ($driver->licenses()->exists()) {
            $completedSteps++;
        }

        // Check if medical info is complete
        if ($driver->medicalQualification()->exists()) {
            $completedSteps++;
        }

        // Check if experience/training info is complete
        if ($driver->experiences()->exists() || $driver->trainingSchools()->exists()) {
            $completedSteps++;
        }

        // Check if employment history is complete
        if ($driver->employmentCompanies()->exists()) {
            $completedSteps++;
        }

        // Check if all documents are uploaded
        if ($driver->hasRequiredDocuments()) {
            $completedSteps++;
        }

        return round(($completedSteps / $totalSteps) * 100);
    }

    /**
     * Show the details for a specific driver.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $driver = UserDriverDetail::with([
            'user', 
            'carrier', 
            'application',
            'licenses',
            'medicalQualification',
            'experiences',
            'trainingSchools',
            'trafficConvictions',
            'accidents',
            'employmentCompanies',
        ])->findOrFail($id);

        return view('admin.drivers.list-driver.driver-show', [
            'driver' => $driver
        ]);
    }

    /**
     * Deactivate a driver.
     *
     * @param  \App\Models\UserDriverDetail  $driver
     * @return \Illuminate\Http\Response
     */
    public function deactivate(UserDriverDetail $driver)
    {
        $driver->status = UserDriverDetail::STATUS_INACTIVE;
        $driver->save();

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver has been deactivated.');
    }

    /**
     * Activate a driver.
     *
     * @param  \App\Models\UserDriverDetail  $driver
     * @return \Illuminate\Http\Response
     */
    public function activate(UserDriverDetail $driver)
    {
        $driver->status = UserDriverDetail::STATUS_ACTIVE;
        $driver->save();

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver has been activated.');
    }

    /**
     * Download driver documents as ZIP.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function downloadDocuments($id)
    {
        $driver = UserDriverDetail::findOrFail($id);
        $zipFileName = 'driver_' . $id . '_documents_' . date('Y-m-d') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);
        
        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        $zip = new \ZipArchive();
        
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            // Add license documents
            if ($driver->licenses && $driver->licenses->count() > 0) {
                foreach ($driver->licenses as $license) {
                    $this->addMediaToZip($zip, $license, 'license_front', 'Licenses/License_' . $license->license_number . '_Front');
                    $this->addMediaToZip($zip, $license, 'license_back', 'Licenses/License_' . $license->license_number . '_Back');
                }
            }
            
            // Add medical card
            if ($driver->medicalQualification) {
                $this->addMediaToZip($zip, $driver->medicalQualification, 'medical_card', 'Medical/Medical_Card');
            }
            
            // Add training certificates
            foreach ($driver->trainingSchools as $school) {
                $certificates = $school->getMedia('school_certificates');
                foreach ($certificates as $index => $certificate) {
                    $localName = 'Training/' . $school->school_name . '/Certificate_' . ($index + 1) . '.' . $certificate->extension;
                    $zip->addFile($certificate->getPath(), $localName);
                }
            }
            
            // Add application PDF
            if ($driver->application && $driver->application->hasMedia('application_pdf')) {
                $applicationPdf = $driver->application->getFirstMedia('application_pdf');
                $zip->addFile($applicationPdf->getPath(), 'Application/Complete_Application.pdf');
            }
            
            // Add lease agreement documents
            $basePath = storage_path('app/public/driver/' . $driver->id . '/vehicle_verifications/');
            $leaseAgreementThirdPartyPath = $basePath . 'lease_agreement_third_party.pdf';
            $leaseAgreementOwnerPath = $basePath . 'lease_agreement_owner_operator.pdf';
            
            if (file_exists($leaseAgreementThirdPartyPath)) {
                $zip->addFile($leaseAgreementThirdPartyPath, 'Lease_Agreements/Third_Party_Lease_Agreement.pdf');
            }
            
            if (file_exists($leaseAgreementOwnerPath)) {
                $zip->addFile($leaseAgreementOwnerPath, 'Lease_Agreements/Owner_Operator_Lease_Agreement.pdf');
            }
            
            $zip->close();
            
            return response()->download($zipPath)->deleteFileAfterSend(true);
        }
        
        return back()->with('error', 'Could not create ZIP file');
    }
    
    /**
     * Helper method to add media to ZIP file
     */
    private function addMediaToZip($zip, $model, $collection, $zipPath)
    {
        if ($model->hasMedia($collection)) {
            $media = $model->getFirstMedia($collection);
            $extension = $media->extension ?: pathinfo($media->file_name, PATHINFO_EXTENSION);
            $localName = $zipPath . '.' . $extension;
            $zip->addFile($media->getPath(), $localName);
        }
    }
}