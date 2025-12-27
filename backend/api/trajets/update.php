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
    echo json_encode(['error' => 'Trajet ID is required']);
    exit;
}

$id = $data['id'];

try {
    $db = Database::getInstance()->getConnection();

    // Check if trajet exists
    $stmt = $db->prepare("SELECT id FROM trajets WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Trajet not found']);
        exit;
    }

    // Update trajet information
    $update_fields = [];
    $update_values = [];

    if (isset($data['nom_trajet'])) {
        $update_fields[] = "nom_trajet = ?";
        $update_values[] = trim($data['nom_trajet']);
    }
    if (isset($data['description'])) {
        $update_fields[] = "description = ?";
        $update_values[] = trim($data['description']);
    }
    if (isset($data['bus_id'])) {
        // Check if bus exists and is active
        $stmt = $db->prepare("SELECT id, statut FROM bus WHERE id = ?");
        $stmt->execute([$data['bus_id']]);
        $bus = $stmt->fetch();

        if (!$bus) {
            http_response_code(404);
            echo json_encode(['error' => 'Bus not found']);
            exit;
        }

        if ($bus['statut'] !== 'Actif') {
            http_response_code(400);
            echo json_encode(['error' => 'Bus is not active']);
            exit;
        }

        $update_fields[] = "bus_id = ?";
        $update_values[] = $data['bus_id'];
    }
    if (isset($data['distance_km'])) {
        $update_fields[] = "distance_km = ?";
        $update_values[] = $data['distance_km'];
    }
    if (isset($data['duree_estimee'])) {
        $update_fields[] = "duree_estimee = ?";
        $update_values[] = $data['duree_estimee'];
    }
    if (isset($data['heure_depart'])) {
        $update_fields[] = "heure_depart = ?";
        $update_values[] = $data['heure_depart'];
    }
    if (isset($data['heure_arrivee'])) {
        $update_fields[] = "heure_arrivee = ?";
        $update_values[] = $data['heure_arrivee'];
    }
    if (isset($data['prix_base'])) {
        $update_fields[] = "prix_base = ?";
        $update_values[] = $data['prix_base'];
    }
    if (isset($data['statut'])) {
        $update_fields[] = "statut = ?";
        $update_values[] = $data['statut'];
    }

    if (!empty($update_fields)) {
        $update_values[] = $id;
        $stmt = $db->prepare("UPDATE trajets SET " . implode(', ', $update_fields) . " WHERE id = ?");
        $stmt->execute($update_values);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Trajet updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
