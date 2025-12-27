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
        SELECT b.id, b.numero, b.marque, b.modele, b.annee_fabrication, b.capacite, b.statut, b.date_creation,
               c.nom as chauffeur_nom, c.prenom as chauffeur_prenom,
               r.nom as responsable_nom, r.prenom as responsable_prenom,
               COUNT(i.id) as nombre_eleves
        FROM bus b
        LEFT JOIN chauffeurs ch ON b.chauffeur_id = ch.id
        LEFT JOIN utilisateurs c ON ch.utilisateur_id = c.id
        LEFT JOIN responsables_bus rb ON b.responsable_id = rb.id
        LEFT JOIN utilisateurs r ON rb.utilisateur_id = r.id
        LEFT JOIN inscriptions i ON b.id = i.bus_id AND i.statut = 'Active'
        GROUP BY b.id
        ORDER BY b.numero
    ");
    $stmt->execute();
    $bus = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $bus
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
