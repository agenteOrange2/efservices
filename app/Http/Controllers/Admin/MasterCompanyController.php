<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Driver\MasterCompany;
use App\Models\Admin\Driver\DriverEmploymentCompany;
use App\Models\UserDriverDetail;
use Illuminate\Http\Request;

class MasterCompanyController extends Controller
{
    /**
     * Display a listing of the companies.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = MasterCompany::query();

        // Aplicar filtros si existen
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('company_name', 'like', '%' . $search . '%');
        }

        if ($request->filled('state')) {
            $query->where('state', $request->get('state'));
        }

        if ($request->filled('city')) {
            $query->where('city', $request->get('city'));
        }

        $companies = $query->withCount('driverEmploymentCompanies')
                          ->orderBy('company_name')
                          ->paginate(15);
        
        // Obtener todos los estados y ciudades disponibles para los filtros
        $allStates = MasterCompany::whereNotNull('state')
                                 ->where('state', '!=', '')
                                 ->distinct()
                                 ->pluck('state')
                                 ->sort();
        
        $allCities = MasterCompany::whereNotNull('city')
                                 ->where('city', '!=', '')
                                 ->distinct()
                                 ->pluck('city')
                                 ->sort();
        
        return view('admin.companies.index', compact('companies', 'allStates', 'allCities'));
    }

    /**
     * Show the form for creating a new company.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.companies.create');
    }

    /**
     * Store a newly created company in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'contact' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'fax' => 'nullable|string|max:50',
        ]);

        $company = MasterCompany::create($validated);

        return redirect()->route('admin.companies.index')
            ->with('success', 'Company created successfully');
    }

    /**
     * Display the specified company.
     *
     * @param  \App\Models\Admin\Driver\MasterCompany  $company
     * @return \Illuminate\Http\Response
     */
    public function show(MasterCompany $company)
    {
        // Obtener el historial de empleados asociados a esta empresa
        $employmentHistory = DriverEmploymentCompany::where('master_company_id', $company->id)
            ->with('userDriverDetail.user')
            ->paginate(15);

        return view('admin.companies.show', compact('company', 'employmentHistory'));
    }

    /**
     * Show the form for editing the specified company.
     *
     * @param  \App\Models\Admin\Driver\MasterCompany  $company
     * @return \Illuminate\Http\Response
     */
    public function edit(MasterCompany $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    /**
     * Update the specified company in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\Driver\MasterCompany  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MasterCompany $company)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'contact' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'fax' => 'nullable|string|max:50',
        ]);

        $company->update($validated);

        return redirect()->route('admin.companies.index')
            ->with('success', 'Company updated successfully');
    }

    /**
     * Remove the specified company from storage.
     *
     * @param  \App\Models\Admin\Driver\MasterCompany  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(MasterCompany $company)
    {
        // Verificar si la empresa tiene historiales de empleo asociados
        $hasEmploymentHistory = DriverEmploymentCompany::where('master_company_id', $company->id)->exists();
        
        if ($hasEmploymentHistory) {
            return redirect()->route('admin.companies.index')
                ->with('error', 'Cannot delete company: It has employment history records associated.');
        }

        $company->delete();

        return redirect()->route('admin.companies.index')
            ->with('success', 'Company deleted successfully');
    }
}
