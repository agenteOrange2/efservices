@extends('../themes/' . $activeTheme)
@section('title', 'Create Vehicle Make')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
        ['label' => 'Makes', 'url' => route('admin.vehicle-makes.index')],
        ['label' => 'Create', 'active' => true],
    ];
@endphp
@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12 sm:col-span-8 sm:col-start-3 lg:col-span-6 lg:col-start-4 xl:col-span-4 xl:col-start-5">
            <div class="mt-7">
                <div class="box box--stacked flex flex-col">
                    <div class="box-body">
                        <h2 class="block font-medium mb-4 text-lg">Create Vehicle Make</h2>
                        <form action="{{ route('admin.vehicle-makes.store') }}" method="POST">
                            @csrf
                            <div class="mt-5">
                                <label for="name" class="form-label">Name</label>
                                <x-base.form-input 
                                    id="name"
                                    name="name"
                                    type="text"
                                    placeholder="Enter make name"
                                    value="{{ old('name') }}"
                                />
                                @error('name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="flex items-center justify-between mt-6">
                                <a href="{{ route('admin.vehicle-makes.index') }}" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    Create Make
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection