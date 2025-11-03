<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->dateTime('start_time');
            $table->integer('duration_minutes')->default(60);
            $table->text('description')->nullable();

            $table->enum('status', [
                'preliminary',
                'confirmed',
                'cancelled',
                'no_show',
                'cancelled_by_feer',
                'cancelled_by_client', // dodajemy od razu
                'attended'
            ])->default('preliminary');

            $table->string('approved_by_name')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
