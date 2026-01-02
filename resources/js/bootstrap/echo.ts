/**
 * Laravel Echo Configuration
 *
 * Sprint 21 (F4): Real-time Chat Updates with Laravel Reverb
 *
 * This file sets up Laravel Echo to connect to the Reverb WebSocket server
 * for real-time broadcasting.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Extend Window interface for Echo and Pusher
declare global {
    interface Window {
        Pusher: typeof Pusher;
        Echo: Echo<'reverb'>;
    }
}

// Make Pusher globally available (required by Echo)
window.Pusher = Pusher;

// Initialize Laravel Echo with Reverb configuration
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
