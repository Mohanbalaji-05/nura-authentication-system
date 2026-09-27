<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use MongoDB\Client;

// Load .env if it exists.
// On Railway, environment variables are provided directly by Railway.
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Get environment variables from .env, Railway, or server environment.
function envValue(string $key): string
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    return $value === false ? '' : $value;
}

try {

    // ====================
    // MySQL Connection
    // ====================

    $host = envValue('DB_HOST');
    $dbname = envValue('DB_NAME');
    $username = envValue('DB_USER');
    $password = envValue('DB_PASSWORD');

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );


    // ====================
    // MongoDB Connection
    // ====================

    $mongoClient = new Client(
        envValue('MONGO_URI')
    );

    $mongoDatabase = $mongoClient->selectDatabase(
        envValue('MONGO_DB')
    );

    $mongoProfiles = $mongoDatabase->selectCollection(
        envValue('MONGO_COLLECTION')
    );


    // ====================
    // Redis Connection
    // ====================

    $redis = new Redis();

    $redis->connect(
        envValue('REDIS_HOST'),
        (int) envValue('REDIS_PORT')
    );

    // Authenticate with Railway Redis credentials
    $redisUser = envValue('REDIS_USER');
    $redisPassword = envValue('REDIS_PASSWORD');

    if ($redisUser !== '' && $redisPassword !== '') {
        $redis->auth([
            $redisUser,
            $redisPassword
        ]);
    }


} catch (Throwable $e) {

    // Do not expose database or server details
    http_response_code(500);

    header('Content-Type: application/json');

    echo json_encode([
        'success' => false,
        'message' => 'Server configuration error. Please try again later.'
    ]);

    exit;
}