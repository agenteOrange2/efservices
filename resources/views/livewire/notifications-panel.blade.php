<div class="notifications-panel">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">Notificaciones ({{ $unreadCount }} sin leer)</h2>
        @if($unreadCount > 0)
            <button wire:click="markAllAsRead" class="text-sm text-blue-600 hover:text-blue-800">
                Marcar todas como leídas
            </button>
        @endif
    </div>

    <div class="space-y-4">
        @forelse($notifications as $notification)
            <div class="p-4 rounded-lg shadow {{ $notification->is_read ? 'bg-gray-50' : 'bg-white border-l-4 border-blue-500' }}">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <p class="font-medium">{{ $notification->message }}</p>
                        <p class="text-sm text-gray-600">
                            {{ $notification->sent_at->diffForHumans() }}
                        </p>
                    </div>
                    @if(!$notification->is_read)
                        <button 
                            wire:click="markAsRead({{ $notification->id }})"
                            class="text-sm text-gray-600 hover:text-gray-800"
                        >
                            Marcar como leída
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center text-gray-600 py-4">
                No hay notificaciones
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>