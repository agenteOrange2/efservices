@extends('../themes/' . $activeTheme)
@section('title', 'Edit Message')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Messages', 'url' => route('admin.messages.index')],
['label' => 'Edit Message', 'active' => true],
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
            <h2 class="text-lg font-medium">Edit Message</h2>
            <div class="text-slate-500 text-sm mt-1">
                Update message details and recipients
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <x-base.button as="a" href="{{ route('admin.messages.show', $message) }}" variant="outline-secondary" class="w-full sm:w-auto">
                <x-base.lucide class="w-4 h-4 mr-2" icon="arrow-left" />
                Back to Message
            </x-base.button>
        </div>
    </div>

    <!-- Message Form -->
    <form action="{{ route('admin.messages.update', $message) }}" method="POST" class="mt-5">
        @csrf
        @method('PUT')

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
                                value="{{ old('subject', $message->subject) }}" 
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
                            >{{ old('message', $message->message) }}</x-base.form-textarea>
                            @error('message')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                            <div class="text-slate-500 text-sm mt-1">
                                Maximum 2000 characters. Current: <span id="messageCount">{{ strlen(old('message', $message->message)) }}</span>
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
                                <option value="low" {{ old('priority', $message->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="normal" {{ old('priority', $message->priority) == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="high" {{ old('priority', $message->priority) == 'high' ? 'selected' : '' }}>High</option>
                            </x-base.form-select>
                            @error('priority')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($message->status === 'draft')
                        <!-- Status -->
                        <div class="mb-5">
                            <x-base.form-label for="status">Status</x-base.form-label>
                            <x-base.form-select name="status" id="status">
                                <option value="draft" {{ old('status', $message->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="sent" {{ old('status', $message->status) == 'sent' ? 'selected' : '' }}>Send Now</option>
                            </x-base.form-select>
                            <div class="text-slate-500 text-sm mt-1">
                                Change to "Send Now" to immediately send this message to all recipients.
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Current Recipients -->
                @if($message->recipients->count() > 0)
                <div class="box box--stacked mt-5">
                    <div class="box-header p-5 border-b border-slate-200/60">
                        <h3 class="box-title">Current Recipients ({{ $message->recipients->count() }})</h3>
                    </div>
                    <div class="box-body p-0">
                        <div class="overflow-x-auto">
                            <x-base.table>
                                <x-base.table.thead>
                                    <x-base.table.tr>
                                        <x-base.table.th class="whitespace-nowrap">Recipient</x-base.table.th>
                                        <x-base.table.th class="whitespace-nowrap">Type</x-base.table.th>
                                        <x-base.table.th class="whitespace-nowrap">Status</x-base.table.th>
                                        @if($message->status === 'draft')
                                        <x-base.table.th class="whitespace-nowrap">Action</x-base.table.th>
                                        @endif
                                    </x-base.table.tr>
                                </x-base.table.thead>
                                <x-base.table.tbody>
                                    @foreach($message->recipients as $recipient)
                                    <x-base.table.tr>
                                        <x-base.table.td>
                                            <div class="font-medium">{{ $recipient->name }}</div>
                                            <div class="text-slate-500 text-xs">{{ $recipient->email }}</div>
                                        </x-base.table.td>
                                        <x-base.table.td>
                                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ ucfirst($recipient->recipient_type) }}
                                            </span>
                                        </x-base.table.td>
                                        <x-base.table.td>
                                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $recipient->delivery_status_color }}">
                                                {{ ucfirst($recipient->delivery_status) }}
                                            </span>
                                        </x-base.table.td>
                                        @if($message->status === 'draft')
                                        <x-base.table.td>
                                            <x-base.button 
                                                type="button" 
                                                variant="outline-danger" 
                                                size="sm"
                                                onclick="removeRecipient({{ $recipient->id }})"
                                            >
                                                <x-base.lucide class="w-3 h-3" icon="x" />
                                            </x-base.button>
                                        </x-base.table.td>
                                        @endif
                                    </x-base.table.tr>
                                    @endforeach
                                </x-base.table.tbody>
                            </x-base.table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Add Recipients (only for draft messages) -->
                @if($message->status === 'draft')
                <div class="box box--stacked mt-5">
                    <div class="box-header p-5 border-b border-slate-200/60">
                        <h3 class="box-title">Add Recipients</h3>
                    </div>
                    <div class="box-body p-5">
                        <!-- Recipient Type Selection -->
                        <div class="mb-5">
                            <x-base.form-label>Add Recipient Type</x-base.form-label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                <div>
                                    <x-base.form-check>
                                        <x-base.form-check.input 
                                            type="radio" 
                                            name="add_recipient_type" 
                                            value="specific_drivers" 
                                            id="add_specific_drivers"
                                        />
                                        <x-base.form-check.label for="add_specific_drivers">
                                            Specific Drivers
                                        </x-base.form-check.label>
                                    </x-base.form-check>
                                </div>
                                <div>
                                    <x-base.form-check>
                                        <x-base.form-check.input 
                                            type="radio" 
                                            name="add_recipient_type" 
                                            value="custom_emails" 
                                            id="add_custom_emails"
                                        />
                                        <x-base.form-check.label for="add_custom_emails">
                                            Custom Emails
                                        </x-base.form-check.label>
                                    </x-base.form-check>
                                </div>
                            </div>
                        </div>

                        <!-- Specific Drivers Selection -->
                        <div id="add_specific_drivers_section" class="mb-5" style="display: none;">
                            <x-base.form-label for="add_driver_ids">Select Drivers to Add</x-base.form-label>
                            <x-base.form-select name="add_driver_ids[]" id="add_driver_ids" multiple size="6">
                                @foreach($availableDrivers as $driver)
                                <option value="{{ $driver->id }}">
                                    {{ $driver->user->name ?? 'N/A' }} ({{ $driver->user->email ?? 'N/A' }}) - {{ $driver->carrier->name ?? 'N/A' }}
                                </option>
                                @endforeach
                            </x-base.form-select>
                            <div class="text-slate-500 text-sm mt-1">
                                Hold Ctrl/Cmd to select multiple drivers
                            </div>
                        </div>

                        <!-- Custom Emails -->
                        <div id="add_custom_emails_section" class="mb-5" style="display: none;">
                            <x-base.form-label for="add_custom_emails">Additional Email Addresses</x-base.form-label>
                            <x-base.form-textarea 
                                name="add_custom_emails" 
                                id="add_custom_emails_input" 
                                rows="3"
                                placeholder="Enter email addresses separated by commas or new lines..."
                            ></x-base.form-textarea>
                            <div class="text-slate-500 text-sm mt-1">
                                Enter email addresses separated by commas or new lines
                            </div>
                        </div>
                    </div>
                </div>
                @endif
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
                            @if($message->status === 'draft')
                            <x-base.button type="submit" name="action" value="send" variant="primary" class="w-full">
                                <x-base.lucide class="w-4 h-4 mr-2" icon="send" />
                                Update & Send
                            </x-base.button>
                            
                            <x-base.button type="submit" name="action" value="draft" variant="outline-primary" class="w-full">
                                <x-base.lucide class="w-4 h-4 mr-2" icon="save" />
                                Save Changes
                            </x-base.button>
                            @else
                            <x-base.button type="submit" variant="primary" class="w-full">
                                <x-base.lucide class="w-4 h-4 mr-2" icon="save" />
                                Update Message
                            </x-base.button>
                            @endif
                            
                            <x-base.button type="button" onclick="previewMessage()" variant="outline-secondary" class="w-full">
                                <x-base.lucide class="w-4 h-4 mr-2" icon="eye" />
                                Preview Changes
                            </x-base.button>
                        </div>
                    </div>
                </div>

                <!-- Message Info -->
                <div class="box box--stacked mt-5">
                    <div class="box-header p-5 border-b border-slate-200/60">
                        <h3 class="box-title">Message Info</h3>
                    </div>
                    <div class="box-body p-5">
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Status:</span>
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $message->status_color }}">
                                    {{ ucfirst($message->status) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Created:</span>
                                <span>{{ $message->created_at->format('M d, Y') }}</span>
                            </div>
                            @if($message->sent_at)
                            <div class="flex justify-between">
                                <span class="text-slate-600">Sent:</span>
                                <span>{{ $message->sent_at->format('M d, Y H:i') }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-slate-600">Recipients:</span>
                                <span>{{ $message->recipients->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($message->status === 'draft')
                <!-- Edit Limitations -->
                <div class="box box--stacked mt-5">
                    <div class="box-header p-5 border-b border-slate-200/60">
                        <h3 class="box-title">Edit Notes</h3>
                    </div>
                    <div class="box-body p-5">
                        <div class="space-y-3 text-sm text-slate-600">
                            <div class="flex items-start gap-2">
                                <x-base.lucide class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" icon="info" />
                                <div>You can edit all message details while in draft status.</div>
                            </div>
                            <div class="flex items-start gap-2">
                                <x-base.lucide class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" icon="users" />
                                <div>Add or remove recipients as needed.</div>
                            </div>
                            <div class="flex items-start gap-2">
                                <x-base.lucide class="w-4 h-4 text-yellow-500 mt-0.5 flex-shrink-0" icon="send" />
                                <div>Once sent, only basic details can be updated.</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
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

    // Add recipient type handling
    const addRecipientTypeRadios = document.querySelectorAll('input[name="add_recipient_type"]');
    const addSpecificDriversSection = document.getElementById('add_specific_drivers_section');
    const addCustomEmailsSection = document.getElementById('add_custom_emails_section');

    addRecipientTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Hide all sections first
            addSpecificDriversSection.style.display = 'none';
            addCustomEmailsSection.style.display = 'none';

            // Show relevant section
            if (this.value === 'specific_drivers') {
                addSpecificDriversSection.style.display = 'block';
            } else if (this.value === 'custom_emails') {
                addCustomEmailsSection.style.display = 'block';
            }
        });
    });
});

function previewMessage() {
    const subject = document.getElementById('subject').value;
    const message = document.getElementById('message').value;
    const priority = document.getElementById('priority').value;

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
    const currentRecipients = {{ $message->recipients->count() }};
    document.getElementById('preview-recipients').textContent = `${currentRecipients} current recipient(s)`;

    // Show modal
    const modal = tailwind.Modal.getOrCreateInstance(document.querySelector("#previewModal"));
    modal.show();
}

function removeRecipient(recipientId) {
    if (confirm('Are you sure you want to remove this recipient?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/messages/{{ $message->id }}/recipients/${recipientId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection