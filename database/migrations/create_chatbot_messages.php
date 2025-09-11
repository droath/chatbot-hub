<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_messages', static function (Blueprint $table) {
            $table->id();
            $table->string('parent_id');
            $table->string('parent_type');
            $table->jsonb('message')->nullable();
            $table->morphs('owner');
            $table->timestamps();
            $table->index(['parent_id', 'parent_type']);
            $table->index(['owner_id', 'owner_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_messages');
    }
};
