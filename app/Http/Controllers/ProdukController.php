<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProdukController extends Controller
{
    public function index()
    {
        $products = Produk::all()->map(function ($product) {
            return $product->append('image_url');
        });
        return response()->json($products);
    }

    public function getProductsByCategory(Category $category){
        $products = $category->products()->paginate(4);

        return response()->json($products);
    }

     public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id'=> 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Produk::create($data);
        return response()->json($product->append('image_url'), 201);
    }

    public function show(Produk $product)
    {
        return response()->json($product->append('image_url'));
    }

    public function update(Request $request, Produk $product)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:255',
            'deskripsi' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'data gagal diperbarui',
                'error' => $validator->errors()
            ]);
        }

        $data = $validator->validated();

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image); // Hapus gambar lama
            }
            $data['image'] = $request->file('image')->store('products', 'public'); // Upload gambar baru
        }

        $product->update($data);
        return response()->json($product->append('image_url'));
    }

     public function destroy(Produk $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        return response()->json(null, 204);
    }
}
