@extends('../themes/' . $activeTheme)
@section('title', 'Assign Company Driver -')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Assign Company Driver', 'active' => true],
    ];
@endphp
@section('subcontent')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-tie mr-2"></i>
                        Assign Company Driver to Vehicle {{ $vehicle->unit_number }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.vehicles.show', $vehicle) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Vehicle
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Vehicle Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-truck"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Vehicle</span>
                                    <span class="info-box-number">{{ $vehicle->unit_number }}</span>
                                    <span class="info-box-more">{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-building"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Carrier</span>
                                    <span class="info-box-number">{{ $vehicle->carrier->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assignment Form -->
                    <form action="{{ route('admin.vehicle-driver-assignments.store') }}" method="POST" id="assignmentForm">
                        @csrf
                        <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                        <input type="hidden" name="assignment_type" value="company_driver">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Driver Selection -->
                                <div class="form-group">
                                    <label for="user_driver_detail_id" class="required">Select Company Driver</label>
                                    <select name="user_driver_detail_id" id="user_driver_detail_id" class="form-control select2 @error('user_driver_detail_id') is-invalid @enderror" required>
                                        <option value="">-- Select a Driver --</option>
                                        @foreach($availableDrivers as $driver)
                                            <option value="{{ $driver->id }}" 
                                                {{ old('user_driver_detail_id') == $driver->id ? 'selected' : '' }}
                                                data-phone="{{ $driver->user->phone ?? '' }}"
                                                data-email="{{ $driver->user->email ?? '' }}"
                                                data-license="{{ $driver->license_number ?? '' }}">
                                                {{ $driver->user->name ?? 'Unknown' }} 
                                                @if($driver->license_number)
                                                    (License: {{ $driver->license_number }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_driver_detail_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($availableDrivers->isEmpty())
                                        <small class="text-muted">No available drivers found for this carrier. All drivers may already be assigned to vehicles.</small>
                                    @endif
                                </div>

                                <!-- Driver Details Preview -->
                                <div id="driverDetails" class="card card-outline card-info" style="display: none;">
                                    <div class="card-header">
                                        <h3 class="card-title">Driver Details</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong>Phone:</strong>
                                                <span id="driverPhone">-</span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Email:</strong>
                                                <span id="driverEmail">-</span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>License:</strong>
                                                <span id="driverLicense">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Assignment Date -->
                                <div class="form-group">
                                    <label for="assignment_date" class="required">Assignment Date</label>
                                    <input type="date" name="assignment_date" id="assignment_date" 
                                           class="form-control @error('assignment_date') is-invalid @enderror" 
                                           value="{{ old('assignment_date', date('Y-m-d')) }}" required>
                                    @error('assignment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Notes -->
                                <div class="form-group">
                                    <label for="notes">Assignment Notes</label>
                                    <textarea name="notes" id="notes" rows="3" 
                                              class="form-control @error('notes') is-invalid @enderror" 
                                              placeholder="Optional notes about this assignment...">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Maximum 1000 characters</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Assignment Summary -->
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">Assignment Summary</h3>
                                    </div>
                                    <div class="card-body">
                                        <dl class="row">
                                            <dt class="col-sm-5">Vehicle:</dt>
                                            <dd class="col-sm-7">{{ $vehicle->unit_number }}</dd>
                                            
                                            <dt class="col-sm-5">Driver Type:</dt>
                                            <dd class="col-sm-7">
                                                <span class="badge badge-info">Company Driver</span>
                                            </dd>
                                            
                                            <dt class="col-sm-5">Carrier:</dt>
                                            <dd class="col-sm-7">{{ $vehicle->carrier->name ?? 'N/A' }}</dd>
                                            
                                            <dt class="col-sm-5">Available Drivers:</dt>
                                            <dd class="col-sm-7">
                                                <span class="badge badge-{{ $availableDrivers->count() > 0 ? 'success' : 'warning' }}">
                                                    {{ $availableDrivers->count() }}
                                                </span>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>

                                <!-- Important Notes -->
                                <div class="alert alert-info">
                                    <h5><i class="icon fas fa-info"></i> Important!</h5>
                                    <ul class="mb-0">
                                        <li>Only unassigned drivers from the same carrier are shown</li>
                                        <li>The assignment will be effective immediately</li>
                                        <li>Any previous assignment will be automatically ended</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                        <i class="fas fa-user-check"></i> Assign Driver
                                    </button>
                                    <a href="{{ route('admin.vehicles.show', $vehicle) }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<style>
.required:after {
    content: " *";
    color: red;
}

.info-box-more {
    font-size: 0.875rem;
    color: #6c757d;
}

#driverDetails {
    margin-top: 1rem;
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('#user_driver_detail_id').select2({
        theme: 'bootstrap4',
        placeholder: '-- Select a Driver --',
        allowClear: true
    });

    // Handle driver selection
    $('#user_driver_detail_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const submitBtn = $('#submitBtn');
        const driverDetails = $('#driverDetails');
        
        if (selectedOption.val()) {
            // Show driver details
            $('#driverPhone').text(selectedOption.data('phone') || '-');
            $('#driverEmail').text(selectedOption.data('email') || '-');
            $('#driverLicense').text(selectedOption.data('license') || '-');
            
            driverDetails.show();
            submitBtn.prop('disabled', false);
        } else {
            // Hide driver details
            driverDetails.hide();
            submitBtn.prop('disabled', true);
        }
    });

    // Form validation
    $('#assignmentForm').on('submit', function(e) {
        const driverId = $('#user_driver_detail_id').val();
        const assignmentDate = $('#assignment_date').val();
        
        if (!driverId) {
            e.preventDefault();
            toastr.error('Please select a driver.');
            return false;
        }
        
        if (!assignmentDate) {
            e.preventDefault();
            toastr.error('Please select an assignment date.');
            return false;
        }
        
        // Show loading state
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Assigning...');
    });

    // Character counter for notes
    $('#notes').on('input', function() {
        const maxLength = 1000;
        const currentLength = $(this).val().length;
        const remaining = maxLength - currentLength;
        
        if (remaining < 100) {
            $(this).next('.text-muted').html(`Maximum 1000 characters (${remaining} remaining)`);
            if (remaining < 0) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        }
    });

    // Trigger change event if there's a pre-selected value
    if ($('#user_driver_detail_id').val()) {
        $('#user_driver_detail_id').trigger('change');
    }
});
</script>
@endpush