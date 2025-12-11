<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon; // Carbon kütüphanesi eklendi
use App\Models\Order; // Order Model ilişki kontrolü için eklendi

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
        'meal_price'
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
    // 🟢 HAS_PURCHASED ACCESSOR (ÖNEMLİ EKLENTİ)
    // -----------------------------------------------------------------

    // 'has_purchased' alanını her zaman JSON çıktısına ekler.
    protected $appends = ['has_purchased'];

    /**
     * Kullanıcının bugün için yemek satın alıp almadığını kontrol eden Accessor.
     *
     * @return bool
     */
    public function getHasPurchasedAttribute(): bool
    {
        // Bugünün başlangıç ve bitiş zamanları (Günlük sıfırlamayı sağlar)
        $startOfDay = Carbon::today()->startOfDay();
        $endOfDay = Carbon::today()->endOfDay();

        // SADECE BUGÜNE AİT, ÖDENMİŞ (paid) sipariş kaydını arıyoruz
        // Eğer Order modeli yoksa veya yanlış yoldaysa bu kısım hata verir.
        // Hata durumunda default olarak false dönecek şekilde tasarlanmıştır.
        try {
            return Order::where('user_id', (string)$this->id)
                ->where('status', 'paid') 
                ->whereBetween('date', [$startOfDay, $endOfDay])
                ->exists();
        } catch (\Exception $e) {
            // Eğer veritabanı veya model hatası olursa, güvenli tarafta kalıp false döndür.
            return false;
        }
    }
}