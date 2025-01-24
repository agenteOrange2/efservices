<div>
 

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold text-xl mb-4">Carriers</h3>
        <div class="flex justify-between items-center">
            <div>
                    <p class="text-gray-600">Total</p>
                    <p class="text-3xl font-bold">{{ $totalCarriers }}</p>
                </div>
                <div>
                    <p class="text-green-600">Active: {{ $activeCarriers }}</p>
                    <p class="text-yellow-600">Pending: {{ $pendingCarriers }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-xl mb-4">Drivers</h3>
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-gray-600">Total</p>
                    <p class="text-3xl font-bold">{{ $totalDrivers }}</p>
                </div>
                <div>
                    <p class="text-green-600">Active: {{ $activeDrivers }}</p>
                    <p class="text-yellow-600">Pending: {{ $pendingDrivers }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-xl mb-4">Documents</h3>
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-gray-600">Total</p>
                    <p class="text-3xl font-bold">{{ $totalDocuments }}</p>
                </div>
                <div>
                    <p class="text-green-600">Approved: {{ $approvedDocuments }}</p>
                    <p class="text-yellow-600">Pending: {{ $pendingDocuments }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-xl mb-4">Recent Carriers</h3>
            <div class="divide-y">
                @foreach($recentCarriers as $carrier)
                    <div class="py-3">
                        <p class="font-medium">{{ $carrier->name }}</p>
                        <p class="text-sm text-gray-600">Added {{ $carrier->created_at->diffForHumans() }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-xl mb-4">Recent Drivers</h3>
            <div class="divide-y">
                @foreach($recentDrivers as $driver)
                    <div class="py-3">
                        <p class="font-medium">{{ $driver->user->name }}</p>
                        <p class="text-sm text-gray-600">{{ $driver->carrier->name }} - Added {{ $driver->created_at->diffForHumans() }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Carrier</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td class="px-6 py-4">{{ $user['name'] }}</td>
                    <td class="px-6 py-4">{{ $user['email'] }}</td>
                    <td class="px-6 py-4">
                        @if(isset($user['role']))
                        <span class="px-2 py-1 text-xs rounded-full {{ 
                            $user['role'] === 'superadmin' ? 'bg-purple-100 text-purple-800' : 
                            ($user['role'] === 'user_carrier' ? 'bg-blue-100 text-blue-800' : 
                             'bg-green-100 text-green-800') 
                        }}">
                            {{ $user['role'] }}
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if(isset($user['details']) && $user['details'] && $user['role'] !== 'superadmin' && $user['details']->carrier)
                        {{ $user['details']->carrier->name }}
                        @endif
                    </td>
                    <td class="px-6 py-4">{{ $user['created_at'] ?? 'N/A' }}</td>
                    <td class="px-6 py-4">

                        {{-- <a href="{{ route('admin.users.edit', $user['id']) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a> --}}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>