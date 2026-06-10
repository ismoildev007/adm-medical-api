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
        Schema::create('medicine_transactions', function (Blueprint $table) {
            $table->id();

            // Qaysi dori bo'yicha harakat amalga oshirilganligi (medicines jadvaliga bog'liq)
            $table->foreignId('medicine_id')
                ->constrained();

            // Tranzaksiya turi:
            //   income    — omborga kirim (yangi dori keldi)
            //   outcome   — bemorga berildi yoki sarflandi
            //   write_off — yaroqsiz yoki muddati o'tganligi sababli hisobdan chiqarildi
            $table->enum('type', [
                'income',
                'outcome',
                'write_off',
            ]);

            // Ushbu tranzaksiyada kirim/chiqim qilingan dori miqdori
            $table->integer('quantity');

            // Tranzaksiya bo'yicha qo'shimcha izoh (masalan: sabab, manbaa)
            $table->text('comment')->nullable();

            // Tranzaksiyani amalga oshirgan foydalanuvchi (users jadvaliga bog'liq)
            $table->foreignId('created_by')
                ->constrained('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_transactions');
    }
};
