<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_bases', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->decimal('discount_pct', 5, 2)->default(0)->after('price_usd');
        });

        // Backfill UUIDs for existing rows
        DB::table('card_bases')->whereNull('uuid')->orderBy('id')->each(function ($row) {
            DB::table('card_bases')->where('id', $row->id)->update(['uuid' => (string) Str::uuid()]);
        });

        Schema::table('card_bases', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('card_bases', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'discount_pct']);
        });
    }
};
