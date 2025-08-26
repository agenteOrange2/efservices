<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - EFCTS</title>
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
        .success-bounce {
            animation: successBounce 1s ease-out;
        }
        @keyframes successBounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        .celebration {
            animation: celebration 2s ease-in-out infinite;
        }
        @keyframes celebration {
            0%, 100% { transform: rotate(0deg) scale(1); }
            25% { transform: rotate(5deg) scale(1.1); }
            75% { transform: rotate(-5deg) scale(1.1); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-green-50 min-h-screen">
    <!-- Header -->
    <header class="ef-gradient shadow-xl">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/30">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">EFCTS</h1>
                        <p class="text-blue-100 font-medium">Secure Vehicle Verification</p>
                    </div>
                </div>
                <div class="bg-green-500/20 backdrop-blur-sm px-4 py-2 rounded-full border border-green-400/30">
                    <span class="text-green-100 font-semibold text-sm celebration">🎉 Success</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center px-4 py-12">
        <div class="max-w-lg w-full fade-in">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl border border-white/20 p-10 text-center">
                <!-- Success Icon -->
                <div class="mx-auto w-20 h-20 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mb-8 success-bounce shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Thank You!</h2>
                <p class="text-gray-600 mb-8 text-lg leading-relaxed">
                    Excellent! Your vehicle verification has been successfully submitted. We appreciate your cooperation in ensuring safety and compliance.
                </p>
                
                <!-- Success Message -->
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6 mb-8">
                    <div class="flex items-center justify-center mb-3">
                        <div class="bg-green-100 rounded-lg p-2 mr-3">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-green-800 text-lg">Submission Received</h3>
                    </div>
                    <p class="text-green-700 font-medium">
                        Your verification documents are now in our secure system
                    </p>
                </div>
                
                <!-- Next Steps -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
                    <div class="flex items-center justify-center mb-4">
                        <div class="bg-blue-100 rounded-lg p-2 mr-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-blue-800 text-lg">What Happens Next?</h3>
                    </div>
                    <div class="text-left space-y-3">
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-blue-600 font-bold text-sm">1</span>
                            </div>
                            <p class="text-blue-700 font-medium">Our verification team will review your submission</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-blue-600 font-bold text-sm">2</span>
                            </div>
                            <p class="text-blue-700 font-medium">You'll receive an email confirmation within 2 hours</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-blue-600 font-bold text-sm">3</span>
                            </div>
                            <p class="text-blue-700 font-medium">Final verification results within 24-48 hours</p>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Information -->
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 mb-8">
                    <div class="flex items-center justify-center mb-4">
                        <div class="bg-purple-100 rounded-lg p-2 mr-3">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-800 text-lg">Have Questions?</h3>
                    </div>
                    <p class="text-gray-600 mb-4">
                        Our support team is available to assist you throughout the verification process:
                    </p>
                    <div class="space-y-3">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <a href="mailto:support@efcts.com">
                                <span class="font-semibold text-blue-600">support@efcts.com</span>
                            </a>
                        </div>
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span class="font-semibold text-blue-600">+1 (555) 123-4567</span>
                        </div>
                    </div>
                    <div class="mt-4 p-3 bg-purple-50 rounded-lg">
                        <p class="text-sm text-purple-700 font-medium">
                            📧 Keep an eye on your email for updates and next steps!
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
