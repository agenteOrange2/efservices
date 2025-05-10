<div>
    <x-base.dialog.title >
        <div>
            <h2 class="mr-auto text-base font-medium">
                Notifications 
                @if($unreadCount > 0)
                    <span class="ml-2 px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full">
                        {{ $unreadCount }}
                    </span>
                @endif
            </h2>
        </div>
    </x-base.dialog.title>
    <x-base.dialog.description>
        <!-- Lista de notificaciones -->
        <div class="p-1">
            <div class="max-h-[350px] overflow-y-auto pr-2">
                @forelse($notifications as $notification)
                    <div wire:key="notification-{{ $notification->id }}" 
                        class="flex items-center p-3 rounded-lg {{ $notification->read_at ? 'bg-slate-50' : 'bg-primary/10' }} mb-2 transition hover:bg-slate-100">
                        <!-- Usar wire:ignore para conservar el icono -->
                        <div wire:ignore class="flex-shrink-0 mr-3">
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
                                        $icon = $notification->data['icon'] ?? 'Bell';
                                }
                            @endphp
                            <x-base.lucide class="h-5 w-5 {{ $notification->read_at ? 'text-slate-400' : 'text-primary' }}" icon="{{ $icon }}" />
                        </div>
                        <div class="flex-grow">
                            <p class="font-medium">{{ $notification->data['title'] ?? class_basename($notification->type) }}</p>
                            <p class="text-xs text-slate-600">{{ $notification->data['message'] ?? '' }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        @if(!$notification->read_at)
                            <button wire:click="markAsRead('{{ $notification->id }}')" class="ml-2 text-slate-400 hover:text-primary">
                                <x-base.lucide class="h-4 w-4" icon="Check" />
                            </button>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-500">
                        <div wire:ignore>
                            <x-base.lucide class="h-12 w-12 mx-auto mb-2 text-slate-300" icon="BellOff" />
                        </div>
                        <p>You have no notifications</p>
                    </div>
                @endforelse
            </div>
        </div>
    </x-base.dialog.description>
    <x-base.dialog.footer>
        <div class="flex justify-between">
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs text-slate-500 hover:text-primary">
                    Mark all as read                
                </button>
            @endif
            <a href="{{ route('admin.notifications.index') }}" class="text-sm text-primary hover:underline">
                View all notifications
            </a>
        </div>
    </x-base.dialog.footer>
</div>