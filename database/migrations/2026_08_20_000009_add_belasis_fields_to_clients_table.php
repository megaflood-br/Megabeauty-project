<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->string('avatar_path')->nullable()->after('name');
            $table->string('nickname')->nullable()->after('avatar_path');
            $table->string('landline', 20)->nullable()->after('phone');
            $table->string('cnpj', 20)->nullable()->after('document');
            $table->string('rg', 20)->nullable()->after('cnpj');
            $table->foreignId('referred_by_client_id')->nullable()->after('rg')->constrained('clients')->nullOnDelete();
            $table->json('hashtags')->nullable()->after('referred_by_client_id');
            $table->json('dependents')->nullable()->after('hashtags');
            $table->string('address_zip', 16)->nullable()->after('dependents');
            $table->string('address_street')->nullable()->after('address_zip');
            $table->string('address_number', 32)->nullable()->after('address_street');
            $table->string('address_complement')->nullable()->after('address_number');
            $table->string('address_neighborhood')->nullable()->after('address_complement');
            $table->string('address_city')->nullable()->after('address_neighborhood');
            $table->string('address_state', 2)->nullable()->after('address_city');
            $table->string('instagram')->nullable()->after('address_state');
            $table->string('facebook')->nullable()->after('instagram');
            $table->string('tiktok')->nullable()->after('facebook');
            $table->decimal('default_discount_percent', 5, 2)->default(0)->after('tiktok');
            $table->string('default_discount_apply_on', 32)->default('comanda')->after('default_discount_percent');
            $table->boolean('is_active')->default(true)->after('default_discount_apply_on');
            $table->boolean('notifications_enabled')->default(true)->after('is_active');
            $table->boolean('access_blocked')->default(false)->after('notifications_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('referred_by_client_id');
            $table->dropColumn([
                'avatar_path',
                'nickname',
                'landline',
                'cnpj',
                'rg',
                'hashtags',
                'dependents',
                'address_zip',
                'address_street',
                'address_number',
                'address_complement',
                'address_neighborhood',
                'address_city',
                'address_state',
                'instagram',
                'facebook',
                'tiktok',
                'default_discount_percent',
                'default_discount_apply_on',
                'is_active',
                'notifications_enabled',
                'access_blocked',
            ]);
        });
    }
};
