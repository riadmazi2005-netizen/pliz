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

if (!isset($_GET['eleve_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Eleve ID is required']);
    exit;
}

$eleve_id = $_GET['eleve_id'];

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        SELECT p.id, p.date_presence, p.statut, p.heure_arrivee, p.heure_depart,
               t.nom as trajet_nom, t.heure_depart as trajet_heure_depart,
               b.numero as bus_numero, b.marque, b.modele,
               c.nom as chauffeur_nom, c.prenom as chauffeur_prenom
        FROM presences p
        JOIN trajets t ON p.trajet_id = t.id
        JOIN bus b ON t.bus_id = b.id
        LEFT JOIN chauffeurs ch ON b.chauffeur_id = ch.id
        LEFT JOIN utilisateurs c ON ch.utilisateur_id = c.id
        WHERE p.eleve_id = ?
        ORDER BY p.date_presence DESC, t.heure_depart DESC
    ");
    $stmt->execute([$eleve_id]);
    $presences = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $presences
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
