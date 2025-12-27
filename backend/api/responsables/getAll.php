<?php
require_once '../../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        SELECT r.id, u.nom, u.prenom, u.email, u.telephone, r.zone_responsabilite, r.statut, r.date_creation,
               COUNT(b.id) as nombre_bus
        FROM responsables_bus r
        JOIN utilisateurs u ON r.utilisateur_id = u.id
        LEFT JOIN bus b ON r.id = b.responsable_id
        GROUP BY r.id
        ORDER BY u.nom, u.prenom
    ");
    $stmt->execute();
    $responsables = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $responsables
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
