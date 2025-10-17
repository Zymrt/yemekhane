<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('menus', function (Blueprint $collection) {
        $collection->id();
        $collection->date('date')->unique(); // Her tarihe ait sadece bir menü olabilir
        $collection->json('items'); // Yemek listesini JSON olarak saklayacağız
        $collection->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
