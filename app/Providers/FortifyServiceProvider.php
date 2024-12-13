<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

use App\Models\UserCarrier;
use App\Models\UserDriver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // Lógica personalizada de autenticación
        Fortify::authenticateUsing(function (Request $request) {
            $guard = $request->input('guard', 'web'); // Detectar el guard desde la solicitud.
            Auth::shouldUse($guard); // Cambiar dinámicamente al guard correcto.

            $credentials = $request->only(Fortify::username(), 'password');

            // Intentar autenticación con el guard configurado.
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                return Auth::user(); // Usuario autenticado correctamente.
            }

            return null; // Fallar si no hay autenticación.
        });

        // Vista personalizada para user_carrier
        Fortify::loginView(function (Request $request) {
            if ($request->is('user-carrier/*')) {
                return view('auth.user_carrier.login'); // Vista específica para user_carrier
            }

            if ($request->is('user-driver/*')) {
                return view('auth.user_driver.login'); // Vista específica para user_driver
            }

            return view('auth.login'); // Vista por defecto
        });

        // También puedes registrar vistas de registro similares si es necesario.
        Fortify::registerView(function (Request $request) {
            if ($request->is('user-carrier/*')) {
                return view('auth.user_carrier.register'); // Vista específica para user_carrier
            }

            if ($request->is('user-driver/*')) {
                return view('auth.user_driver.register'); // Vista específica para user_driver
            }

            return view('auth.register'); // Vista por defecto
        });
    }
}
