<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; 
use Storage;


class Produk extends Model
{

    use HasFactory;
    protected $fillable = [
        'nama',
        'deskripsi',
        'image',
        'price',
        'category_id',
    ];

    protected function ImageUrl(): Attribute{
        return Attribute::make(
            get: fn() =>$this->image ? Storage::url($this->image): null,
        );
    }

    public function kategori() {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
