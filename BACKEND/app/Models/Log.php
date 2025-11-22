<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'logs';

    protected $fillable = [
        'user_id',      // İşlemi yapan kişi
        'action',       // İşlem başlığı (Örn: "Menü Eklendi")
        'description',  // Detay (Örn: "Mercimek Çorbası sisteme eklendi.")
        'type',         // info, warning, error, critical
        'ip_address',   // Güvenlik için IP
        'user_agent'    // Tarayıcı bilgisi
    ];

    /**
     * Logu oluşturan kullanıcıyı getir
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}