<?php
/**
 * Script pour mettre à jour les mots de passe des comptes de test
 * Exécutez ce script une fois pour mettre à jour les hash bcrypt dans la base de données
 * 
 * Accès: http://localhost/backend/update_test_passwords.php
 */

require_once 'config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDBConnection();
    
    // Générer les hash bcrypt pour les mots de passe
    $password_admin = password_hash('admin123', PASSWORD_DEFAULT);
    $password_respo = password_hash('respo123', PASSWORD_DEFAULT);
    $password_chauffeur = password_hash('chauffeur123', PASSWORD_DEFAULT);
    
    $results = [];
    
    // Mettre à jour l'admin
    $stmt = $pdo->prepare('UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ? AND role = ?');
    $stmt->execute([$password_admin, 'admin@mohamme5.ma', 'admin']);
    $results['admin'] = $stmt->rowCount() > 0 ? 'Mise à jour réussie' : 'Aucun compte trouvé';
    
    // Mettre à jour le responsable
    $stmt = $pdo->prepare('UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ? AND role = ?');
    $stmt->execute([$password_respo, 'responsable_bus@mohammed5.ma', 'responsable']);
    $results['responsable'] = $stmt->rowCount() > 0 ? 'Mise à jour réussie' : 'Aucun compte trouvé';
    
    // Mettre à jour le chauffeur
    $stmt = $pdo->prepare('UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ? AND role = ?');
    $stmt->execute([$password_chauffeur, 'chauffeur@mohammed5.ma', 'chauffeur']);
    $results['chauffeur'] = $stmt->rowCount() > 0 ? 'Mise à jour réussie' : 'Aucun compte trouvé';
    
    // Vérifier les comptes
    $stmt = $pdo->prepare('SELECT email, role FROM utilisateurs WHERE email IN (?, ?, ?)');
    $stmt->execute(['admin@mohamme5.ma', 'responsable_bus@mohammed5.ma', 'chauffeur@mohammed5.ma']);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Mots de passe mis à jour avec succès',
        'results' => $results,
        'accounts' => $accounts,
        'passwords' => [
            'admin@mohamme5.ma' => 'admin123',
            'responsable_bus@mohammed5.ma' => 'respo123',
            'chauffeur@mohammed5.ma' => 'chauffeur123'
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>

