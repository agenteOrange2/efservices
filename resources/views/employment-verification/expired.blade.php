@extends('layouts.guest')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-yellow-600 px-6 py-4">
            <h1 class="text-white text-2xl font-bold">Verification Link Expired</h1>
        </div>
        
        <div class="p-8 text-center">
            <div class="mb-6">
                <svg class="mx-auto h-16 w-16 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            
            <h2 class="text-xl font-semibold mb-4">Verification Link Has Expired</h2>
            
            <p class="mb-6 text-gray-600">
                The employment verification link you are trying to access has expired or has already been used.
            </p>
            
            <p class="text-gray-600">
                Verification links are valid for 7 days from the date they are sent. If you need to verify employment information, please contact the driver or the company that requested this verification to send a new verification request.
            </p>
            
            <div class="mt-8">
                <a href="{{ url('/') }}" class="inline-block px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700">
                    Return to Homepage
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
