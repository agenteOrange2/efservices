@props(['headers' => [], 'data' => [], 'emptyMessage' => 'No data available'])

<div class="overflow-hidden shadow-sm rounded-lg border border-gray-200">
    @if(!empty($data))
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                @if(!empty($headers))
                    <thead class="bg-gray-50">
                        <tr>
                            @foreach($headers as $header)
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ $header }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                @endif
                <tbody class="bg-white divide-y divide-gray-200">
                    {{ $slot }}
                </tbody>
            </table>
        </div>
    @else
        <div class="bg-white px-6 py-12 text-center">
            <div class="mx-auto w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mb-4">
                <x-base.lucide icon="inbox" class="w-6 h-6 text-gray-400" />
            </div>
            <h3 class="text-sm font-medium text-gray-900 mb-1">No Data Found</h3>
            <p class="text-sm text-gray-500">{{ $emptyMessage }}</p>
        </div>
    @endif
</div>