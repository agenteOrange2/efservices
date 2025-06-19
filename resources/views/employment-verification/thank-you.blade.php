@extends('layouts.guest')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-green-600 px-6 py-4">
            <h1 class="text-white text-2xl font-bold">Thank You!</h1>
        </div>
        
        <div class="p-8 text-center">
            <div class="mb-6">
                <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            
            <h2 class="text-xl font-semibold mb-4">Employment Verification Complete</h2>
            
            <p class="mb-6 text-gray-600">
                Thank you for verifying the employment information. Your response has been recorded successfully.
            </p>
            
            <p class="text-gray-600">
                This helps us maintain accurate records and ensures compliance with regulatory requirements.
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
