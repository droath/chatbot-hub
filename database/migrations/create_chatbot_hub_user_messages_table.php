<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('chatbot_hub_user_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)
                ->unique()
                ->constrained()
                ->cascadeOnDelete();
            $table->json('messages')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_hub_user_messages');
    }
};
