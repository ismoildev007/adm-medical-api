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
        Schema::create('visit_medicines', function (Blueprint $table) {
            $table->id();

            // Qaysi tashrif davomida dori tayinlanganligi (tashrif o'chirilsa, yozuv ham o'chadi)
            $table->foreignId('visit_id')
                ->constrained('patient_visits')
                ->cascadeOnDelete();

            // Tayinlangan dori (medicines jadvaliga bog'liq)
            $table->foreignId('medicine_id')
                ->constrained();

            // Bemorga berilgan yoki tayinlangan dori miqdori
            $table->integer('quantity');

            // Bir martalik doza miqdori (masalan: "1 dona", "5 ml", "500 mg")
            $table->string('dosage')->nullable();

            // Dorini qabul qilish bo'yicha to'liq ko'rsatma (qachon, qancha, ovqat bilan/holda)
            $table->text('instruction')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_medicines');
    }
};