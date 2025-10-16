<?php
// database/migrations/2014_10_12_000000_create_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // YALNIZCA 'users' adında bir collection YOKSA oluştur
        if (!Schema::hasCollection('users')) {
            Schema::create('users', function (Blueprint $collection) {
                $collection->index('id'); // MongoDB'de genellikle id'yi indexlemek iyi bir pratiktir.
                $collection->string('name');
                // Diğer sütunlarınız...
                // Örneğin: $collection->string('phone')->unique();
                // Örneğin: $collection->string('password');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}