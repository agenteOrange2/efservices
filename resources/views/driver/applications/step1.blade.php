@extends('../themes/' . $activeTheme)
@section('title', 'New Driver Application - Step 1')

@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12 sm:col-span-10 sm:col-start-2">
        <div class="mb-6">
            <h2 class="text-2xl font-medium">Step 1: Personal Information</h2>
            <div class="mt-2 text-slate-500">Please provide the driver's basic information.</div>
        </div>

        <div class="box box--stacked flex flex-col">
            <form action="{{ route('admin.carrier.user_drivers.application.step1.store', $carrier) }}" method="POST">
                @csrf
                <div class="p-7">
                    <!-- Nombre y Email -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="form-label">First Name</label>
                            <x-base.form-input 
                            name="name" 
                            value="{{ $driver->user->name }}"
                            disabled
                            class="bg-gray-100" />
                        </div>

                        <div>
                            <label class="form-label">Middle Name</label>
                            <x-base.form-input 
                            name="middle_name" 
                            value="{{ $driver->middle_name }}"
                            disabled
                            class="bg-gray-100" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="form-label">Last Name</label>
                            <x-base.form-input 
                            name="last_name" 
                            value="{{ $driver->last_name }}"
                            disabled
                            class="bg-gray-100" />
                        </div>

                        <div>
                            <label class="form-label">Suffix</label>
                            <x-base.form-input name="suffix" value="{{ old('suffix') }}" />
                        </div>
                    </div>

                    <!-- SSN y Birth Date -->
                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="form-label">Social Security Number</label>
                            <x-base.form-input name="social_security_number" 
                                             value="{{ old('social_security_number') }}" 
                                             required 
                                             class="ssn-mask" />
                            @error('social_security_number')
                                <div class="text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Date of Birth</label>
                            <x-base.form-input type="date" 
                                             name="date_of_birth" 
                                             value="{{ old('date_of_birth') }}"
                                             max="{{ now()->subYears(18)->format('Y-m-d') }}" 
                                             required />
                            @error('date_of_birth')
                                <div class="text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="form-label">Email Address</label>
                            <x-base.form-input 
                            type="email"
                            name="email" 
                            value="{{ $driver->user->email }}"
                            disabled
                            class="bg-gray-100" />
                        </div>

                        <div>
                            <label class="form-label">Phone Number</label>
                            <x-base.form-input 
                            name="phone" 
                            value="{{ $driver->phone }}"
                            disabled
                            class="bg-gray-100" />
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 pt-6 border-t">
                        <x-base.button type="submit" variant="primary">
                            Continue to Step 2
                            <x-base.lucide icon="ArrowRight" class="w-4 h-4 ml-2" />
                        </x-base.button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/imask"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Máscara para SSN
        IMask(document.querySelector('.ssn-mask'), {
            mask: '000-00-0000'
        });

        // Máscara para teléfono
        IMask(document.querySelector('.phone-mask'), {
            mask: '(000) 000-0000'
        });
    });
</script>
@endpush