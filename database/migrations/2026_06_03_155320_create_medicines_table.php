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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();

            // Dorining tegishli kategoriyasi (medicine_categories jadvaliga bog'liq)
            $table->foreignId('category_id')
                ->constrained('medicine_categories');

            // Dorining rasmiy nomi
            $table->string('name');

            // Dorini ishlab chiqargan kompaniya yoki zavod nomi
            $table->string('manufacturer')->nullable();

            // O'lchov birligi: dona, quti, ml, mg va boshqalar
            $table->string('unit');

            // Hozirgi vaqtda omborda mavjud miqdor
            $table->integer('quantity')->default(0);

            // Dorining narxi (so'mda yoki valyutada)
            $table->decimal('price', 12, 2)->nullable();

            // Dorining yaroqlilik muddati tugash sanasi
            $table->date('expire_date')->nullable();

            // Dori haqida qo'shimcha tavsif yoki ko'rsatmalar
            $table->text('description')->nullable();

            // Dori faol holati: false bo'lsa, ombordan berilmaydi
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
