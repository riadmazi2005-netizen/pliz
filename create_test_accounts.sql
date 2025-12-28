-- Script pour créer les comptes de test
-- À exécuter pour créer les comptes avec les nouveaux identifiants
-- 
-- IMPORTANT: Les hash bcrypt ci-dessous sont temporaires (pour "test123")
-- Pour générer les vrais hash pour admin123, respo123, chauffeur123, exécutez en PHP:
--   echo password_hash('admin123', PASSWORD_DEFAULT);
--   echo password_hash('respo123', PASSWORD_DEFAULT);
--   echo password_hash('chauffeur123', PASSWORD_DEFAULT);
-- Puis remplacez les valeurs dans les variables @password_* ci-dessous

USE transport_scolaire;

-- Supprimer les anciens comptes s'ils existent
DELETE FROM utilisateurs WHERE email = 'admin@mohamme5.ma' AND role = 'admin';
DELETE FROM utilisateurs WHERE email = 'responsable_bus@mohammed5.ma' AND role = 'responsable';
DELETE FROM utilisateurs WHERE email = 'chauffeur@mohammed5.ma' AND role = 'chauffeur';

-- Hash bcrypt temporaires (pour "test123" - à remplacer par les vrais hash)
-- Pour l'instant, utilisez le mot de passe "test123" pour tous les comptes
-- Une fois les vrais hash générés, remplacez les valeurs ci-dessous

SET @password_admin = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';  -- test123
SET @password_respo = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';   -- test123
SET @password_chauffeur = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; -- test123

-- Créer l'admin
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut)
VALUES ('Admin', 'Système', 'admin@mohamme5.ma', @password_admin, '0612345678', 'admin', 'Actif');

-- Créer le responsable bus
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut)
VALUES ('Responsable', 'Bus', 'responsable_bus@mohammed5.ma', @password_respo, '0612345680', 'responsable', 'Actif');

-- Créer le chauffeur
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut)
VALUES ('Chauffeur', 'Test', 'chauffeur@mohammed5.ma', @password_chauffeur, '0612345681', 'chauffeur', 'Actif');

-- Afficher les comptes créés
SELECT id, nom, prenom, email, role, statut 
FROM utilisateurs 
WHERE email IN ('admin@mohamme5.ma', 'responsable_bus@mohammed5.ma', 'chauffeur@mohammed5.ma')
ORDER BY role, nom;

