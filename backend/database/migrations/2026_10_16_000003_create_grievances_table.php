<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grievances', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->foreignId('sector_id')->constrained('sectors')->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description');
            $table->string('status', 30)->default('submitted')->index();
            $table->dateTime('complained_at');
            $table->dateTime('due_at');
            $table->timestamps();

            $table->index(['sector_id', 'status']);
            $table->index('due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grievances');
    }
};
