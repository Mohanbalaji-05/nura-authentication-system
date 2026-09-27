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
    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    // Validate username
    if ($username === '') {
        $response['message'] = 'Username is required.';
        echo json_encode($response);
        exit;
    }

    if (strlen($username) > 50) {
        $response['message'] = 'Username must not exceed 50 characters.';
        echo json_encode($response);
        exit;
    }

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
    if (strlen($password) < 8) {
        $response['message'] = 'Password must be at least 8 characters.';
        echo json_encode($response);
        exit;
    }

    // Check if username or email already exists
    $checkStmt = $pdo->prepare(
        "SELECT id
         FROM users
         WHERE username = :username OR email = :email
         LIMIT 1"
    );

    $checkStmt->execute([
        ':username' => $username,
        ':email' => $email
    ]);

    if ($checkStmt->fetch()) {
        $response['message'] = 'Username or email already exists.';
        echo json_encode($response);
        exit;
    }

    // Hash password securely
    $hashedPassword = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    // Insert user into MySQL
    $insertStmt = $pdo->prepare(
        "INSERT INTO users (username, email, password)
         VALUES (:username, :email, :password)"
    );

    $insertStmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password' => $hashedPassword
    ]);

    $userId = (int) $pdo->lastInsertId();

    // Create profile in MongoDB
    $profileData = [
        'user_id' => $userId,
        'username' => $username,
        'email' => $email,
        'full_name' => '',
        'phone' => '',
        'age' => null,
        'address' => '',
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ];

    $mongoProfiles->insertOne($profileData);

    // Registration successful
    $response['success'] = true;
    $response['message'] = 'Registration successful.';
    $response['user_id'] = $userId;

} catch (PDOException $e) {

    // Handle MySQL errors without exposing database details
    $response['message'] = 'Registration failed. Please try again.';

} catch (Throwable $e) {

    // Handle MongoDB or unexpected errors
    $response['message'] = 'Registration failed. Please try again.';
}

echo json_encode($response);
exit;