<div class="p-4 bg-white rounded-lg shadow-md" x-data="{
    lived_three_years: @entangle('lived_three_years'),
    applying_position: @entangle('applying_position'),
    has_twic_card: @entangle('has_twic_card'),
    how_did_hear: @entangle('how_did_hear'),
    has_work_history: @entangle('has_work_history'),
    has_attended_training_school: @entangle('has_attended_training_school'),
    has_traffic_convictions: @entangle('has_traffic_convictions'),
    has_accidents: @entangle('has_accidents')
}">
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Progress Bar -->
    <div class="mb-8">
        <div class="flex justify-between mb-3">
            <div class="text-lg font-semibold">Driver Registration</div>
            <div class="text-sm">Step {{ $currentStep }} of {{ $totalSteps }}</div>
        </div>
        <div class="w-full bg-gray-200 rounded h-2">
            <div class="bg-blue-600 h-2 rounded" style="width: {{ ($currentStep / $totalSteps) * 100 }}%"></div>
        </div>
    </div>

    <!-- Step 1: Driver Information -->
    @if ($currentStep == 1)
        <livewire:driver.steps.step-general :driverId="$driverId" :isIndependent="false" :carrier="$carrier" />

        <!-- Step 2: Address Information -->
    @elseif ($currentStep == 2)
        <livewire:driver.steps.address-step :driverId="$driverId" />

        <!-- Step 3: Application Details -->
    @elseif ($currentStep == 3)
        <livewire:driver.steps.application-step :driverId="$driverId" />

        <!-- Step 4: License Information -->
    @elseif ($currentStep == 4)
        <livewire:driver.steps.license-step :driverId="$driverId" />

        <!-- Step 5: Medical Information -->
    @elseif ($currentStep == 5)
        <livewire:driver.steps.medical-step :driverId="$driverId" />

        <!-- Step 6: Training Information -->
    @elseif ($currentStep == 6)
        <livewire:driver.steps.training-step :driverId="$driverId" />

        <!-- Step 7: Traffic Violations -->
    @elseif ($currentStep == 7)
        <livewire:driver.steps.traffic-step :driverId="$driverId" />

        <!-- Step 8: Accident Record -->
    @elseif ($currentStep == 8)
        <livewire:driver.steps.accident-step :driverId="$driverId" />

        <!-- Step 9: FMCSR Requirements -->
    @elseif ($currentStep == 9)
        <livewire:driver.steps.fmcsr-step :driverId="$driverId" />

        <!-- Step 10: Employment History -->
    @elseif ($currentStep == 10)
        <livewire:driver.steps.employment-history-step :driverId="$driverId" />
    @elseif ($currentStep == 11)
        <livewire:admin.driver.driver-company-policy-step :driverId="$driverId" />
    @elseif ($currentStep == 12)
        <livewire:admin.driver.driver-criminal-history-step :driverId="$driverId" />
    @elseif ($currentStep == 13)
        <livewire:admin.driver.driver-certification-step :driverId="$driverId" />
    @elseif ($currentStep == 14)
        <livewire:admin.driver.driver-confirmation :driverId="$driverId" />
    @endif

    <!-- Navigation Buttons -->
    <div class="flex justify-between mt-8">
        <div>
            @if ($currentStep > 1)
                <button type="button" wire:click="prevStep" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                    Previous
                </button>
            @endif
        </div>
        <div class="flex space-x-2">
            <button type="button" wire:click="saveAndExit"
                class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                Save & Exit
            </button>
            @if ($currentStep < $totalSteps)
                <button type="button" wire:click="nextStep"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Next
                </button>
            @else
                <button type="button" wire:click="submitForm"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Finish
                </button>
            @endif
        </div>
    </div>
</div>
