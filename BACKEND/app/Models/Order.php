<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model; // DİKKAT: Model'i buradan al
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'orders';

    /**
     * Toplu atamaya izin verilen alanlar.
     * SENİN CONTROLLER'INA UYGUN HALE GETİRİLDİ.
     */
    protected $fillable = [
        'user_id',
        'menu_id',
        'date',
        'qty',    // 🌟 GEREKLİ
        'price',  // 🌟 GEREKLİ
        'total',  // 🌟 GEREKLİ
        'status', // 🌟 GEREKLİ
    ];

    /**
     * Tarih alanları.
     */
    protected $dates = ['date'];

    /**
     * Siparişi veren kullanıcı.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Sipariş verilen menü.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}