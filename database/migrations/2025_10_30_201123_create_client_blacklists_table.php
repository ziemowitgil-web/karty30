<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_blacklist', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Imię i nazwisko klienta
            $table->text('reason'); // Powód dodania do blacklisty
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_blacklist');
    }
};
