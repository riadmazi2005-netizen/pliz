<?php
require_once '../../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Chauffeur ID is required']);
    exit;
}

$id = $data['id'];

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Check if chauffeur exists
    $stmt = $db->prepare("SELECT utilisateur_id FROM chauffeurs WHERE id = ?");
    $stmt->execute([$id]);
    $chauffeur = $stmt->fetch();

    if (!$chauffeur) {
        http_response_code(404);
        echo json_encode(['error' => 'Chauffeur not found']);
        exit;
    }

    $user_id = $chauffeur['utilisateur_id'];

    // Update user information
    $update_fields = [];
    $update_values = [];

    if (isset($data['nom'])) {
        $update_fields[] = "nom = ?";
        $update_values[] = trim($data['nom']);
    }
    if (isset($data['prenom'])) {
        $update_fields[] = "prenom = ?";
        $update_values[] = trim($data['prenom']);
    }
    if (isset($data['email'])) {
        // Check if email is already taken by another user
        $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
        $stmt->execute([$data['email'], $user_id]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already exists']);
            exit;
        }
        $update_fields[] = "email = ?";
        $update_values[] = trim($data['email']);
    }
    if (isset($data['telephone'])) {
        $update_fields[] = "telephone = ?";
        $update_values[] = trim($data['telephone']);
    }

    if (!empty($update_fields)) {
        $update_values[] = $user_id;
        $stmt = $db->prepare("UPDATE utilisateurs SET " . implode(', ', $update_fields) . " WHERE id = ?");
        $stmt->execute($update_values);
    }

    // Update chauffeur information
    $chauffeur_fields = [];
    $chauffeur_values = [];

    if (isset($data['numero_permis'])) {
        // Check if license number is already taken by another chauffeur
        $stmt = $db->prepare("SELECT id FROM chauffeurs WHERE numero_permis = ? AND id != ?");
        $stmt->execute([$data['numero_permis'], $id]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'License number already exists']);
            exit;
        }
        $chauffeur_fields[] = "numero_permis = ?";
        $chauffeur_values[] = trim($data['numero_permis']);
    }
    if (isset($data['date_expiration_permis'])) {
        $chauffeur_fields[] = "date_expiration_permis = ?";
        $chauffeur_values[] = $data['date_expiration_permis'];
    }
    if (isset($data['statut'])) {
        $chauffeur_fields[] = "statut = ?";
        $chauffeur_values[] = $data['statut'];
    }

    if (!empty($chauffeur_fields)) {
        $chauffeur_values[] = $id;
        $stmt = $db->prepare("UPDATE chauffeurs SET " . implode(', ', $chauffeur_fields) . " WHERE id = ?");
        $stmt->execute($chauffeur_values);
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Chauffeur updated successfully'
    ]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
