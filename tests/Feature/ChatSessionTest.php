<?php

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;

test('guest can create a chat session without messages', function () {
    $response = $this->postJson(route('chat.sessions.store'), [
        'title' => 'Diskusi Konsep AI',
        'model' => 'GPT-4o',
        'platform' => 'web',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('title', 'Diskusi Konsep AI');
    $response->assertJsonPath('model', 'GPT-4o');
    $response->assertJsonPath('platform', 'web');

    $this->assertDatabaseHas('chat_conversations', [
        'title' => 'Diskusi Konsep AI',
        'model' => 'GPT-4o',
        'platform' => 'web',
        'user_id' => null,
    ]);
});

test('authenticated user can create a chat session with initial messages', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('chat.sessions.store'), [
        'title' => 'Project Architecture',
        'model' => 'GPT-4o',
        'platform' => 'web',
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Bantu saya rancang arsitektur microservices.',
            ],
            [
                'role' => 'assistant',
                'content' => 'Tentu, mari kita mulai dari domain decomposition.',
                'reasoning' => 'Thinking process about domain boundaries.',
                'tokens' => 45,
            ],
        ],
    ]);

    $response->assertCreated();
    $uuid = $response->json('uuid');

    $this->assertDatabaseHas('chat_conversations', [
        'uuid' => $uuid,
        'user_id' => $user->id,
        'title' => 'Project Architecture',
    ]);

    $this->assertDatabaseHas('chat_messages', [
        'conversation_uuid' => $uuid,
        'role' => 'user',
        'content' => 'Bantu saya rancang arsitektur microservices.',
    ]);

    $this->assertDatabaseHas('chat_messages', [
        'conversation_uuid' => $uuid,
        'role' => 'assistant',
        'content' => 'Tentu, mari kita mulai dari domain decomposition.',
        'tokens' => 45,
    ]);
});

test('can add a message to an existing chat session', function () {
    $conversation = ChatConversation::create([
        'title' => 'Testing Add Message',
    ]);

    $response = $this->postJson(route('chat.sessions.messages.store', $conversation->uuid), [
        'role' => 'user',
        'content' => 'Pesan tambahan ke sesi yang sudah ada',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('chat_messages', [
        'conversation_uuid' => $conversation->uuid,
        'role' => 'user',
        'content' => 'Pesan tambahan ke sesi yang sudah ada',
    ]);
});

test('can delete a chat session and cascade delete its messages', function () {
    $conversation = ChatConversation::create([
        'title' => 'Sesi yang akan dihapus',
    ]);

    $message = $conversation->messages()->create([
        'role' => 'user',
        'content' => 'Pesan di dalam sesi yang akan dihapus',
    ]);

    $response = $this->deleteJson(route('chat.sessions.destroy', $conversation->uuid));

    $response->assertOk();
    $response->assertJson([
        'message' => 'Chat session deleted successfully.',
        'uuid' => $conversation->uuid,
    ]);

    $this->assertDatabaseMissing('chat_conversations', [
        'uuid' => $conversation->uuid,
    ]);

    $this->assertDatabaseMissing('chat_messages', [
        'uuid' => $message->uuid,
    ]);
});

test('cannot delete another users chat session', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = ChatConversation::create([
        'user_id' => $owner->id,
        'title' => 'Private Session',
    ]);

    $response = $this->actingAs($otherUser)->deleteJson(route('chat.sessions.destroy', $conversation->uuid));

    $response->assertForbidden();

    $this->assertDatabaseHas('chat_conversations', [
        'uuid' => $conversation->uuid,
    ]);
});

test('can retrieve chat session history list', function () {
    $user = User::factory()->create();

    ChatConversation::create([
        'user_id' => $user->id,
        'title' => 'Sesi Pertama User',
    ]);

    ChatConversation::create([
        'user_id' => $user->id,
        'title' => 'Sesi Kedua User',
    ]);

    $response = $this->actingAs($user)->getJson(route('chat.sessions.index'));

    $response->assertOk();
    $response->assertJsonCount(2);
    $response->assertJsonFragment(['title' => 'Sesi Pertama User']);
    $response->assertJsonFragment(['title' => 'Sesi Kedua User']);
});
