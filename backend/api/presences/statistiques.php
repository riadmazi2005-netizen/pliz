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

    // Statistiques générales de présence
    $stats = [];

    // Nombre total d'élèves
    $stmt = $db->query("SELECT COUNT(*) as total_eleves FROM eleves");
    $stats['total_eleves'] = $stmt->fetch()['total_eleves'];

    // Nombre total de présences marquées aujourd'hui
    $stmt = $db->prepare("SELECT COUNT(*) as presences_aujourdhui FROM presences WHERE DATE(date_presence) = CURDATE()");
    $stmt->execute();
    $stats['presences_aujourdhui'] = $stmt->fetch()['presences_aujourdhui'];

    // Taux de présence moyen (derniers 30 jours)
    $stmt = $db->prepare("
        SELECT AVG(present_count / total_count) * 100 as taux_presence_moyen
        FROM (
            SELECT DATE(date_presence) as date,
                   COUNT(CASE WHEN statut = 'Present' THEN 1 END) as present_count,
                   COUNT(*) as total_count
            FROM presences
            WHERE date_presence >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY DATE(date_presence)
        ) daily_stats
    ");
    $stmt->execute();
    $result = $stmt->fetch();
    $stats['taux_presence_moyen'] = round($result['taux_presence_moyen'] ?? 0, 2);

    // Présences par statut (derniers 30 jours)
    $stmt = $db->prepare("
        SELECT statut, COUNT(*) as count
        FROM presences
        WHERE date_presence >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY statut
    ");
    $stmt->execute();
    $stats['presences_par_statut'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Élèves avec le plus d'absences (top 10)
    $stmt = $db->prepare("
        SELECT e.nom, e.prenom, COUNT(*) as absences
        FROM presences p
        JOIN eleves e ON p.eleve_id = e.id
        WHERE p.statut = 'Absent' AND p.date_presence >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY p.eleve_id, e.nom, e.prenom
        ORDER BY absences DESC
        LIMIT 10
    ");
    $stmt->execute();
    $stats['eleves_plus_absents'] = $stmt->fetchAll();

    // Statistiques par trajet
    $stmt = $db->prepare("
        SELECT t.nom as trajet_nom,
               COUNT(p.id) as total_presences,
               SUM(CASE WHEN p.statut = 'Present' THEN 1 ELSE 0 END) as presents,
               ROUND((SUM(CASE WHEN p.statut = 'Present' THEN 1 ELSE 0 END) / COUNT(p.id)) * 100, 2) as taux_presence
        FROM presences p
        JOIN trajets t ON p.trajet_id = t.id
        WHERE p.date_presence >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY t.id, t.nom
        ORDER BY taux_presence DESC
    ");
    $stmt->execute();
    $stats['statistiques_par_trajet'] = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
