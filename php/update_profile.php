<?php
// update_profile.php - Mettre à jour le profil utilisateur
require_once 'config.php';
requireCsrf();

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$firstName = trim(inputString($input['firstName'] ?? '', 50));
$lastName = trim(inputString($input['lastName'] ?? '', 50));
$email = trim(inputString($input['email'] ?? '', 155));
$phone = trim(inputString($input['phone'] ?? '', 20));

if (empty($firstName) || empty($lastName) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'First name, last name, and email are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

try {
    $conn = getDB();

    // Vérifier si l'email est déjà utilisé par un autre utilisateur
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $userId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already in use']);
        exit;
    }

    // Mettre à jour le profil
    $stmt = $conn->prepare("
        UPDATE users
        SET first_name = ?, last_name = ?, email = ?, phone = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$firstName, $lastName, $email, $phone, $userId]);

    // Mettre à jour la session
    $_SESSION['first_name'] = $firstName;
    $_SESSION['last_name'] = $lastName;
    $_SESSION['email'] = $email;

    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . 'Service temporarily unavailable']);
}
