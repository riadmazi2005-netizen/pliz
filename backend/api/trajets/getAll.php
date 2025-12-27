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
        SELECT t.id, t.nom_trajet, t.description, t.distance_km, t.duree_estimee, t.heure_depart,
               t.heure_arrivee, t.prix_base, t.statut, t.date_creation,
               COUNT(DISTINCT i.id) as nombre_eleves,
               b.numero as bus_numero, b.marque, b.modele,
               c.nom as chauffeur_nom, c.prenom as chauffeur_prenom
        FROM trajets t
        LEFT JOIN bus b ON t.bus_id = b.id
        LEFT JOIN chauffeurs ch ON b.chauffeur_id = ch.id
        LEFT JOIN utilisateurs c ON ch.utilisateur_id = c.id
        LEFT JOIN inscriptions i ON t.id = i.trajet_id
        GROUP BY t.id
        ORDER BY t.heure_depart
    ");
    $stmt->execute();
    $trajets = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $trajets
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
