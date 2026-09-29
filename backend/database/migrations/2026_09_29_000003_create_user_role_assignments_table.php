<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_role_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->restrictOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'ended_at']);
            $table->index(['role_id', 'ended_at']);
        });

        $clientRoleId = DB::table('roles')->where('name', 'client')->value('id');

        DB::table('users')->orderBy('id')->chunkById(100, function ($users) use ($clientRoleId): void {
            foreach ($users as $user) {
                $timestamp = now();

                DB::table('user_role_assignments')->insert([
                    'user_id' => $user->id,
                    'role_id' => $clientRoleId,
                    'assigned_at' => $timestamp,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_role_assignments');
    }
};
