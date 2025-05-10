<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">FMCSA's Drug and Alcohol Clearinghouse Electronic Consent Required</h3>
    
    <div class="p-6 bg-blue-50 border-l-4 border-blue-500 mb-6">
        <p class="mb-4">
            Beginning on January 6, 2020, you must provide <strong>electronic consent</strong> for a prospective employer to view your information in the FMCSA's Drug and Alcohol Clearinghouse. 
        </p>
        <p class="mb-4">
            To do this you must register for the Drug and Alcohol Clearinghouse using the link below and provide electronic consent when requested by the prospective employer. If you do not do this, you will be prohibited from operating a commercial motor vehicle for your prospective employer.
        </p>
    </div>
    
    <!-- PDF Instructions Section -->
    <div class="mb-6">
        <h4 class="text-lg font-medium mb-3">Instructions for Providing Consent</h4>
        
        <div class="border rounded-md p-4 bg-gray-50">
            <p class="mb-4">
                If you need further instructions on how to provide electronic consent, please review the PDF document below:
            </p>
            
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-4">
                <div class="flex items-center">
                    <svg class="h-8 w-8 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Drivers Responding to DACH Consent Requests (FMCSA 01.20).pdf</span>
                </div>
                
                <a href="{{ $pdfUrl }}" target="_blank" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 inline-flex items-center">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download Instructions
                </a>
            </div>
            
            <!-- PDF Embed -->
            <div class="border rounded overflow-hidden">
                <iframe src="{{ $pdfUrl }}" class="w-full h-96" style="min-height: 500px;"></iframe>
            </div>
        </div>
    </div>
    
    <!-- Clearinghouse Link Section -->
    <div class="mb-8">
        <h4 class="text-lg font-medium mb-3">Access the FMCSA Clearinghouse</h4>
        
        <div class="bg-gray-50 border rounded-md p-6 text-center">
            <p class="mb-4">
                Click the button below to visit the official FMCSA Drug and Alcohol Clearinghouse website:
            </p>
            
            <a href="https://clearinghouse.fmcsa.dot.gov/" target="_blank" 
               class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 inline-flex items-center justify-center">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                Visit FMCSA Clearinghouse
            </a>
        </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="flex justify-between mt-8">
        <div>
            <button type="button" wire:click="previous" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                Previous
            </button>
        </div>
        <div class="flex space-x-2">
            <button type="button" wire:click="saveAndExit"
                class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                Save & Exit
            </button>
            <button type="button" wire:click="finish"
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Finish
            </button>
        </div>
    </div>
</div>