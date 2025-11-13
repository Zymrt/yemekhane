<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'transactions';

    /**
     * Toplu atamaya izin verilen alanlar.
     */
    protected $fillable = [
        'user_id',
        'type',   // 'credit' (yükleme) veya 'debit' (harcama)
        'amount', // Tutar (float/double olarak)
        'meta',   // Ekstra bilgi (örn: { order_id: '...' } veya { payment_id: '...' })
    ];

    /**
     * 'amount' alanını float olarak cast et.
     */
    protected $casts = [
        'amount' => 'float',
        'meta'   => 'array',
    ];

    /**
     * 'created_at' ve 'updated_at' alanlarını Carbon olarak ele al.
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Bu işlemi yapan kullanıcı.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}