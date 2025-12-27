<?php
// backend/config/session.php
// Fichier de gestion centralisée des sessions

/**
 * Démarre une session de manière sécurisée
 */
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configuration de sécurité pour les sessions
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Lax');
        
        // Durée de vie de la session : 2 heures
        ini_set('session.gc_maxlifetime', 7200);
        ini_set('session.cookie_lifetime', 7200);
        
        session_start();
    }
}

/**
 * Vérifie si l'utilisateur est connecté
 * @return bool
 */
function isUserLoggedIn() {
    startSecureSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 * @param string|array $allowed_roles Rôle(s) autorisé(s)
 * @return bool
 */
function hasRole($allowed_roles) {
    if (!isUserLoggedIn()) {
        return false;
    }
    
    if (is_string($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }
    
    return in_array($_SESSION['role'], $allowed_roles);
}

/**
 * Récupère l'ID de l'utilisateur connecté
 * @return int|null
 */
function getCurrentUserId() {
    startSecureSession();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Récupère le rôle de l'utilisateur connecté
 * @return string|null
 */
function getCurrentUserRole() {
    startSecureSession();
    return $_SESSION['role'] ?? null;
}

/**
 * Vérifie si la session est expirée (timeout après 2 heures d'inactivité)
 * @return bool
 */
function isSessionExpired() {
    startSecureSession();
    
    if (!isset($_SESSION['login_time'])) {
        return false;
    }
    
    $timeout = 7200; // 2 heures en secondes
    $elapsed = time() - $_SESSION['login_time'];
    
    return $elapsed > $timeout;
}

/**
 * Envoie une réponse d'erreur d'authentification
 * @param string $message Message d'erreur personnalisé
 */
function sendAuthError($message = 'Non authentifié') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => $message,
        'auth_required' => true
    ]);
    exit;
}

/**
 * Envoie une réponse d'erreur d'autorisation (droits insuffisants)
 * @param string $message Message d'erreur personnalisé
 */
function sendForbiddenError($message = 'Accès refusé') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => $message,
        'insufficient_permissions' => true
    ]);
    exit;
}

/**
 * Vérifie l'authentification et le rôle requis
 * Envoie une erreur et termine le script si non autorisé
 * @param string|array|null $required_roles Rôle(s) requis (null = tous les rôles authentifiés)
 */
function requireAuth($required_roles = null) {
    if (!isUserLoggedIn()) {
        sendAuthError('Vous devez être connecté pour accéder à cette ressource');
    }
    
    if (isSessionExpired()) {
        sendAuthError('Votre session a expiré. Veuillez vous reconnecter.');
    }
    
    if ($required_roles !== null && !hasRole($required_roles)) {
        sendForbiddenError('Vous n\'avez pas les droits nécessaires pour accéder à cette ressource');
    }
}

/**
 * Met à jour le timestamp de la dernière activité
 */
function updateSessionActivity() {
    startSecureSession();
    $_SESSION['last_activity'] = time();
}
?>