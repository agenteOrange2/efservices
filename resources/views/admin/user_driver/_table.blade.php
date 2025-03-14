<table class="w-full text-left border-b border-slate-200/60">
    <thead>
        <tr>
            <th class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                Name</th>
            <th class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                Email</th>
            <th class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                License Number</th>
            <th class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                Vehicle</th>
            <th class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                Status</th>
            <th class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($userDrivers as $userDriver)
            <tr>
                <td class="px-5 border-b border-dashed py-4">{{ $userDriver->user->name }}</td>
                <td class="px-5 border-b border-dashed py-4">{{ $userDriver->user->email }}</td>
                <td class="px-5 border-b border-dashed py-4">{{ $userDriver->license_number }}</td>
                <td class="px-5 border-b border-dashed py-4">{{ $userDriver->assignedVehicle->model ?? 'No Vehicle' }}</td>
                <td class="px-5 border-b border-dashed py-4">
                    {{ $userDriver->status_name }}
                </td>
                <td class="flex px-5 border-b border-dashed py-4">
                    <a href="{{ route('admin.carrier.user_drivers.edit', ['carrier' => $carrier->slug, 'userDriverDetail' => $userDriver->id]) }}" 
                        class="flex items-center text-primary">
                        <x-base.lucide icon="Edit" class="w-4 h-4 mr-1"/>
                        Edit
                    </a>
                    <form action="{{ route('admin.carrier.user_drivers.destroy', ['carrier' => $carrier->slug, 'userDriverDetail' => $userDriver->id]) }}"
                        method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 ml-3"
                            onclick="return confirm('Are you sure?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-5 border-b border-dashed py-4 text-center">No drivers found for this carrier.</td>
            </tr>
        @endforelse
    </tbody>
</table>