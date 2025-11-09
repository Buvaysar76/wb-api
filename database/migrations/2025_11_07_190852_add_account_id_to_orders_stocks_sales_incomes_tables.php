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
        $tables = ['orders', 'stocks', 'sales', 'incomes'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('account_id')
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['orders', 'stocks', 'sales', 'incomes'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['account_id']);
                $table->dropColumn('account_id');
            });
        }
    }
};
