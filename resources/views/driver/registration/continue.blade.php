{{-- resources/views/driver/registration/continue.blade.php --}}
<x-driver-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold mb-4">Complete Your Registration</h1>
                    <p class="mb-4">You need to finish your driver application before you can access the dashboard.</p>
                    
                    @if(isset($driverDetail) && $driverDetail)
                        <livewire:driver.driver-registration-manager 
                            :driverId="$driverDetail->id"
                            :currentStep="$step"
                        />
                    @else
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">Unable to load your driver profile. Please contact support.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
</x-driver-layout>