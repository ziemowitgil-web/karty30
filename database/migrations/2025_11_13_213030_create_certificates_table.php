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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();

            // Powiązanie z użytkownikiem lub osobą
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // Nazwa certyfikatu
            $table->string('name');

            // Organizacja wydająca certyfikat
            $table->string('organization')->nullable();

            // Data ważności certyfikatu
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();

            // Status certyfikatu: active, revoked, expired
            $table->string('status')->default('active');

            // Opcjonalne pole do przechowywania numeru certyfikatu lub identyfikatora
            $table->string('certificate_number')->nullable()->unique();

            // Dodatkowe uwagi
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
