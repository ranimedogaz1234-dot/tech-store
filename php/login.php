<?php

require_once 'config.php';
requireCsrf();
require_once 'user.php';
require_once __DIR__ . '/LoginThrottle.php';

$jsonRequest = stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') === 0;
$input = $jsonRequest ? json_decode(file_get_contents('php://input'), true) : $_POST;
if (!is_array($input)) { http_response_code(400); exit(json_encode(['success'=>false,'message'=>'Invalid input'])); }
$username = inputString($input['username'] ?? $input['email'] ?? '', 155);
$password = inputString($input['password'] ?? '', 1024);
if ($jsonRequest) header('Content-Type: application/json; charset=utf-8');

if (empty($username) || empty($password)) {
    if ($jsonRequest) exit(json_encode(['success'=>false,'message'=>'All fields are required']));
    echo "<script>alert('Tous les champs sont requis'); window.location.href='../login.html';</script>";
    exit;
}

try {
    $db = getDB();
    $throttle = new LoginThrottle($db);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'local';
    if (!$throttle->allowed($username,$ip)) {
        http_response_code(429); header('Retry-After: 900');
        $message = 'Too many login attempts. Try again later.';
        exit($jsonRequest ? json_encode(['success'=>false,'message'=>$message]) : $message);
    }
    $user = new User($db);

    $result = $user->login($username, $password);

    if ($result['success']) {
        if ($jsonRequest) exit(json_encode($result));
        if ($result['user']['role'] === 'admin') {
            header('Location: ../admin-panel.php');
        } else {
            header('Location: ../dashboard.html');
        }
        exit;
    } else {
        $throttle->failed($username,$ip);
        if ($jsonRequest) exit(json_encode($result));
        echo "<script>alert(" . json_encode($result['message'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) . "); window.location.href='../login.html';</script>";
    }

} catch (Exception $e) {
    if ($jsonRequest) { http_response_code(500); exit(json_encode(['success'=>false,'message'=>'Service temporarily unavailable'])); }
    echo "<script>alert('Erreur: " . 'Service temporarily unavailable' . "'); window.location.href='../login.html';</script>";
}
