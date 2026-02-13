<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['info', 'warning', 'danger'])->default('info');
            $table->string('title');
            $table->text('message');
            $table->float('water_level')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index('type');
            $table->index('is_read');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
