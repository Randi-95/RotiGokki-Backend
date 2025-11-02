<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetailPesanan extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pesanan_id',
        'product_id',
        'quantity',
        'price',
    ];

    /**
     * Mendefinisikan relasi "belongsTo" ke model Pesanan.
     * Satu item detail ini milik satu pesanan.
     */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class);
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model Product.
     * Satu item detail ini merujuk ke satu produk.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }

     public function details(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'pesanan_id');
    }
}