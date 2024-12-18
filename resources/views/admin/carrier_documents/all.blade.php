@extends('../themes/' . $activeTheme)
@section('title', 'All Document Carriers')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Document Carriers', 'active' => true],
    ];
@endphp

@section('subcontent')

    <x-base.notificationtoast.notification-toast :notification="session('notification')" />
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    All Documents
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="#"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                        Add New Document
                    </x-base.button>
                </div>
            </div>
            <div class="box box--stacked flex flex-col mt-5">
                <div class="flex flex-col gap-y-2 p-5 sm:flex-row sm:items-center">
                    <div class="container mx-auto mt-6">
                        <h1 class="text-2xl font-bold mb-4">Document Review Panel</h1>
                    
                        <table class="w-full table-auto border-collapse border border-gray-300">
                            <thead>
                                <tr>
                                    <th class="border p-2">Carrier</th>
                                    <th class="border p-2">Document Type</th>
                                    <th class="border p-2">Status</th>
                                    <th class="border p-2">Uploaded File</th>
                                    <th class="border p-2">Notes</th>
                                    <th class="border p-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($documents as $document)
                                <tr>
                                    <td class="border p-2">{{ $document->carrier->name }}</td>
                                    <td class="border p-2">{{ $document->documentType->name }}</td>
                                    <td class="border p-2">
                                        <span class="px-2 py-1 rounded 
                                            @if ($document->status == 0) bg-yellow-300 text-yellow-800 
                                            @elseif ($document->status == 1) bg-green-300 text-green-800 
                                            @else bg-red-300 text-red-800 
                                            @endif">
                                            {{ $document->status_name }}
                                        </span>
                                    </td>
                                    <td class="border p-2">
                                        @if ($document->getFirstMediaUrl('carrier_documents'))
                                            <a href="{{ $document->getFirstMediaUrl('carrier_documents') }}" 
                                               target="_blank" 
                                               class="text-blue-500 underline">View File</a>
                                        @else
                                            No File Uploaded
                                        @endif
                                    </td>
                                    <td class="border p-2">{{ $document->notes ?? 'No notes' }}</td>
                                    <td class="border p-2">
                                        <form action="{{ route('admin.carrier.admin_documents.process-review', [$document->carrier->slug, $document->id]) }}" method="POST">
                                            @csrf
                                            <div class="flex space-x-2">
                                                <select name="status" class="border rounded p-1">
                                                    <option value="1" {{ $document->status == 1 ? 'selected' : '' }}>Approved</option>
                                                    <option value="2" {{ $document->status == 2 ? 'selected' : '' }}>Rejected</option>
                                                </select>
                                                <input type="text" name="notes" placeholder="Add notes" class="border rounded p-1" value="{{ $document->notes }}">
                                                <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded">Update</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center p-4">No documents found.</td>
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
