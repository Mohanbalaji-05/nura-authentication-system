<?php

header('Content-Type: application/json');

require_once __DIR__ . '/config.php';

$response = [
    'success' => false,
    'message' => ''
];

try {

    // Get session token from cookie
    $sessionToken = $_COOKIE['session_token'] ?? '';

    if ($sessionToken !== '') {

        // Delete session from Redis
        $sessionKey = 'session:' . $sessionToken;

        $redis->del($sessionKey);

        // Remove session cookie from browser
        setcookie(
            'session_token',
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }

    $response['success'] = true;
    $response['message'] = 'Logout successful.';

} catch (Throwable $e) {

    // Do not expose internal error details
    $response['message'] = 'Logout failed. Please try again.';
}

echo json_encode($response);
exit;