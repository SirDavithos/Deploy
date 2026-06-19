<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Filtros avanzados
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('paternal_last_name', 'like', "%{$search}%")
                  ->orWhere('maternal_last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('ci_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('slug', $request->input('role'));
            });
        }

        // Ordenación
        $query->orderBy('created_at', 'desc');

        $users = $query->paginate(20)->appends($request->except('page'));

        return Inertia::render('Admin/Users/Index', [
            'users'  => $users,
            'filters' => $request->only(['search', 'status', 'role']),
        ]);
    }

    // Método para exportar PDF con los filtros actuales
    public function exportPdf(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('paternal_last_name', 'like', "%{$search}%")
                  ->orWhere('maternal_last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('ci_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('slug', $request->input('role'));
            });
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('pdf.admin.users', compact('users'));

        return $pdf->download('usuarios.pdf');
    }

    public function edit(User $user)
    {
        $user->load('phones', 'addresses', 'taxData', 'roles');
        return Inertia::render('Admin/Users/Edit', ['editUser' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'first_name'          => 'required|string|max:255',
            'paternal_last_name'  => 'required|string|max:255',
            'maternal_last_name'  => 'nullable|string|max:255',
            'ci_number'           => 'required|string|unique:users,ci_number,'.$user->id,
            'ci_extension'        => 'required|string|size:2',
            'birth_date'          => 'required|date',
            'email'               => 'required|email|unique:users,email,'.$user->id,
            'password'            => 'nullable|min:8|confirmed',
            'status'              => 'required|in:active,inactive,suspended',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return back()->with('status', 'Usuario actualizado.');
    }
}