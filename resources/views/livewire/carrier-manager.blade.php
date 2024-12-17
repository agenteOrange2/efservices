<div>
    @if (!$isCreating)
        <div class="box box--stacked flex flex-col">
            <div class="p-7">
                {{-- <div class="flex justify-between items-center mb-4">
                    <input type="text" wire:model.live.debounce.500ms="search" placeholder="Search Carriers..."
                        class="border p-2 rounded">
                        <x-base.button wire:click="createCarrier"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4 stroke-[1.3]"><path d="M12 20h9"/><path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z"/><path d="m15 5 3 3"/></svg>
                        
                        Add New Carrier
                    </x-base.button>                    
                </div> --}}
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
