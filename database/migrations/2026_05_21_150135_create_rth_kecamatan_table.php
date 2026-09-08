<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('v_rth_kelurahan', function (Blueprint $table) {
            $table->id();

            $table->string('kelurahan', 100);
            $table->smallInteger('tahun');

            $table->decimal('luas_rth', 10, 4);
            $table->decimal('luas_wilayah', 10, 4);

            $table->decimal('pct_rth', 6, 2)->nullable();

            $table->integer('jumlah_penduduk')->nullable();

            $table->decimal('kepadatan', 10, 2)->nullable();

            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->unique(['kelurahan', 'tahun']);

            $table->index('tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('v_rth_kelurahan');
    }
};