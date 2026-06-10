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
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'manufacturer']);
        });

        Schema::dropIfExists('medicine_categories');
    }

    public function down(): void
    {
        Schema::create('medicine_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('medicine_categories')->after('id');
            $table->string('manufacturer')->nullable()->after('name');
        });
    }
};
