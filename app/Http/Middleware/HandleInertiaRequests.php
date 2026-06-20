<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => function () use ($request) {
                    if (! $request->user()) return null;
                    $user = $request->user();
                    return [
                        'id'                  => $user->id,
                        'first_name'          => $user->first_name,
                        'paternal_last_name'  => $user->paternal_last_name,
                        'maternal_last_name'  => $user->maternal_last_name,
                        'ci_number'           => $user->ci_number,
                        'ci_extension'        => $user->ci_extension,
                        'birth_date'          => optional($user->birth_date)->format('Y-m-d'), // ← esto lo soluciona
                        'email'               => $user->email,
                        'avatar'              => $user->avatar,
                    ];
                },
                'userRoles' => function () use ($request) {
                    if (! $request->user()) return [];
                    return $request->user()->roles()->pluck('slug');
                },
                'userShop' => function () use ($request) {
                    if (! $request->user()) return null;
                    return $request->user()->shop;
                },
                // Nuevo: carrito
                'cartCount' => function () use ($request) {
                    if (! $request->user()) return 0;
                    return $request->user()->cartItems()->count();
                },
                'cartItems' => function () use ($request) {
                    if (! $request->user()) return [];
                    return $request->user()->cartItems()->with('product.shop')->get();
                },
            ],
        ];
    }
}