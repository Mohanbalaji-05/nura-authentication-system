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

    if ($sessionToken === '') {
        $response['message'] = 'You are not logged in.';
        echo json_encode($response);
        exit;
    }

    // Get session from Redis
    $sessionKey = 'session:' . $sessionToken;

    $sessionData = $redis->get($sessionKey);

    if (!$sessionData) {
        $response['message'] = 'Session expired. Please login again.';
        echo json_encode($response);
        exit;
    }

    $user = json_decode($sessionData, true);

    if (!is_array($user) || !isset($user['user_id'])) {
        $response['message'] = 'Invalid session.';
        echo json_encode($response);
        exit;
    }

    $userId = (int) $user['user_id'];

    // GET - Load profile
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $profile = $mongoProfiles->findOne([
            'user_id' => $userId
        ]);

        if (!$profile) {
            $response['message'] = 'Profile not found.';
            echo json_encode($response);
            exit;
        }

        $response['success'] = true;
        $response['message'] = 'Profile loaded successfully.';

        $response['profile'] = [
            'full_name' => $profile['full_name'] ?? '',
            'phone' => $profile['phone'] ?? '',
            'age' => $profile['age'] ?? null,
            'address' => $profile['address'] ?? ''
        ];

        echo json_encode($response);
        exit;
    }

    // POST - Update profile
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $data = json_decode(
            file_get_contents('php://input'),
            true
        );

        if (!is_array($data)) {
            $response['message'] = 'Invalid profile data.';
            echo json_encode($response);
            exit;
        }

        $fullName = trim($data['full_name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $age = $data['age'] ?? null;
        $address = trim($data['address'] ?? '');

        // Validate full name
        if ($fullName === '') {
            $response['message'] = 'Full name is required.';
            echo json_encode($response);
            exit;
        }

        if (strlen($fullName) > 100) {
            $response['message'] = 'Full name must not exceed 100 characters.';
            echo json_encode($response);
            exit;
        }

        // Validate phone
        if ($phone === '') {
            $response['message'] = 'Phone number is required.';
            echo json_encode($response);
            exit;
        }

        if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
            $response['message'] = 'Please enter a valid phone number.';
            echo json_encode($response);
            exit;
        }

        // Validate age
        if ($age !== null && $age !== '') {

            if (
                !is_numeric($age) ||
                (int) $age < 1 ||
                (int) $age > 120
            ) {
                $response['message'] = 'Please enter a valid age.';
                echo json_encode($response);
                exit;
            }

            $age = (int) $age;

        } else {

            $age = null;
        }

        // Validate address
        if (strlen($address) > 255) {
            $response['message'] = 'Address must not exceed 255 characters.';
            echo json_encode($response);
            exit;
        }

        // Update MongoDB profile
        $mongoProfiles->updateOne(
            [
                'user_id' => $userId
            ],
            [
                '$set' => [
                    'full_name' => $fullName,
                    'phone' => $phone,
                    'age' => $age,
                    'address' => $address,
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ]
            ]
        );

        $response['success'] = true;
        $response['message'] = 'Profile saved successfully.';

        echo json_encode($response);
        exit;
    }

    // Unsupported request method
    $response['message'] = 'Invalid request method.';

} catch (Throwable $e) {

    // Do not expose internal error details
    $response['message'] = 'Unable to process profile request. Please try again.';
}

echo json_encode($response);
exit;