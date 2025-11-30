<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Pesanan;
use App\Models\Produk; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Str;

class OrderController extends Controller
{
  

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) 
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'User tidak terautentikasi'], 401);
            }

            // 'with('outlet')' <-- Ini adalah kemungkinan biang keroknya
            $query = Pesanan::query()->latest();

            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('customer_name', 'like', "%{$searchTerm}%")
                      ->orWhere('id', 'like', "%{$searchTerm}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('date')) {
                $query->whereDate('created_at', $request->input('date'));
            }

            if ($user->role === 'admin_outlet') {
                $query->where('outlet_id', $user->outlet_id);
            } elseif ($user->role === 'superadmin' && $request->filled('outlet_id')) {
                $query->where('outlet_id', $request->input('outlet_id'));
            }

            $pesanans = $query->paginate(4)->appends($request->query()); 
            return response()->json($pesanans, 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Query di OrderController@index Gagal: ' . $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_whatsapp' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'total_amount' => 'required|numeric',
            'outlet_id' => 'required|integer|exists:outlets,id', 
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer|exists:produks,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data gagal dimasukkan', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $pesanan = Pesanan::create([
                'customer_name' => $request->customer_name,
                'customer_whatsapp' => $request->customer_whatsapp,
                'shipping_address' => $request->shipping_address,
                'total_amount' => $request->total_amount,
                'status' => 'pending',
                'outlet_id' => $request->outlet_id,
            ]);

            foreach ($request->items as $item) {
                $stockPivot = DB::table('outlet_produk')
                    ->where('outlet_id', $request->outlet_id)
                    ->where('product_id', $item['product_id']);

                $currentStock = $stockPivot->value('stok');
                if (is_null($currentStock) || $currentStock < $item['quantity']) {
                    $productName = Produk::where('id', $item['product_id'])->value('nama') ?? ('produk ID ' . $item['product_id']);
                    $remainingStock = $currentStock ?? 0;
                    throw new \Exception('STOK_TIDAK_CUKUP: Stok ' . $productName . ' tidak mencukupi di outlet ini. Sisa stok: ' . $remainingStock);
                }

                DetailPesanan::create([
                    'pesanan_id' => $pesanan->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
                
                $stockPivot->decrement('stok', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pesanan berhasil dibuat',
                'order_id' => $pesanan->id,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            $errorMessage = $th->getMessage();
            if (Str::startsWith($errorMessage, 'STOK_TIDAK_CUKUP:')) {
                return response()->json([
                    'message' => trim(Str::after($errorMessage, 'STOK_TIDAK_CUKUP:')),
                ], 422);
            }

            return response()->json([
                'message' => 'Terjadi kesalahan pada server',
                'error' => $errorMessage
            ], 500);
        }
    }

    public function cancel(Pesanan $pesanan)
    {
        $user = Auth::user();
        if ($user->role === 'admin_outlet' && $pesanan->outlet_id !== $user->outlet_id) {
            return response()->json(['message' => 'Akses ditolak. Anda tidak bisa membatalkan pesanan outlet lain.'], 403);
        }
        if ($pesanan->status !== 'pending') {
            return response()->json(['message' => 'Hanya pesanan dengan status Pending yang bisa dibatalkan.'], 422);
        }

        DB::beginTransaction();
        try {
            $pesanan->load('details');
            foreach ($pesanan->details as $detail) {
                DB::table('outlet_produk')
                    ->where('outlet_id', $pesanan->outlet_id) 
                    ->where('product_id', $detail->product_id)
                    ->increment('stok', $detail->quantity);
            }

            $pesanan->status = 'Dibatalkan';
            $pesanan->save();

            DB::commit();
            return response()->json(['message' => 'Pesanan berhasil dibatalkan dan stok telah dikembalikan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan saat membatalkan pesanan.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Pesanan $pesanan)
    {
        $user = Auth::user();
        if ($user->role === 'admin_outlet' && $pesanan->outlet_id !== $user->outlet_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $pesanan->load('details.product', 'outlet'); 

        return response()->json($pesanan, 200);
    }

    /**
     * Update the status of the specified resource in storage.
     */
    public function updateStatus(Request $request, Pesanan $pesanan)
    {
        $user = Auth::user();
        if ($user->role === 'admin_outlet' && $pesanan->outlet_id !== $user->outlet_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        $validator = Validator::make($request->all(), [
            'status' => [
                'required',
                'string',
                Rule::in(['Proses', 'Pending', 'Dibatalkan', 'Selesai']),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        try {
            if ($request->status === 'Dibatalkan' && $pesanan->status !== 'Dibatalkan') {
                if ($pesanan->status === 'pending') {
                    return $this->cancel($pesanan);
                }
            }

            $pesanan->status = $request->status;
            $pesanan->save();

            return response()->json([
                'message' => 'Status pesanan berhasil diperbarui',
                'data' => $pesanan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server saat memperbarui status',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getStats() {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'User tidak terautentikasi'], 401);
            }
            
            $today = Carbon::now('Asia/Jakarta')->toDateString(); 
            $basePesananQuery = Pesanan::query();
            if ($user->role === 'admin_outlet') {
                $basePesananQuery->where('outlet_id', $user->outlet_id);
            }
            
            $queryHariIni = clone $basePesananQuery;
            $pesananHariIni = $queryHariIni->whereRaw("
                DATE(CONVERT_TZ(created_at, '+00:00', '+07:00')) = ?
            ", [$today])->count();

            $queryPending = clone $basePesananQuery;
            $menungguPembayaran = $queryPending->where('status', 'Pending')->count();

            $queryProses = clone $basePesananQuery;
            $perluDiproses = $queryProses->where('status', 'Proses')->count();

            return response()->json([
                'pesananHariIni' => $pesananHariIni,
                'mengungguPembayaran' => $menungguPembayaran,
                'perluDiProses' => $perluDiproses
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Query di OrderController@getStats Gagal: ' . $th->getMessage()
            ], 500);
        }
    }
}