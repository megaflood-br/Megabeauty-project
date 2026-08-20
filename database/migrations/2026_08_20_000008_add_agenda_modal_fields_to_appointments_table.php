<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->json('items')->nullable()->after('price');
            $table->string('color', 16)->nullable()->after('items');
            $table->boolean('send_reminder')->default(true)->after('color');
            $table->boolean('squeeze')->default(false)->after('send_reminder');
            $table->string('repeat_rule', 32)->default('none')->after('squeeze');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropColumn(['items', 'color', 'send_reminder', 'squeeze', 'repeat_rule']);
        });
    }
};
