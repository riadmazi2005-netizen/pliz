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
        SELECT e.id, e.nom, e.prenom, e.date_naissance, e.adresse, e.telephone_parent, e.email_parent, e.classe,
               e.statut, e.date_creation, e.tuteur_id,
               u.nom as tuteur_nom, u.prenom as tuteur_prenom,
               i.bus_id, b.numero as bus_numero, b.marque, b.modele
        FROM eleves e
        LEFT JOIN utilisateurs u ON e.tuteur_id = u.id
        LEFT JOIN inscriptions i ON e.id = i.eleve_id AND i.statut = 'Active'
        LEFT JOIN bus b ON i.bus_id = b.id
        ORDER BY e.nom, e.prenom
    ");
    $stmt->execute();
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
