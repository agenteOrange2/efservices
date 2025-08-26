<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Error - EFCTS</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .ef-gradient { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); }
        .fade-in { animation: fadeIn 0.8s ease-out; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .error-shake {
            animation: errorShake 0.5s ease-in-out;
        }
        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-red-50 min-h-screen">
    <!-- Header -->
    <header class="ef-gradient shadow-xl">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">EFCTS</h1>
                        <p class="text-blue-100 font-medium">Secure Vehicle Verification</p>
                    </div>
                </div>
                <div class="bg-red-500/20 backdrop-blur-sm px-4 py-2 rounded-full border border-red-400/30">
                    <span class="text-red-100 font-semibold text-sm">⚠ Error</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center px-4 py-12">
        <div class="max-w-lg w-full fade-in">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl border border-white/20 p-10 text-center">
                <!-- Error Icon -->
                <div class="mx-auto w-20 h-20 bg-gradient-to-br from-red-400 to-red-600 rounded-full flex items-center justify-center mb-8 error-shake shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Verification Error</h2>
                <p class="text-gray-600 mb-8 text-lg leading-relaxed">
                    We encountered an issue while processing your vehicle verification request. Don't worry - we're here to help resolve this quickly.
                </p>
                
                <!-- Error Details -->
                <div class="bg-gradient-to-r from-red-50 to-orange-50 border border-red-200 rounded-xl p-6 mb-8">
                    <div class="flex items-center justify-center mb-4">
                        <div class="bg-red-100 rounded-lg p-2 mr-3">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-red-800 text-lg">Possible Causes</h3>
                    </div>
                    <div class="text-left space-y-3">
                        <div class="flex items-start space-x-3">
                            <div class="w-2 h-2 bg-red-400 rounded-full mt-2 flex-shrink-0"></div>
                            <p class="text-red-700 font-medium">The verification link may have expired</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-2 h-2 bg-red-400 rounded-full mt-2 flex-shrink-0"></div>
                            <p class="text-red-700 font-medium">The token might be invalid or corrupted</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-2 h-2 bg-red-400 rounded-full mt-2 flex-shrink-0"></div>
                            <p class="text-red-700 font-medium">The vehicle may have already been processed</p>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Information -->
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 mb-8">
                    <div class="flex items-center justify-center mb-4">
                        <div class="bg-blue-100 rounded-lg p-2 mr-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-800 text-lg">Get Immediate Help</h3>
                    </div>
                    <p class="text-gray-600 mb-4">
                        Our support team is ready to assist you with resolving this verification issue:
                    </p>
                    <div class="space-y-3">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span class="font-semibold text-blue-600">support@efservices.la</span>
                        </div>
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span class="font-semibold text-blue-600">+1 (555) 123-4567</span>
                        </div>
                    </div>
                    <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                        <p class="text-sm text-blue-700 font-medium">
                            💡 Tip: Please include your vehicle information when contacting support for faster assistance.
                        </p>
                    </div>
                </div>
                
                <a href="https://efservices.la" class="inline-flex items-center justify-center w-full ef-gradient text-white px-8 py-4 rounded-xl hover:shadow-lg transition-all duration-200 font-semibold text-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Return to Website
                </a>
            </div>
        </div>
    </main>


</body>
</html>
