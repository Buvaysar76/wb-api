<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unique(['account_id', 'odid']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->unique(['account_id', 'sale_id']);
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->unique(['account_id', 'income_id']);
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->unique([
                'account_id',
                'date',
                'warehouse_name',
                'barcode',
                'nm_id'
            ], 'stocks_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', fn(Blueprint $t) => $t->dropUnique(['account_id', 'odid']));
        Schema::table('sales', fn(Blueprint $t) => $t->dropUnique(['account_id', 'sale_id']));
        Schema::table('incomes', fn(Blueprint $t) => $t->dropUnique(['account_id', 'income_id']));
        Schema::table('stocks', fn(Blueprint $t) => $t->dropUnique('stocks_unique'));
    }
};
