<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 50)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $timestamp = now();

        DB::table('roles')->insert([
            ['name' => 'admin', 'description' => 'Administrator', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'client', 'description' => 'Standard client', 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
