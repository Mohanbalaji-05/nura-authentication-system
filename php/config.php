<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use MongoDB\Client;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {

    // ====================
    // MySQL Connection
    // ====================

    $host = $_ENV['DB_HOST'];
    $dbname = $_ENV['DB_NAME'];
    $username = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASSWORD'];

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
        $_ENV['MONGO_URI']
    );

    $mongoDatabase = $mongoClient->selectDatabase(
        $_ENV['MONGO_DB']
    );

    $mongoProfiles = $mongoDatabase->selectCollection(
        $_ENV['MONGO_COLLECTION']
    );


    // ====================
    // Redis Connection
    // ====================

    $redis = new Redis();

    $redis->connect(
        $_ENV['REDIS_HOST'],
        (int) $_ENV['REDIS_PORT']
    );


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