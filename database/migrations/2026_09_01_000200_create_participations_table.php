<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participations', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 80);
            $table->string('department', 80)->index();
            $table->string('selected_word', 30)->index();
            $table->boolean('is_custom_word')->default(false);
            $table->string('message', 150);
            $table->string('image_path')->nullable();
            $table->boolean('show_image')->default(false);
            $table->string('status', 20)->default('pending')->index();
            $table->boolean('consent')->default(false);
            $table->timestamp('consented_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_demo')->default(false)->index();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participations');
    }
};
