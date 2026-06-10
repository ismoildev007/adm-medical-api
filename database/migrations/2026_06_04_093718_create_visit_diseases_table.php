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
        Schema::create('visit_diseases', function (Blueprint $table) {
            $table->id();

            // Qaysi tashrifda ushbu kasallik aniqlanganligi (tashrif o'chirilsa, yozuv ham o'chadi)
            $table->foreignId('visit_id')
                ->constrained('patient_visits')
                ->cascadeOnDelete();

            // Aniqlangan kasallik (diseases jadvaliga bog'liq)
            // Bir tashrifda bir nechta kasallik qayd etilishi mumkin
            $table->foreignId('disease_id')
                ->constrained();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_diseases');
    }
};