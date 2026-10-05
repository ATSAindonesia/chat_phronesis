<?php

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('AI Chat');
    $response->assertSee('Recent chats');
    $response->assertSee('Start a new conversation');
    $response->assertSee('Message AI...');
    $response->assertSee('GPT-4o');
    $response->assertSee('New chat');
    $response->assertSee('theme-toggle-btn');
    $response->assertSee('toggleTheme');
    $response->assertSee('dark:bg-[#0A0A0A]');
});
