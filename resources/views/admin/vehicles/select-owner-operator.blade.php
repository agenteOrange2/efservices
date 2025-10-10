@extends('../themes/' . $activeTheme)
@section('title', 'Select Owner Operator - Vehicle Assignment')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Select Owner Operator', 'active' => true],
    ];
@endphp
@section('subcontent')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Select Owner Operator for Vehicle Assignment</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.vehicles.assign-driver-type', $vehicle->id) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Driver Type
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Vehicle Information -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h5><i class="fas fa-truck"></i> Vehicle Information</h5>
                                <p class="mb-1"><strong>Make/Model:</strong> {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})</p>
                                <p class="mb-1"><strong>VIN:</strong> {{ $vehicle->vin }}</p>
                                <p class="mb-0"><strong>Unit Number:</strong> {{ $vehicle->company_unit_number ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    @if($ownerOperators->count() > 0)
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="mb-3">Available Owner Operators</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>License Number</th>
                                                <th>License State</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($ownerOperators as $ownerOperator)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $ownerOperator->user->name }}</strong>
                                                        @if($ownerOperator->ownerOperatorDetail)
                                                            <br><small class="text-muted">Owner Operator ID: {{ $ownerOperator->id }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $ownerOperator->user->email }}</td>
                                                    <td>{{ $ownerOperator->phone ?? 'N/A' }}</td>
                                                    <td>{{ $ownerOperator->license_number ?? 'N/A' }}</td>
                                                    <td>{{ $ownerOperator->license_state ?? 'N/A' }}</td>
                                                    <td>
                                                        <form action="{{ route('admin.vehicles.assign-to-driver', $vehicle->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="user_driver_detail_id" value="{{ $ownerOperator->id }}">
                                                            <input type="hidden" name="driver_type" value="owner_operator">
                                                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to assign this vehicle to {{ $ownerOperator->user->name }}?')">
                                                                <i class="fas fa-check"></i> Assign Vehicle
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-warning">
                                    <h5><i class="fas fa-exclamation-triangle"></i> No Owner Operators Available</h5>
                                    <p class="mb-3">There are currently no registered owner operators in the system.</p>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.drivers.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Register New Driver
                                        </a>
                                        <a href="{{ route('admin.vehicles.assign-driver-type', $vehicle->id) }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Back to Driver Type Selection
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable if there are records
    @if($ownerOperators->count() > 0)
    $('.table').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "pageLength": 10,
        "order": [[0, "asc"]],
        "columnDefs": [
            { "orderable": false, "targets": [5] } // Disable sorting on Action column
        ]
    });
    @endif
});
</script>
@endsection