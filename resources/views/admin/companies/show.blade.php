@extends('../themes/' . $activeTheme)
@section('title', 'Company Details: ' . $company->company_name)

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Companies', 'url' => route('admin.companies.index')],
        ['label' => 'Details', 'active' => true],
    ];
@endphp

@section('subcontent')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.companies.index') }}" class="flex items-center text-blue-600 hover:text-blue-800 mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Companies
        </a>
        <h1 class="text-2xl font-bold text-gray-800">{{ $company->company_name }}</h1>
    </div>

    <div class="mb-6">
        <div class="flex space-x-2">
            <a href="{{ route('admin.companies.edit', $company) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-800 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Company
            </a>
            <form action="{{ route('admin.companies.destroy', $company) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this company?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:border-red-800 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete Company
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="p-4 border-b">
            <h2 class="text-xl font-semibold text-gray-700">Company Information</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Company Name</h3>
                    <p class="mt-1 text-lg text-gray-900">{{ $company->company_name }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Contact Person</h3>
                    <p class="mt-1 text-lg text-gray-900">{{ $company->contact ?? 'Not specified' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Address</h3>
                    <p class="mt-1 text-lg text-gray-900">
                        {{ $company->address ? $company->address . ', ' : '' }}
                        {{ $company->city ? $company->city . ', ' : '' }}
                        {{ $company->state ? $company->state . ' ' : '' }}
                        {{ $company->zip ?? '' }}
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Contact Information</h3>
                    <p class="mt-1 text-lg text-gray-900">
                        {{ $company->phone ? 'Phone: ' . $company->phone : '' }}
                        {{ $company->email ? ($company->phone ? ' | ' : '') . 'Email: ' . $company->email : '' }}
                    </p>
                </div>
                @if($company->fax)
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Fax</h3>
                    <p class="mt-1 text-lg text-gray-900">{{ $company->fax }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-700">Employment History Records</h2>
            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">{{ $employmentHistory->total() }} Total Records</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position Held</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employment Period</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($employmentHistory as $history)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($history->userDriverDetail && $history->userDriverDetail->user)
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $history->userDriverDetail->user->name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $history->userDriverDetail->user->email }}
                                </div>
                            @else
                                <div class="text-sm text-gray-500">Driver data not found</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $history->positions_held }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $history->employed_from ? $history->employed_from->format('M Y') : 'N/A' }} - 
                                {{ $history->employed_to ? $history->employed_to->format('M Y') : 'Present' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if(!$history->email)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    No Email
                                </span>
                            @elseif($history->email_sent)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Email Sent
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Not Sent
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($history->userDriverDetail)
                            <a href="{{ url('admin/drivers/' . $history->userDriverDetail->id . '/view') }}" class="text-blue-600 hover:text-blue-900">
                                View Driver
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                            No employment history records found for this company
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t">
            {{ $employmentHistory->links() }}
        </div>
    </div>
</div>
@endsection
