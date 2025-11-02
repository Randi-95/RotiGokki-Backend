<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pesanan extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_whatsapp',
        'shipping_address',
        'total_amount',
        'status',
    ];

    /**
     * [PERBAIKAN]
     * Hapus komentar dan ganti nama fungsinya menjadi 'details'
     */
  public function details(): HasMany
    {
        // Parameter:
        // 1. Nama model yang berelasi (DetailPesanan::class)
        // 2. Foreign key di tabel 'detail_pesanans' (default: pesanan_id)
        // 3. Local key di tabel 'pesanans' (default: id)
        return $this->hasMany(DetailPesanan::class, 'pesanan_id', 'id');
    }
}