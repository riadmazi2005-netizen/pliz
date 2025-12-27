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

if (!isset($_GET['chauffeur_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Chauffeur ID is required']);
    exit;
}

$chauffeur_id = $_GET['chauffeur_id'];

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        SELECT a.id, a.date_accident, a.description, a.lieu, a.gravite, a.nombre_blesses,
               a.nombre_deces, a.cout_estime, a.statut, a.date_creation,
               b.numero as bus_numero, b.marque, b.modele
        FROM accidents a
        JOIN bus b ON a.bus_id = b.id
        WHERE a.chauffeur_id = ?
        ORDER BY a.date_accident DESC
    ");
    $stmt->execute([$chauffeur_id]);
    $accidents = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $accidents
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
