<?php
// backend/api/inscriptions/update.php
require_once '../../config/database.php';

// Configuration CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestion des requêtes OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Récupération et validation des données
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Format JSON invalide']);
    exit;
}

// Vérifier que l'ID est fourni
if (!isset($data['id']) || empty($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'L\'ID de l\'inscription est requis']);
    exit;
}

$inscription_id = $data['id'];

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Vérifier que l'inscription existe
    $stmt = $db->prepare("SELECT id, eleve_id, statut FROM inscriptions WHERE id = ?");
    $stmt->execute([$inscription_id]);
    $inscription = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inscription) {
        $db->rollBack();
        http_response_code(404);
        echo json_encode(['error' => 'Inscription non trouvée']);
        exit;
    }

    // Construire la requête de mise à jour dynamiquement
    $update_fields = [];
    $update_values = [];
    $allowed_fields = ['statut', 'montant_mensuel', 'date_debut', 'date_fin', 'bus_id'];

    foreach ($allowed_fields as $field) {
        if (isset($data[$field])) {
            $update_fields[] = "$field = ?";
            $update_values[] = $data[$field];
        }
    }

    // Si aucun champ à mettre à jour
    if (empty($update_fields)) {
        $db->rollBack();
        http_response_code(400);
        echo json_encode(['error' => 'Aucun champ à mettre à jour']);
        exit;
    }

    // Validation du statut si fourni
    if (isset($data['statut'])) {
        $valid_statuts = ['Active', 'Suspendue', 'Terminée'];
        if (!in_array($data['statut'], $valid_statuts)) {
            $db->rollBack();
            http_response_code(400);
            echo json_encode(['error' => 'Statut invalide']);
            exit;
        }

        // Si le statut devient "Terminée" ou "Suspendue", mettre à jour l'élève
        if (in_array($data['statut'], ['Terminée', 'Suspendue'])) {
            $stmt = $db->prepare("UPDATE eleves SET statut = 'Inactif' WHERE id = ?");
            $stmt->execute([$inscription['eleve_id']]);
        } elseif ($data['statut'] === 'Active' && $inscription['statut'] !== 'Active') {
            // Si réactivation, mettre l'élève en Actif
            $stmt = $db->prepare("UPDATE eleves SET statut = 'Actif' WHERE id = ?");
            $stmt->execute([$inscription['eleve_id']]);
        }
    }

    // Si changement de bus, vérifier la capacité
    if (isset($data['bus_id']) && $data['bus_id'] != null) {
        // Vérifier que le bus existe
        $stmt = $db->prepare("SELECT capacite, statut FROM bus WHERE id = ?");
        $stmt->execute([$data['bus_id']]);
        $bus = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$bus) {
            $db->rollBack();
            http_response_code(404);
            echo json_encode(['error' => 'Bus non trouvé']);
            exit;
        }

        if ($bus['statut'] !== 'Actif') {
            $db->rollBack();
            http_response_code(400);
            echo json_encode(['error' => 'Le bus n\'est pas actif']);
            exit;
        }

        // Vérifier la capacité
        $stmt = $db->prepare("
            SELECT COUNT(*) as nb_inscrits 
            FROM inscriptions 
            WHERE bus_id = ? AND statut = 'Active' AND id != ?
        ");
        $stmt->execute([$data['bus_id'], $inscription_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['nb_inscrits'] >= $bus['capacite']) {
            $db->rollBack();
            http_response_code(409);
            echo json_encode(['error' => 'Le bus a atteint sa capacité maximale']);
            exit;
        }
    }

    // Exécuter la mise à jour
    $update_values[] = $inscription_id;
    $sql = "UPDATE inscriptions SET " . implode(', ', $update_fields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($update_values);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Inscription mise à jour avec succès'
    ]);

} catch (PDOException $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    error_log("Erreur mise à jour inscription DB: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la mise à jour de l\'inscription']);
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    error_log("Erreur mise à jour inscription: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}
?>