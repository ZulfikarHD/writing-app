<?php

use App\Models\ChatThread;
use App\Models\Novel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Chat Channels - Sprint 21 (F4): Real-time Chat Updates
|--------------------------------------------------------------------------
*/

/**
 * Authorize access to a specific chat thread channel.
 * Users can only subscribe to threads they own.
 */
Broadcast::channel('chat.thread.{threadId}', function ($user, int $threadId) {
    $thread = ChatThread::find($threadId);
    if (! $thread) {
        return false;
    }

    return $thread->user_id === $user->id;
});

/**
 * Authorize access to novel-level chat updates channel.
 * Users can only subscribe to novels they own.
 */
Broadcast::channel('chat.novel.{novelId}', function ($user, int $novelId) {
    $novel = Novel::find($novelId);
    if (! $novel) {
        return false;
    }

    return $novel->user_id === $user->id;
});
