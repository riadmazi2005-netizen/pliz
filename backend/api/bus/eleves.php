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

if (!isset($_GET['bus_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Bus ID is required']);
    exit;
}

$bus_id = $_GET['bus_id'];

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        SELECT e.id, e.nom, e.prenom, e.date_naissance, e.adresse, e.telephone_parent, e.email_parent, e.classe,
               i.date_inscription, i.date_debut, i.date_fin, i.statut as inscription_statut,
               u.nom as tuteur_nom, u.prenom as tuteur_prenom
        FROM eleves e
        JOIN inscriptions i ON e.id = i.eleve_id
        LEFT JOIN utilisateurs u ON e.tuteur_id = u.id
        WHERE i.bus_id = ? AND i.statut = 'Active'
        ORDER BY e.nom, e.prenom
    ");
    $stmt->execute([$bus_id]);
    $eleves = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $eleves
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
