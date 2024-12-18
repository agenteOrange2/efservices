@extends('../themes/' . $activeTheme)

@section('title', 'Carrier Documents Overview')

@section('subcontent')
    <h1 class="text-2xl font-bold mb-4">Carriers Document Review</h1>

    <table class="table-auto w-full border-collapse border">
        <thead>
            <tr>
                <th class="border p-2">Carrier Name</th>
                <th class="border p-2">First User Carrier</th>
                <th class="border p-2">Document Status</th>
                <th class="border p-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($carriers as $carrier)
                <tr>
                    <td class="border p-2">{{ $carrier->name }}</td>
                    <td class="border p-2">{{ optional($carrier->userCarriers->first())->name ?? 'N/A' }}</td>
                    <td class="border p-2">
                        @if ($carrier->document_status == 'active')
                            <span class="text-green-500 font-bold">Active</span>
                        @elseif ($carrier->document_status == 'pending')
                            <span class="text-yellow-500 font-bold">Pending</span>
                        @else
                            <span class="text-red-500 font-bold">Inactive</span>
                        @endif
                    </td>
                    <td class="border p-2">
                        <a href="{{ route('admin.carrier.admin_documents.review', $carrier->slug) }}" 
                           class="text-blue-500 underline">Ver Archivos</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
