<div class="p-4 bg-white rounded-lg shadow-md" x-data="{
    lived_three_years: @entangle('lived_three_years'),
    applying_position: @entangle('applying_position'),
    has_twic_card: @entangle('has_twic_card'),
    how_did_hear: @entangle('how_did_hear'),
    has_work_history: @entangle('has_work_history'),
    has_attended_training_school: @entangle('has_attended_training_school')
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
    
    <!-- Header específico para edición -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">Editar Driver: {{ $name }} {{ $last_name }}</h2>
        <div class="text-sm text-gray-500">ID: {{ $userDriverDetail->id }}</div>
    </div>

    <!-- Progress Bar -->
    <div class="mb-8">
        <div class="flex justify-between mb-3">
            <div class="text-lg font-semibold">Edición de Driver</div>
            <div class="text-sm">Step {{ $currentStep }} of 6</div>
        </div>
        <div class="w-full bg-gray-200 rounded h-2">
            <div class="bg-blue-600 h-2 rounded" style="width: {{ ($currentStep / 6) * 100 }}%"></div>
        </div>
    </div>

    <!-- Incluir los Steps (mismos que DriverRegistrationForm) -->
    @if ($currentStep == 1)
        @include('livewire.admin.driver.steps.step-general')
    @elseif ($currentStep == 2)
        @include('livewire.admin.driver.steps.step-address')
    @elseif ($currentStep == 3)
        @include('livewire.admin.driver.steps.step-application')
    @elseif ($currentStep === 4)
        @include('livewire.admin.driver.steps.step-licenses')
    @elseif ($currentStep === 5)
        @include('livewire.admin.driver.steps.step-medical')
    @elseif ($currentStep === 6)
        @include('livewire.admin.driver.steps.step-training')
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
            @if ($currentStep < 6)
                <button type="button" wire:click="nextStep"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Next
                </button>
            @else
                <button type="button" wire:click="updateDriver"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Update Driver
                </button>
            @endif
        </div>
    </div>
</div>