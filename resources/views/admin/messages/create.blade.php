@extends('../themes/' . $activeTheme)
@section('title', isset($message) ? 'Edit Message' : 'Create New Message')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Messages', 'url' => route('admin.messages.index')],
['label' => isset($message) ? 'Edit Message' : 'Create Message', 'active' => true],
];
@endphp

@section('subcontent')
<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
    <div class="alert alert-success flex items-center mb-5">
        <x-base.lucide class="w-6 h-6 mr-2" icon="check-circle" />
        {{ session('success') }}
    </div>
    @endif

    @if (session()->has('error'))
    <div class="alert alert-danger flex items-center mb-5">
        <x-base.lucide class="w-6 h-6 mr-2" icon="alert-circle" />
        {{ session('error') }}
    </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center justify-between mt-8">
        <div>
            <h2 class="text-lg font-medium">{{ isset($message) ? 'Edit Message' : 'Create New Message' }}</h2>
            <div class="text-slate-500 text-sm mt-1">
                {{ isset($message) ? 'Update message details and recipients' : 'Compose and send a new message to drivers or other recipients' }}
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <x-base.button as="a" href="{{ route('admin.messages.index') }}" variant="outline-secondary" class="w-full sm:w-auto">
                <x-base.lucide class="w-4 h-4 mr-2" icon="arrow-left" />
                Back to Messages
            </x-base.button>
        </div>
    </div>

    <!-- Message Form -->
    <form action="{{ isset($message) ? route('admin.messages.update', $message) : route('admin.messages.store') }}" method="POST" class="mt-5">
        @csrf
        @if(isset($message))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2">
                <div class="box box--stacked">
                    <div class="box-header p-5 border-b border-slate-200/60">
                        <h3 class="box-title">Message Details</h3>
                    </div>
                    <div class="box-body p-5">
                        <!-- Subject -->
                        <div class="mb-5">
                            <x-base.form-label for="subject">Subject *</x-base.form-label>
                            <x-base.form-input 
                                type="text" 
                                name="subject" 
                                id="subject" 
                                value="{{ old('subject', $message->subject ?? '') }}" 
                                placeholder="Enter message subject..."
                                class="@error('subject') border-red-500 @enderror"
                                required 
                            />
                            @error('subject')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Message Content -->
                        <div class="mb-5">
                            <x-base.form-label for="message">Message Content *</x-base.form-label>
                            <x-base.form-textarea 
                                name="message" 
                                id="message" 
                                rows="8"
                                placeholder="Enter your message content here..."
                                class="@error('message') border-red-500 @enderror"
                                required
                            >{{ old('message', $message->message ?? '') }}</x-base.form-textarea>
                            @error('message')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                            <div class="text-slate-500 text-sm mt-1">
                                Maximum 2000 characters. Current: <span id="messageCount">{{ strlen(old('message', $message->message ?? '')) }}</span>
                            </div>
                        </div>

                        <!-- Priority -->
                        <div class="mb-5">
                            <x-base.form-label for="priority">Priority *</x-base.form-label>
                            <x-base.form-select 
                                name="priority" 
                                id="priority"
                                class="@error('priority') border-red-500 @enderror"
                                required
                            >
                                <option value="">Select Priority</option>
                                <option value="low" {{ old('priority', $message->priority ?? '') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="normal" {{ old('priority', $message->priority ?? '') == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="high" {{ old('priority', $message->priority ?? '') == 'high' ? 'selected' : '' }}>High</option>
                            </x-base.form-select>
                            @error('priority')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Recipients Section -->
                <div class="box box--stacked mt-5">
                    <div class="box-header p-5 border-b border-slate-200/60">
                        <h3 class="box-title">Recipients</h3>
                    </div>
                    <div class="box-body p-5">
                        <!-- Recipient Type Selection -->
                        <div class="mb-5">
                            <x-base.form-label class="text-base font-semibold text-slate-700 mb-3">Recipient Type *</x-base.form-label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                                <!-- All Drivers Card -->
                                <div class="recipient-card">
                                    <input 
                                        type="radio" 
                                        name="recipient_type" 
                                        value="all_drivers" 
                                        id="all_drivers"
                                        class="hidden recipient-radio"
                                        {{ old('recipient_type') == 'all_drivers' ? 'checked' : '' }}
                                    />
                                    <label for="all_drivers" class="recipient-label cursor-pointer block p-4 border-2 border-slate-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-all duration-200">
                                        <div class="flex items-center justify-center flex-col text-center">
                                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mb-3">
                                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                            </div>
                                            <h3 class="font-semibold text-slate-700 mb-1">All Drivers</h3>
                                            <p class="text-sm text-slate-500">Send to all active drivers</p>
                                        </div>
                                    </label>
                                </div>

                                <!-- Specific Drivers Card -->
                                <div class="recipient-card">
                                    <input 
                                        type="radio" 
                                        name="recipient_type" 
                                        value="specific_drivers" 
                                        id="specific_drivers"
                                        class="hidden recipient-radio"
                                        {{ old('recipient_type') == 'specific_drivers' ? 'checked' : '' }}
                                    />
                                    <label for="specific_drivers" class="recipient-label cursor-pointer block p-4 border-2 border-slate-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-all duration-200">
                                        <div class="flex items-center justify-center flex-col text-center">
                                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-3">
                                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                                </svg>
                                            </div>
                                            <h3 class="font-semibold text-slate-700 mb-1">Specific Drivers</h3>
                                            <p class="text-sm text-slate-500">Choose individual drivers</p>
                                        </div>
                                    </label>
                                </div>

                                <!-- Custom Emails Card -->
                                <div class="recipient-card">
                                    <input 
                                        type="radio" 
                                        name="recipient_type" 
                                        value="custom_emails" 
                                        id="custom_emails"
                                        class="hidden recipient-radio"
                                        {{ old('recipient_type') == 'custom_emails' ? 'checked' : '' }}
                                    />
                                    <label for="custom_emails" class="recipient-label cursor-pointer block p-4 border-2 border-slate-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-all duration-200">
                                        <div class="flex items-center justify-center flex-col text-center">
                                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mb-3">
                                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                            <h3 class="font-semibold text-slate-700 mb-1">Custom Emails</h3>
                                            <p class="text-sm text-slate-500">Enter email addresses</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @error('recipient_type')
                                <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Specific Drivers Selection -->
                        <div id="specific_drivers_section" class="mb-5" style="display: none;">
                            <x-base.form-label for="driver_ids">Select Drivers</x-base.form-label>
                            <x-base.form-select name="driver_ids[]" id="driver_ids" multiple size="8">
                                @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" 
                                    {{ in_array($driver->id, old('driver_ids', [])) ? 'selected' : '' }}>
                                    {{ $driver->user->name ?? 'N/A' }} ({{ $driver->user->email ?? 'N/A' }}) - {{ $driver->carrier->name ?? 'N/A' }}
                                </option>
                                @endforeach
                            </x-base.form-select>
                            <div class="text-slate-500 text-sm mt-1">
                                Hold Ctrl/Cmd to select multiple drivers
                            </div>
                        </div>

                        <!-- Custom Emails -->
                        <div id="custom_emails_section" class="mb-5" style="display: none;">
                            <x-base.form-label for="custom_emails">Custom Email Addresses</x-base.form-label>
                            <x-base.form-textarea 
                                name="custom_emails" 
                                id="custom_emails_input" 
                                rows="4"
                                placeholder="Enter email addresses separated by commas or new lines..."
                            >{{ old('custom_emails') }}</x-base.form-textarea>
                            <div class="text-slate-500 text-sm mt-1">
                                Enter email addresses separated by commas or new lines (e.g., user1@example.com, user2@example.com)
                            </div>
                        </div>

                        <!-- Carrier Filter for All Drivers -->
                        <div id="carrier_filter_section" class="mb-5" style="display: none;">
                            <x-base.form-label for="carrier_filter">Filter by Carrier (Optional)</x-base.form-label>
                            <x-base.form-select name="carrier_filter" id="carrier_filter">
                                <option value="">All Carriers</option>
                                @foreach($carriers as $carrier)
                                <option value="{{ $carrier->id }}" {{ old('carrier_filter') == $carrier->id ? 'selected' : '' }}>
                                    {{ $carrier->name }}
                                </option>
                                @endforeach
                            </x-base.form-select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Actions -->
                <div class="box box--stacked">
                    <div class="box-header p-5 border-b border-slate-200/60">
                        <h3 class="box-title">Actions</h3>
                    </div>
                    <div class="box-body p-5">
                        <div class="space-y-3">
                            <x-base.button type="submit" name="status" value="sent" variant="primary" class="w-full">
                                <x-base.lucide class="w-4 h-4 mr-2" icon="send" />
                                Send Message
                            </x-base.button>
                            
                            <x-base.button type="submit" name="status" value="draft" variant="outline-primary" class="w-full">
                                <x-base.lucide class="w-4 h-4 mr-2" icon="save" />
                                Save as Draft
                            </x-base.button>
                            
                            <x-base.button type="button" onclick="previewMessage()" variant="outline-secondary" class="w-full">
                                <x-base.lucide class="w-4 h-4 mr-2" icon="eye" />
                                Preview Message
                            </x-base.button>
                        </div>
                    </div>
                </div>

                <!-- Message Tips -->
                <div class="box box--stacked mt-5">
                    <div class="box-header p-5 border-b border-slate-200/60">
                        <h3 class="box-title">Tips</h3>
                    </div>
                    <div class="box-body p-5">
                        <div class="space-y-3 text-sm text-slate-600">
                            <div class="flex items-start gap-2">
                                <x-base.lucide class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" icon="info" />
                                <div>Use clear and concise subject lines for better engagement.</div>
                            </div>
                            <div class="flex items-start gap-2">
                                <x-base.lucide class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" icon="check" />
                                <div>High priority messages will be highlighted in the recipient's inbox.</div>
                            </div>
                            <div class="flex items-start gap-2">
                                <x-base.lucide class="w-4 h-4 text-yellow-500 mt-0.5 flex-shrink-0" icon="clock" />
                                <div>Draft messages can be edited and sent later.</div>
                            </div>
                            <div class="flex items-start gap-2">
                                <x-base.lucide class="w-4 h-4 text-purple-500 mt-0.5 flex-shrink-0" icon="users" />
                                <div>You can track delivery and read status for each recipient.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Preview Modal -->
<x-base.dialog id="previewModal" size="xl">
    <x-base.dialog.panel>
        <x-base.dialog.title>
            <h2 class="mr-auto text-base font-medium">Message Preview</h2>
        </x-base.dialog.title>
        <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12">
                <div class="border rounded-lg p-4 bg-slate-50">
                    <div class="mb-3">
                        <strong>Subject:</strong> <span id="preview-subject"></span>
                    </div>
                    <div class="mb-3">
                        <strong>Priority:</strong> <span id="preview-priority" class="px-2 py-1 rounded-full text-xs font-medium"></span>
                    </div>
                    <div class="mb-3">
                        <strong>Message:</strong>
                        <div id="preview-message" class="mt-2 p-3 bg-white rounded border whitespace-pre-wrap"></div>
                    </div>
                    <div>
                        <strong>Recipients:</strong> <span id="preview-recipients"></span>
                    </div>
                </div>
            </div>
        </x-base.dialog.description>
        <x-base.dialog.footer>
            <x-base.button type="button" variant="outline-secondary" class="w-20 mr-1" data-tw-dismiss="modal">
                Close
            </x-base.button>
        </x-base.dialog.footer>
    </x-base.dialog.panel>
</x-base.dialog>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter for message
    const messageTextarea = document.getElementById('message');
    const messageCount = document.getElementById('messageCount');
    
    messageTextarea.addEventListener('input', function() {
        messageCount.textContent = this.value.length;
        if (this.value.length > 2000) {
            messageCount.classList.add('text-red-500');
        } else {
            messageCount.classList.remove('text-red-500');
        }
    });

    // Recipient type handling
    const recipientTypeRadios = document.querySelectorAll('input[name="recipient_type"]');
    const specificDriversSection = document.getElementById('specific_drivers_section');
    const customEmailsSection = document.getElementById('custom_emails_section');
    const carrierFilterSection = document.getElementById('carrier_filter_section');

    recipientTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Hide all sections first
            specificDriversSection.style.display = 'none';
            customEmailsSection.style.display = 'none';
            carrierFilterSection.style.display = 'none';

            // Show relevant section
            if (this.value === 'specific_drivers') {
                specificDriversSection.style.display = 'block';
            } else if (this.value === 'custom_emails') {
                customEmailsSection.style.display = 'block';
            } else if (this.value === 'all_drivers') {
                carrierFilterSection.style.display = 'block';
            }
        });
    });

    // Trigger change event on page load to show correct section
    const checkedRadio = document.querySelector('input[name="recipient_type"]:checked');
    if (checkedRadio) {
        checkedRadio.dispatchEvent(new Event('change'));
    }
});

