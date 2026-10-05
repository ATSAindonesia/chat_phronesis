<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $uuid
 * @property string $conversation_uuid
 * @property string $role
 * @property string $content
 * @property string|null $reasoning
 * @property int|null $tokens
 * @property \Illuminate\Support\Carbon $created_at
 */
class ChatMessage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'chat_messages';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'conversation_uuid',
        'role',
        'content',
        'reasoning',
        'tokens',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tokens' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_uuid', 'uuid');
    }
}
