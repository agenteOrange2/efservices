@extends('../themes/' . $activeTheme)
@section('title', 'Messages Dashboard')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Messages', 'url' => route('admin.messages.index')],
['label' => 'Dashboard', 'active' => true],
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

    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center justify-between mt-8">
        <div>
            <h2 class="text-lg font-medium">Messages Dashboard</h2>
            <div class="text-slate-500 text-sm mt-1">
                Overview of message statistics and recent activity
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <x-base.button as="a" href="{{ route('admin.messages.create') }}" variant="primary" class="w-full sm:w-auto">
                <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                New Message
            </x-base.button>
            <x-base.button as="a" href="{{ route('admin.messages.index') }}" variant="outline-secondary" class="w-full sm:w-auto">
                <x-base.lucide class="w-4 h-4 mr-2" icon="list" />
                All Messages
            </x-base.button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
        <!-- Total Messages -->
        <div class="box box--stacked">
            <div class="box-body p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg">
                            <x-base.lucide class="w-6 h-6 text-blue-600" icon="mail" />
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</div>
                        <div class="text-sm text-slate-500">Total Messages</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sent Messages -->
        <div class="box box--stacked">
            <div class="box-body p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg">
                            <x-base.lucide class="w-6 h-6 text-green-600" icon="send" />
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-slate-900">{{ $stats['sent'] }}</div>
                        <div class="text-sm text-slate-500">Sent Messages</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Draft Messages -->
        <div class="box box--stacked">
            <div class="box-body p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-lg">
                            <x-base.lucide class="w-6 h-6 text-yellow-600" icon="edit" />
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-slate-900">{{ $stats['draft'] }}</div>
                        <div class="text-sm text-slate-500">Draft Messages</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Failed Messages -->
        <div class="box box--stacked">
            <div class="box-body p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-lg">
                            <x-base.lucide class="w-6 h-6 text-red-600" icon="alert-circle" />
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-slate-900">{{ $stats['failed'] }}</div>
                        <div class="text-sm text-slate-500">Failed Messages</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
        <!-- Message Status Distribution -->
        <div class="box box--stacked">
            <div class="box-header p-5 border-b border-slate-200/60">
                <h3 class="box-title">Message Status Distribution</h3>
            </div>
            <div class="box-body p-5">
                <div class="space-y-4">
                    @foreach($statusDistribution as $status => $count)
                    @php
                        $percentage = $stats['total'] > 0 ? round(($count / $stats['total']) * 100, 1) : 0;
                        $colorClass = match($status) {
                            'sent' => 'bg-green-500',
                            'draft' => 'bg-yellow-500',
                            'failed' => 'bg-red-500',
                            default => 'bg-blue-500'
                        };
                    @endphp
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full {{ $colorClass }} mr-3"></div>
                            <span class="text-sm font-medium">{{ ucfirst($status) }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm text-slate-600 mr-2">{{ $count }}</span>
                            <span class="text-xs text-slate-500">({{ $percentage }}%)</span>
                        </div>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div class="{{ $colorClass }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Priority Distribution -->
        <div class="box box--stacked">
            <div class="box-header p-5 border-b border-slate-200/60">
                <h3 class="box-title">Priority Distribution</h3>
            </div>
            <div class="box-body p-5">
                <div class="space-y-4">
                    @foreach($priorityDistribution as $priority => $count)
                    @php
                        $percentage = $stats['total'] > 0 ? round(($count / $stats['total']) * 100, 1) : 0;
                        $colorClass = match($priority) {
                            'high' => 'bg-red-500',
                            'normal' => 'bg-blue-500',
                            'low' => 'bg-gray-500',
                            default => 'bg-slate-500'
                        };
                    @endphp
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full {{ $colorClass }} mr-3"></div>
                            <span class="text-sm font-medium">{{ ucfirst($priority) }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm text-slate-600 mr-2">{{ $count }}</span>
                            <span class="text-xs text-slate-500">({{ $percentage }}%)</span>
                        </div>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div class="{{ $colorClass }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Messages and Delivery Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8">
        <!-- Recent Messages -->
        <div class="lg:col-span-2">
            <div class="box box--stacked">
                <div class="box-header p-5 border-b border-slate-200/60 flex items-center justify-between">
                    <h3 class="box-title">Recent Messages</h3>
                    <x-base.button as="a" href="{{ route('admin.messages.index') }}" variant="outline-secondary" size="sm">
                        View All
                    </x-base.button>
                </div>
                <div class="box-body p-0">
                    @if($recentMessages->count() > 0)
                    <div class="overflow-x-auto">
                        <x-base.table>
                            <x-base.table.thead>
                                <x-base.table.tr>
                                    <x-base.table.th class="whitespace-nowrap">Subject</x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">Status</x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">Priority</x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">Recipients</x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">Date</x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">Actions</x-base.table.th>
                                </x-base.table.tr>
                            </x-base.table.thead>
                            <x-base.table.tbody>
                                @foreach($recentMessages as $message)
                                <x-base.table.tr>
                                    <x-base.table.td>
                                        <div class="font-medium">{{ Str::limit($message->subject, 30) }}</div>
                                        <div class="text-slate-500 text-xs">
                                            {{ $message->sender->name ?? 'System' }}
                                        </div>
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $message->status_color }}">
                                            {{ ucfirst($message->status) }}
                                        </span>
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $message->priority_color }}">
                                            {{ ucfirst($message->priority) }}
                                        </span>
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        <span class="text-sm">{{ $message->recipients_count }}</span>
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        <div class="text-sm">{{ $message->created_at->format('M d, Y') }}</div>
                                        <div class="text-xs text-slate-500">{{ $message->created_at->format('H:i') }}</div>
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        <div class="flex items-center gap-2">
                                            <x-base.button as="a" href="{{ route('admin.messages.show', $message) }}" variant="outline-secondary" size="sm">
                                                <x-base.lucide class="w-3 h-3" icon="eye" />
                                            </x-base.button>
                                            @if($message->status === 'draft')
                                            <x-base.button as="a" href="{{ route('admin.messages.edit', $message) }}" variant="outline-primary" size="sm">
                                                <x-base.lucide class="w-3 h-3" icon="edit" />
                                            </x-base.button>
                                            @endif
                                        </div>
                                    </x-base.table.td>
                                </x-base.table.tr>
                                @endforeach
                            </x-base.table.tbody>
                        </x-base.table>
                    </div>
                    @else
                    <div class="p-8 text-center">
                        <x-base.lucide class="w-12 h-12 text-slate-400 mx-auto mb-4" icon="mail" />
                        <h3 class="text-lg font-medium text-slate-900 mb-2">No Messages Yet</h3>
                        <p class="text-slate-500 mb-4">Start by creating your first message to drivers.</p>
                        <x-base.button as="a" href="{{ route('admin.messages.create') }}" variant="primary">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                            Create Message
                        </x-base.button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Delivery Statistics -->
        <div class="lg:col-span-1">
            <div class="box box--stacked">
                <div class="box-header p-5 border-b border-slate-200/60">
                    <h3 class="box-title">Delivery Statistics</h3>
                </div>
                <div class="box-body p-5">
                    <div class="space-y-4">
                        <!-- Total Recipients -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <x-base.lucide class="w-4 h-4 text-blue-500 mr-2" icon="users" />
                                <span class="text-sm">Total Recipients</span>
                            </div>
                            <span class="text-sm font-medium">{{ $deliveryStats['total'] }}</span>
                        </div>

                        <!-- Delivered -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <x-base.lucide class="w-4 h-4 text-green-500 mr-2" icon="check-circle" />
                                <span class="text-sm">Delivered</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm font-medium mr-2">{{ $deliveryStats['delivered'] }}</span>
                                <span class="text-xs text-slate-500">
                                    ({{ $deliveryStats['total'] > 0 ? round(($deliveryStats['delivered'] / $deliveryStats['total']) * 100, 1) : 0 }}%)
                                </span>
                            </div>
                        </div>

                        <!-- Pending -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <x-base.lucide class="w-4 h-4 text-yellow-500 mr-2" icon="clock" />
                                <span class="text-sm">Pending</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm font-medium mr-2">{{ $deliveryStats['pending'] }}</span>
                                <span class="text-xs text-slate-500">
                                    ({{ $deliveryStats['total'] > 0 ? round(($deliveryStats['pending'] / $deliveryStats['total']) * 100, 1) : 0 }}%)
                                </span>
                            </div>
                        </div>

                        <!-- Failed -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <x-base.lucide class="w-4 h-4 text-red-500 mr-2" icon="x-circle" />
                                <span class="text-sm">Failed</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm font-medium mr-2">{{ $deliveryStats['failed'] }}</span>
                                <span class="text-xs text-slate-500">
                                    ({{ $deliveryStats['total'] > 0 ? round(($deliveryStats['failed'] / $deliveryStats['total']) * 100, 1) : 0 }}%)
                                </span>
                            </div>
                        </div>

                        <!-- Read -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <x-base.lucide class="w-4 h-4 text-purple-500 mr-2" icon="eye" />
                                <span class="text-sm">Read</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm font-medium mr-2">{{ $deliveryStats['read'] }}</span>
                                <span class="text-xs text-slate-500">
                                    ({{ $deliveryStats['delivered'] > 0 ? round(($deliveryStats['read'] / $deliveryStats['delivered']) * 100, 1) : 0 }}%)
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Rate Progress -->
                    <div class="mt-6 pt-4 border-t border-slate-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium">Delivery Rate</span>
                            <span class="text-sm text-slate-600">
                                {{ $deliveryStats['total'] > 0 ? round(($deliveryStats['delivered'] / $deliveryStats['total']) * 100, 1) : 0 }}%
                            </span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $deliveryStats['total'] > 0 ? round(($deliveryStats['delivered'] / $deliveryStats['total']) * 100, 1) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="box box--stacked mt-5">
                <div class="box-header p-5 border-b border-slate-200/60">
                    <h3 class="box-title">Quick Actions</h3>
                </div>
                <div class="box-body p-5">
                    <div class="space-y-3">
                        <x-base.button as="a" href="{{ route('admin.messages.create') }}" variant="primary" class="w-full">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                            New Message
                        </x-base.button>
                        
                        @if($stats['draft'] > 0)
                        <x-base.button as="a" href="{{ route('admin.messages.index', ['status' => 'draft']) }}" variant="outline-primary" class="w-full">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="edit" />
                            View Drafts ({{ $stats['draft'] }})
                        </x-base.button>
                        @endif
                        
                        @if($stats['failed'] > 0)
                        <x-base.button as="a" href="{{ route('admin.messages.index', ['status' => 'failed']) }}" variant="outline-danger" class="w-full">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="alert-circle" />
                            Failed Messages ({{ $stats['failed'] }})
                        </x-base.button>
                        @endif
                        
                        <x-base.button as="a" href="{{ route('admin.messages.index') }}" variant="outline-secondary" class="w-full">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="list" />
                            All Messages
                        </x-base.button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection