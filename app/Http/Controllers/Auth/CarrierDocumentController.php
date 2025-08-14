<?php

namespace App\Http\Controllers\Auth;

use App\Models\Carrier;
use Illuminate\Http\Request;
use App\Models\UserCarrierDetail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\CarrierDocumentService;

class CarrierDocumentController extends Controller
{
    protected $carrierDocumentService;

    public function __construct(CarrierDocumentService $carrierDocumentService)
    {
        $this->carrierDocumentService = $carrierDocumentService;
    }

    /**
     * Mostrar la