function previewMessage() {
    const subject = document.getElementById('subject').value;
    const message = document.getElementById('message').value;
    const priority = document.getElementById('priority').value;
    const recipientType = document.querySelector('input[name="recipient_type"]:checked')?.value;

    // Update preview content
    document.getElementById('preview-subject').textContent = subject || 'No subject';
    document.getElementById('preview-message').textContent = message || 'No message content';
    
    const prioritySpan = document.getElementById('preview-priority');
    prioritySpan.textContent = priority ? priority.charAt(0).toUpperCase() + priority.slice(1) : 'Not selected';
    
    // Set priority color
    prioritySpan.className = 'px-2 py-1 rounded-full text-xs font-medium ';
    if (priority === 'high') {
        prioritySpan.className += 'bg-red-100 text-red-800';
    } else if (priority === 'normal') {
        prioritySpan.className += 'bg-blue-100 text-blue-800';
    } else if (priority === 'low') {
        prioritySpan.className += 'bg-gray-100 text-gray-800';
    } else {
        prioritySpan.className += 'bg-slate-100 text-slate-800';
    }

    // Update recipients info
    let recipientsText = 'No recipients selected';
    if (recipientType === 'all_drivers') {
        const carrierFilter = document.getElementById('carrier_filter').value;
        recipientsText = carrierFilter ? 'All drivers from selected carrier' : 'All drivers';
    } else if (recipientType === 'specific_drivers') {
        const selectedDrivers = document.getElementById('driver_ids').selectedOptions;
        recipientsText = `${selectedDrivers.length} selected driver(s)`;
    } else if (recipientType === 'custom_emails') {
        const customEmails = document.getElementById('custom_emails_input').value;
        const emailCount = customEmails.split(/[,\n]/).filter(email => email.trim()).length;
        recipientsText = `${emailCount} custom email(s)`;
    }
    
    document.getElementById('preview-recipients').textContent = recipientsText;

    // Show modal
    const modal = tailwind.Modal.getOrCreateInstance(document.querySelector("#previewModal"));
    modal.show();
}
</script>

<style>
/* Custom styles for recipient cards */
.recipient-card .recipient-radio:checked + .recipient-label {
    border-color: #3b82f6 !important;
    background-color: #eff6ff !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.recipient-card .recipient-radio:checked + .recipient-label h3 {
    color: #1d4ed8 !important;
}

.recipient-card .recipient-radio:checked + .recipient-label p {
    color: #3730a3 !important;
}

.recipient-card .recipient-label:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.recipient-card .recipient-radio:checked + .recipient-label:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

/* Animation for smooth transitions */
.recipient-card .recipient-label {
    transition: all 0.2s ease-in-out;
}
</style>

@endpush
@endsection