<?php

header('Content-Type: application/json');

require_once __DIR__ . '/config.php';

$response = [
    'success' => false,
    'message' => ''
];

try {

    // Get JSON data sent from JavaScript
    $data = json_decode(file_get_contents('php://input'), true);

    if (!is_array($data)) {
        $response['message'] = 'Invalid request data.';
        echo json_encode($response);
        exit;
    }

    // Get input values
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
        echo json_encode($response);
        exit;
    }

    if (strlen($email) > 100) {
        $response['message'] = 'Email must not exceed 100 characters.';
        echo json_encode($response);
        exit;
    }

    // Validate password
    if ($password === '') {
        $response['message'] = 'Password is required.';
        echo json_encode($response);
        exit;
    }

    // Find user by email
    $stmt = $pdo->prepare(
        "SELECT id, username, email, password
         FROM users
         WHERE email = :email
         LIMIT 1"
    );

    $stmt->execute([
        ':email' => $email
    ]);

    $user = $stmt->fetch();

    // Check user and password
    if (!$user || !password_verify($password, $user['password'])) {
        $response['message'] = 'Invalid email or password.';
        echo json_encode($response);
        exit;
    }

    // Generate secure session token
    $sessionToken = bin2hex(random_bytes(32));

    $sessionKey = 'session:' . $sessionToken;

    $sessionData = [
        'user_id' => (int) $user['id'],
        'username' => $user['username'],
        'email' => $user['email']
    ];

    // Store session in Redis for 1 hour
    $redis->setex(
        $sessionKey,
        3600,
        json_encode($sessionData)
    );

    // Store session token in HTTP-only cookie
    setcookie(
        'session_token',
        $sessionToken,
        [
            'expires' => time() + 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );

    // Login successful
    $response['success'] = true;
    $response['message'] = 'Login successful.';
    $response['username'] = $user['username'];

} catch (Throwable $e) {

    // Do not expose internal error details
    $response['message'] = 'Login failed. Please try again.';
}

echo json_encode($response);
exit;