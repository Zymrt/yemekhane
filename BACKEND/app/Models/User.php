<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use App\Models\Order;

class User extends Model implements Authenticatable
{
    use AuthenticatableTrait, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    /**
     * Toplu atamaya (Mass Assignment) izin verilen alanlar.
     */
    protected $fillable = [
        'name',
        'surname',
        'phone',
        'email',
        'password',
        'unit',
        'balance',
        'status',
        'document_path',
        'role',
        'meal_price',
    ];

    /**
     * Varsayılan değerler.
     */
    protected $attributes = [
        'role' => 'user',
        'status' => 'pending',
        'balance' => 0,
    ];

    protected $hidden = ['password'];

    // -----------------------------------------------------------------
    // 🟢 has_purchased => JSON'a HER ZAMAN eklensin
    // -----------------------------------------------------------------
    protected $appends = ['has_purchased'];

    /**
     * Kullanıcının BUGÜN için yemek satın alıp almadığını döndürür.
     * Order koleksiyonunda bugünün tarihine göre kontrol eder.
     */
    public function getHasPurchasedAttribute(): bool
{
    try {
        // İstanbul saatine göre bugünün tarihi (YIL-AY-GÜN)
        $today = Carbon::today('Europe/Istanbul')->toDateString();

        // Mongo kullanıcı ID'si (primary key)
        $userId = (string) $this->getKey();   // 🔥 ARTIK DOĞRU!

        return Order::where('user_id', $userId)
            ->where('status', 'paid')              // sipariş ödenmiş olacak
            ->whereDate('date', $today)            // order.date = bugünün menü tarihi
            ->exists();
    } catch (\Exception $e) {
        return false;
    }
}
}
