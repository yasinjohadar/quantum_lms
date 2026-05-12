<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('pricing_mode', 20)->default('inherit')->after('is_free')
                  ->comment('وضع التسعير: inherit, free, paid, subscription, bundle_only, hidden');
        });

        DB::table('subjects')->where('is_free_override', true)->update(['pricing_mode' => 'free']);
        DB::table('subjects')
            ->where('is_free_override', false)
            ->where('can_purchase_separately', false)
            ->update(['pricing_mode' => 'bundle_only']);
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('pricing_mode');
        });
    }
};
