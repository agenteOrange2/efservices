@extends('../themes/' . $activeTheme)
@section('title', 'Dashboard EF Services ')


@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('carrier.dashboard')],        
        ['label' => 'Dashboard', 'active' => true],
    ];
@endphp

@section('subcontent')

<div class="flex flex-col gap-y-7">
    <div class="box box--stacked flex flex-col p-5">
        <form action="{{ route('carrier.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-12 gap-x-6 gap-y-7">
                <div class="col-span-12 xl:col-span-8">
                    <!-- Información básica -->
                    <div class="box box--stacked flex flex-col p-5">
                        <div class="mb-5 text-xl font-medium">Basic Information</div>
                        
                        <div class="grid grid-cols-12 gap-x-6 gap-y-5">
                            <div class="col-span-12 sm:col-span-6">
                                <x-base.form-label>Company Name</x-base.form-label>
                                <x-base.form-input 
                                    type="text"
                                    name="name"
                                    value="{{ old('name', $carrier->name) }}"
                                    placeholder="Enter company name"
                                />
                            </div>
                            
                            <div class="col-span-12 sm:col-span-6">
                                <x-base.form-label>Phone</x-base.form-label>
                                <x-base.form-input 
                                    type="text"
                                    name="phone"
                                    value="{{ old('phone', $carrierDetail->phone) }}"
                                    placeholder="Enter phone number"
                                />
                            </div>

                            <div class="col-span-12">
                                <x-base.form-label>Address</x-base.form-label>
                                <x-base.form-textarea
                                    name="address"
                                    placeholder="Enter address"
                                >{{ old('address', $carrier->address) }}</x-base.form-textarea>
                            </div>

                            <!-- Otros campos... -->
                            
                            <div class="col-span-12 flex items-center justify-end gap-x-3">
                                <x-base.button
                                    type="button"
                                    variant="secondary"
                                    href="{{ route('carrier.profile') }}"
                                >
                                    Cancel
                                </x-base.button>
                                <x-base.button
                                    type="submit"
                                    variant="primary"
                                >
                                    Save Changes
                                </x-base.button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 xl:col-span-4">
                    <!-- Logo upload -->
                    <div class="box box--stacked flex flex-col p-5">
                        <div class="mb-5 text-xl font-medium">Company Logo</div>
                        <div class="flex flex-col items-center gap-y-5">
                            <div class="image-fit h-40 w-40">
                                <img
                                    class="rounded-full"
                                    src="{{ $carrier->getFirstMediaUrl('logo_carrier') ?: asset('build/assets/images/placeholders/200x200.jpg') }}"
                                    alt="{{ $carrier->name }}"
                                >
                            </div>
                            <x-base.form-input
                                type="file"
                                name="logo_carrier"
                                accept="image/*"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection