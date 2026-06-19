<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LoginController extends Controller
{
    /**
     * Manejar la solicitud de inicio de sesión.
     */
    public function login(Request $request)
    {
        // 1. Validaciones con mensajes personalizados en español
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'El correo electrónico es un campo obligatorio.',
            'email.email' => 'Por favor, introduce una dirección de correo válida.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        // 2. Intento de autenticación con la opción de "recordarme"
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Regla de negocio de seguridad: Verificar si la cuenta no está suspendida/inactiva
            if ($user->status !== 'active') {
                Auth::logout();
                return response()->json([
                    'errors' => [
                        'email' => ['Esta cuenta se encuentra inactiva. Comuníquese con el administrador del sistema.']
                    ]
                ], 403);
            }

            // 3. Auditoría de Seguridad requerida para el software (Último login e IP)
            $user->update([
                'last_login_at' => Carbon::now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Regenerar la sesión para prevenir vulnerabilidades de fijación de sesión
            $request->session()->regenerate();

            // Cargar los slugs de los roles para enviarlos al Frontend
            $roles = $user->roles->pluck('slug');

            return response()->json([
                'message' => '¡Inicio de sesión exitoso!',
                'user' => [
                    'id' => $user->id,
                    'full_name' => trim("{$user->first_name} {$user->paternal_last_name} {$user->maternal_last_name}"),
                    'email' => $user->email,
                    'roles' => $roles,
                    'avatar' => $user->avatar
                ]
            ], 200);
        }

        // Si las credenciales fallan, respondemos con el estándar de errores de Laravel (422)
        return response()->json([
            'errors' => [
                'email' => ['Las credenciales proporcionadas no coinciden con nuestros registros.']
            ]
        ], 422);
    }

    /**
     * Cerrar la sesión del usuario de forma segura.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Limpieza y destrucción segura de tokens de sesión en el servidor
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.'
        ], 200);
    }
}