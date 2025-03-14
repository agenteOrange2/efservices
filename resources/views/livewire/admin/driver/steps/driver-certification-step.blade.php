<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Application Certification</h3>

    <div class="mb-6">
        <p class="text-base mb-2">This certifies that this application was completed by me, and that all entries on it and information in it are true and complete to the best of my knowledge.</p>
    </div>

    <!-- Safety Performance History -->
    <div class="mb-6">
        <h4 class="text-lg font-medium mb-3">Safety Performance History Investigation — Previous USDOT Regulated Employers</h4>
        
        <p class="text-sm mb-4">
            I hereby specifically authorize you to release the following information to the specified company and their agents for the purposes of investigation as required by §391.23 and §40.321(b) of the Federal Motor Carrier Safety Regulations. You are hereby released from any and all liability which may result from furnishing such information.
        </p>

        <!-- Employment History Table -->
        <div class="overflow-x-auto mb-4">
            <table class="min-w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-4 py-2 text-left">Company Name</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Address</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">City</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">State</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Zip</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Employed From</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Employed To</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employmentHistory as $company)
                        <tr>
                            <td class="border border-gray-300 px-4 py-2">{{ $company['company_name'] }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $company['address'] }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $company['city'] }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $company['state'] }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $company['zip'] }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $company['employed_from'] }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $company['employed_to'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="border border-gray-300 px-4 py-2 text-center">No employment history available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Electronic Signature Agreement -->
    <div class="mb-6">
        <p class="text-base mb-4">By signing below, I agree to use an electronic signature and acknowledge that an electronic signature is as legally binding as an ink signature.</p>
        
        <!-- Include SignaturePad library -->
        <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>

        <div x-data="{ 
            showModal: false,
            signaturePad: null,
            
            openModal() {
                this.showModal = true;
                this.$nextTick(() => {
                    this.initSignaturePad();
                });
            },
            
            closeModal() {
                this.showModal = false;
            },
            
            initSignaturePad() {
                let canvas = document.getElementById('signature-pad');
                this.signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
                    penColor: 'rgb(0, 0, 0)'
                });
                
                this.resizeCanvas();
            },
            
            resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const canvas = document.getElementById('signature-pad');
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
            },
            
            clear() {
                this.signaturePad.clear();
            },
            
            save() {
                if (this.signaturePad.isEmpty()) {
                    alert('Please provide a signature first.');
                    return;
                }
                const dataURL = this.signaturePad.toDataURL('image/png');
                @this.set('signature', dataURL);
                this.closeModal();
            }
        }">
            <!-- Signature Button and Preview -->
            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="text-md font-medium">Signature</h4>
                    <button type="button" @click="openModal()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        {{ !empty($signature) ? 'Change Signature' : 'Sign Now' }}
                    </button>
                </div>
                
                <!-- Signature Preview -->
                @if (!empty($signature))
                    <div class="border border-gray-300 rounded-md p-4 bg-gray-50">
                        <p class="text-sm text-gray-500 mb-2">Your Signature:</p>
                        <div class="bg-white border border-gray-200 rounded p-3">
                            <img src="{{ $signature }}" alt="Your Signature" class="max-h-32 mx-auto">
                        </div>
                    </div>
                @else
                    <div class="border border-gray-300 rounded-md p-4 bg-gray-50 text-center text-gray-500">
                        <p>No signature provided. Click the "Sign Now" button to add your signature.</p>
                    </div>
                @endif
            </div>
            
            <!-- Signature Modal -->
            <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-auto bg-black bg-opacity-50 flex items-center justify-center">
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Please Sign Below</h3>
                        <button @click="closeModal()" class="text-gray-500 hover:text-gray-700">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="border-2 border-gray-300 rounded-md mb-4">
                        <canvas id="signature-pad" class="w-full h-64 cursor-crosshair"></canvas>
                    </div>
                    
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="clear()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                            Clear
                        </button>
                        <button type="button" @click="save()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Save Signature
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        @error('signature')
            <span class="text-red-500 text-sm block mt-1">{{ $message }}</span>
        @enderror
    </div>

    <!-- Certification Acceptance -->
    <div class="mb-6">
        <div class="flex items-center">
            <input type="checkbox" id="certificationAccepted" wire:model="certificationAccepted"
                class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded mr-2">
            <label for="certificationAccepted" class="text-sm font-medium text-gray-700">
                I hereby certify that all information provided in this application is true and complete to the best of my knowledge.
            </label>
        </div>
        @error('certificationAccepted')
            <span class="text-red-500 text-sm block mt-1">{{ $message }}</span>
        @enderror
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
            <button type="button" wire:click="complete"
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Complete Application
            </button>
        </div>
    </div>
</div>