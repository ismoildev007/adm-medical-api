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
        Schema::create('sick_leaves', function (Blueprint $table) {
            $table->id();

            // Ushbu kasallik varag'i qaysi tashrifga asoslanib berilganligi (patient_visits jadvaliga bog'liq)
            $table->foreignId('visit_id')
                ->constrained('patient_visits');

            // Kasallik varaqasi boshlanadigan sana
            $table->date('start_date');

            // Kasallik varaqasi tugaydigan sana (xodim ishga qaytish kerak bo'lgan kun)
            $table->date('end_date');

            // Dam olishning tibbiy sababi yoki shifokor xulosasi
            $table->text('reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sick_leaves');
    }
};