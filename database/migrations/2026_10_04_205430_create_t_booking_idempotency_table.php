<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_booking_idempotency', function (Blueprint $table) {
            $table->string('token', 36)->primary();
            $table->string('payment_code', 50)->unique();
            $table->string('booking_id', 36)->nullable()->unique();
            $table->timestamps();

            $table->foreign('booking_id')
                ->references('id')
                ->on('t_booking')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_booking_idempotency');
    }
};
