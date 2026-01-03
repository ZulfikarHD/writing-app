# 🚀 Reverb Quick Start

**TL;DR:** Start Reverb WebSocket server untuk enable real-time chat updates.

## ✅ Prerequisites

Already installed:
- ✅ Laravel Reverb package
- ✅ laravel-echo (v2.2.7)
- ✅ pusher-js (v8.4.0)
- ✅ Environment variables configured
- ✅ Frontend built with Echo integration

## 🎯 Start Reverb (3 Commands)

### Option 1: Separate Terminals (Recommended for Development)

```powershell
# Terminal 1: Reverb WebSocket Server
php artisan reverb:start --debug

# Terminal 2: Vite Dev Server
yarn run dev

# Terminal 3: Laravel Server (optional)
php artisan serve
```

### Option 2: Composer Script (One Command)

```powershell
composer run dev
```

This may run both Reverb and Vite concurrently.

## ✅ Verify It's Working

### 1. Check Reverb Terminal

You should see:
```
[2026-01-03 10:30:00] Reverb server started on 127.0.0.1:8080
[2026-01-03 10:30:00] Application: writing-app
```

### 2. Check Browser Console (F12)

You should see:
```
Echo: Connected to reverb
Pusher: Connection established
```

### 3. Test Real-time Chat

1. Open Chat panel
2. Send a message
3. Open same chat in another tab
4. Message appears in both tabs instantly ✅

## 🔧 Troubleshooting

| Issue | Solution |
|-------|----------|
| "Echo is not defined" | Run `yarn run build` |
| "Connection refused" | Start Reverb: `php artisan reverb:start` |
| "Port 8080 in use" | Change port in `.env`: `REVERB_PORT=8081` |
| No real-time updates | Check `BROADCAST_CONNECTION=reverb` in `.env` |
| Auth failed | Verify user is logged in |

## 📖 Full Documentation

For comprehensive setup, configuration, troubleshooting, and production deployment:

👉 **[Complete Reverb Setup Guide](./reverb-setup-guide.md)**

Includes:
- Detailed installation steps
- Environment configuration explained
- Advanced configuration options
- Production deployment guide
- Complete troubleshooting section
- Monitoring & debugging tips

---

**Current Configuration:**
- Host: `127.0.0.1`
- Port: `8080`
- Scheme: `http`
- App Key: `local-app-key`

---

*Need help? Check the [Full Setup Guide](./reverb-setup-guide.md) or [Sprint 23 Documentation](./10-sprints/sprint-23-chat-enhancement-features.md)*
