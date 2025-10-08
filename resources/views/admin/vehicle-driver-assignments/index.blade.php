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
                    <h3 class="card-title">Vehicle Driver Assignments</h3>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-control" name="type" onchange="filterAssignments()">
                                <option value="">All Types</option>
                                <option value="company_driver" {{ request('type') == 'company_driver' ? 'selected' : '' }}>Company Driver</option>
                                <option value="owner_operator" {{ request('type') == 'owner_operator' ? 'selected' : '' }}>Owner Operator</option>
                                <option value="third_party" {{ request('type') == 'third_party' ? 'selected' : '' }}>Third Party</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" name="status" onchange="filterAssignments()">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                            </select>
                        </div>
                    </div>

                    <!-- Assignments Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Vehicle</th>
                                    <th>Driver</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Effective Date</th>
                                    <th>Termination Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignments as $assignment)
                                    <tr>
                                        <td>{{ $assignment->id }}</td>
                                        <td>{{ $assignment->vehicle->unit_number ?? 'N/A' }}</td>
                                        <td>{{ $assignment->user->name ?? 'N/A' }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $assignment->assignment_type)) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $assignment->status == 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($assignment->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $assignment->effective_date ? $assignment->effective_date->format('Y-m-d') : 'N/A' }}</td>
                                        <td>{{ $assignment->termination_date ? $assignment->termination_date->format('Y-m-d') : 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('admin.vehicle-driver-assignments.show', $assignment) }}" class="btn btn-sm btn-info">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No assignments found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($assignments->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $assignments->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterAssignments() {
    const type = document.querySelector('select[name="type"]').value;
    const status = document.querySelector('select[name="status"]').value;
    
    const params = new URLSearchParams();
    if (type) params.append('type', type);
    if (status) params.append('status', status);
    
    window.location.href = '{{ route("admin.vehicle-driver-assignments.index") }}' + (params.toString() ? '?' + params.toString() : '');
}
</script>
@endsection