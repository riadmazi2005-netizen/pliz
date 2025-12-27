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
    echo json_encode(['error' => 'Tuteur ID is required']);
    exit;
}

$id = $data['id'];

try {
    $db = Database::getInstance()->getConnection();

    // Check if tuteur exists
    $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE id = ? AND role = 'tuteur'");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Tuteur not found']);
        exit;
    }

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
        $stmt->execute([$data['email'], $id]);
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
    if (isset($data['statut'])) {
        $update_fields[] = "statut = ?";
        $update_values[] = $data['statut'];
    }

    if (!empty($update_fields)) {
        $update_values[] = $id;
        $stmt = $db->prepare("UPDATE utilisateurs SET " . implode(', ', $update_fields) . " WHERE id = ?");
        $stmt->execute($update_values);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Tuteur updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
