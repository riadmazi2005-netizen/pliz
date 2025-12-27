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
    echo json_encode(['error' => 'Bus ID is required']);
    exit;
}

$id = $data['id'];

try {
    $db = Database::getInstance()->getConnection();

    // Check if bus exists
    $stmt = $db->prepare("SELECT id FROM bus WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Bus not found']);
        exit;
    }

    // Update bus information
    $update_fields = [];
    $update_values = [];

    if (isset($data['numero'])) {
        // Check if numero is already taken by another bus
        $stmt = $db->prepare("SELECT id FROM bus WHERE numero = ? AND id != ?");
        $stmt->execute([$data['numero'], $id]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Bus number already exists']);
            exit;
        }
        $update_fields[] = "numero = ?";
        $update_values[] = trim($data['numero']);
    }
    if (isset($data['marque'])) {
        $update_fields[] = "marque = ?";
        $update_values[] = trim($data['marque']);
    }
    if (isset($data['modele'])) {
        $update_fields[] = "modele = ?";
        $update_values[] = trim($data['modele']);
    }
    if (isset($data['annee_fabrication'])) {
        $update_fields[] = "annee_fabrication = ?";
        $update_values[] = $data['annee_fabrication'];
    }
    if (isset($data['capacite'])) {
        $update_fields[] = "capacite = ?";
        $update_values[] = $data['capacite'];
    }
    if (isset($data['chauffeur_id'])) {
        $update_fields[] = "chauffeur_id = ?";
        $update_values[] = $data['chauffeur_id'];
    }
    if (isset($data['responsable_id'])) {
        $update_fields[] = "responsable_id = ?";
        $update_values[] = $data['responsable_id'];
    }
    if (isset($data['statut'])) {
        $update_fields[] = "statut = ?";
        $update_values[] = $data['statut'];
    }

    if (!empty($update_fields)) {
        $update_values[] = $id;
        $stmt = $db->prepare("UPDATE bus SET " . implode(', ', $update_fields) . " WHERE id = ?");
        $stmt->execute($update_values);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Bus updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
