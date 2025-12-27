<?php
// backend/api/auth/register.php
require_once '../../config/database.php';

// Configuration CORS sécurisée
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Récupération et validation des données JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Format JSON invalide']);
    exit;
}

// Validation des champs requis
$required_fields = ['nom', 'prenom', 'email', 'mot_de_passe', 'telephone'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty(trim($data[$field]))) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => "Le champ '$field' est obligatoire"
        ]);
        exit;
    }
}

// Validation de l'email
$email = trim($data['email']);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Format d\'email invalide'
    ]);
    exit;
}

// Validation du téléphone (format marocain)
$telephone = trim($data['telephone']);
if (!preg_match('/^(06|07)\d{8}$/', $telephone)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Format de téléphone invalide (ex: 0612345678)'
    ]);
    exit;
}

// Validation du mot de passe
if (strlen($data['mot_de_passe']) < 6) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Le mot de passe doit contenir au moins 6 caractères'
    ]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Vérifier si l'email existe déjà
    $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        $db->rollBack();
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'error' => 'Cet email est déjà utilisé'
        ]);
        exit;
    }

    // Hachage sécurisé du mot de passe
    $hashed_password = password_hash($data['mot_de_passe'], PASSWORD_BCRYPT, ['cost' => 12]);

    // Insertion de l'utilisateur avec le rôle 'tuteur' par défaut
    $stmt = $db->prepare("
        INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut)
        VALUES (?, ?, ?, ?, ?, 'tuteur', 'Actif')
    ");
    
    $stmt->execute([
        trim($data['nom']),
        trim($data['prenom']),
        $email,
        $hashed_password,
        $telephone
    ]);
    
    $user_id = $db->lastInsertId();
    $db->commit();

    // Démarrage de la session pour connecter automatiquement l'utilisateur
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user_id;
    $_SESSION['role'] = 'tuteur';
    $_SESSION['user_email'] = $email;
    $_SESSION['login_time'] = time();

    echo json_encode([
        'success' => true,
        'message' => 'Inscription réussie',
        'user' => [
            'id' => $user_id,
            'nom' => trim($data['nom']),
            'prenom' => trim($data['prenom']),
            'email' => $email,
            'telephone' => $telephone,
            'role' => 'tuteur',
            'statut' => 'Actif'
        ]
    ]);

} catch (PDOException $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    error_log("Erreur inscription DB: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors de l\'inscription'
    ]);
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    error_log("Erreur inscription: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur'
    ]);
}
?>