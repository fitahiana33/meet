<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grievance_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('grievance_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30);
            $table->foreignId('changed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('changed_at');
            $table->timestamps();
            $table->index(['grievance_id', 'changed_at']);
        });

        Schema::create('grievance_assignment_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('grievance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('changed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('changed_at');
            $table->timestamps();
            $table->index(['grievance_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grievance_assignment_histories');
        Schema::dropIfExists('grievance_status_histories');
    }
};
