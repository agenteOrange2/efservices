@extends('../themes/' . $activeTheme)
@section('title', 'Notifications')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Users', 'url' => route('admin.users.index')],
        ['label' => 'Super Admins', 'active' => true],
    ];
@endphp

@section('subcontent')
<div class="content">
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Notifications</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            @if(Auth::user()->unreadNotifications->count() > 0)
                <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST" class="mr-2">
                    @csrf
                    <button type="submit" class="btn btn-primary shadow-md">Mark All as Read</button>
                </form>
            @endif
            
            @if(Auth::user()->notifications->count() > 0)
                <form action="{{ route('admin.notifications.delete-all') }}" method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete all filtered notifications?');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="filter" value="{{ $currentFilter }}">
                    @if($currentType)
                        <input type="hidden" name="type" value="{{ $currentType }}">
                    @endif
                    <button type="submit" class="btn btn-danger shadow-md">Delete All</button>
                </form>
            @endif
        </div>
    </div>
    
    <div class="grid grid-cols-12 gap-6 mt-5">
        <!-- Filter Panel -->
        <div class="col-span-12 md:col-span-3 xl:col-span-2">
            <div class="box p-5 intro-y">
                <div class="flex flex-col">
                    <div class="text-base font-medium mb-3">Filter</div>
                    
                    <div class="space-y-1">
                        <a href="{{ route('admin.notifications.index') }}" 
                           class="flex items-center px-3 py-2 rounded-md {{ $currentFilter === 'all' ? 'bg-primary text-white' : 'hover:bg-slate-100' }}">
                            <span class="truncate">All Notifications</span>
                        </a>
                        <a href="{{ route('admin.notifications.index', ['filter' => 'unread', 'type' => $currentType]) }}" 
                           class="flex items-center px-3 py-2 rounded-md {{ $currentFilter === 'unread' ? 'bg-primary text-white' : 'hover:bg-slate-100' }}">
                            <span class="truncate">Unread</span>
                        </a>
                        <a href="{{ route('admin.notifications.index', ['filter' => 'read', 'type' => $currentType]) }}" 
                           class="flex items-center px-3 py-2 rounded-md {{ $currentFilter === 'read' ? 'bg-primary text-white' : 'hover:bg-slate-100' }}">
                            <span class="truncate">Read</span>
                        </a>
                    </div>
                    
                    @if(count($notificationTypes) > 0)
                        <div class="border-t border-slate-200 dark:border-darkmode-400 mt-5 pt-5">
                            <div class="text-base font-medium mb-3">Notification Types</div>
                            <div class="space-y-1">
                                <a href="{{ route('admin.notifications.index', ['filter' => $currentFilter]) }}" 
                                   class="flex items-center px-3 py-2 rounded-md {{ !$currentType ? 'bg-primary text-white' : 'hover:bg-slate-100' }}">
                                    <span class="truncate">All Types</span>
                                </a>
                                @foreach($notificationTypes as $type)
                                    <a href="{{ route('admin.notifications.index', ['filter' => $currentFilter, 'type' => $type['id']]) }}" 
                                       class="flex items-center px-3 py-2 rounded-md {{ $currentType == $type['id'] ? 'bg-primary text-white' : 'hover:bg-slate-100' }}">
                                        <span class="truncate">{{ $type['name'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Notifications List -->
        <div class="col-span-12 md:col-span-9 xl:col-span-10">
            <div class="box intro-y">
                <div class="p-5">
                    @forelse($notifications as $notification)
                        <div class="intro-x">
                            <div class="flex items-center px-5 py-3 mb-3 box {{ $notification->read_at ? 'bg-slate-50' : 'bg-primary/5 border-primary/10' }} hover:bg-slate-100 rounded-md transition">
                                <div class="flex-none mr-4">
                                    @php
                                        $icon = 'Bell';
                                        
                                        // Determinar icono basado en el tipo de notificación
                                        $notificationType = class_basename($notification->type);
                                        
                                        switch($notificationType) {
                                            case 'NewCarrierNotification':
                                            case 'NewUserCarrierNotification':
                                                $icon = 'Building2';
                                                break;
                                            case 'NewDocumentUploadedNotification':
                                                $icon = 'FileText';
                                                break;
                                            case 'NewUserDriverNotification':
                                            case 'NewDriverNotificationAdmin':
                                            case 'NewDriverCreatedNotification':
                                                $icon = 'User';
                                                break;
                                            default:
                                                $icon = 'Bell';
                                        }
                                    @endphp
                                    <i data-lucide="{{ $icon }}" class="w-6 h-6 {{ $notification->read_at ? 'text-slate-500' : 'text-primary' }}"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <div class="mr-auto">
                                            <div class="font-medium {{ $notification->read_at ? '' : 'text-primary' }}">
                                                {{ $notification->data['title'] ?? class_basename($notification->type) }}
                                            </div>
                                            <div class="text-slate-500 text-xs mt-0.5">{{ $notification->data['message'] ?? '' }}</div>
                                        </div>
                                        <div class="text-xs text-slate-500 ml-auto whitespace-nowrap">
                                            {{ $notification->created_at->format('M d, Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-none ml-4 flex">
                                    @if($notification->read_at)
                                        <form action="{{ route('admin.notifications.mark-as-unread', $notification->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-slate-500 hover:text-primary" title="Mark as unread">
                                                <i data-lucide="MessageSquare" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.notifications.mark-as-read', $notification->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-slate-500 hover:text-primary" title="Mark as read">
                                                <i data-lucide="CheckSquare" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" class="ml-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-500 hover:text-danger" 
                                                onclick="return confirm('Are you sure you want to delete this notification?');" title="Delete">
                                            <i data-lucide="Trash2" class="w-5 h-5"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center p-10">
                            <i data-lucide="BellOff" class="w-16 h-16 mx-auto mt-3 text-slate-300"></i>
                            <div class="mt-5 text-slate-600">
                                <p class="text-lg font-medium mb-1">No notifications found</p>
                                <p>
                                    {{ $currentFilter === 'unread' ? 'You have no unread notifications.' : 
                                      ($currentFilter === 'read' ? 'You have no read notifications.' : 'You have no notifications.') }}
                                </p>
                            </div>
                        </div>
                    @endforelse
                    
                    <!-- Pagination -->
                    <div class="mt-5">
                        {{ $notifications->appends(['filter' => $currentFilter, 'type' => $currentType])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    // Ensure Lucide icons are initialized
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endsection