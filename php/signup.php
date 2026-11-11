<?php
require_once 'config.php';
requireCsrf();
require_once 'user.php';

$jsonRequest = stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') === 0;
$input = $jsonRequest ? json_decode(file_get_contents('php://input'), true) : $_POST;
if (!is_array($input)) { http_response_code(400); exit(json_encode(['success'=>false,'message'=>'Invalid input'])); }
$fullname = trim(inputString($input['fullname'] ?? (inputString($input['firstName'] ?? '',50).' '.inputString($input['lastName'] ?? '',50)), 100));
$email = inputString($input['email'] ?? '', 155);
$password = inputString($input['password'] ?? '', 72);
if ($jsonRequest) header('Content-Type: application/json; charset=utf-8');

if (empty($fullname) || empty($email) || empty($password)) {
    if ($jsonRequest) exit(json_encode(['success'=>false,'message'=>'All fields are required']));
    echo "<script>alert('Tous les champs sont requis'); window.location.href='../signup.html';</script>";
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    if ($jsonRequest) exit(json_encode(['success'=>false,'message'=>'Invalid email']));
    echo "<script>alert('Email invalide'); window.location.href='../signup.html';</script>";
    exit;
}

$nameParts = explode(' ', trim($fullname), 2);
$firstName = $nameParts[0] ?? '';
$lastName = $nameParts[1] ?? '';

try {
    $db = getDB();
    $user = new User($db);

    // Inscription
    $result = $user->register(
        $email, // Use email as username
        $email,
        $password,
        $firstName,
        $lastName,
        null // phone
    );

    if ($jsonRequest) exit(json_encode($result));
    if ($result['success']) {
        echo "<script>alert('Inscription réussie! Vous pouvez maintenant vous connecter.'); window.location.href='../login.html';</script>";
    } else {
        echo "<script>alert(" . json_encode($result['message'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) . "); window.location.href='../signup.html';</script>";
    }

} catch (Exception $e) {
    if ($jsonRequest) { http_response_code(500); exit(json_encode(['success'=>false,'message'=>'Service temporarily unavailable'])); }
    echo "<script>alert('Erreur: " . 'Service temporarily unavailable' . "'); window.location.href='../signup.html';</script>";
}
