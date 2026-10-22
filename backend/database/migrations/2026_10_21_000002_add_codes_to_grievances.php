<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grievance_code_sequences', function (Blueprint $table): void {
            $table->date('sequence_date')->primary();
            $table->unsignedBigInteger('next_number');
        });

        Schema::table('grievances', function (Blueprint $table): void {
            $table->string('code', 32)->nullable()->after('id');
        });

        $sequences = [];

        DB::table('grievances')
            ->select(['id', 'created_at', 'complained_at'])
            ->orderBy('id')
            ->chunkById(500, function ($grievances) use (&$sequences): void {
                foreach ($grievances as $grievance) {
                    $date = Carbon::parse($grievance->created_at ?? $grievance->complained_at)->format('Ymd');
                    $number = ($sequences[$date] ?? 0) + 1;
                    $sequences[$date] = $number;

                    DB::table('grievances')
                        ->where('id', $grievance->id)
                        ->update(['code' => sprintf('DO-%s-%03d', $date, $number)]);
                }
            });

        foreach ($sequences as $date => $number) {
            DB::table('grievance_code_sequences')->insert([
                'sequence_date' => Carbon::createFromFormat('Ymd', $date)->toDateString(),
                'next_number' => $number + 1,
            ]);
        }

        Schema::table('grievances', function (Blueprint $table): void {
            $table->unique('code');
        });
    }

    public function down(): void
    {
        Schema::table('grievances', function (Blueprint $table): void {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });

        Schema::dropIfExists('grievance_code_sequences');
    }
};
