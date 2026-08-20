<?php

declare(strict_types=1);

use App\Enums\TenantStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug');
            $table->string('subdomain', 63)->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->string('status')->default(TenantStatus::Trial->value);
            $table->string('owner_email');
            $table->string('phone')->nullable();
            $table->string('timezone', 64)->default('America/Sao_Paulo');
            $table->string('locale', 16)->default('pt_BR');
            $table->timestamp('trial_ends_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
