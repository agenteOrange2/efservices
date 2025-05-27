<div class="flex flex-col md:p-5 p-0 box box--stacked" >
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Progress Bar -->
    <div class="mb-8 p-5 md:p-0">
        <div class="flex justify-between mb-3">
            <div class="text-lg font-semibold">Driver Registration</div>
            <div class="text-slate-500 text-xs">Step {{ $currentStep }} of {{ $totalSteps }}</div>            
        </div>
        <div class="w-full bg-gray-200 rounded h-2">
            <div class="bg-blue-600 h-2 rounded" style="width: {{ ($currentStep / $totalSteps) * 100 }}%"></div>
        </div>
    </div>

    <!-- Step Content -->
    @if ($currentStep == 1)
        <livewire:admin.driver.driver-general-info-step :driverId="$driverId" :carrier="$carrier" />
    @elseif ($currentStep == 2)
        <livewire:admin.driver.driver-address-step :driverId="$driverId" />
    @elseif ($currentStep == 3)
    <livewire:admin.driver.driver-application-step :driver-id="$driverId" :key="'app-step-'.$driverId" />
    @elseif ($currentStep == 4)
        <livewire:admin.driver.driver-license-step :driverId="$driverId" />
    @elseif ($currentStep == 5)
        <livewire:admin.driver.driver-medical-step :driverId="$driverId" />
    @elseif ($currentStep == 6)
        <livewire:admin.driver.driver-training-step :driverId="$driverId" />
    @elseif ($currentStep == 7)
        <livewire:admin.driver.driver-traffic-step :driverId="$driverId" />
    @elseif ($currentStep == 8)
        <livewire:admin.driver.driver-accident-step :driverId="$driverId" />
    @elseif ($currentStep == 9)
        @livewire('\\App\\Livewire\\Admin\\Driver\\DriverFMCSRStep', ['driverId' => $driverId])
    @elseif ($currentStep == 10)
        <livewire:admin.driver.driver-employment-history-step :driverId="$driverId" />
    @elseif ($currentStep == 11)
        <livewire:admin.driver.driver-company-policy-step :driverId="$driverId" />
    @elseif ($currentStep == 12)
        <livewire:admin.driver.driver-criminal-history-step :driverId="$driverId" />
    @elseif ($currentStep == 13)
        <livewire:admin.driver.driver-certification-step :driverId="$driverId" />
    @elseif ($currentStep == 14)
        <livewire:admin.driver.driver-confirmation :driverId="$driverId" />
    @endif
</div>