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
        Schema::table('tabel_inspection', function (Blueprint $table) {
            $table->integer('hose')->nullable()->change();
        });

        Schema::table('tabel_produk', function (Blueprint $table) {
            $table->integer('hose')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tabel_inspection', function (Blueprint $table) {
            $table->integer('hose')->nullable(false)->change();
        });

        Schema::table('tabel_produk', function (Blueprint $table) {
            $table->integer('hose')->nullable(false)->change();
        });
    }
};
