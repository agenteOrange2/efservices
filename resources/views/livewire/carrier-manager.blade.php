<div>
    @if (!$isCreating)
        <div class="box box--stacked flex flex-col">
            <div class="p-7">
                <div class="flex justify-between items-center mb-4">
                    <input type="text" wire:model.live.debounce.500ms="search" placeholder="Search Carriers..." class="border p-2 rounded">
                    <x-base.button onclick="window.location.href='{{ route('admin.carrier.create', ['slug' => uniqid()]) }}'" variant="primary">
                        Add New Carrier
                    </x-base.button>
                    
                </div>
                <div class="overflow-auto xl:overflow-visible">
                    <table class="w-full text-left border-b border-slate-200/60">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Address</th>
                                <th>State</th>
                                <th>Zipcode</th>
                                <th>EIN</th>
                                <th>DOT</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($carriers as $carrier)
                                <tr>
                                    <td>{{ $carrier->name }}</td>
                                    <td>{{ $carrier->address }}</td>
                                    <td>{{ $carrier->state }}</td>
                                    <td>{{ $carrier->zipcode }}</td>
                                    <td>{{ $carrier->ein_number }}</td>
                                    <td>{{ $carrier->dot_number }}</td>
                                    <td>
                                        <button wire:click="editCarrier({{ $carrier->id }})" class="bg-yellow-500 text-white p-1 rounded">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center p-4">No carriers found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        @include('livewire.partials.carrier-form')
    @endif
</div>
