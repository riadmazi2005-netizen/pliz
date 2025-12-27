<?php
require_once '../../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$required_fields = ['eleve_id', 'date_presence', 'statut'];
foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Field '$field' is required"]);
        exit;
    }
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Check if presence already exists for this student and date
    $stmt = $db->prepare("SELECT id FROM presences WHERE eleve_id = ? AND DATE(date_presence) = DATE(?)");
    $stmt->execute([$data['eleve_id'], $data['date_presence']]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Update existing presence
        $stmt = $db->prepare("
            UPDATE presences SET
                statut = ?,
                heure_montage = ?,
                heure_descente = ?,
                commentaire = ?,
                trajet_id = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['statut'],
            $data['heure_montage'] ?? null,
            $data['heure_descente'] ?? null,
            $data['commentaire'] ?? null,
            $data['trajet_id'] ?? null,
            $existing['id']
        ]);
    } else {
        // Create new presence
        $stmt = $db->prepare("
            INSERT INTO presences (eleve_id, date_presence, statut, heure_montage, heure_descente, commentaire, trajet_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['eleve_id'],
            $data['date_presence'],
            $data['statut'],
            $data['heure_montage'] ?? null,
            $data['heure_descente'] ?? null,
            $data['commentaire'] ?? null,
            $data['trajet_id'] ?? null
        ]);
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Presence marked successfully'
    ]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
