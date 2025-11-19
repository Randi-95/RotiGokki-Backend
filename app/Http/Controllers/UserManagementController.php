<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    private function ensureSuperAdmin(): void
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'superadmin') {
            throw new HttpResponseException(
                response()->json(['message' => 'Akses ditolak.'], 403)
            );
        }
    }

    public function index(Request $request)
    {
        $this->ensureSuperAdmin();

        $query = User::with('outlet')
            ->where('role', 'admin_outlet')
            ->latest();

        if ($request->filled('search')) {
            $search = strtolower($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) like ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(email) like ?', ["%{$search}%"]);
            });
        }

        if ($request->filled('outlet_id')) {
            $query->where('outlet_id', $request->input('outlet_id'));
        }

        $perPage = (int) $request->input('per_page', 6);
        $perPage = $perPage > 0 ? min($perPage, 20) : 6;

        $users = $query->paginate($perPage)->appends($request->query());

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'outlet_id' => 'required|integer|exists:outlets,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin_outlet',
            'outlet_id' => $validated['outlet_id'],
        ]);

        return response()->json([
            'message' => 'Akun admin outlet berhasil dibuat.',
            'data' => $user->load('outlet'),
        ], 201);
    }

    public function destroy(User $user)
    {
        $this->ensureSuperAdmin();

        if ($user->role !== 'admin_outlet') {
            return response()->json([
                'message' => 'Hanya akun admin outlet yang dapat dihapus.',
            ], 422);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'Akun admin outlet berhasil dihapus.',
        ]);
    }
}
