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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('foreign_table', 100)->nullable(false);
            $table->foreignId('store_id')->nullable(false);
            $table->string('postal_code', 8)->nullable(false);
            $table->string('state', 2)->nullable(false);
            $table->string('city', 200)->nullable(false);
            $table->string('sublocality', 200)->nullable(false);
            $table->string('street', 200)->nullable(false);
            $table->string('street_number', 200)->nullable(false);
            $table->string('complement', 200)->nullable(false);
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
