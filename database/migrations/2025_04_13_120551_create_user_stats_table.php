<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_stats', function (Blueprint $table) {
            $table->id(); // Standard auto-incrementing primary key
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->integer('total_attempts')->default(0);
            $table->integer('total_completed_levels')->default(0);
            $table->integer('total_score')->default(0);
            $table->integer('time_spent')->default(0); // in seconds
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_stats');
    }
};
