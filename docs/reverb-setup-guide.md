# 🔌 Laravel Reverb Setup Guide

**Version:** 1.0.0  
**Last Updated:** 2026-01-03  
**Complexity:** Intermediate  
**Estimated Setup Time:** 15-30 minutes

## 📋 Table of Contents

1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Installation Steps](#installation-steps)
4. [Environment Configuration](#environment-configuration)
5. [Backend Configuration](#backend-configuration)
6. [Frontend Configuration](#frontend-configuration)
7. [Starting the Reverb Server](#starting-the-reverb-server)
8. [Testing the Connection](#testing-the-connection)
9. [Troubleshooting](#troubleshooting)
10. [Production Deployment](#production-deployment)
11. [Advanced Configuration](#advanced-configuration)

---

## 🎯 Overview

Laravel Reverb adalah first-party WebSocket server untuk Laravel yang memungkinkan real-time communication antara server dan client. Dalam aplikasi ini, Reverb digunakan untuk:

- **Real-time Chat Updates** - Pesan baru muncul langsung tanpa refresh
- **Thread Synchronization** - Update thread title/status terlihat di semua tab
- **Multi-tab Support** - Perubahan di satu tab langsung terlihat di tab lain
- **Presence Channels** (future) - Melihat user lain yang sedang online

### Why Reverb?

- ✅ **Native Laravel Integration** - Tidak perlu third-party services
- ✅ **Zero Configuration** - Setup minimal untuk development
- ✅ **Scalable** - Support Redis untuk horizontal scaling
- ✅ **Cost-effective** - Gratis untuk self-hosting
- ✅ **Fast** - Built dengan high-performance event loop

---

## 📦 Prerequisites

Pastikan hal-hal berikut sudah terinstall:

### Required
- ✅ **PHP 8.4.1** or higher
- ✅ **Laravel 12** (already installed)
- ✅ **Composer** (package manager untuk PHP)
- ✅ **Node.js & Yarn** (untuk frontend build)
- ✅ **Running Laravel application** (app sudah bisa dijalankan)

### Optional (for Production)
- Redis server (untuk scaling)
- Process manager (Supervisor, systemd)
- SSL certificate (untuk WSS connections)

### Already Installed in This Project
- ✅ `laravel-echo` - Client-side library (v2.2.7)
- ✅ `pusher-js` - Protocol implementation (v8.4.0)
- ✅ Laravel Reverb package (via Composer)

---

## 🚀 Installation Steps

### Step 1: Verify Reverb Package Installation

Check if Reverb is installed:

```powershell
php artisan reverb:install --help
```

If you see help output, Reverb is installed. If not, install it:

```powershell
composer require laravel/reverb
php artisan reverb:install
```

The install command will:
- Publish `config/reverb.php` configuration file
- Publish `config/broadcasting.php` if not exists
- Set up default environment variables

### Step 2: Verify NPM Packages

Check `package.json` for required packages:

```json
"dependencies": {
  "laravel-echo": "^2.2.7",
  "pusher-js": "^8.4.0"
}
```

If missing, install them:

```powershell
yarn add laravel-echo pusher-js
```

---

## ⚙️ Environment Configuration

### Step 3: Configure `.env` File

Add or update these variables in your `.env` file:

```env
# Broadcasting Configuration
BROADCAST_CONNECTION=reverb

# Laravel Reverb Server Configuration
REVERB_APP_ID=writing-app
REVERB_APP_KEY=local-app-key
REVERB_APP_SECRET=local-app-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

# Reverb Server Settings (Optional)
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

# Frontend Reverb Configuration (Must match backend!)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### Environment Variables Explained

| Variable | Purpose | Default | Notes |
|----------|---------|---------|-------|
| `BROADCAST_CONNECTION` | Active broadcast driver | `null` | Set to `reverb` untuk enable WebSocket |
| `REVERB_APP_ID` | Application identifier | - | Dapat diubah, unique per app |
| `REVERB_APP_KEY` | Client authentication key | - | **MUST match** `VITE_REVERB_APP_KEY` |
| `REVERB_APP_SECRET` | Server-side secret | - | **Keep secret**, untuk auth verification |
| `REVERB_HOST` | WebSocket host | `127.0.0.1` | Gunakan domain untuk production |
| `REVERB_PORT` | WebSocket port | `8080` | Ensure port is available |
| `REVERB_SCHEME` | Protocol | `http` | Use `https` untuk production |
| `VITE_REVERB_*` | Frontend config | - | **MUST match** backend config |

### Step 4: Clear and Cache Configuration

After updating `.env`, clear and recache config:

```powershell
php artisan config:clear
php artisan config:cache
```

---

## 🔧 Backend Configuration

### Step 5: Verify `config/broadcasting.php`

Ensure Reverb connection is configured:

```php
'connections' => [
    'reverb' => [
        'driver' => 'reverb',
        'key' => env('REVERB_APP_KEY'),
        'secret' => env('REVERB_APP_SECRET'),
        'app_id' => env('REVERB_APP_ID'),
        'options' => [
            'host' => env('REVERB_HOST'),
            'port' => env('REVERB_PORT', 443),
            'scheme' => env('REVERB_SCHEME', 'https'),
            'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
        ],
    ],
],
```

### Step 6: Verify `config/reverb.php`

Check server settings:

```php
'servers' => [
    'reverb' => [
        'host' => env('REVERB_SERVER_HOST', '0.0.0.0'),
        'port' => env('REVERB_SERVER_PORT', 8080),
        'hostname' => env('REVERB_HOST'),
        // ... other settings
    ],
],

'apps' => [
    'apps' => [
        [
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'app_id' => env('REVERB_APP_ID'),
            'allowed_origins' => ['*'],  // Restrict in production!
            'ping_interval' => env('REVERB_APP_PING_INTERVAL', 60),
            'activity_timeout' => env('REVERB_APP_ACTIVITY_TIMEOUT', 30),
        ],
    ],
],
```

### Step 7: Verify Broadcast Routes

Ensure broadcast auth route is registered in `bootstrap/app.php`:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    channels: __DIR__.'/../routes/channels.php',  // ✅ Required!
    // ...
)
```

### Step 8: Verify Channel Authorization

Check `routes/channels.php` has your chat channels:

```php
use App\Models\ChatThread;
use App\Models\Novel;
use Illuminate\Support\Facades\Broadcast;

// Chat Thread Channel
Broadcast::channel('chat.thread.{threadId}', function ($user, int $threadId) {
    $thread = ChatThread::find($threadId);
    if (!$thread) {
        return false;
    }
    return $thread->user_id === $user->id;
});

// Novel Channel (for thread list updates)
Broadcast::channel('chat.novel.{novelId}', function ($user, int $novelId) {
    $novel = Novel::find($novelId);
    if (!$novel) {
        return false;
    }
    return $novel->user_id === $user->id;
});
```

### Step 9: Verify Broadcast Events

Your broadcast events should implement `ShouldBroadcast`:

**`app/Events/ChatMessageCreated.php`:**
```php
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatMessageCreated implements ShouldBroadcast
{
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.thread.'.$this->message->thread_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.created';
    }

    public function broadcastWith(): array
    {
        return ['message' => $this->message->toArray()];
    }
}
```

---

## 🎨 Frontend Configuration

### Step 10: Verify Echo Bootstrap File

Check `resources/js/bootstrap/echo.ts` exists and is configured:

```typescript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Extend Window interface
declare global {
    interface Window {
        Pusher: typeof Pusher;
        Echo: Echo<'reverb'>;
    }
}

// Make Pusher globally available
window.Pusher = Pusher;

// Initialize Laravel Echo
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

export default window.Echo;
```

### Step 11: Import Echo in Application

Ensure Echo is imported in `resources/js/app.ts`:

```typescript
import '../css/app.css';
import './bootstrap/echo';  // ✅ IMPORTANT: Must be imported!

import { createInertiaApp } from '@inertiajs/vue3';
// ... rest of your imports
```

**⚠️ Common Mistake:** Lupa mengimport `echo.ts` akan menyebabkan WebSocket tidak terkoneksi!

### Step 12: Rebuild Frontend Assets

After configuration changes, rebuild:

```powershell
yarn run build
```

For development with hot-reload:

```powershell
yarn run dev
```

---

## 🚀 Starting the Reverb Server

### Development Mode

#### Option 1: Start Reverb Separately (Recommended for Debugging)

```powershell
# Terminal 1: Start Reverb with debug output
php artisan reverb:start --debug

# Terminal 2: Start Vite dev server
yarn run dev

# Terminal 3: Start Laravel server (if needed)
php artisan serve
```

#### Option 2: Use Composer Script (If Available)

```powershell
composer run dev
```

This may run both Vite and Reverb concurrently using `concurrently` package.

### Check If Reverb is Running

You should see output like:

```
[2026-01-03 10:30:00] Reverb server started on 127.0.0.1:8080
[2026-01-03 10:30:00] Application: writing-app
[2026-01-03 10:30:00] Allowed origins: *
[2026-01-03 10:30:00] Max connections: unlimited
```

### Common Reverb Commands

```powershell
# Start Reverb
php artisan reverb:start

# Start with debug output
php artisan reverb:start --debug

# Start on specific host/port
php artisan reverb:start --host=0.0.0.0 --port=8080

# Restart Reverb (if already running)
php artisan reverb:restart
```

---

## 🧪 Testing the Connection

### Step 13: Browser Console Verification

1. **Open your application in browser**
2. **Open Browser DevTools** (F12)
3. **Check Console tab** for Echo connection messages:

✅ **Success indicators:**
```
Echo: Connecting to reverb...
Echo: Connected to reverb
Pusher: Connection established
```

❌ **Error indicators:**
```
WebSocket connection to 'ws://127.0.0.1:8080' failed
Echo: Connection error
Pusher: Connection timeout
```

### Step 14: Test Real-time Chat

1. **Open application workspace**
2. **Open Chat panel**
3. **Send a message**
4. **Open same chat in another browser tab/window**
5. **Verify message appears in both tabs simultaneously**

### Step 15: Network Tab Inspection

In DevTools Network tab:
1. Filter by `WS` (WebSocket)
2. Look for connection to `ws://127.0.0.1:8080/app/local-app-key`
3. Status should be `101 Switching Protocols`
4. Messages tab should show ping/pong frames

### Step 16: Reverb Debug Log

Check Reverb server terminal output:

```
[2026-01-03 10:35:21] New connection: 127.0.0.1:54321
[2026-01-03 10:35:21] Authenticating chat.thread.123
[2026-01-03 10:35:21] Subscribed to private-chat.thread.123
[2026-01-03 10:35:25] Broadcasting message.created to private-chat.thread.123
```

---

## 🔧 Troubleshooting

### Issue 1: "Echo is not defined"

**Symptoms:**
- Console error: `Uncaught ReferenceError: Echo is not defined`
- WebSocket connection never attempts

**Causes:**
- Echo not imported in `app.ts`
- Frontend build outdated

**Solutions:**
```powershell
# 1. Verify echo.ts is imported
# Check resources/js/app.ts has: import './bootstrap/echo';

# 2. Rebuild frontend
yarn run build

# 3. Clear browser cache
Ctrl + Shift + Delete
```

### Issue 2: "Connection Refused" / "ERR_CONNECTION_REFUSED"

**Symptoms:**
- `WebSocket connection to 'ws://127.0.0.1:8080' failed: Connection refused`

**Causes:**
- Reverb server not running
- Wrong host/port configuration
- Firewall blocking port

**Solutions:**
```powershell
# 1. Start Reverb server
php artisan reverb:start --debug

# 2. Check if port is available
netstat -ano | findstr :8080

# 3. Try different port
# Update .env:
REVERB_PORT=8081
VITE_REVERB_PORT=8081

# 4. Check firewall settings
# Allow port 8080 in Windows Firewall
```

### Issue 3: "Authentication Failed" / "403 Forbidden"

**Symptoms:**
- WebSocket connects but channels fail to subscribe
- Console: `Pusher: Auth failed`

**Causes:**
- User not authenticated
- Channel authorization logic incorrect
- CSRF token missing

**Solutions:**
```powershell
# 1. Verify user is logged in
# Check Laravel session

# 2. Check channel authorization
# routes/channels.php should return true for authorized users

# 3. Verify broadcast auth route exists
php artisan route:list | findstr "broadcasting/auth"

# 4. Check CSRF token
# Ensure meta tag exists in HTML:
# <meta name="csrf-token" content="{{ csrf_token() }}">
```

### Issue 4: "Reverb Keeps Stopping"

**Symptoms:**
- Reverb server crashes after a few seconds
- Memory errors in console

**Causes:**
- PHP memory limit too low
- Corrupted cache
- Configuration mismatch

**Solutions:**
```powershell
# 1. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 2. Increase PHP memory limit
# php.ini: memory_limit = 256M

# 3. Check for errors
php artisan reverb:start --debug

# 4. Verify environment variables loaded
php artisan config:show broadcasting
```

### Issue 5: "BROADCAST_CONNECTION set to log"

**Symptoms:**
- Events logged but not broadcast
- No WebSocket activity

**Causes:**
- `.env` has `BROADCAST_CONNECTION=log` instead of `reverb`

**Solutions:**
```powershell
# 1. Update .env
# Change: BROADCAST_CONNECTION=reverb

# 2. Clear config cache
php artisan config:clear
php artisan config:cache

# 3. Restart Reverb
php artisan reverb:restart
```

### Issue 6: "Environment Variables Not Loading"

**Symptoms:**
- Vite variables undefined
- `import.meta.env.VITE_REVERB_APP_KEY` returns undefined

**Causes:**
- Vite not restarted after `.env` changes
- Variables missing `VITE_` prefix

**Solutions:**
```powershell
# 1. Stop Vite dev server (Ctrl+C)

# 2. Verify variables have VITE_ prefix
# .env:
# VITE_REVERB_APP_KEY=local-app-key
# VITE_REVERB_HOST=127.0.0.1

# 3. Restart Vite
yarn run dev

# 4. Verify in browser console
console.log(import.meta.env.VITE_REVERB_APP_KEY)
```

### Issue 7: "Messages Not Broadcasting"

**Symptoms:**
- WebSocket connected
- Channel subscribed
- But messages don't appear in real-time

**Causes:**
- Events not dispatched
- Event not implementing `ShouldBroadcast`
- Queue not processing (if events queued)

**Solutions:**
```php
// 1. Verify event is dispatched
// In ChatService.php or wherever message is created:
ChatMessageCreated::dispatch($message);

// 2. Verify event implements ShouldBroadcast
class ChatMessageCreated implements ShouldBroadcast {
    // ... implementation
}

// 3. Check if events are queued
// If using ShouldQueue, start queue worker:
php artisan queue:work

// 4. Test with sync queue
// .env: QUEUE_CONNECTION=sync
```

---

## 🚀 Production Deployment

### Production Environment Configuration

Update `.env` for production:

```env
# Use secure connections
REVERB_SCHEME=https
REVERB_HOST=your-domain.com
REVERB_PORT=443

# Frontend config
VITE_REVERB_SCHEME=https
VITE_REVERB_HOST=your-domain.com
VITE_REVERB_PORT=443

# Restrict origins (IMPORTANT!)
REVERB_ALLOWED_ORIGINS=https://your-domain.com

# Enable Redis scaling for multiple servers
REVERB_SCALING_ENABLED=true
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### SSL/TLS Configuration

Update `config/reverb.php`:

```php
'servers' => [
    'reverb' => [
        'options' => [
            'tls' => [
                'local_cert' => '/path/to/cert.pem',
                'local_pk' => '/path/to/key.pem',
                'verify_peer' => false,
            ],
        ],
    ],
],
```

### Process Management with Supervisor

Create `/etc/supervisor/conf.d/reverb.conf`:

```ini
[program:reverb]
process_name=%(program_name)s
command=php /var/www/html/artisan reverb:start
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/reverb.log
stopwaitsecs=3600
```

Start Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb
```

### Nginx Configuration

Add WebSocket proxy to Nginx:

```nginx
server {
    # ... your existing config

    location /reverb {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 86400;
    }
}
```

### Scaling with Redis

Enable scaling in `config/reverb.php`:

```php
'scaling' => [
    'enabled' => env('REVERB_SCALING_ENABLED', true),
    'channel' => env('REVERB_SCALING_CHANNEL', 'reverb'),
    'server' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', '6379'),
        'password' => env('REDIS_PASSWORD'),
    ],
],
```

---

## 🎛️ Advanced Configuration

### Custom Ping Interval

Adjust connection health checks:

```env
# .env
REVERB_APP_PING_INTERVAL=60  # seconds between pings
REVERB_APP_ACTIVITY_TIMEOUT=30  # seconds before timeout
```

### Connection Limits

Limit concurrent connections:

```env
REVERB_APP_MAX_CONNECTIONS=1000
```

### Message Size Limits

Prevent large messages:

```env
REVERB_MAX_REQUEST_SIZE=10000  # bytes
REVERB_APP_MAX_MESSAGE_SIZE=10000  # bytes
```

### Presence Channels (Future)

For presence channels (user online status):

```php
// routes/channels.php
Broadcast::channel('workspace.{novelId}', function ($user, int $novelId) {
    if ($user->canAccessNovel($novelId)) {
        return [
            'id' => $user->id,
            'name' => $user->name,
        ];
    }
});
```

```typescript
// Frontend
Echo.join(`workspace.${novelId}`)
    .here((users) => {
        console.log('Users here:', users);
    })
    .joining((user) => {
        console.log('User joined:', user.name);
    })
    .leaving((user) => {
        console.log('User left:', user.name);
    });
```

### Custom Event Listeners

Add custom event handling:

```typescript
// resources/js/composables/useChatRealtime.ts
Echo.private(`chat.thread.${threadId}`)
    .listen('.message.created', (e) => {
        // Handle new message
    })
    .listen('.message.deleted', (e) => {
        // Handle deleted message
    })
    .listen('.thread.archived', (e) => {
        // Handle archived thread
    });
```

---

## 📊 Monitoring & Debugging

### Enable Debug Mode

```powershell
php artisan reverb:start --debug
```

### Check Active Connections

Monitor Reverb stats (if using Laravel Pulse):

```powershell
php artisan pulse:check
```

### Log WebSocket Events

Add logging to events:

```php
use Illuminate\Support\Facades\Log;

class ChatMessageCreated implements ShouldBroadcast
{
    public function __construct(public ChatMessage $message)
    {
        Log::info('Broadcasting message', [
            'message_id' => $this->message->id,
            'thread_id' => $this->message->thread_id,
        ]);
    }
}
```

### Browser Console Debugging

```javascript
// Enable Pusher logging
window.Pusher.logToConsole = true;
```

---

## 📚 Additional Resources

- **Laravel Reverb Docs**: https://laravel.com/docs/12.x/reverb
- **Laravel Broadcasting Docs**: https://laravel.com/docs/12.x/broadcasting
- **Laravel Echo Docs**: https://github.com/laravel/echo
- **Pusher Protocol Docs**: https://pusher.com/docs/channels/library_auth_reference/pusher-websockets-protocol

---

## ✅ Quick Start Checklist

### Initial Setup
- [ ] Verify Reverb package installed
- [ ] Add `.env` variables (backend + frontend)
- [ ] Clear and cache config
- [ ] Import Echo in `app.ts`
- [ ] Rebuild frontend assets

### Start Services
- [ ] Start Reverb server: `php artisan reverb:start --debug`
- [ ] Start Vite dev: `yarn run dev`
- [ ] Start Laravel: `php artisan serve` (if needed)

### Verify Connection
- [ ] Check browser console for Echo connection
- [ ] Check Reverb terminal for connection logs
- [ ] Test real-time message in chat
- [ ] Test multi-tab synchronization

### Troubleshooting
- [ ] If Echo undefined → Import echo.ts and rebuild
- [ ] If connection refused → Start Reverb server
- [ ] If auth failed → Check channel authorization
- [ ] If messages not broadcasting → Check event dispatch

---

## 🎉 Success!

If you've completed all steps and see WebSocket connections in browser DevTools, your Reverb setup is complete! Real-time chat should now work seamlessly.

For production deployment, refer to the [Production Deployment](#production-deployment) section.

**Need Help?**  
- Check [Troubleshooting](#troubleshooting) section
- Review [Sprint 23 documentation](./10-sprints/sprint-23-chat-enhancement-features.md)
- Check Laravel logs: `storage/logs/laravel.log`
- Check Reverb output with `--debug` flag
