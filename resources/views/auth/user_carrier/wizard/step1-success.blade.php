@extends('layouts.app')

@section('title', 'Email Verification Required')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center py-6 sm:py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Progress Stepper -->
        <div class="mb-6 sm:mb-8">
            @include('components.progress-stepper', [
                'steps' => [
                    ['label' => 'Basic Info', 'completed' => true],
                    ['label' => 'Email Verification', 'current' => true],
                    ['label' => 'Company Info', 'completed' => false],
                    ['label' => 'Membership', 'completed' => false]
                ],
                'size' => 'sm'
            ])
        </div>

        <!-- Success Card -->
        <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 lg:p-8 border border-gray-100">
            <!-- Success Icon -->
            <div class="flex justify-center mb-4 sm:mb-6">
                <div class="w-12 h-12 sm:w-16 sm:h-16 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Title -->
            <div class="text-center mb-4 sm:mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">
                    Check Your Email!
                </h2>
                <p class="text-sm sm:text-base text-gray-600">
                    We've sent a verification link to your email address.
                </p>
            </div>

            <!-- Instructions -->
            <div class="bg-blue-50 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 mb-1">
                            Next Steps:
                        </h3>
                        <div class="text-sm text-blue-700 space-y-1">
                            <p>1. Check your email inbox (and spam folder)</p>
                            <p>2. Click the verification link in the email</p>
                            <p>3. Complete your company information</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Status -->
            <div class="text-center mb-4 sm:mb-6">
                <p class="text-sm text-gray-500 mb-2">
                    Didn't receive the email?
                </p>
                <button 
                    type="button" 
                    id="resendEmailBtn"
                    class="text-blue-600 hover:text-blue-800 font-medium text-xs sm:text-sm transition-colors duration-200"
                    onclick="resendVerificationEmail()"
                >
                    Resend verification email
                </button>
            </div>

            <!-- Support Link -->
            <div class="text-center pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-500 mb-2">
                    Having trouble?
                </p>
                <a 
                    href="{{ route('carrier.support') }}" 
                    class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors duration-200"
                >
                    Contact Support
                </a>
            </div>
        </div>

        <!-- Auto-refresh Notice -->
        <div class="text-center">
            <p class="text-xs text-gray-500">
                This page will automatically refresh when your email is verified.
            </p>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-sm mx-4">
        <div class="flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">Sending email...</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
let checkInterval;
let resendCooldown = false;

// Función para reenviar email de verificación
function resendVerificationEmail() {
    if (resendCooldown) {
        return;
    }
    
    const btn = document.getElementById('resendEmailBtn');
    const modal = document.getElementById('loadingModal');
    
    // Mostrar loading
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    btn.disabled = true;
    
    // Simular envío de email (aquí iría la llamada AJAX real)
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        
        // Mostrar mensaje de éxito
        showNotification('Verification email sent successfully!', 'success');
        
        // Activar cooldown
        resendCooldown = true;
        btn.textContent = 'Email sent! Check your inbox';
        btn.classList.add('text-green-600');
        
        setTimeout(() => {
            resendCooldown = false;
            btn.disabled = false;
            btn.textContent = 'Resend verification email';
            btn.classList.remove('text-green-600');
        }, 60000); // 1 minuto de cooldown
    }, 2000);
}

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Animar entrada
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Remover después de 5 segundos
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 5000);
}

// Verificar estado de verificación cada 10 segundos
function startEmailVerificationCheck() {
    checkInterval = setInterval(async () => {
        try {
            const response = await fetch('/carrier/wizard/check-verification', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.verified) {
                clearInterval(checkInterval);
                showNotification('Email verified successfully! Redirecting...', 'success');
                
                setTimeout(() => {
                    window.location.href = '{{ route("carrier.wizard.step2") }}';
                }, 2000);
            }
        } catch (error) {
            console.error('Error checking verification status:', error);
        }
    }, 10000); // Verificar cada 10 segundos
}

// Iniciar verificación cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    startEmailVerificationCheck();
});

// Limpiar interval cuando se cierra la página
window.addEventListener('beforeunload', function() {
    if (checkInterval) {
        clearInterval(checkInterval);
    }
});
</script>
@endpush
@endsection