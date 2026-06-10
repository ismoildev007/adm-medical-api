<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('position_translations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('object_id');
            $table->string('language_code');
            $table->string('name');
            $table->index('object_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('position_translations');
    }
};