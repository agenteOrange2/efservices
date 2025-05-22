{{-- resources/views/livewire/driver/driver-registration-manager.blade.php --}}
<div class="flex flex-col p-5 box box--stacked driver-registration-manager">
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
            <div class="text-lg font-semibold">
                @if ($isIndependent)
                    Independent Driver Registration
                @else
                    Driver Registration for {{ $carrier?->name ?? 'Your Carrier' }}
                @endif
            </div>
            <div class="text-sm">Step {{ $currentStep }} of {{ $totalSteps }}</div>
        </div>
        <div class="w-full bg-gray-200 rounded h-2">
            <div class="bg-blue-600 h-2 rounded" style="width: {{ ($currentStep / $totalSteps) * 100 }}%"></div>
        </div>
    </div>

    <!-- Componentes de pasos -->
    @if ($currentStep == 1)
        <livewire:driver.steps.step-general :driver-id="$driverId" :is-independent="$isIndependent" :carrier="$carrier" />
    @elseif ($currentStep == 2)
        <livewire:driver.steps.address-step :driver-id="$driverId" />
    @elseif ($currentStep == 3)
        <livewire:driver.steps.application-step :driver-id="$driverId" />
    @elseif ($currentStep == 4)
        <livewire:driver.steps.license-step :driver-id="$driverId" />
    @elseif ($currentStep == 5)
        <livewire:driver.steps.medical-step :driver-id="$driverId" />
    @elseif ($currentStep == 6)
        <livewire:driver.steps.training-step :driver-id="$driverId" />
    @elseif ($currentStep == 7)
        <livewire:driver.steps.traffic-step :driver-id="$driverId" />
    @elseif ($currentStep == 8)
        <livewire:driver.steps.accident-step :driver-id="$driverId" />
    @elseif ($currentStep == 9)
        <livewire:driver.steps.f-m-c-s-r-step :driver-id="$driverId" />
    @elseif ($currentStep == 10)
        <livewire:driver.steps.employment-history-step :driver-id="$driverId" />
    @elseif ($currentStep == 11)
        <livewire:driver.steps.company-policy-step :driver-id="$driverId" />
    @elseif ($currentStep == 12)
        <livewire:driver.steps.criminal-history-step :driver-id="$driverId" />
    @elseif ($currentStep == 13)
        <livewire:driver.steps.certification-step :driver-id="$driverId" />
    @elseif ($currentStep === 14)
    <livewire:driver.steps.fmcsaclearinghouse-step :driver-id="$driverId" />    
    @endif

    
</div>
