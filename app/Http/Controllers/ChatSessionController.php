<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Services\TokenportalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ChatSessionController extends Controller
{
    /**
     * Get available models from Tokenportal provider.
     */
    public function models(Request $request, TokenportalService $tokenportal): JsonResponse
    {
        $refresh = $request->boolean('refresh');
        $models = $tokenportal->getModels($refresh);

        return response()->json([
            'status' => 'success',
            'provider' => 'tokenportal',
            'default' => config('services.tokenportal.default_model', 'kimi-k27-code'),
            'data' => $models,
        ]);
    }

    /**
     * Display a listing of chat sessions.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;

        $conversations = ChatConversation::query()
            ->when($userId, function ($query, $userId) {
                return $query->where('user_id', $userId);
            }, function ($query) {
                return $query->whereNull('user_id');
            })
            ->where('archived', false)
            ->with(['messages' => function ($query) {
                $query->oldest('created_at');
            }])
            ->latest('updated_at')
            ->get();

        return response()->json($conversations);
    }

    /**
     * Store a newly created chat session in storage (Create function).
     */
    public function store(Request $request, TokenportalService $tokenportal): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:64'],
            'platform' => ['nullable', 'string', 'in:web,android,ios,desktop,api'],
            'generate_reply' => ['nullable', 'boolean'],
            'search_mode' => ['nullable', 'boolean'],
            'agent_mode' => ['nullable', 'boolean'],
            'messages' => ['nullable', 'array'],
            'messages.*.role' => ['required_with:messages', 'string', 'in:user,assistant,system,tool'],
            'messages.*.content' => ['required_with:messages', 'string'],
            'messages.*.reasoning' => ['nullable', 'string'],
            'messages.*.tokens' => ['nullable', 'integer'],
        ]);

        $defaultModel = config('services.tokenportal.default_model', 'kimi-k27-code');
        $chosenModel = ! empty($validated['model']) ? $validated['model'] : $defaultModel;

        // Auto-generate title if none provided and first message is present
        $title = $validated['title'] ?? null;
        if (! $title && ! empty($validated['messages'][0]['content'])) {
            $firstContent = $validated['messages'][0]['content'];
            $title = mb_substr(trim($firstContent), 0, 30);
            if (mb_strlen($firstContent) > 30) {
                $title .= '...';
            }
        }
        if (! $title) {
            $title = 'Chat baru';
        }

        $conversation = DB::transaction(function () use ($validated, $request, $title, $chosenModel) {
            $conversation = ChatConversation::create([
                'user_id' => $request->user()?->id,
                'title' => $title,
                'model' => $chosenModel,
                'platform' => $validated['platform'] ?? 'web',
                'archived' => false,
            ]);

            if (! empty($validated['messages'])) {
                foreach ($validated['messages'] as $msg) {
                    $conversation->messages()->create([
                        'role' => $msg['role'],
                        'content' => $msg['content'],
                        'reasoning' => $msg['reasoning'] ?? null,
                        'tokens' => $msg['tokens'] ?? null,
                    ]);
                }
            }

            return $conversation;
        });

        // Optionally generate AI reply from Tokenportal if requested
        if ($request->boolean('generate_reply') && ! empty($validated['messages'])) {
            $history = array_map(function ($msg) {
                return [
                    'role' => (string) $msg['role'],
                    'content' => (string) $msg['content'],
                ];
            }, $validated['messages']);

            try {
                $reply = $tokenportal->chatCompletion($history, $chosenModel);
                $conversation->messages()->create([
                    'role' => 'assistant',
                    'content' => $reply['content'],
                    'reasoning' => $reply['reasoning'],
                    'tokens' => $reply['tokens'],
                ]);
            } catch (RuntimeException $e) {
                // If AI call fails, return error or the conversation with error note
                return response()->json([
                    'message' => 'Chat session created, but AI completion failed: '.$e->getMessage(),
                    'conversation' => $conversation->load('messages'),
                ], 502);
            }
        }

        return response()->json($conversation->load('messages'), 201);
    }

    /**
     * Display the specified chat session.
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $conversation = ChatConversation::with(['messages' => function ($query) {
            $query->oldest('created_at');
        }])->where('uuid', $uuid)->firstOrFail();

        $this->authorizeAccess($request, $conversation);

        return response()->json($conversation);
    }

    /**
     * Send a user message and generate AI reply from Tokenportal.
     */
    public function sendMessageAndReply(Request $request, string $uuid, TokenportalService $tokenportal): JsonResponse
    {
        $conversation = ChatConversation::where('uuid', $uuid)->firstOrFail();
        $this->authorizeAccess($request, $conversation);

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'model' => ['nullable', 'string', 'max:64'],
            'search_mode' => ['nullable', 'boolean'],
            'agent_mode' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['model']) && $validated['model'] !== $conversation->model) {
            $conversation->update(['model' => $validated['model']]);
        }

        // 1. Create User Message
        $userMessage = $conversation->messages()->create([
            'role' => 'user',
            'content' => $validated['content'],
        ]);

        // Auto update title if this is the first interaction or still default
        if ($conversation->title === 'Chat baru' || empty($conversation->title)) {
            $cleanTitle = mb_substr(trim($validated['content']), 0, 30);
            if (mb_strlen($validated['content']) > 30) {
                $cleanTitle .= '...';
            }
            $conversation->update(['title' => $cleanTitle]);
        }

        // 2. Fetch conversation messages for context (latest 30)
        $messages = $conversation->messages()
            ->oldest('created_at')
            ->get()
            ->map(function ($msg) {
                return [
                    'role' => $msg->role,
                    'content' => $msg->content,
                ];
            })
            ->toArray();

        // 3. Call Tokenportal Provider
        $modelToUse = $conversation->model ?: config('services.tokenportal.default_model', 'kimi-k27-code');

        try {
            $reply = $tokenportal->chatCompletion($messages, $modelToUse);

            // 4. Save Assistant Message
            $assistantMessage = $conversation->messages()->create([
                'role' => 'assistant',
                'content' => $reply['content'],
                'reasoning' => $reply['reasoning'],
                'tokens' => $reply['tokens'],
            ]);

            $conversation->touch();

            return response()->json([
                'user_message' => $userMessage,
                'assistant_message' => $assistantMessage,
                'conversation' => $conversation->fresh(['messages']),
            ], 200);
        } catch (RuntimeException $e) {
            return response()->json([
                'error' => 'AI Generation Failed',
                'message' => $e->getMessage(),
                'user_message' => $userMessage,
            ], 502);
        }
    }

    /**
     * Store a raw message in an existing chat session.
     */
    public function storeMessage(Request $request, string $uuid): JsonResponse
    {
        $conversation = ChatConversation::where('uuid', $uuid)->firstOrFail();
        $this->authorizeAccess($request, $conversation);

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:user,assistant,system,tool'],
            'content' => ['required', 'string'],
            'reasoning' => ['nullable', 'string'],
            'tokens' => ['nullable', 'integer'],
        ]);

        $message = $conversation->messages()->create($validated);
        $conversation->touch();

        return response()->json($message, 201);
    }

    /**
     * Standalone OpenAI-compatible completions endpoint.
     */
    public function completions(Request $request, TokenportalService $tokenportal): JsonResponse
    {
        $validated = $request->validate([
            'model' => ['nullable', 'string', 'max:64'],
            'messages' => ['required', 'array', 'min:1'],
            'messages.*.role' => ['required', 'string'],
            'messages.*.content' => ['required', 'string'],
            'temperature' => ['nullable', 'numeric'],
            'max_tokens' => ['nullable', 'integer'],
        ]);

        try {
            $result = $tokenportal->chatCompletion(
                $validated['messages'],
                $validated['model'] ?? null,
                $request->only(['temperature', 'max_tokens'])
            );

            return response()->json($result['raw']);
        } catch (RuntimeException $e) {
            return response()->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'type' => 'tokenportal_error',
                ],
            ], 502);
        }
    }

    /**
     * Remove the specified chat session from storage (Delete function).
     */
    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $conversation = ChatConversation::where('uuid', $uuid)->firstOrFail();
        $this->authorizeAccess($request, $conversation);

        $conversation->delete();

        return response()->json([
            'message' => 'Chat session deleted successfully.',
            'uuid' => $uuid,
        ]);
    }

    /**
     * Check if current user has permission to access this conversation.
     */
    protected function authorizeAccess(Request $request, ChatConversation $conversation): void
    {
        if ($conversation->user_id && $request->user() && $conversation->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized access to this chat session.');
        }
    }
}
