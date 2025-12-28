<?php
require_once '../../config/headers.php';
require_once '../../config/database.php';

$pdo = getDBConnection();
$stmt = $pdo->prepare('SELECT id, nom, prenom, email, telephone, role, statut FROM utilisateurs WHERE role = ? AND statut = ?');
$stmt->execute(['admin', 'Actif']);
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'data' => $admins
]);
?>

