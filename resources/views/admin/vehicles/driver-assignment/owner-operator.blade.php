@extends('../themes/' . $activeTheme)
@section('title', 'Assign Owner Operator')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Assign Owner Operator', 'active' => true],
    ];
@endphp
@section('subcontent')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-cog mr-2"></i>
                        Assign Owner Operator to Vehicle {{ $vehicle->unit_number }}
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
                        <input type="hidden" name="assignment_type" value="owner_operator">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Owner Information -->
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-user"></i> Owner Information
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="owner_name" class="required">Owner Name</label>
                                                    <input type="text" name="owner_name" id="owner_name" 
                                                           class="form-control @error('owner_name') is-invalid @enderror" 
                                                           value="{{ old('owner_name') }}" 
                                                           placeholder="Enter owner's full name" required>
                                                    @error('owner_name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="owner_phone" class="required">Phone Number</label>
                                                    <input type="tel" name="owner_phone" id="owner_phone" 
                                                           class="form-control @error('owner_phone') is-invalid @enderror" 
                                                           value="{{ old('owner_phone') }}" 
                                                           placeholder="(555) 123-4567" required>
                                                    @error('owner_phone')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="owner_email" class="required">Email Address</label>
                                            <input type="email" name="owner_email" id="owner_email" 
                                                   class="form-control @error('owner_email') is-invalid @enderror" 
                                                   value="{{ old('owner_email') }}" 
                                                   placeholder="owner@example.com" required>
                                            @error('owner_email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Assignment Details -->
                                <div class="card card-outline card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-calendar-alt"></i> Assignment Details
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="assignment_date" class="required">Assignment Date</label>
                                            <input type="date" name="assignment_date" id="assignment_date" 
                                                   class="form-control @error('assignment_date') is-invalid @enderror" 
                                                   value="{{ old('assignment_date', date('Y-m-d')) }}" required>
                                            @error('assignment_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="notes">Assignment Notes</label>
                                            <textarea name="notes" id="notes" rows="4" 
                                                      class="form-control @error('notes') is-invalid @enderror" 
                                                      placeholder="Optional notes about this owner operator assignment...">{{ old('notes') }}</textarea>
                                            @error('notes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Maximum 1000 characters</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Assignment Summary -->
                                <div class="card card-outline card-warning">
                                    <div class="card-header">
                                        <h3 class="card-title">Assignment Summary</h3>
                                    </div>
                                    <div class="card-body">
                                        <dl class="row">
                                            <dt class="col-sm-5">Vehicle:</dt>
                                            <dd class="col-sm-7">{{ $vehicle->unit_number }}</dd>
                                            
                                            <dt class="col-sm-5">Driver Type:</dt>
                                            <dd class="col-sm-7">
                                                <span class="badge badge-warning">Owner Operator</span>
                                            </dd>
                                            
                                            <dt class="col-sm-5">Carrier:</dt>
                                            <dd class="col-sm-7">{{ $vehicle->carrier->name ?? 'N/A' }}</dd>
                                            
                                            <dt class="col-sm-5">VIN:</dt>
                                            <dd class="col-sm-7">{{ $vehicle->vin ?? 'N/A' }}</dd>
                                        </dl>
                                    </div>
                                </div>

                                <!-- Owner Operator Info -->
                                <div class="alert alert-warning">
                                    <h5><i class="icon fas fa-exclamation-triangle"></i> Owner Operator</h5>
                                    <ul class="mb-0">
                                        <li>The owner operates their own vehicle</li>
                                        <li>They are responsible for maintenance and insurance</li>
                                        <li>Contact information is required for communication</li>
                                        <li>Any previous assignment will be automatically ended</li>
                                    </ul>
                                </div>

                                <!-- Contact Preview -->
                                <div class="card card-outline card-secondary" id="contactPreview" style="display: none;">
                                    <div class="card-header">
                                        <h3 class="card-title">Contact Preview</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center">
                                            <i class="fas fa-user-circle fa-3x text-muted mb-2"></i>
                                            <h5 id="previewName">-</h5>
                                            <p class="text-muted mb-1">
                                                <i class="fas fa-phone"></i> <span id="previewPhone">-</span>
                                            </p>
                                            <p class="text-muted">
                                                <i class="fas fa-envelope"></i> <span id="previewEmail">-</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-warning" id="submitBtn">
                                        <i class="fas fa-user-cog"></i> Assign Owner Operator
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
<style>
.required:after {
    content: " *";
    color: red;
}

.info-box-more {
    font-size: 0.875rem;
    color: #6c757d;
}

#contactPreview {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.form-control:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Phone number formatting
    $('#owner_phone').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length >= 6) {
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        } else if (value.length >= 3) {
            value = value.replace(/(\d{3})(\d+)/, '($1) $2');
        }
        $(this).val(value);
        updateContactPreview();
    });

    // Update contact preview
    function updateContactPreview() {
        const name = $('#owner_name').val();
        const phone = $('#owner_phone').val();
        const email = $('#owner_email').val();
        const preview = $('#contactPreview');
        
        if (name || phone || email) {
            $('#previewName').text(name || 'Owner Name');
            $('#previewPhone').text(phone || 'Phone Number');
            $('#previewEmail').text(email || 'Email Address');
            preview.show();
        } else {
            preview.hide();
        }
    }

    // Bind update events
    $('#owner_name, #owner_email').on('input', updateContactPreview);

    // Form validation
    $('#assignmentForm').on('submit', function(e) {
        const name = $('#owner_name').val().trim();
        const phone = $('#owner_phone').val().trim();
        const email = $('#owner_email').val().trim();
        const assignmentDate = $('#assignment_date').val();
        
        if (!name) {
            e.preventDefault();
            toastr.error('Please enter the owner name.');
            $('#owner_name').focus();
            return false;
        }
        
        if (!phone) {
            e.preventDefault();
            toastr.error('Please enter the phone number.');
            $('#owner_phone').focus();
            return false;
        }
        
        if (!email) {
            e.preventDefault();
            toastr.error('Please enter the email address.');
            $('#owner_email').focus();
            return false;
        }
        
        if (!assignmentDate) {
            e.preventDefault();
            toastr.error('Please select an assignment date.');
            $('#assignment_date').focus();
            return false;
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            toastr.error('Please enter a valid email address.');
            $('#owner_email').focus();
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

    // Initialize contact preview if there are old values
    updateContactPreview();
});
</script>
@endpush