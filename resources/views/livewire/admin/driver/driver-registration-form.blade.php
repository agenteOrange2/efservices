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

    @if ($successMessage)
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ $successMessage }}
        </div>
    @endif

    <!-- Progress Bar -->
    <div class="mb-8">
        <div class="flex justify-between mb-3">
            <div class="text-lg font-semibold">Driver Registration</div>
            <div class="text-sm">Step {{ $currentStep }} of 10</div>
        </div>
        <div class="w-full bg-gray-200 rounded h-2">
            <div class="bg-blue-600 h-2 rounded" style="width: {{ ($currentStep / 10) * 100 }}%"></div>
        </div>
    </div>

    <!-- Step 1: Driver Information -->
    @if ($currentStep == 1)
        @include('livewire.admin.driver.steps.step-general')
        <!-- Step 2: Address Information -->
    @elseif ($currentStep == 2)
        @include('livewire.admin.driver.steps.step-address')
        <!-- Step 3: Application Details -->
    @elseif ($currentStep == 3)
        @include('livewire.admin.driver.steps.step-application')
    @elseif ($currentStep === 4)
        @include('livewire.admin.driver.steps.step-licenses')
    @elseif ($currentStep === 5)
        @include('livewire.admin.driver.steps.step-medical')
    @elseif ($currentStep === 6)
        @include('livewire.admin.driver.steps.step-training')
    @elseif ($currentStep === 7)
        @include('livewire.admin.driver.steps.step-traffic')
    @elseif ($currentStep === 8)
        @include('livewire.admin.driver.steps.step-accident')
    @elseif ($currentStep === 9)
        @include('livewire.admin.driver.steps.step-fmcsr')
    @elseif ($currentStep === 10)
        @include('livewire.admin.driver.steps.step-employment-history')
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

            @if ($currentStep < 10)
                <button type="button" wire:click="nextStep"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Next
                </button>
            @else
                <button type="button" wire:click="submitForm"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Submit
                </button>
            @endif
        </div>
    </div>
</div>
