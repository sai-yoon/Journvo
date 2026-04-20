<?php
// database/migrations/2024_01_01_000003_create_messages_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('conversation_id');
            $table->enum('sender_type', ['user', 'ai']);
            $table->text('content');
            $table->timestamps();

            $table->foreign('conversation_id')
                  ->references('id')->on('conversations')
                  ->onDelete('cascade');

            $table->index('conversation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
