<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_staff', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('staff_id');
            $table->bigInteger('employee_id');
            $table->bigInteger('department_id');
            $table->bigInteger('position_id')->nullable();
            $table->boolean('main_staff')->default(false);
            $table->bigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_staff');
    }
};