{{-- resources/views/auth/user_driver/carrier_inactive_error.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrier Not Available - EF Services</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-md">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <div>
                    <img src="{{ asset('build/logo.png') }}" alt="EF Services Logo" class="h-10">
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Driver Registration</h1>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow container mx-auto px-4 py-8">
            <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-center mb-6">
                        <div class="bg-yellow-100 rounded-full p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Carrier Not Available</h2>
                    
                    <p class="text-gray-600 text-center mb-6">
                        @if(isset($carrier) && $carrier->status === 'pending')
                            We're sorry, but the carrier you're trying to register with is currently pending approval.
                        @else
                            We're sorry, but the carrier you're trying to register with is currently not active.
                        @endif
                    </p>
                    
                    @if(isset($carrier))
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Carrier Information</h3>
                        
                        <div class="flex items-center justify-center mb-4">
                            @if($carrier->getFirstMedia('logo'))
                                <img src="{{ $carrier->getFirstMedia('logo')->getUrl() }}" alt="{{ $carrier->name }}" class="h-20 object-contain">
                            @else
                                <div class="h-20 w-20 flex items-center justify-center bg-gray-200 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Name</p>
                                <p class="font-medium">{{ $carrier->name }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500">Status</p>
                                <p class="font-medium">
                                    @if($carrier->status === 'pending')
                                        <span class="text-yellow-600">Pending Approval</span>
                                    @else
                                        <span class="text-red-600">Inactive</span>
                                    @endif
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500">DOT Number</p>
                                <p class="font-medium">{{ $carrier->dot_number }}</p>
                            </div>
                            
                            @if($carrier->mc_number)
                            <div>
                                <p class="text-sm text-gray-500">MC Number</p>
                                <p class="font-medium">{{ $carrier->mc_number }}</p>
                            </div>
                            @endif
                            
                            <div>
                                <p class="text-sm text-gray-500">Location</p>
                                <p class="font-medium">{{ $carrier->address }}, {{ $carrier->state }} {{ $carrier->zipcode }}</p>
                            </div>
                            
                            @if($carrier->phone)
                            <div>
                                <p class="text-sm text-gray-500">Phone</p>
                                <p class="font-medium">{{ $carrier->phone }}</p>
                            </div>
                            @endif
                            
                            @if($carrier->email)
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="font-medium">{{ $carrier->email }}</p>
                            </div>
                            @endif
                        </div>
                        
                        <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                            <p class="text-blue-800 text-sm">
                                @if($carrier->status === 'pending')
                                    <span class="font-medium">Note:</span> This carrier is currently pending approval by our administrators. 
                                    You may check back later or select another carrier to register with.
                                @else
                                    <span class="font-medium">Note:</span> This carrier is currently not active in our system.
                                    Please select another carrier to register with.
                                @endif
                            </p>
                        </div>
                    </div>
                    @endif
                    
                    <div class="flex flex-col md:flex-row justify-center space-y-3 md:space-y-0 md:space-x-4">
                        <a href="{{ url('/driver/register') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded text-center transition-colors duration-300">
                            Select Another Carrier
                        </a>
                        <a href="{{ url('/') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-6 rounded text-center transition-colors duration-300">
                            Return to Homepage
                        </a>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-6">
            <div class="container mx-auto px-4 text-center">
                <p>&copy; {{ date('Y') }} EF Services. All rights reserved.</p>
            </div>
        </footer>
    </div>
</body>
</html>
