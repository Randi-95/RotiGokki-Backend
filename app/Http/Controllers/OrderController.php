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

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) 
    {
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

        $pesanans = $query->paginate(4); 
        return response()->json($pesanans, 200);
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
            ]);

            foreach ($request->items as $item) {
                DetailPesanan::create([
                    'pesanan_id' => $pesanan->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);


                $produk = Produk::find($item['product_id']);
                $produk->decrement('stock', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pesanan berhasil dibuat',
                'order_id' => $pesanan->id,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan pada server',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function cancel(Pesanan $pesanan)
    {
        if ($pesanan->status !== 'pending') {
            return response()->json(['message' => 'Hanya pesanan dengan status Pending yang bisa dibatalkan.'], 422);
        }
        
        DB::beginTransaction();
        try {
            $pesanan->load('details.product');

            foreach ($pesanan->details as $detail) {
                Produk::find($detail->product_id)->increment('stock', $detail->quantity);
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
        
        $pesanan->load('details.product');

        return response()->json($pesanan, 200);

       
    }

    /**
     * Update the status of the specified resource in storage.
     * Ini adalah method baru untuk mengubah status
     */
    public function updateStatus(Request $request, Pesanan $pesanan)
    {
        $validator = Validator::make($request->all(), [
            'status' => [
                'required',
                'string',
                Rule::in(['Proses', 'Pending', 'Dibatalkan','Selesai']), 
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        try {
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
       $today = Carbon::now('Asia/Jakarta')->toDateString(); 

        $pesananHariIni = Pesanan::whereRaw("
            DATE(CONVERT_TZ(created_at, '+00:00', '+07:00')) = ?
        ", [$today])->count();


        $menungguPembayaran = Pesanan::where('status', 'Pending')->count();
        $perluDiproses = Pesanan::where('status', 'Proses')->count();

        return response()->json([
            'pesananHariIni' => $pesananHariIni,
            'mengungguPembayaran' => $menungguPembayaran,
            'perluDiProses' => $perluDiproses
        ],202);
    }
}
