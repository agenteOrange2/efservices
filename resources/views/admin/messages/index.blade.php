@extends('../themes/' . $activeTheme)
@section('title', 'Messages Management')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Messages', 'active' => true],
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
            <h2 class="text-lg font-medium">Messages Management</h2>
            <div class="text-slate-500 text-sm mt-1">
                Manage and track all admin messages sent to drivers and other recipients
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <x-base.button as="a" href="{{ route('admin.messages.create') }}" variant="primary" class="w-full sm:w-auto">
                <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                New Message
            </x-base.button>
            <x-base.button as="a" href="{{ route('admin.messages.dashboard') }}" variant="outline-primary" class="w-full sm:w-auto">
                <x-base.lucide class="w-4 h-4 mr-2" icon="bar-chart-3" />
                Dashboard
            </x-base.button>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="box box--stacked mt-5 p-3">
        <div class="box-header">
            <h3 class="box-title">Filter Messages</h3>
        </div>
        <div class="box-body p-5">
            <form action="{{ route('admin.messages.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <x-base.form-label for="search">Search Messages</x-base.form-label>
                    <x-base.form-input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Subject or message content..." />
                </div>
                <div>
                    <x-base.form-label for="status">Status</x-base.form-label>
                    <x-base.form-select name="status" id="status">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </x-base.form-select>
                </div>
                <div>
                    <x-base.form-label for="priority">Priority</x-base.form-label>
                    <x-base.form-select name="priority" id="priority">
                        <option value="">All Priorities</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    </x-base.form-select>
                </div>
                <div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <x-base.form-label for="date_from">Date (from)</x-base.form-label>
                            <x-base.litepicker name="date_from" value="{{ request('date_from') }}" placeholder="Select date" />
                        </div>
                        <div>
                            <x-base.form-label for="date_to">Date (to)</x-base.form-label>
                            <x-base.litepicker name="date_to" value="{{ request('date_to') }}" placeholder="Select date" />
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-4 flex gap-2">
                    <x-base.button type="submit" variant="primary" class="flex-1">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="filter" />
                        Apply Filters
                    </x-base.button>
                    <x-base.button as="a" href="{{ route('admin.messages.index') }}" variant="outline-secondary">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="x" />
                        Clear
                    </x-base.button>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages Table -->
    <div class="box box--stacked mt-5">
        <div class="box-header p-3">
            <h3 class="box-title">Messages List ({{ $messages->total() }} total)</h3>
        </div>
        <div class="box-body p-0">
            @if($messages->count() > 0)
            <div class="overflow-x-auto">
                <x-base.table class="border-separate border-spacing-y-[10px]">
                    <x-base.table.thead>
                        <x-base.table.tr>
                            <x-base.table.th class="whitespace-nowrap">Subject</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">Sender</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">Recipients</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">Priority</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">Status</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">Sent Date</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap text-center">Actions</x-base.table.th>
                        </x-base.table.tr>
                    </x-base.table.thead>
                    <x-base.table.tbody>
                        @foreach($messages as $message)
                        <x-base.table.tr>
                            <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-slate-200 rounded-full flex items-center justify-center mr-3">
                                        <x-base.lucide class="w-5 h-5 text-slate-500" icon="mail" />
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ Str::limit($message->subject, 40) }}</div>
                                        <div class="text-slate-500 text-xs">ID: {{ $message->id }}</div>
                                    </div>
                                </div>
                            </x-base.table.td>
                            <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                <div class="font-medium">{{ $message->sender->name ?? 'System' }}</div>
                                <div class="text-slate-500 text-xs">{{ $message->sender->email ?? 'system@efservices.com' }}</div>
                            </x-base.table.td>
                            <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                <div class="font-medium">{{ $message->recipients->count() }} recipient(s)</div>
                                <div class="text-slate-500 text-xs">
                                    @php
                                        $deliveredCount = $message->recipients->where('delivery_status', 'delivered')->count();
                                        $readCount = $message->recipients->whereNotNull('read_at')->count();
                                    @endphp
                                    {{ $deliveredCount }} delivered, {{ $readCount }} read
                                </div>
                            </x-base.table.td>
                            <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $message->priority_color }}">
                                    {{ ucfirst($message->priority) }}
                                </span>
                            </x-base.table.td>
                            <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $message->status_color }}">
                                    {{ ucfirst($message->status) }}
                                </span>
                            </x-base.table.td>
                            <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                @if($message->sent_at)
                                <div class="font-medium">{{ $message->sent_at->format('M d, Y') }}</div>
                                <div class="text-slate-500 text-xs">{{ $message->sent_at->format('H:i') }}</div>
                                @else
                                <span class="text-slate-400">Not sent</span>
                                @endif
                            </x-base.table.td>
                            <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <x-base.button as="a" href="{{ route('admin.messages.show', $message) }}" variant="outline-primary" size="sm" title="View Message Details">
                                        <x-base.lucide class="w-4 h-4" icon="eye" />
                                    </x-base.button>

                                    @if($message->status === 'draft')
                                    <x-base.button as="a" href="{{ route('admin.messages.edit', $message) }}" variant="outline-warning" size="sm" title="Edit Message">
                                        <x-base.lucide class="w-4 h-4" icon="edit" />
                                    </x-base.button>
                                    @endif

                                    <x-base.menu class="h-5">
                                        <x-base.menu.button class="h-5 w-5 text-slate-500">
                                            <x-base.lucide class="h-5 w-5 fill-slate-400/70 stroke-slate-400/70" icon="MoreVertical" />
                                        </x-base.menu.button>
                                        <x-base.menu.items class="w-40">
                                            @if($message->status === 'draft')
                                            <x-base.menu.item href="{{ route('admin.messages.edit', $message) }}">
                                                <x-base.lucide class="mr-2 h-4 w-4" icon="edit" />
                                                Edit
                                            </x-base.menu.item>
                                            @endif
                                            <x-base.menu.item href="{{ route('admin.messages.show', $message) }}">
                                                <x-base.lucide class="mr-2 h-4 w-4" icon="eye" />
                                                View Details
                                            </x-base.menu.item>
                                            @if($message->status !== 'draft')
                                            <x-base.menu.item onclick="duplicateMessage({{ $message->id }})">
                                                <x-base.lucide class="mr-2 h-4 w-4" icon="copy" />
                                                Duplicate
                                            </x-base.menu.item>
                                            @endif
                                            <x-base.menu.item class="text-red-600" onclick="deleteMessage({{ $message->id }})">
                                                <x-base.lucide class="mr-2 h-4 w-4" icon="trash-2" />
                                                Delete
                                            </x-base.menu.item>
                                        </x-base.menu.items>
                                    </x-base.menu>
                                </div>
                            </x-base.table.td>
                        </x-base.table.tr>
                        @endforeach
                    </x-base.table.tbody>
                </x-base.table>
            </div>

            <!-- Pagination -->
            <div class="p-5">
                {{ $messages->appends(request()->query())->links() }}
            </div>
            @else
            <div class="p-10 text-center">
                <x-base.lucide class="w-16 h-16 text-slate-300 mx-auto mb-4" icon="mail" />
                <h3 class="text-lg font-medium text-slate-600 mb-2">No Messages Found</h3>
                <p class="text-slate-500 mb-4">There are no messages matching your current filters.</p>
                <x-base.button as="a" href="{{ route('admin.messages.create') }}" variant="primary">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                    Create First Message
                </x-base.button>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<x-base.dialog id="deleteMessageModal">
    <x-base.dialog.panel>
        <div class="p-5 text-center">
            <x-base.lucide class="w-16 h-16 text-red-500 mx-auto mt-3" icon="x-circle" />
            <div class="text-3xl mt-5">Are you sure?</div>
            <div class="text-slate-500 mt-2">
                Do you really want to delete this message? This process cannot be undone.
            </div>
        </div>
        <div class="px-5 pb-8 text-center">
            <x-base.button type="button" variant="outline-secondary" class="w-24 mr-1" data-tw-dismiss="modal">
                Cancel
            </x-base.button>
            <x-base.button type="button" variant="danger" class="w-24" onclick="confirmDelete()">
                Delete
            </x-base.button>
        </div>
    </x-base.dialog.panel>
</x-base.dialog>

@push('scripts')
<script>
let messageToDelete = null;

function deleteMessage(messageId) {
    messageToDelete = messageId;
    const modal = tailwind.Modal.getOrCreateInstance(document.querySelector("#deleteMessageModal"));
    modal.show();
}

function confirmDelete() {
    if (messageToDelete) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/messages/${messageToDelete}`;
        
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

function duplicateMessage(messageId) {
    // Redirect to create page with message ID to duplicate
    window.location.href = `/admin/messages/create?duplicate=${messageId}`;
}
</script>
@endpush
@endsection