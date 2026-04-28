<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fullz', function (Blueprint $table) {
            $table->decimal('price_usd', 10, 2)->nullable()->after('status');
        });

        Schema::table('fsaid', function (Blueprint $table) {
            $table->decimal('price_usd', 10, 2)->nullable()->after('status');
        });

        // Backfill from base price (PostgreSQL UPDATE ... FROM syntax)
        DB::statement('UPDATE fullz SET price_usd = fullz_bases.price_usd FROM fullz_bases WHERE fullz.base_id = fullz_bases.id');
        DB::statement('UPDATE fsaid SET price_usd = fsaid_bases.price_usd FROM fsaid_bases WHERE fsaid.base_id = fsaid_bases.id');
    }

    public function down(): void
    {
        Schema::table('fullz', function (Blueprint $table) {
            $table->dropColumn('price_usd');
        });

        Schema::table('fsaid', function (Blueprint $table) {
            $table->dropColumn('price_usd');
        });
    }
};
