<div>
    <div class="flex justify-between mb-4">
        <input type="text" wire:model="search" placeholder="Search carriers..." class="border p-2 rounded">
        <select wire:model="filter.status" class="border p-2 rounded">
            <option value="">All Statuses</option>
            <option value="{{ \App\Models\CarrierDocument::STATUS_PENDING }}">Pending</option>
            <option value="{{ \App\Models\CarrierDocument::STATUS_APPROVED }}">Approved</option>
            <option value="{{ \App\Models\CarrierDocument::STATUS_REJECTED }}">Rejected</option>
        </select>
        <input type="text" wire:model="filter.carrier" placeholder="Filter by carrier..." class="border p-2 rounded">
        <input type="date" wire:model="filter.date_range.start" class="border p-2 rounded">
        <input type="date" wire:model="filter.date_range.end" class="border p-2 rounded">
        <select wire:model="perPage" class="border p-2 rounded">
            <option value="10">10 per page</option>
            <option value="25">25 per page</option>
            <option value="50">50 per page</option>
            <option value="100">100 per page</option>
        </select>
    </div>

    <table class="min-w-full bg-white">
        <thead>
            <tr>
                <th class="py-2 px-4 border-b" wire:click="sortBy('name')">Carrier Name</th>
                <th class="py-2 px-4 border-b" wire:click="sortBy('userCarriers.name')">User Carrier</th>
                <th class="py-2 px-4 border-b">Progress</th>
                <th class="py-2 px-4 border-b">Status</th>
                <th class="py-2 px-4 border-b">Joined Date</th>
                <th class="py-2 px-4 border-b">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($carriers as $carrier)
                <tr>
                    <td class="py-2 px-4 border-b">{{ $carrier->name }}</td>
                    <td class="py-2 px-4 border-b">{{ optional($carrier->userCarriers->first())->name ?? 'N/A' }}</td>
                    <td class="py-2 px-4 border-b">
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $carrier->completion_percentage }}%"></div>
                        </div>
                        <span class="text-xs font-medium text-blue-700 dark:text-white">{{ round($carrier->completion_percentage) }}%</span>
                    </td>
                    <td class="py-2 px-4 border-b">{{ $carrier->document_status }}</td>
                    <td class="py-2 px-4 border-b">{{ $carrier->created_at->format('d M Y') }}</td>
                    <td class="py-2 px-4 border-b">
                        <a href="{{ route('admin.carrier.documents', $carrier->slug) }}" class="text-blue-500 hover:underline">View Documents</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{-- {{ $carriers->links() }} --}}
    </div>
</div>