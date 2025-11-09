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
        'outlet_id'
    ];


  public function details(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'pesanan_id', 'id');
    }
}