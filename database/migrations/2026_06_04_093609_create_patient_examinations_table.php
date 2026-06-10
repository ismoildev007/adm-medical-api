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
        Schema::create('patient_examinations', function (Blueprint $table) {
            $table->id();

            // Qaysi tashrifga tegishli ko'rik ma'lumotlari (tashrif o'chirilsa, ko'rik ham o'chadi)
            $table->foreignId('visit_id')
                ->constrained('patient_visits')
                ->cascadeOnDelete();

            // Ko'rik paytidagi tana harorati (°C), masalan: 36.6
            $table->decimal('temperature', 4, 1)->nullable();

            // Qon bosimi qiymati (masalan: "120/80")
            $table->string('blood_pressure')->nullable();

            // Minutiga urish soni (puls chastotasi)
            $table->integer('pulse')->nullable();

            // Bemorning o'zi bildirgan shikoyatlar (og'riq, bosh aylanish va h.k.)
            $table->text('complaints')->nullable();

            // Shifokorning ko'rik natijasidagi xulosalari va tavsiyalari
            $table->text('doctor_notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_examinations');
    }
};