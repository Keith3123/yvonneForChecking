import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbKey  = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = import.meta.env.VITE_REVERB_HOST  ?? 'localhost';
const reverbPort = parseInt(import.meta.env.VITE_REVERB_PORT ?? '8080');

if (!reverbKey) {
    console.error('❌ VITE_REVERB_APP_KEY is not set');
} else {
    window.Echo = new Echo({
        broadcaster:       'reverb',
        key:               reverbKey,
        wsHost:            reverbHost,
        wsPort:            reverbPort,
        wssPort:           reverbPort,
        forceTLS:          false,
        disableStats:      true,
        enabledTransports: ['ws'],
    });

    console.log(`🔌 Reverb: ws://${reverbHost}:${reverbPort}`);
}