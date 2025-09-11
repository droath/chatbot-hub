<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_memory', static function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->jsonb('value');
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            // Optimize for key lookups and expiration cleanup
            $table->index(['key', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_memory');
    }
};
