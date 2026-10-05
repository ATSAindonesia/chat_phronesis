<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // 1. Tabel chat_conversations
            DB::statement("
                CREATE TABLE chat_conversations (
                    uuid CHAR(36) NOT NULL DEFAULT (UUID()),
                    user_id BIGINT UNSIGNED NULL,
                    title VARCHAR(255) NOT NULL DEFAULT 'Chat baru',
                    model VARCHAR(64) NULL,
                    platform VARCHAR(16) NOT NULL DEFAULT 'web',
                    archived TINYINT(1) NOT NULL DEFAULT 0,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (uuid),
                    CONSTRAINT chk_chat_conversations_platform CHECK (platform IN ('web', 'android', 'ios', 'desktop', 'api')),
                    CONSTRAINT fk_chat_conversations_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
                    INDEX idx_chat_conversations_user_updated (user_id, updated_at DESC)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 2. Tabel chat_messages
            DB::statement("
                CREATE TABLE chat_messages (
                    uuid CHAR(36) NOT NULL DEFAULT (UUID()),
                    conversation_uuid CHAR(36) NOT NULL,
                    role VARCHAR(16) NOT NULL,
                    content TEXT NOT NULL,
                    reasoning TEXT NULL,
                    tokens INT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (uuid),
                    CONSTRAINT chk_chat_messages_role CHECK (role IN ('user', 'assistant', 'system', 'tool')),
                    CONSTRAINT fk_chat_messages_conversation FOREIGN KEY (conversation_uuid) REFERENCES chat_conversations (uuid) ON DELETE CASCADE,
                    INDEX idx_chat_messages_conversation_created (conversation_uuid, created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 3. Trigger trg_messages_touch_conversation
            DB::unprepared("
                CREATE TRIGGER trg_messages_touch_conversation
                AFTER INSERT ON chat_messages
                FOR EACH ROW
                BEGIN
                    UPDATE chat_conversations
                    SET updated_at = CURRENT_TIMESTAMP
                    WHERE uuid = NEW.conversation_uuid;
                END;
            ");
        } else {
            Schema::create('chat_conversations', function (Blueprint $table) {
                $table->uuid('uuid')->primary();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->string('title')->default('Chat baru');
                $table->string('model', 64)->nullable();
                $table->string('platform', 16)->default('web');
                $table->boolean('archived')->default(false);
                $table->timestamps();
                $table->index(['user_id', 'updated_at']);
            });

            Schema::create('chat_messages', function (Blueprint $table) {
                $table->uuid('uuid')->primary();
                $table->foreignUuid('conversation_uuid')->constrained('chat_conversations', 'uuid')->cascadeOnDelete();
                $table->string('role', 16);
                $table->text('content');
                $table->text('reasoning')->nullable();
                $table->integer('tokens')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['conversation_uuid', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Drop trigger dulu
            DB::unprepared("DROP TRIGGER IF EXISTS trg_messages_touch_conversation;");

            // Baru tabel anak
            DB::statement("DROP TABLE IF EXISTS chat_messages;");

            // Baru tabel induk
            DB::statement("DROP TABLE IF EXISTS chat_conversations;");
        } else {
            Schema::dropIfExists('chat_messages');
            Schema::dropIfExists('chat_conversations');
        }
    }
};
