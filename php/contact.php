<?php
// contact.php - Traitement du formulaire de contact
require_once 'config.php';
requireCsrf();

header('Content-Type: application/json');

// Récupérer les données via POST
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?? '';

// Validation
if (empty($name) || empty($email) || empty($subject)) {
    echo json_encode(['success' => false, 'message' => 'Tous les champs requis doivent être remplis']);
    exit;
}

// Valider l'email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email invalide']);
    exit;
}
foreach (['name'=>50,'email'=>155,'phone'=>50,'subject'=>255,'message'=>10000] as $key=>$limit) {
    if (!is_string($$key) || strlen($$key)>$limit || ($key !== 'message' && preg_match('/[\r\n]/', $$key))) {
        http_response_code(400); exit(json_encode(['success'=>false,'message'=>'Invalid contact fields']));
    }
}

try {
    $db = getDB();

    // Insérer le message dans la base de données
    $query = "INSERT INTO contact_messages (name, email, phone, subject, message, status)
              VALUES (?, ?, ?, ?, ?, 'new')";
    $stmt = $db->prepare($query);
    $stmt->execute([
        $name,
        $email,
        $phone,
        $subject,
        $message
    ]);

    // Envoyer un email à l'admin (optionnel) - Commenté pour éviter les erreurs en développement
    // $to = ADMIN_EMAIL;
    // $subject_email = "Nouveau message de contact: " . $subject;
    // $message_email = "Nom: " . $name . "\n";
    // $message_email .= "Email: " . $email . "\n";
    // $message_email .= "Téléphone: " . ($phone ?: 'N/A') . "\n\n";
    // $message_email .= "Message:\n" . $message;
    // $headers = "From: " . $email;
    // @mail($to, $subject_email, $message_email, $headers);

    echo json_encode([
        'success' => true,
        'message' => 'Message envoyé avec succès'
    ]);

} catch (Exception $e) {
    error_log('Contact form error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Impossible d’envoyer le message. Réessayez plus tard.'
    ]);
}
