@extends('../themes/' . $activeTheme)
@section('title', 'Select Third Party Driver')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Select Third Party Driver', 'active' => true],
    ];
@endphp
@section('subcontent')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Select Third Party Driver for Vehicle Assignment</h3>
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

                    @if($thirdPartyDrivers->count() > 0)
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="mb-3">Available Third Party Drivers</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Company</th>
                                                <th>License Number</th>
                                                <th>License State</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($thirdPartyDrivers as $thirdPartyDriver)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $thirdPartyDriver->user->name }}</strong>
                                                        @if($thirdPartyDriver->thirdPartyDetail)
                                                            <br><small class="text-muted">Third Party ID: {{ $thirdPartyDriver->id }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $thirdPartyDriver->user->email }}</td>
                                                    <td>{{ $thirdPartyDriver->phone ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($thirdPartyDriver->thirdPartyDetail)
                                                            {{ $thirdPartyDriver->thirdPartyDetail->company_name ?? 'N/A' }}
                                                            @if($thirdPartyDriver->thirdPartyDetail->dba_name)
                                                                <br><small class="text-muted">DBA: {{ $thirdPartyDriver->thirdPartyDetail->dba_name }}</small>
                                                            @endif
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>{{ $thirdPartyDriver->license_number ?? 'N/A' }}</td>
                                                    <td>{{ $thirdPartyDriver->license_state ?? 'N/A' }}</td>
                                                    <td>
                                                        <form action="{{ route('admin.vehicles.assign-to-driver', $vehicle->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="user_driver_detail_id" value="{{ $thirdPartyDriver->id }}">
                                                            <input type="hidden" name="driver_type" value="third_party">
                                                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to assign this vehicle to {{ $thirdPartyDriver->user->name }}?')">
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
                                    <h5><i class="fas fa-exclamation-triangle"></i> No Third Party Drivers Available</h5>
                                    <p class="mb-3">There are currently no registered third party drivers in the system.</p>
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

                    <!-- Option to create new third party driver -->
                    @if($thirdPartyDrivers->count() > 0)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="fas fa-plus-circle"></i> Don't see the driver you're looking for?</h6>
                                        <p class="card-text">You can register a new third party driver if they're not listed above.</p>
                                        <a href="{{ route('admin.drivers.create') }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-plus"></i> Register New Third Party Driver
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
    @if($thirdPartyDrivers->count() > 0)
    $('.table').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "pageLength": 10,
        "order": [[0, "asc"]],
        "columnDefs": [
            { "orderable": false, "targets": [6] } // Disable sorting on Action column
        ]
    });
    @endif
});
</script>
@endsection