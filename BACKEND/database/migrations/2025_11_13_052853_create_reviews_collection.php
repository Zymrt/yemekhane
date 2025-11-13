<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // MongoDB driver kullanıyorsan 'create' collection oluşturur
        Schema::connection('mongodb')->table('reviews', function (Blueprint $table) {
            $table->id();
            
            // Sütun tipi belirtmene gerek yok ama index için belirtiyoruz:
            // user_id ve menu_id ikilisi BENZERSİZ olmalı.
            // Bu sayede aynı kişi aynı menüye 2. kez yorum atarsa MongoDB hata fırlatır.
            $table->unique(['user_id', 'menu_id']);
        });
    }

    public function down()
    {
        Schema::connection('mongodb')->dropIfExists('reviews');
    }
};