<!-- resources/views/livewire/admin/driver/steps/driver-confirmation.blade.php -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex items-center mb-6">
        <div class="bg-green-100 p-2 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h2 class="text-xl font-bold ml-3 text-gray-800">Registration Complete!</h2>
    </div>

    <div class="mb-8">
        <p class="text-gray-700 mb-4">
            The driver registration has been completed successfully. All information has been saved.
        </p>

        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-primary">Important Notice</h3>
                    <div class="mt-2 text-sm text-primary">
                        <p class="font-semibold">FMCSA's Drug and Alcohol Clearinghouse Electronic Consent Required</p>
                        <p class="mt-2">
                            Beginning on January 6, 2020, the driver must provide <strong>electronic consent</strong>
                            for a prospective employer to view their information in the FMCSA's Drug and Alcohol
                            Clearinghouse.
                        </p>
                        <p class="mt-2">
                            To do this, the driver must register for the Drug and Alcohol Clearinghouse using the link
                            below and provide electronic consent when requested by the prospective employer. If they do
                            not do this, they will be prohibited from operating a commercial motor vehicle for their
                            prospective employer.
                        </p>
                        <div class="mt-3">
                            <a href="https://clearinghouse.fmcsa.dot.gov/register" target="_blank"
                                class="text-primary hover:text-primary font-medium underline">
                                Register for the FMCSA Clearinghouse
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-between mt-6">

        <x-base.button type="button" wire:click="previous" class="w-full sm:w-44" variant="secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z"
                    clip-rule="evenodd" />
            </svg> Previous
        </x-base.button>

        <button type="button" wire:click="finish"
            class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition flex items-center"
            wire:loading.attr="disabled" wire:loading.class="opacity-75 cursor-not-allowed">
            <span wire:loading.remove wire:target="finish">Complete Registration</span>
            <span wire:loading wire:target="finish" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                Processing...
            </span>
        </button>
    </div>

</div>
