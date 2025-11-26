<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'logs';

    protected $fillable = [
        'user_id',      // İşlemi yapan kişi
        'user_name',    // İşlemi yapan kişinin adı (Sonradan eklendi, loglarda görünsün diye)
        'action',       // İşlem başlığı (Örn: "Menü Eklendi")
        'details',      // Detay (description yerine details kullandık controllerda)
        'description',  // Alternatif detay alanı
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