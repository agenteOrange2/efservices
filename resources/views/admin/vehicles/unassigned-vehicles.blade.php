@extends('../themes/' . $activeTheme)
@section('title', 'Unassigned Vehicles')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Unassigned Vehicles', 'active' => true],
    ];
@endphp
@section('subcontent')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Unassigned Vehicles</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Vehicles
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($unassignedVehicles->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Vehicle Info</th>
                                        <th>VIN</th>
                                        <th>License Plate</th>
                                        <th>Status</th>
                                        <th>Carrier</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($unassignedVehicles as $vehicle)
                                        <tr>
                                            <td>
                                                <strong>{{ $vehicle->year }} {{ $vehicle->make->name ?? 'N/A' }} {{ $vehicle->model }}</strong><br>
                                                <small class="text-muted">{{ $vehicle->vehicle_type->name ?? 'N/A' }}</small>
                                            </td>
                                            <td>{{ $vehicle->vin }}</td>
                                            <td>{{ $vehicle->license_plate ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $vehicle->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($vehicle->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $vehicle->carrier->name ?? 'N/A' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.vehicles.assign-driver-type', $vehicle->id) }}" 
                                                       class="btn btn-primary btn-sm">
                                                        <i class="fas fa-user-plus"></i> Assign Driver
                                                    </a>
                                                    <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" 
                                                       class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $unassignedVehicles->links() }}
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i>
                            <strong>No unassigned vehicles found.</strong><br>
                            All vehicles currently have driver assignments.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh every 30 seconds to show real-time updates
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            location.reload();
        }
    }, 30000);
</script>
@endpush