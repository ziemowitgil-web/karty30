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
        Schema::create('cost_and_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // name
            $table->string('service'); // service
            $table->boolean('classes_included')->default(false); // classes included
            $table->date('valid_from')->nullable(); // valid from
            $table->date('valid_to')->nullable(); // valid to
            $table->decimal('amount', 10, 2); // amount in zł,gr
            $table->string('mpp_number')->nullable(); // nr MPP (miejsce powstawania przychodu)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_and_invoices');
    }
};
