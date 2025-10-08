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
                    <h3 class="card-title">Assignment Details #{{ $assignment->id }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.vehicle-driver-assignments.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h5>Basic Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Assignment ID:</strong></td>
                                    <td>{{ $assignment->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Vehicle:</strong></td>
                                    <td>{{ $assignment->vehicle->unit_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Driver:</strong></td>
                                    <td>{{ $assignment->user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Assignment Type:</strong></td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $assignment->assignment_type)) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $assignment->status == 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($assignment->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Effective Date:</strong></td>
                                    <td>{{ $assignment->effective_date ? $assignment->effective_date->format('Y-m-d') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Termination Date:</strong></td>
                                    <td>{{ $assignment->termination_date ? $assignment->termination_date->format('Y-m-d') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Assigned By:</strong></td>
                                    <td>{{ $assignment->assignedBy->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created At:</strong></td>
                                    <td>{{ $assignment->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Type-specific Details -->
                        <div class="col-md-6">
                            @if($assignment->assignment_type == 'owner_operator' && $assignment->ownerOperatorDetail)
                                <h5>Owner Operator Details</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Owner Name:</strong></td>
                                        <td>{{ $assignment->ownerOperatorDetail->owner_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Owner Phone:</strong></td>
                                        <td>{{ $assignment->ownerOperatorDetail->owner_phone ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Owner Email:</strong></td>
                                        <td>{{ $assignment->ownerOperatorDetail->owner_email ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Contract Agreed:</strong></td>
                                        <td>{{ $assignment->ownerOperatorDetail->contract_agreed ? 'Yes' : 'No' }}</td>
                                    </tr>
                                </table>
                            @elseif($assignment->assignment_type == 'third_party' && $assignment->thirdPartyDetail)
                                <h5>Third Party Details</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Company Name:</strong></td>
                                        <td>{{ $assignment->thirdPartyDetail->third_party_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td>{{ $assignment->thirdPartyDetail->third_party_phone ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td>{{ $assignment->thirdPartyDetail->third_party_email ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Contact Person:</strong></td>
                                        <td>{{ $assignment->thirdPartyDetail->third_party_contact ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            @elseif($assignment->assignment_type == 'company_driver' && $assignment->companyDriverDetail)
                                <h5>Company Driver Details</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Employee ID:</strong></td>
                                        <td>{{ $assignment->companyDriverDetail->employee_id ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Department:</strong></td>
                                        <td>{{ $assignment->companyDriverDetail->department ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Supervisor:</strong></td>
                                        <td>{{ $assignment->companyDriverDetail->supervisor_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Base Rate:</strong></td>
                                        <td>${{ $assignment->companyDriverDetail->base_rate ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="btn-group" role="group">
                                @if($assignment->status == 'active')
                                    <button type="button" class="btn btn-warning" onclick="terminateAssignment({{ $assignment->id }})">
                                        <i class="fas fa-stop"></i> Terminate Assignment
                                    </button>
                                @endif
                                <a href="{{ route('admin.vehicles.show', $assignment->vehicle) }}" class="btn btn-info">
                                    <i class="fas fa-truck"></i> View Vehicle
                                </a>
                                <a href="{{ route('admin.users.show', $assignment->user) }}" class="btn btn-primary">
                                    <i class="fas fa-user"></i> View Driver
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function terminateAssignment(assignmentId) {
    if (confirm('Are you sure you want to terminate this assignment?')) {
        // Add termination logic here
        alert('Termination functionality would be implemented here');
    }
}
</script>
@endsection