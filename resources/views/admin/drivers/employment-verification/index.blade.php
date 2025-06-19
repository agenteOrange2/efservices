@extends('../themes/' . $activeTheme)
@section('title', 'Employment Verifications')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Employment Verifications', 'active' => true],
    ];
@endphp

@section('subcontent')

    <div class="flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Employment Verifications</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('admin.drivers.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Drivers</span>
            </a>
        </div>
    </div>

    <div class="box box--stacked mt-5">
        <div class="box-body p-5">
            <form action="{{ route('admin.drivers.employment-verification.index') }}" method="GET"
                class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <!-- Estado -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status" name="status"
                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                        <option value="">All</option>
                        <option value="verified" @selected(request('status') === 'verified')>Verified</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    </select>
                </div>

                <!-- Conductor -->
                <div>
                    <label for="driver" class="block text-sm font-medium text-gray-700 mb-1">Driver</label>
                    <select id="driver" name="driver"
                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                        <option value="">All drivers</option>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}" @selected(request('driver') == $driver->id)>
                                {{ $driver->user->name }} {{ $driver->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex items-end gap-2 mt-2 md:mt-0">
                    <button type="submit" class="btn btn-primary w-full md:w-auto">Filter</button>
                    <a href="{{ route('admin.drivers.employment-verification.index') }}"
                        class="btn btn-secondary w-full md:w-auto">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="box box--stacked mt-5">
        <div class="box-body p-5">
            <div class="mt-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap px-6 py-3">Driver</th>
                                <th class="whitespace-nowrap px-6 py-3">Company</th>
                                <th class="whitespace-nowrap px-6 py-3">Email</th>
                                <th class="whitespace-nowrap px-6 py-3">Send Date</th>
                                <th class="whitespace-nowrap px-6 py-3">Status</th>
                                <th class="whitespace-nowrap px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employmentVerifications as $verification)
                                <tr>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.drivers.show', $verification->userDriverDetail->id) }}"
                                            class="font-medium whitespace-nowrap">
                                            {{ $verification->userDriverDetail->user->name }}
                                            {{ $verification->userDriverDetail->last_name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $verification->masterCompany ? $verification->masterCompany->name : 'Custom company' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $verification->email }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $verification->updated_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($verification->verification_status == 'verified')
                                            <div class="flex items-center text-success">
                                                <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Verified
                                            </div>
                                        @elseif($verification->verification_status == 'rejected')
                                            <div class="flex items-center text-danger">
                                                <i data-lucide="x-circle" class="w-4 h-4 mr-1"></i> Rejected
                                            </div>
                                        @else
                                            <div class="flex items-center text-warning">
                                                <i data-lucide="clock" class="w-4 h-4 mr-1"></i> Pending
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex">
                                            <a href="{{ route('admin.drivers.employment-verification.show', $verification->id) }}"
                                                class="btn btn-sm btn-primary mr-1">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>

                                            <form
                                                action="{{ route('admin.drivers.employment-verification.resend', $verification->id) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-secondary mr-1"
                                                    title="Resend email">
                                                    <i data-lucide="mail" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No employment verifications available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5">
                    {{ $employmentVerifications->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Lucide.createIcons();
        });
    </script>
@endpush
