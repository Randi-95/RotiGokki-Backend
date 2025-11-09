<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Produk;
use App\Models\Outlet;

class StokController extends Controller
{
    /**
     * Terapkan middleware untuk proteksi
     */


    /**
     * Mengambil daftar produk beserta stoknya (VERSI BARU DENGAN DEBUG)
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $outletId = null;

            if ($user->role === 'superadmin') {
                $validator = Validator::make($request->query(), [
                    'outlet_id' => 'required|integer|exists:outlets,id'
                ]);
                
                if ($validator->fails()) {
                    return response()->json([]);
                }
                
                $outletId = $request->query('outlet_id');
                
            } elseif ($user->role === 'admin_outlet') {
                $outletId = $user->outlet_id;
            }

            if (!$outletId) {
                return response()->json(['message' => 'Outlet tidak ditemukan'], 404);
            }

            $produkDenganStok = Produk::query()
                ->select(
                    'produks.id as product_id',
                    'produks.nama as nama_produk',
                    'produks.price',
                    'categories.name as nama_kategori',
                    'op.stok'
                )
                ->leftJoin('categories', 'produks.category_id', '=', 'categories.id')
                ->leftJoin('outlet_produk as op', function($join) use ($outletId) {
                    $join->on('produks.id', '=', 'op.product_id')
                         ->where('op.outlet_id', '=', $outletId);
                })
                ->orderBy('produks.nama', 'asc')
                ->get()
                ->map(function($produk) {
                    $produk->stok = $produk->stok ?? 0;
                    $produk->nama_kategori = $produk->nama_kategori ?? 'Tanpa Kategori';
                    return $produk;
                });
            return response()->json($produkDenganStok);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Query di StokController@index Gagal: ' . $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ], 500);
        }
    }



    public function updateStok(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'outlet_id' => 'required|integer|exists:outlets,id',
            'updates' => 'required|array',
            'updates.*.product_id' => 'required|integer|exists:produks,id',
            'updates.*.stok' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $outletId = $request->outlet_id;
        if ($user->role === 'admin_outlet' && $user->outlet_id != $outletId) {
            return response()->json(['message' => 'Akses ditolak. Anda hanya bisa mengubah stok outlet Anda sendiri.'], 403);
        }

        DB::beginTransaction();
        try {
            foreach ($request->updates as $item) {
                DB::table('outlet_produk')
                    ->updateOrInsert(
                        [ 
                            'outlet_id' => $outletId,
                            'product_id' => $item['product_id']
                        ],
                        [ 
                            'stok' => $item['stok'],
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
            }
            DB::commit();
            
            return response()->json(['message' => 'Stok berhasil diperbarui!']);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat update stok',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}



