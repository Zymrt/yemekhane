<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model; // <-- burası çok önemli

class Menu extends Model
{
    protected $connection = 'mongodb';   // hangi bağlantıyı kullanacağını belirt
    protected $collection = 'menus';     // koleksiyon adı (MongoDB’de tablo değil koleksiyon olur)

    protected $fillable = ['date', 'items'];
}
