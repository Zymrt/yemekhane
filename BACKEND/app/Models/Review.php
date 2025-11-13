<?php

namespace App\Models;

// DİKKAT: Burası değişti (SQL yerine MongoDB)
use MongoDB\Laravel\Eloquent\Model; 
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    protected $connection = 'mongodb'; // Bağlantı adı
    protected $collection = 'reviews'; // Collection adı

    // create veya updateOrCreate kullanırken izin verilen alanlar
    protected $fillable = [
        'user_id', 
        'menu_id', 
        'rating', 
        'comment', 
        'date' // Controller'da 'date' alanını da kullanıyorsun, buraya eklemelisin
    ];

    // Tarih formatlaması için (tarihsel sorgularda işine yarar)
    protected $dates = ['date'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function menu() {
        return $this->belongsTo(Menu::class);
    }
}