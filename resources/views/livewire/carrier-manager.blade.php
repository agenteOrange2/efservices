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
                                <th
                                    class="px-5 border-b w-5 border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                    Name</th>
                                <th
                                    class="px-5 border-b w-5 border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                    Address</th>
                                <th
                                    class="px-5 border-b w-5 border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                    State</th>
                                <th
                                    class="px-5 border-b w-5 border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                    Zipcode</th>
                                <th
                                    class="px-5 border-b w-5 border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                    EIN</th>
                                <th
                                    class="px-5 border-b w-5 border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                    DOT</th>
                                <th
                                    class="px-5 border-b w-5 border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($carriers as $carrier)
                                <tr class="[&_td]:last:border-b-0">
                                    <td class="px-5 border-b border-dashed py-4">{{ $carrier->name }}</td>
                                    <td class="px-5 border-b border-dashed py-4">{{ $carrier->address }}</td>
                                    <td class="px-5 border-b border-dashed py-4">{{ $carrier->state }}</td>
                                    <td class="px-5 border-b border-dashed py-4">{{ $carrier->zipcode }}</td>
                                    <td class="px-5 border-b border-dashed py-4">{{ $carrier->ein_number }}</td>
                                    <td class="px-5 border-b border-dashed py-4">{{ $carrier->dot_number }}</td>
                                    <td class="px-5 border-b border-dashed py-4">
                                        <button wire:click="editCarrier({{ $carrier->id }})"
                                            class="bg-yellow-500 text-white p-1 rounded">Edit</button>
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
        <div class="flex flex-col gap-y-3 2xl:flex-row 2xl:items-center">
            <ul role="tablist"
                class="p-0.5 border flex box mr-auto w-full flex-col rounded-[0.6rem] border-slate-200 bg-white sm:flex-row 2xl:w-auto">
                <li id="example-1-tab" role="presentation"
                    class="focus-visible:outline-none flex-1 bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem] [&amp;[aria-selected='true']_button]:text-current">
                    <button data-tw-target="#example-1" role="tab"
                        wire:click="switchTab('carrier')"                        
                        class="cursor-pointer appearance-none px-3 border border-transparent transition-colors  flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] py-2.5 text-[0.94rem] text-slate-500 xl:w-40 active
                        {{ $activeTab === 'carrier' ? 'font-semibold' : '' }}"
                         >Carrier</button>
                </li>
                <li id="example-2-tab" role="presentation"
                    class="focus-visible:outline-none flex-1 bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem] [&amp;[aria-selected='true']_button]:text-current">
                    <button data-tw-target="#example-2" role="tab"
                        class="cursor-pointer appearance-none px-3 border border-transparent transition-colors [&amp;.active]:text-slate-700 [&amp;.active]:border [&amp;.active]:shadow-sm [&amp;.active]:font-medium [&amp;.active]:border-slate-200 [&amp;.active]:bg-white [&amp;.active]:dark:text-slate-300 [&amp;.active]:dark:bg-darkmode-400 [&amp;.active]:dark:border-darkmode-400 flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] py-2.5 text-[0.94rem] text-slate-500 xl:w-40
                        {{ $activeTab === 'users' ? 'font-semibold' : '' }}"
                        wire:click="switchTab('users')">Users</button>
                </li>
                <li id="example-3-tab" role="presentation"
                    class="focus-visible:outline-none flex-1 bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem] [&amp;[aria-selected='true']_button]:text-current">
                    <button data-tw-target="#example-3" role="tab"
                        class="cursor-pointer appearance-none px-3 border border-transparent transition-colors [&amp;.active]:text-slate-700 [&amp;.active]:border [&amp;.active]:shadow-sm [&amp;.active]:font-medium [&amp;.active]:border-slate-200 [&amp;.active]:bg-white [&amp;.active]:dark:text-slate-300 [&amp;.active]:dark:bg-darkmode-400 [&amp;.active]:dark:border-darkmode-400 flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] py-2.5 text-[0.94rem] text-slate-500 xl:w-40
                        {{ $activeTab === 'documents' ? 'font-semibold' : '' }}"
                        wire:click="switchTab('documents')">Achievements</button>
                </li>
            </ul>

        </div>
        <div class="mb-4">
            {{-- <ul class="flex border-b">
                <li class="-mb-px mr-1">
                    <button wire:click="switchTab('carrier')"
                        class="bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 {{ $activeTab === 'carrier' ? 'font-semibold' : '' }}">Carrier</button>
                </li>
                <li class="mr-1">
                    <button wire:click="switchTab('users')"
                        class="bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 {{ $activeTab === 'users' ? 'font-semibold' : '' }}">Users</button>
                </li>
                <li class="mr-1">
                    <button wire:click="switchTab('documents')"
                        class="bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 {{ $activeTab === 'documents' ? 'font-semibold' : '' }}">Documents</button>
                </li>
            </ul> --}}
            <div class="border-l border-r border-b p-4">
                @if ($activeTab === 'carrier')
                    @include('livewire.partials.carrier-form')
                @elseif ($activeTab === 'users')
                    <livewire:user-manager :carrier="$carrier['id']" />
                @elseif ($activeTab === 'documents')
                    <livewire:document-manager :carrier="$carrier['id']" />
                @endif
            </div>
        </div>
    @endif
</div>
