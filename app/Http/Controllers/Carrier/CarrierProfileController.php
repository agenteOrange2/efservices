<?php

namespace App\Http\Controllers\Carrier;

use App\Models\DocumentType;
use Illuminate\Http\Request;
use App\Models\CarrierDocument;
use App\Models\UserCarrierDetail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CarrierProfileUpdateRequest;

class CarrierProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $carrierDetail = $user->carrierDetails;
        $carrier = $carrierDetail->carrier;
        
        // Cálculos para el perfil y documentos
        $totalDocuments = DocumentType::count();
        $uploadedDocuments = CarrierDocument::where('carrier_id', $carrier->id)
            ->where('status', CarrierDocument::STATUS_APPROVED)
            ->count();
        $documentProgress = $totalDocuments > 0 ? ($uploadedDocuments / $totalDocuments) * 100 : 0;

        $pendingDocuments = CarrierDocument::where('carrier_id', $carrier->id)
            ->where('status', '!=', CarrierDocument::STATUS_APPROVED)
            ->with('documentType')
            ->get();

        // Obtener usuarios asociados
        $userCarriers = UserCarrierDetail::where('carrier_id', $carrier->id)
            ->with('user')
            ->get();

        $membership = $carrier->membership;

        return view('carrier.profile.index', compact(
            'user',
            'carrierDetail',
            'carrier',
            'documentProgress',
            'pendingDocuments',
            'userCarriers',
            'membership',
            'totalDocuments',
            'uploadedDocuments'
        ));
    }

    public function edit()
    {
        $user = Auth::user();
        $carrierDetail = $user->carrierDetails;
        $carrier = $carrierDetail->carrier;

        return view('carrier.profile.edit', compact('user', 'carrierDetail', 'carrier'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $carrier = $user->carrierDetails->carrier;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zipcode' => 'required|string|max:10',
            'ein_number' => 'required|string|max:255',
            'dot_number' => 'required|string|max:255',
            'mc_number' => 'nullable|string|max:255',
            'state_dot' => 'nullable|string|max:255',
            'ifta_account' => 'nullable|string|max:255',
            'phone' => 'required|string|max:15',
            'logo_carrier' => 'nullable|image|max:2048',
        ]);

        // Actualizar carrier
        $carrier->update($validated);

        // Actualizar carrier details
        $user->carrierDetails->update([
            'phone' => $validated['phone'],
        ]);

        // Manejar la foto/logo si se subió
        if ($request->hasFile('logo_carrier')) {
            $carrier->addMediaFromRequest('logo_carrier')
                ->usingFileName(strtolower(str_replace(' ', '_', $carrier->name)) . '.webp')
                ->toMediaCollection('logo_carrier');
        }

        return redirect()->route('carrier.profile.index')
            ->with('success', 'Profile updated successfully');
    }
}