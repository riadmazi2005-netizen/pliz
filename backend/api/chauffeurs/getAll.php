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
        SELECT c.id, u.nom, u.prenom, u.email, u.telephone, c.numero_permis, c.date_expiration_permis,
               c.nombre_accidents, c.statut, c.date_creation,
               b.numero as bus_numero, b.marque, b.modele
        FROM chauffeurs c
        JOIN utilisateurs u ON c.utilisateur_id = u.id
        LEFT JOIN bus b ON b.chauffeur_id = c.id
        ORDER BY u.nom, u.prenom
    ");
    $stmt->execute();
    $chauffeurs = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $chauffeurs
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
