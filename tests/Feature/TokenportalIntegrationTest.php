<?php

use App\Models\ChatConversation;
use Illuminate\Support\Facades\Http;

test('can retrieve models from tokenportal via web and api endpoints', function () {
    Http::fake([
        'https://tokenportal.id/v1/models' => Http::response([
            'object' => 'list',
            'data' => [
                [
                    'id' => 'kimi-k27-code',
                    'object' => 'model',
                    'owned_by' => 'Moonshot',
                    'context_window' => 200000,
                    'max_tokens' => 64000,
                ],
                [
                    'id' => 'qwen-35b-fast',
                    'object' => 'model',
                    'owned_by' => 'Alibaba',
                    'context_window' => 262128,
                    'max_tokens' => 65536,
                ],
            ],
        ], 200),
    ]);

    // 1. Web route
    $response = $this->getJson(route('chat.models', ['refresh' => true]));
    $response->assertOk();
    $response->assertJsonPath('status', 'success');
    $response->assertJsonPath('provider', 'tokenportal');
    $response->assertJsonPath('default', 'kimi-k27-code');
    $response->assertJsonFragment(['id' => 'kimi-k27-code', 'owned_by' => 'Moonshot']);

    // 2. API route
    $apiResponse = $this->getJson('/api/v1/models');
    $apiResponse->assertOk();
    $apiResponse->assertJsonFragment(['id' => 'qwen-35b-fast']);
});

test('can send message and receive ai completion from tokenportal provider', function () {
    Http::fake([
        'https://tokenportal.id/v1/chat/completions' => Http::response([
            'id' => 'chatcmpl-test-12345',
            'object' => 'chat.completion',
            'created' => time(),
            'model' => 'kimi-k27-code',
            'choices' => [
                [
                    'index' => 0,
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Halo! Ini respon dari Kimi K2.7 Code.',
                        'reasoning' => 'User menyapa halo, balas dengan salam ramah.',
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => [
                'prompt_tokens' => 10,
                'completion_tokens' => 20,
                'total_tokens' => 30,
            ],
        ], 200),
    ]);

    $conversation = ChatConversation::create([
        'title' => 'Chat baru',
        'model' => 'kimi-k27-code',
        'platform' => 'web',
    ]);

    $response = $this->postJson(route('chat.sessions.send', $conversation->uuid), [
        'content' => 'Halo! Siapa namamu?',
        'model' => 'kimi-k27-code',
    ]);

    $response->assertOk();
    $response->assertJsonPath('user_message.content', 'Halo! Siapa namamu?');
    $response->assertJsonPath('assistant_message.content', 'Halo! Ini respon dari Kimi K2.7 Code.');
    $response->assertJsonPath('assistant_message.reasoning', 'User menyapa halo, balas dengan salam ramah.');
    $response->assertJsonPath('assistant_message.tokens', 30);

    // Verify DB records
    $this->assertDatabaseHas('chat_messages', [
        'conversation_uuid' => $conversation->uuid,
        'role' => 'user',
        'content' => 'Halo! Siapa namamu?',
    ]);

    $this->assertDatabaseHas('chat_messages', [
        'conversation_uuid' => $conversation->uuid,
        'role' => 'assistant',
        'content' => 'Halo! Ini respon dari Kimi K2.7 Code.',
        'reasoning' => 'User menyapa halo, balas dengan salam ramah.',
        'tokens' => 30,
    ]);
});

test('can create chat session with instant tokenportal ai completion', function () {
    Http::fake([
        'https://tokenportal.id/v1/chat/completions' => Http::response([
            'id' => 'chatcmpl-init-5678',
            'object' => 'chat.completion',
            'created' => time(),
            'model' => 'kimi-k27-code',
            'choices' => [
                [
                    'index' => 0,
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Tentu, saya siap membantu kode PHP Anda.',
                        'reasoning' => 'Bantu user dengan kode PHP.',
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => [
                'total_tokens' => 45,
            ],
        ], 200),
    ]);

    $response = $this->postJson(route('chat.sessions.store'), [
        'title' => 'Diskusi Laravel & AI',
        'model' => 'kimi-k27-code',
        'platform' => 'web',
        'generate_reply' => true,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Bantu buatkan script di Laravel',
            ],
        ],
    ]);

    $response->assertCreated();
    $uuid = $response->json('uuid');

    $this->assertDatabaseHas('chat_messages', [
        'conversation_uuid' => $uuid,
        'role' => 'user',
        'content' => 'Bantu buatkan script di Laravel',
    ]);

    $this->assertDatabaseHas('chat_messages', [
        'conversation_uuid' => $uuid,
        'role' => 'assistant',
        'content' => 'Tentu, saya siap membantu kode PHP Anda.',
    ]);
});

test('openai compatible api chat completion endpoint works as curl example', function () {
    Http::fake([
        'https://tokenportal.id/v1/chat/completions' => Http::response([
            'id' => 'chatcmpl-curl-example',
            'object' => 'chat.completion',
            'created' => time(),
            'model' => 'kimi-k27-code',
            'choices' => [
                [
                    'index' => 0,
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Halo! Ada yang bisa saya bantu hari ini?',
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => [
                'total_tokens' => 25,
            ],
        ], 200),
    ]);

    // Mirroring user curl request to /api/v1/chat/completions
    $response = $this->postJson('/api/v1/chat/completions', [
        'model' => 'kimi-k27-code',
        'messages' => [
            ['role' => 'user', 'content' => 'Halo!'],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonPath('id', 'chatcmpl-curl-example');
    $response->assertJsonPath('model', 'kimi-k27-code');
    $response->assertJsonPath('choices.0.message.content', 'Halo! Ada yang bisa saya bantu hari ini?');
});

test('can save and retrieve assistant message containing bold style and markdown table', function () {
    $tableMarkdown = "**Arsitektur:**\n\n| No | Nama Makanan | Asal Daerah | Jenis |\n| :--- | :--- | :--- | :--- |\n| 1 | Rendang | Sumatera Barat | Makanan Berat |\n| 2 | Gudeg | Yogyakarta | Makanan Berat |";

    Http::fake([
        'https://tokenportal.id/v1/chat/completions' => Http::response([
            'id' => 'chatcmpl-table-test',
            'object' => 'chat.completion',
            'created' => time(),
            'model' => 'kimi-k27-code',
            'choices' => [
                [
                    'index' => 0,
                    'message' => [
                        'role' => 'assistant',
                        'content' => $tableMarkdown,
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => [
                'total_tokens' => 88,
            ],
        ], 200),
    ]);

    $conversation = ChatConversation::create([
        'title' => 'Tabel Makanan',
        'model' => 'kimi-k27-code',
    ]);

    $response = $this->postJson(route('chat.sessions.send', $conversation->uuid), [
        'content' => 'Berikan daftar makanan khas',
        'model' => 'kimi-k27-code',
    ]);

    $response->assertOk();
    $response->assertJsonPath('assistant_message.content', $tableMarkdown);

    // Verify retrieval
    $showResponse = $this->getJson(route('chat.sessions.show', $conversation->uuid));
    $showResponse->assertOk();
    $showResponse->assertJsonPath('messages.1.content', $tableMarkdown);
});
