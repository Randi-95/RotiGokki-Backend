<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OutletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
    {
      $query = Outlet::query();

        if ($request->filled('search')) { 
            $searchTerm = $request->search;
            
            $query->where(function($q) use ($searchTerm) {
                $q->where(DB::raw('LOWER(nama)'), 'like', '%' . strtolower($searchTerm) . '%')
                  ->orWhere(DB::raw('LOWER(alamat)'), 'like', '%' . strtolower($searchTerm) . '%');
            });
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

         $outlets = $query->latest()->paginate(3)->appends($request->query());
        return response()->json($outlets);
    }

    public function cities()
    {
        $cities = Outlet::select('city')->distinct()->orderBy('city')->get();
        return response()->json($cities->pluck('city'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'alamat' => 'required|string',
            'city' => 'required',
            'jam' => 'required|string',
            'nomor_telepon' => '',
            'link_map' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'data gagal ditambahkan',
                'error' => $validator->errors()
            ], 402);
        }

        $outlet = Outlet::create($request->all());
        return response()->json([
            'status' => true,
            'message' => 'data berhasil dimasukkan',
            'data' => $outlet
        ],202);

    }

    /**
     * Display the specified resource.
     */
    public function show(Outlet $outlet)
    {
        //
    }

    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'alamat' => 'required|string',
            'jam' => 'required|string',
            'nomor_telepon' => 'required',
            'link_map' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'data gagal ditambahkan',
                'error' => $validator->errors()
            ], 402);
        }

        $outlets = Outlet::findOrFail($id);
        $outlets->update($request->all());
        return response()->json([
            'status' => true,
            'message' => 'data berhasil dimasukkan',
            'data' => $outlets
        ],202);
    }

   

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id)
    {
        $outlets = Outlet::findOrFail($id);
        $outlets->delete();

        return response()->json([
            'status' => true,
            'message' => 'data berhasil dihapus',
        ],202);
    }

    public function listAll()
    {
        $outlets = Outlet::orderBy('nama', 'asc')->get();
        return response()->json($outlets);
    }

    public function countOutlet() {
        $length = Outlet::count();

        return response()->json(
            [
                'length'=> $length
            ],202);
    }
}
