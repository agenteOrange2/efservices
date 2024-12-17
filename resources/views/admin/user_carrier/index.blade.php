@extends('../themes/' . $activeTheme)
@section('title', 'User Carriers for Carrier: ' . $carrier->name)

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'User Carriers for Carrier: ' . $carrier->name, 'active' => true],
    ];
@endphp

@section('subcontent')

    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">

            <div>
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
                            <h2 class="text-lg font-bold text-red-600">Límite Alcanzado</h2>
                            <p class="mt-4 text-gray-600">
                                Has alcanzado el límite máximo de usuarios permitidos. Por favor, actualiza tu plan o
                                contacta al administrador.
                            </p>
                            <div class="mt-6 flex justify-end">
                                <button id="closeModal" class="px-4 py-2 bg-gray-300 rounded-lg text-gray-800">
                                    Cerrar
                                </button>
                                <a href="{{ route('admin.membership.index') }}"
                                    class="ml-3 px-4 py-2 bg-primary-500 text-white rounded-lg">
                                    Ver Planes
                                </a>
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

            {{-- TABS --}}
            <div class="border-b border-gray-200 dark:border-gray-700">
                <ul class="flex flex-wrap text-sm font-medium text-center text-gray-500 dark:text-gray-400">
                    <!-- Tab Carrier -->
                    <li class="flex-grow">
                        <a href="{{ route('admin.carrier.edit', $carrier->slug) }}"
                            class="inline-flex items-center justify-center w-full p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group
                            {{ request()->routeIs('admin.carrier.edit') ? 'text-primary border-blue-600 dark:text-blue-500 dark:border-blue-500' : '' }}">
                            <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.edit') ? 'text-primary dark:text-primary' : '' }}"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z" />
                            </svg>
                            Profile Carrier
                        </a>
                    </li>
                    <!-- Tab Users -->
                    <li class="flex-grow">
                        <a href="{{ route('admin.carrier.user_carriers.index', $carrier->slug) }}"
                            class="inline-flex items-center justify-center w-full p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group
                            {{ request()->routeIs('admin.carrier.user_carriers.*') ? 'text-primary border-blue-600 dark:text-primary dark:border-primary' : '' }}">
                            <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.user_carriers.*') ? 'text-primary dark:text-primary' : '' }}"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 18 18">
                                <path
                                    d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z" />
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
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 18 20">
                                <path
                                    d="M16 1h-3.278A1.992 1.992 0 0 0 11 0H7a1.993 1.993 0 0 0-1.722 1H2a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2Zm-3 14H5a1 1 0 0 1 0-2h8a1 1 0 0 1 0 2Zm0-4H5a1 1 0 0 1 0-2h8a1 1 0 1 1 0 2Zm0-5H5a1 1 0 0 1 0-2h2V2h4v2h2a1 1 0 1 1 0 2Z" />
                            </svg>
                            Documents
                        </a>
                    </li>
                </ul>
            </div>

            <div class="px-7">
                <div class="box box--stacked flex flex-col">
                    <div class="overflow-auto xl:overflow-visible">
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
                                        <td class="px-5 border-b border-dashed py-4">{{ $userCarrier->job_position }}</td>
                                        <td class="px-5 border-b border-dashed py-4">{{ $userCarrier->status_name }}</td>
                                        <td class="px-5 border-b border-dashed py-4">
                                            <a href="{{ route('admin.carrier.user_carriers.edit', [$carrier->slug, $userCarrier->id]) }}"
                                                class="btn btn-sm btn-warning">Edit</a>
                                            <form
                                                action="{{ route('admin.carrier.user_carriers.destroy', [$carrier->id, $userCarrier->id]) }}"
                                                method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure?')">Delete</button>
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

    {{-- <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    Users Carrier
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="{{ route('admin.carrier.user_carriers.create', $carrier) }}"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                        Add New User Carrier
                    </x-base.button>
                </div>
            </div>
            <div class="box box--stacked flex flex-col mt-5">
                <livewire:generic-table class="p-0" model="App\Models\UserCarrier" :columns="[
                    'name' => 'Name',
                    'email' => 'Email',
                    'phone' => 'Phone',
                    'job_position' => 'Job Position',
                    'carrier.name' => 'Carrier Name',
                    'status' => 'Status',
                    'created_at' => 'Created At',
                ]" :searchableFields="['name', 'email', 'phone', 'job_position', 'status', 'carrier.name']"
                    editRoute="admin.user_carrier.edit" :filters="[
                        'status' => [
                            'type' => 'select',
                            'label' => 'Status',
                            'options' => [
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ],
                        ],
                    ]" />
            </div>
        </div>
    </div> --}}
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
