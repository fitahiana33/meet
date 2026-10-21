<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entities', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 180)->unique();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('declarants', function (Blueprint $table): void {
            $table->id();
            $table->string('first_name', 120);
            $table->string('last_name', 120);
            $table->string('email')->nullable()->index();
            $table->string('address')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });

        Schema::create('declarant_grievance', function (Blueprint $table): void {
            $table->foreignId('declarant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grievance_id')->constrained()->cascadeOnDelete();
            $table->primary(['declarant_id', 'grievance_id']);
            $table->timestamps();
        });

        Schema::table('grievances', function (Blueprint $table): void {
            $table->foreignId('entity_id')->nullable()->after('sector_id')->constrained('entities')->nullOnDelete();
            $table->index(['entity_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('grievances', function (Blueprint $table): void {
            $table->dropIndex(['entity_id', 'status']);
            $table->dropConstrainedForeignId('entity_id');
        });

        Schema::dropIfExists('declarant_grievance');
        Schema::dropIfExists('declarants');
        Schema::dropIfExists('entities');
    }
};
