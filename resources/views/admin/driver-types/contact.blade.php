@extends('../themes/' . $activeTheme)
@section('title', 'Contact Driver')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Driver Types', 'url' => route('admin.driver-types.index')],
['label' => 'Contact Driver', 'active' => true],
];
@endphp

@section('subcontent')
<div class="grid grid-cols-12 gap-y-10 gap-x-6">
    <div class="col-span-12">
        <!-- Header -->
        <div class="flex flex-col md:h-10 gap-y-3 md:items-center md:flex-row">
            <div class="text-base font-medium group-[.mode--light]:text-white">
                Contact Driver - {{ $driver->user->name ?? 'N/A' }}
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.driver-types.index') }}" variant="outline-secondary">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="arrow-left" />
                    Back to List
                </x-base.button>
                <x-base.button as="a" href="{{ route('admin.driver-types.show', $driver) }}" variant="outline-primary">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="eye" />
                    View Driver
                </x-base.button>
            </div>
        </div>

        <!-- Driver Information Summary -->
        <div class="box box--stacked mt-5">
            <div class="box-header p-5">
                <h3 class="box-title">Driver Information</h3>
            </div>
            <div class="box-body p-5">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-slate-200 rounded-full flex items-center justify-center">
                        <x-base.lucide class="w-6 h-6 text-slate-500" icon="user" />
                    </div>
                    <div>
                        <div class="font-medium text-lg">{{ $driver->user->name ?? 'N/A' }}</div>
                        <div class="text-slate-500">
                            <x-base.lucide class="w-4 h-4 inline mr-1" icon="mail" />
                            {{ $driver->user->email ?? 'N/A' }} |
                            <x-base.lucide class="w-4 h-4 inline mr-1" icon="building" />
                            Carrier: {{ $driver->carrier->name ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="box box--stacked mt-5">
            <div class="box-header p-5">
                <h3 class="box-title">Send Message</h3>
            </div>
            <div class="box-body p-5">
                @if(session('error'))
                <div class="alert alert-danger mb-4">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="alert-circle" />
                    {{ session('error') }}
                </div>
                @endif

                <form action="{{ route('admin.driver-types.send-contact', $driver) }}" method="POST">
                    @csrf
                    <div class="space-y-6">
                        <!-- Subject and Priority -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <x-base.form-label for="subject">Subject *</x-base.form-label>
                                <x-base.form-input
                                    type="text"
                                    id="subject"
                                    name="subject"
                                    value="{{ old('subject') }}"
                                    placeholder="Enter message subject..."
                                    required />
                                @error('subject')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <x-base.form-label for="priority">Priority *</x-base.form-label>
                                <x-base.form-select id="priority" name="priority" required>
                                    <option value="">Select priority...</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>
                                        🟢 Low Priority
                                    </option>
                                    <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>
                                        🟡 Normal Priority
                                    </option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>
                                        🔴 High Priority
                                    </option>
                                </x-base.form-select>
                                @error('priority')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Message -->
                        <div>
                            <x-base.form-label for="message">Message *</x-base.form-label>
                            <x-base.form-textarea
                                id="message"
                                name="message"
                                rows="8"
                                placeholder="Enter your message to the driver..."
                                required>{{ old('message') }}</x-base.form-textarea>
                            @error('message')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                            <div class="text-slate-500 text-sm mt-1">Maximum 2000 characters</div>
                        </div>

                        <!-- Message Templates (Optional) -->
                        <div class="bg-slate-50 p-4 rounded-lg">
                            <h4 class="font-medium text-slate-700 mb-3">Quick Message Templates</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <x-base.button
                                    type="button"
                                    variant="outline-secondary"
                                    size="sm"
                                    onclick="setTemplate('vehicle_assignment', 'Vehicle Assignment Required', 'Hello {{ $driver->user->name }},\n\nWe need to discuss your vehicle assignment. Please contact us at your earliest convenience.\n\nBest regards,\nAdmin Team')">
                                    Vehicle Assignment
                                </x-base.button>
                                <x-base.button
                                    type="button"
                                    variant="outline-secondary"
                                    size="sm"
                                    onclick="setTemplate('document_update', 'Document Update Required', 'Hello {{ $driver->user->name }},\n\nWe need you to update some documents in your profile. Please log in to your account and complete the required updates.\n\nBest regards,\nAdmin Team')">
                                    Document Update
                                </x-base.button>
                                <x-base.button
                                    type="button"
                                    variant="outline-secondary"
                                    size="sm"
                                    onclick="setTemplate('schedule_meeting', 'Schedule Meeting', 'Hello {{ $driver->user->name }},\n\nWe would like to schedule a meeting with you to discuss your employment. Please let us know your availability.\n\nBest regards,\nAdmin Team')">
                                    Schedule Meeting
                                </x-base.button>
                                <x-base.button
                                    type="button"
                                    variant="outline-secondary"
                                    size="sm"
                                    onclick="setTemplate('general_inquiry', 'General Inquiry', 'Hello {{ $driver->user->name }},\n\nWe hope this message finds you well. We wanted to reach out regarding your employment status.\n\nBest regards,\nAdmin Team')">
                                    General Inquiry
                                </x-base.button>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end gap-3 pt-6 border-t">
                            <x-base.button as="a" href="{{ route('admin.driver-types.index') }}" variant="outline-secondary">
                                Cancel
                            </x-base.button>
                            <x-base.button type="submit" variant="primary">
                                <x-base.lucide class="w-4 h-4 mr-2" icon="send" />
                                Send Message
                            </x-base.button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="box box--stacked mt-5">
            <div class="box-header p-5">
                <h3 class="box-title">Contact Information</h3>
            </div>
            <div class="box-body p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium text-slate-700 mb-3">Driver Details</h4>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <x-base.lucide class="w-4 h-4 text-slate-500 mr-2" icon="user" />
                                <span class="text-slate-600">Name:</span>
                                <span class="ml-2 font-medium">{{ $driver->user->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center">
                                <x-base.lucide class="w-4 h-4 text-slate-500 mr-2" icon="mail" />
                                <span class="text-slate-600">Email:</span>
                                <span class="ml-2 font-medium">{{ $driver->user->email ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center">
                                <x-base.lucide class="w-4 h-4 text-slate-500 mr-2" icon="phone" />
                                <span class="text-slate-600">Phone:</span>
                                <span class="ml-2 font-medium">{{ $driver->phone ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-medium text-slate-700 mb-3">Employment Details</h4>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <x-base.lucide class="w-4 h-4 text-slate-500 mr-2" icon="building" />
                                <span class="text-slate-600">Carrier:</span>
                                <span class="ml-2 font-medium">{{ $driver->carrier->name ?? 'N/A' }}</span>
                            </div>
                            @if($driver->driverEmploymentCompanies->count() > 0)
                            <div class="flex items-center">
                                <x-base.lucide class="w-4 h-4 text-slate-500 mr-2" icon="briefcase" />
                                <span class="text-slate-600">Company:</span>
                                <span class="ml-2 font-medium">{{ $driver->driverEmploymentCompanies->first()->company->company_name ?? 'N/A' }}</span>
                            </div>
                            @endif
                            <div class="flex items-center">
                                <x-base.lucide class="w-4 h-4 text-slate-500 mr-2" icon="calendar" />
                                <span class="text-slate-600">Registered:</span>
                                <span class="ml-2 font-medium">{{ $driver->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function setTemplate(type, subject, message) {
        document.getElementById('subject').value = subject;
        document.getElementById('message').value = message;

        // Set priority based on template type
        const prioritySelect = document.getElementById('priority');
        switch (type) {
            case 'vehicle_assignment':
                prioritySelect.value = 'high';
                break;
            case 'document_update':
                prioritySelect.value = 'normal';
                break;
            case 'schedule_meeting':
                prioritySelect.value = 'normal';
                break;
            default:
                prioritySelect.value = 'low';
        }
    }
</script>
@endsection