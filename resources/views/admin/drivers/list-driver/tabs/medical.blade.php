{{-- Medical Tab --}}
<div class="space-y-6">
    {{-- Medical Certificate --}}
    @if ($driver->medicalQualification)
    <x-driver.info-card title="Medical Qualification" icon="heart">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Medical Examiner</label>
                <p class="text-sm text-gray-900">{{ $driver->medicalQualification->medical_examiner_name }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Registry Number</label>
                <p class="text-sm text-gray-900 font-mono">{{ $driver->medicalQualification->medical_examiner_registry_number }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Medical Card Expiration</label>
                <p class="text-sm text-gray-900">{{ $driver->medicalQualification->medical_card_expiration_date ? $driver->medicalQualification->medical_card_expiration_date->format('M d, Y') : 'N/A' }}
                    @if ($driver->medicalQualification->medical_card_expiration_date)
                    @if ($driver->medicalQualification->medical_card_expiration_date->isPast())
                    <span
                        class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Expired</span>
                    @elseif($driver->medicalQualification->medical_card_expiration_date->diffInDays(now()) < 30)
                        <span
                        class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Expiring
                        Soon</span>
                        @else
                        <span
                            class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Valid</span>
                        @endif
                        @endif
                </p>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Expiration Date</label>
                <p class="text-sm text-gray-900">
                    @if ($driver->medicalQualification->social_security_number)
                    XXX-XX-{{ substr($driver->medicalQualification->social_security_number, -4) }}
                    @else
                    Not provided
                    @endif
                </p>
            </div>
        </div>

        {{-- Medical Documents --}}
        @if ($driver->medicalQualification->getFirstMediaUrl('medical_card'))
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="text-sm font-medium text-gray-900 mb-4">View Medical Card</h4>
            <div class="flex flex-wrap gap-4">
                <x-ui.action-button
                    href="{{ $driver->medicalQualification->getFirstMediaUrl('medical_card') }}"
                    icon="file-text"
                    variant="secondary"
                    size="sm"
                    target="_blank">
                    View Certificate
                </x-ui.action-button>
            </div>
        </div>
        @else
        <p class="text-slate-500">No medical card uploaded</p>
        @endif

        @if ($driver->getMedia('medical_records')->count() > 0)
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="text-sm font-medium text-gray-900 mb-4">View Medical Records</h4>
            <div class="flex flex-wrap gap-4">
                <x-ui.action-button
                    href="{{ $driver->getFirstMediaUrl('medical_records') }}"
                    icon="file-text"
                    variant="secondary"
                    size="sm"
                    target="_blank">
                    View Medical Records
                </x-ui.action-button>
            </div>
        </div>
        @else
        <p class="text-slate-500">No medical records uploaded</p>
        @endif
    </x-driver.info-card>
    @endif
</div>