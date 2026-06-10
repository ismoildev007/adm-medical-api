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
        Schema::create('patient_visits', function (Blueprint $table) {
            $table->id();

            // Tibbiy yordamga murojaat qilgan xodim (employees jadvaliga bog'liq)
            $table->foreignId('employee_id')
                ->constrained();

            // Xodimni qabul qilgan shifokor (users jadvaliga bog'liq)
            $table->foreignId('doctor_id')
                ->constrained('users');

            // Tashrif turi:
            //   self          — xodim o'zi mustaqil murojaat qildi
            //   brigadier     — brigadir yoki mas'ul shaxs olib keldi
            //   medical_exam  — rejalashtirilgan tibbiy ko'rik (profilaktika)
            $table->enum('visit_type', [
                'self',
                'brigadier',
                'medical_exam',
            ]);

            // Xodim qabul qilingan aniq sana va vaqt
            $table->datetime('visited_at');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_visits');
    }
};