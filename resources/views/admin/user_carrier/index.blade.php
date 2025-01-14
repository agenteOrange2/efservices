@extends('../themes/' . $activeTheme)
@section('title', 'User Carriers for Carrier: ' . $carrier->name)

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Carriers', 'url' => route('admin.carrier.user_carriers.index', $carrier->slug)],
        ['label' => 'User Carriers: ' . $carrier->name, 'active' => true],
    ];
@endphp

@section('subcontent')

    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">

            <div class="mb-10">
                @if ($carrier->userCarriers->count() < $carrier->membership->max_carrier)
                    <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                        <div class="text-base font-medium group-[.mode--light]:text-white">
                            <h2 class="text-2xl">User Carriers for Carrier: <span>{{ $carrier->name }}</span></h2>
                        </div>
                        <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                            @if ($carrier->userCarriers->count() < $carrier->membership->max_drivers)                            
                                <x-base.button as="a"
                                    href="{{ route('admin.carrier.user_carriers.create', $carrier) }}"
                                    class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                                    variant="primary">
                                    Add User Carrier
                                </x-base.button>
                            @else
                                <button class="btn btn-secondary" disabled>
                                    Max User Carriers Reached
                                </button>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="w-full mb-10">
                        <div role="alert"
                            class="alert relative border rounded-md px-5 py-4 bg-primary border-primary text-white dark:border-primary">
                            <div class="flex items-center">
                                <div class="text-lg font-medium">
                                    Max User Carriers Reached
                                </div>
                                <div class="ml-auto rounded-md bg-white px-1 text-xs text-slate-700">
                                    Notice
                                </div>
                            </div>
                            <div class="mt-3">
                                You have exceeded your user limit, if you need more carrier users, please upgrade your plan
                                or contact the administration to upgrade your plan.
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            @if (session('exceeded_limit'))
                <!-- Modal -->
                <div id="limitModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50">
                    <div class="bg-white rounded-lg shadow-lg max-w-lg w-full">
                        <div class="p-6">
                            <h2 class="text-lg font-bold text-red-600">User Limit Reached</h2>
                            <p class="mt-4 text-gray-600">
                                You have reached the maximum number of users allowed. Please upgrade your plan or contact the administrator
                            </p>
                            <div class="mt-6 flex justify-end">
                                <button id="closeModal" class="px-4 py-2 bg-gray-300 rounded-lg text-gray-800">
                                    Close
                                </button>
                                {{-- <a href="{{ route('admin.membership.index') }}"
                                    class="ml-3 px-4 py-2 bg-primary-500 text-white rounded-lg">
                                    Ver Planes
                                </a> --}}
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    document.getElementById('closeModal').addEventListener('click', function() {
                        document.getElementById('limitModal').style.display = 'none';
                    });
                </script>
            @endif



            <div class="px-7">
                <div class="box box--stacked flex flex-col">
                    <div class="overflow-auto xl:overflow-visible">
                        {{-- TABS --}}
                        <div class="border-b border-gray-200 dark:border-gray-700">
                            <ul class="flex flex-wrap text-sm font-medium text-center text-gray-500 dark:text-gray-400">
                                <!-- Tab Carrier -->
                                <li class="flex-grow">
                                    <a href="{{ route('admin.carrier.edit', $carrier->slug) }}"
                                        class="inline-flex items-center justify-center w-full p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group
                        {{ request()->routeIs('admin.carrier.edit') ? 'text-primary border-primary dark:text-primary dark:border-primary' : '' }}">

                                        <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.edit') ? 'text-primary dark:text-primary' : '' }}"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 20a6 6 0 0 0-12 0" />
                                            <circle cx="12" cy="10" r="4" />
                                            <circle cx="12" cy="12" r="10" />
                                        </svg>
                                        Profile Carrier
                                    </a>
                                </li>
                                <!-- Tab Users -->
                                <li class="flex-grow">
                                    <a href="{{ route('admin.carrier.user_carriers.index', $carrier->slug) }}"
                                        class="inline-flex items-center justify-center w-full p-4 border-b-2  rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group {{ request()->routeIs('admin.carrier.user_carriers.*') ? 'text-primary border-primary ' : '' }}">
                                        <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.user_carriers.*') ? 'text-primary dark:text-primary' : '' }}"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                            <circle cx="9" cy="7" r="4" />
                                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                        </svg>
                                        Users
                                    </a>
                                </li>
                                <!-- Tab Documents -->
                                {{-- Uncomment if needed --}}
                                <li class="flex-grow">
                                    <a href="{{ route('admin.carrier.documents', $carrier->slug) }}"
                                        class="inline-flex items-center justify-center w-full p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group
                        {{ request()->routeIs('admin.carrier.documents') ? 'text-primary border-blue-600 dark:text-primary dark:border-primary' : '' }}">
                                        <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.documents') ? 'text-primary dark:text-primary' : '' }}"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" />
                                            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                            <path d="m3 15 2 2 4-4" />
                                        </svg>
                                        Documents
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <table class="w-full text-left border-b border-slate-200/60">
                            <thead>
                                <tr>
                                    <th
                                        class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Name</th>
                                    <th
                                        class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Email</th>
                                    <th
                                        class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Job Position</th>
                                    <th
                                        class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Status</th>
                                    <th
                                        class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($userCarriers as $userCarrier)
                                    <tr>
                                        <td class="px-5 border-b border-dashed py-4">{{ $userCarrier->name }}</td>
                                        <td class="px-5 border-b border-dashed py-4">{{ $userCarrier->email }}</td>
                                        <td class="px-5 border-b border-dashed py-4">{{ $userCarrier->carrierDetails->job_position ?? 'N/A' }}</td>
                                        <td class="px-5 border-b border-dashed py-4">
                                            {{ $userCarrier->carrierDetails->status_name ?? 'N/A' }}
                                        </td>
                                        <td class="px-5 border-b border-dashed py-4">
                                            <a href="{{ route('admin.carrier.user_carriers.edit', ['carrier' => $carrier->slug, 'userCarrierDetails' => $userCarrier->carrierDetails->id]) }}">
                                                Editar
                                            </a>
                                                                               
                                            <form
                                            action="{{ route('admin.carrier.user_carriers.destroy', ['carrier' => $carrier->slug, 'userCarrier' => $userCarrier->id]) }}"
                                            method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                Delete
                                            </button>
                                        </form>
                                        
                                        
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-5 border-b border-dashed py-4 text-center">No user
                                            carriers
                                            found for this carrier.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


@pushOnce('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const maxCarriers = {{ $carrier->membership->max_carrier ?? 1 }};
            const currentCarriers = {{ $carrier->userCarriers->count() }};

            if (currentCarriers >= maxCarriers) {
                document.querySelector('form').addEventListener('submit', function(event) {
                    event.preventDefault();
                    alert('No puedes agregar más usuarios. Actualiza tu plan o contacta al administrador.');
                });
            }
        });
    </script>
@endPushOnce
