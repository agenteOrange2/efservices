<?php

namespace App\Http\Controllers\Admin;


use App\Models\User;
use App\Models\Carrier;
use App\Models\DocumentType;
use App\Models\CarrierDocument;
use App\Models\UserDriverDetail;
use App\Models\UserCarrierDetail;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class HomeController extends Controller
{
    //    public function dashboard()
    //    {
    //        $totalCarriers = Carrier::count();
    //        $activeCarriers = Carrier::where('status', 1)->count();
    //        $pendingCarriers = Carrier::where('status', 0)->count();

    //        $totalDrivers = UserDriverDetail::count();
    //        $activeDrivers = UserDriverDetail::where('status', 1)->count();
    //        $pendingDrivers = UserDriverDetail::where('status', 0)->count();

    //        $totalDocuments = CarrierDocument::count();
    //        $pendingDocuments = CarrierDocument::where('status', 0)->count();
    //        $approvedDocuments = CarrierDocument::where('status', 1)->count();

    //        $recentCarriers = Carrier::latest()
    //            ->take(5)
    //            ->get();

    //        $recentDrivers = UserDriverDetail::with('user', 'carrier')
    //            ->latest()
    //            ->take(5)
    //            ->get();

    //        $recentDocuments = CarrierDocument::with('carrier', 'documentType')
    //            ->latest()
    //            ->take(5)
    //            ->get();

    //        $monthlyStats = Carrier::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
    //            ->whereYear('created_at', Carbon::now()->year)
    //            ->groupBy('month')
    //            ->get()
    //            ->pluck('count', 'month')
    //            ->toArray();

    //            $monthlyCarriers = Carrier::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
    //            ->whereYear('created_at', Carbon::now()->year)
    //            ->groupBy('month')
    //            ->pluck('count', 'month');

    //        $monthlyDrivers = UserDriverDetail::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
    //            ->whereYear('created_at', Carbon::now()->year)
    //            ->groupBy('month')
    //            ->pluck('count', 'month');
    //        return view('admin.dashboard', compact(
    //            'totalCarriers',
    //            'activeCarriers', 
    //            'pendingCarriers',
    //            'totalDrivers',
    //            'activeDrivers',
    //            'pendingDrivers',
    //            'totalDocuments',
    //            'pendingDocuments',
    //            'approvedDocuments',
    //            'recentCarriers',
    //            'recentDrivers',
    //            'recentDocuments',
    //            'monthlyStats',
    //             'monthlyCarriers',
    //             'monthlyDrivers'
    //        ));
    //    }

    public function dashboard(): View
    {
        return view('admin/dashboard');
    }
}
