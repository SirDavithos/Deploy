<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Muestra la vista de registro (Renderizada con Vue mediante Inertia).
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Procesa la solicitud de registro de un nuevo usuario.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Calcular la mayoría de edad
        $fechaMinimaAdulto = now()->subYears(18)->format('Y-m-d');

        // 2. Validación de datos (Si falla aquí, Laravel redirige antes de tocar la Base de Datos)
        $request->validate([
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'paternal_last_name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'maternal_last_name' => ['nullable', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'ci_number' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/', 'unique:'.User::class],
            'ci_extension' => ['required', 'string', 'size:2', 'in:LP,CB,SC,OR,PT,CH,TJ,BE,PD'],
            'birth_date' => ['required', 'date', 'before_or_equal:' . $fechaMinimaAdulto],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'first_name.regex' => 'El nombre no puede contener números ni caracteres especiales.',
            'paternal_last_name.regex' => 'El apellido paterno no puede contener números.',
            'maternal_last_name.regex' => 'El apellido materno no puede contener números.',
            'ci_number.regex' => 'El carnet de identidad solo debe contener números.',
            'birth_date.before_or_equal' => 'Debes ser mayor de edad (18 años) para registrarte en Punto Boliviano.',
        ]);

        // 3. Proteger la persistencia de datos con un bloque Try-Catch y Transacciones
        try {
            // Iniciamos la transacción en MySQL
            DB::beginTransaction();

            // A. Crear el registro en la tabla 'users'
            $user = User::create([
                'first_name' => $request->first_name,
                'paternal_last_name' => $request->paternal_last_name,
                'maternal_last_name' => $request->maternal_last_name,
                'ci_number' => $request->ci_number,
                'ci_extension' => $request->ci_extension,
                'birth_date' => $request->birth_date,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'accepted_terms_at' => now(),
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // B. Buscar o crear el Rol
            $customerRole = Role::firstOrCreate(
                ['slug' => 'customer'],
                ['name' => 'Comprador']
            );
            
            // C. Adjuntar el rol en la tabla pivote (Si esto falla, se cancela el paso A)
            $user->roles()->attach($customerRole->id);

            // Si el código llegó hasta aquí sin errores, guardamos todo permanentemente
            DB::commit();

            // 4. Disparar eventos de login y redirección (Fuera de la transacción por rendimiento)
            event(new Registered($user));
            Auth::login($user);

            return redirect('/');

        } catch (\Exception $e) {
            // ¡ALERTA! Algo salió mal en el proceso (ej: base de datos caída, error de sintaxis, etc.)
            // Deshacemos todo lo que se haya intentado escribir en este bloque 'try'
            DB::rollBack();

            // Redirigimos al usuario hacia atrás con un mensaje de error global para que lo intente de nuevo
            return back()->withErrors([
                'email' => 'Ocurrió un error inesperado al procesar tu registro en el servidor. Por favor, inténtalo de nuevo.'
            ]);
        }
    }
}