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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();

            $table->enum('status', ['enrolled', 'ready', 'to_settle', 'other'])->default('enrolled'); // status klienta
            $table->text('problem')->nullable(); // opis problemu
            $table->string('equipment')->nullable(); // sprzęt, np. wózek, laptop, itp.

            $table->date('date_of_birth')->nullable(); // data urodzenia
            $table->enum('gender', ['male', 'female', 'other'])->nullable(); // płeć
            $table->string('address')->nullable(); // adres klienta
            $table->text('notes')->nullable(); // dodatkowe uwagi
            $table->enum('preferred_contact_method', ['email', 'phone', 'sms'])->default('email'); // preferowany kontakt

            $table->boolean('consent')->default(false); // zgoda na przetwarzanie danych
            $table->json('available_days')->nullable(); // dni dostępności
            $table->json('time_slots')->nullable(); // przedziały czasowe dostępności

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
