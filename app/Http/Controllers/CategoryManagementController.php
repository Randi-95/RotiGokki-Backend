<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryManagementController extends Controller
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

        $query = Category::query()->withCount('products')->latest();

        if ($request->filled('search')) {
            $search = strtolower($request->input('search'));
            $query->whereRaw('LOWER(name) like ?', ["%{$search}%"]);
        }

        $perPage = (int) $request->input('per_page', 8);
        $perPage = $perPage > 0 ? min($perPage, 20) : 8;

        $categories = $query->paginate($perPage)->appends($request->query());

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:categories,name'
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan.',
            'data' => $category
        ], 201);
    }

    public function update(Request $request, Category $category)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:categories,name,' . $category->id
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Kategori berhasil diperbarui.',
            'data' => $category
        ]);
    }

    public function destroy(Category $category)
    {
        $this->ensureSuperAdmin();

        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'Kategori tidak dapat dihapus karena masih memiliki produk.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus.'
        ]);
    }
}
