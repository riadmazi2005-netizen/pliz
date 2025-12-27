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
        SELECT u.id, u.nom, u.prenom, u.email, u.telephone, u.statut, u.date_creation,
               COUNT(DISTINCT e.id) as nombre_eleves,
               COUNT(DISTINCT CASE WHEN p.statut = 'Impayé' THEN p.id END) as paiements_impayes
        FROM utilisateurs u
        LEFT JOIN eleves e ON u.id = e.tuteur_id
        LEFT JOIN inscriptions i ON e.id = i.eleve_id
        LEFT JOIN paiements p ON i.id = p.inscription_id
        WHERE u.role = 'tuteur'
        GROUP BY u.id
        ORDER BY u.nom, u.prenom
    ");
    $stmt->execute();
    $tuteurs = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $tuteurs
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
