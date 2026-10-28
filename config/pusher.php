<?php
/** Pusher Channels configuration. Replace these values from the Pusher dashboard. */
define('PUSHER_APP_KEY', 'APP_KEY');
define('PUSHER_APP_SECRET', 'APP_SECRET'); // Server-side only.
define('PUSHER_APP_ID', 'APP_ID');
define('PUSHER_CLUSTER', 'CLUSTER');

function isPusherConfigured(): bool {
    return PUSHER_APP_KEY !== 'APP_KEY' && PUSHER_APP_SECRET !== 'APP_SECRET'
        && PUSHER_APP_ID !== 'APP_ID' && PUSHER_CLUSTER !== 'CLUSTER';
}

function getPusherClientConfig(): array {
    return ['key' => PUSHER_APP_KEY, 'cluster' => PUSHER_CLUSTER, 'enabled' => isPusherConfigured()];
}
