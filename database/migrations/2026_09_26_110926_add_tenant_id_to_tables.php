<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'users',
            'hotels',
            'guests',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->string('tenant_id')->nullable()->after('id');
                $tableBlueprint->index('tenant_id');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'users',
            'hotels',
            'guests',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->dropColumn('tenant_id');
            });
        }
    }
};
