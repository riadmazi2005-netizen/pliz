-- ============================================
-- DONNÉES DE TEST COMPLÈTES
-- Pour Admin, Chauffeur et Responsable
-- ============================================
-- À exécuter APRÈS transport_scolaire.sql
-- Mot de passe pour tous les comptes : test123
-- ============================================

USE transport_scolaire;

-- Nettoyer les données existantes (optionnel - décommentez si nécessaire)
-- TRUNCATE TABLE notifications;
-- TRUNCATE TABLE paiements;
-- TRUNCATE TABLE demandes;
-- TRUNCATE TABLE presences;
-- TRUNCATE TABLE inscriptions;
-- TRUNCATE TABLE eleves;
-- TRUNCATE TABLE bus;
-- TRUNCATE TABLE trajets;
-- TRUNCATE TABLE responsables_bus;
-- TRUNCATE TABLE chauffeurs;
-- DELETE FROM utilisateurs WHERE role != 'admin';

-- ============================================
-- 1. CRÉER LES UTILISATEURS DE TEST
-- ============================================
-- Mot de passe hashé pour "test123" : $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut) VALUES
-- Admin de test
('Admin', 'Système', 'admin@transport.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612345678', 'admin', 'Actif'),

-- Chauffeurs de test (3 chauffeurs)
('Idrissi', 'Ahmed', 'ahmed.idrissi@transport.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612345682', 'chauffeur', 'Actif'),
('Tazi', 'Youssef', 'youssef.tazi@transport.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612345683', 'chauffeur', 'Actif'),
('El Fassi', 'Karim', 'karim.elfassi@transport.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612345684', 'chauffeur', 'Actif'),

-- Responsables de test (2 responsables)
('Kettani', 'Nadia', 'nadia.kettani@transport.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612345685', 'responsable', 'Actif'),
('Benjelloun', 'Omar', 'omar.benjelloun@transport.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612345686', 'responsable', 'Actif'),

-- Tuteurs de test (pour tester les relations)
('Alami', 'Mohammed', 'mohammed.alami@email.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612345679', 'tuteur', 'Actif'),
('Benjelloun', 'Fatima', 'fatima.benjelloun@email.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612345680', 'tuteur', 'Actif');

-- ============================================
-- 2. CRÉER LES CHAUFFEURS
-- ============================================
-- Note: Utilisation de INSERT IGNORE pour éviter les doublons
INSERT IGNORE INTO chauffeurs (utilisateur_id, numero_permis, date_expiration_permis, nombre_accidents, statut) VALUES
(2, 'CH-001956', DATE_ADD(CURDATE(), INTERVAL 2 YEAR), 0, 'Actif'),        -- Ahmed Idrissi
(3, 'CH-009789', DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1, 'Actif'),        -- Youssef Tazi
(4, 'CH-000123', DATE_ADD(CURDATE(), INTERVAL 3 YEAR), 0, 'Actif');        -- Karim El Fassi

-- ============================================
-- 3. CRÉER LES RESPONSABLES BUS
-- ============================================
-- Utilisation de INSERT IGNORE pour éviter les doublons
INSERT IGNORE INTO responsables_bus (utilisateur_id, zone_responsabilite, statut) VALUES
(5, 'Zone Centre - Maarif, Gauthier, 2 Mars', 'Actif'),      -- Nadia Kettani
(6, 'Zone Nord - Sidi Maarouf, Californie, Oasis', 'Actif'); -- Omar Benjelloun

-- ============================================
-- 4. CRÉER LES TRAJETS
-- ============================================
-- Utilisation de INSERT IGNORE pour éviter les doublons
INSERT IGNORE INTO trajets (nom, zones, heure_depart_matin_a, heure_arrivee_matin_a, heure_depart_soir_a, heure_arrivee_soir_a, heure_depart_matin_b, heure_arrivee_matin_b, heure_depart_soir_b, heure_arrivee_soir_b) VALUES
('Trajet Centre', '["Maarif", "Gauthier", "2 Mars", "Ain Diab"]', '07:30:00', '08:00:00', '17:00:00', '17:30:00', '08:00:00', '08:30:00', '17:30:00', '18:00:00'),
('Trajet Nord', '["Sidi Maarouf", "Californie", "Oasis", "Ain Sebaa"]', '07:00:00', '08:00:00', '16:45:00', '17:30:00', '07:45:00', '08:30:00', '17:15:00', '18:00:00'),
('Trajet Sud', '["Hay Hassani", "Oulfa", "Sbata", "Hay Mohammadi"]', '07:15:00', '08:00:00', '16:50:00', '17:35:00', '08:00:00', '08:45:00', '17:20:00', '18:05:00');

-- ============================================
-- 5. CRÉER LES BUS
-- ============================================
-- Utilisation de INSERT IGNORE pour éviter les doublons
INSERT IGNORE INTO bus (numero, marque, modele, annee_fabrication, capacite, chauffeur_id, responsable_id, trajet_id, statut) VALUES
('BUS-001', 'Mercedes', 'Sprinter', 2020, 50, 1, 1, 1, 'Actif'),    -- Chauffeur: Ahmed (chauffeur_id=1), Responsable: Nadia (responsable_id=1)
('BUS-002', 'Volvo', '9700', 2019, 45, 2, 1, 1, 'Actif'),           -- Chauffeur: Youssef (chauffeur_id=2), Responsable: Nadia
('BUS-003', 'Iveco', 'Daily', 2021, 35, 3, 2, 2, 'Actif'),          -- Chauffeur: Karim (chauffeur_id=3), Responsable: Omar (responsable_id=2)
('BUS-004', 'Mercedes', 'Sprinter', 2022, 50, NULL, 2, 3, 'Actif'); -- Pas de chauffeur assigné, Responsable: Omar

-- ============================================
-- 6. CRÉER DES ÉLÈVES (pour tester les relations)
-- ============================================
INSERT INTO eleves (nom, prenom, date_naissance, adresse, telephone_parent, email_parent, classe, tuteur_id, statut) VALUES
-- Élèves de Mohammed Alami (tuteur_id = 7)
('Alami', 'Yasmine', '2012-03-15', '123 Rue Maarif, Casablanca', '0612345679', 'mohammed.alami@email.ma', 'CE2', 7, 'Actif'),
('Alami', 'Karim', '2014-09-22', '123 Rue Maarif, Casablanca', '0612345679', 'mohammed.alami@email.ma', 'CP', 7, 'Actif'),

-- Élèves de Fatima Benjelloun (tuteur_id = 8)
('Benjelloun', 'Salma', '2011-06-10', '45 Boulevard Gauthier, Casablanca', '0612345680', 'fatima.benjelloun@email.ma', 'CM1', 8, 'Actif'),
('Benjelloun', 'Mehdi', '2013-01-20', '45 Boulevard Gauthier, Casablanca', '0612345680', 'fatima.benjelloun@email.ma', 'CE1', 8, 'Actif');

-- ============================================
-- 7. CRÉER DES INSCRIPTIONS
-- ============================================
INSERT INTO inscriptions (eleve_id, bus_id, date_inscription, date_debut, date_fin, statut, montant_mensuel) VALUES
(1, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 'Active', 400.00), -- Yasmine -> BUS-001
(2, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 'Active', 400.00), -- Karim -> BUS-001
(3, 2, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 'Active', 400.00), -- Salma -> BUS-002
(4, 3, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 'Active', 400.00); -- Mehdi -> BUS-003

-- ============================================
-- 8. CRÉER DES PRÉSENCES (pour tester)
-- ============================================
INSERT INTO presences (eleve_id, date, present_matin, present_soir, bus_id, responsable_id, chauffeur_id, remarque) VALUES
(1, CURDATE(), TRUE, TRUE, 1, 1, 1, 'Présent'),
(2, CURDATE(), TRUE, TRUE, 1, 1, 1, 'Présent'),
(3, CURDATE(), TRUE, FALSE, 2, 1, 2, 'Absent le soir'),
(4, CURDATE(), FALSE, TRUE, 3, 2, 3, 'Absent le matin'),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), TRUE, TRUE, 1, 1, 1, NULL),
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), TRUE, TRUE, 1, 1, 1, NULL);

-- ============================================
-- 9. CRÉER DES DEMANDES
-- ============================================
INSERT INTO demandes (eleve_id, tuteur_id, type_demande, description, statut) VALUES
(4, 8, 'inscription', 'Demande d\'inscription pour Mehdi Benjelloun', 'En attente'),
(1, 7, 'modification', 'Changement de zone pour Yasmine Alami', 'En attente');

-- ============================================
-- 10. CRÉER DES NOTIFICATIONS
-- ============================================
INSERT INTO notifications (destinataire_id, destinataire_type, titre, message, type, lue) VALUES
-- Notifications pour Admin
(1, 'admin', 'Nouvelle inscription', 'Nouvelle demande d\'inscription reçue pour Mehdi Benjelloun', 'info', FALSE),
(1, 'admin', 'Paiement en attente', '3 paiements en attente de validation', 'alerte', FALSE),
(1, 'admin', 'Bus en maintenance', 'Le bus BUS-004 nécessite une révision', 'avertissement', FALSE),

-- Notifications pour Chauffeurs
(2, 'chauffeur', 'Nouveau trajet assigné', 'Vous avez été assigné au BUS-001 sur le trajet Centre', 'info', FALSE),
(2, 'chauffeur', 'Rappel: Inspection', 'Votre permis expire dans 2 ans. Pensez à le renouveler.', 'info', TRUE),
(3, 'chauffeur', 'Trajet du jour', 'Votre trajet du jour: BUS-002 - Trajet Centre', 'info', FALSE),
(4, 'chauffeur', 'Bienvenue', 'Bienvenue dans le système de transport scolaire', 'info', FALSE),

-- Notifications pour Responsables
(5, 'responsable', 'Nouveau bus assigné', 'Le BUS-001 et BUS-002 sont sous votre responsabilité', 'info', FALSE),
(5, 'responsable', 'Présences du jour', '2 élèves absents aujourd\'hui sur votre zone', 'alerte', FALSE),
(6, 'responsable', 'Zone Nord', 'Vous êtes responsable de la Zone Nord - 2 bus', 'info', FALSE),
(6, 'responsable', 'Nouvelle inscription', 'Nouvel élève inscrit sur votre zone: Mehdi Benjelloun', 'info', FALSE);

-- ============================================
-- 11. CRÉER DES PAIEMENTS
-- ============================================
INSERT INTO paiements (inscription_id, montant, mois, annee, date_paiement, mode_paiement, statut) VALUES
(1, 400.00, MONTH(CURDATE()), YEAR(CURDATE()), DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Virement', 'Payé'),
(1, 400.00, MONTH(DATE_ADD(CURDATE(), INTERVAL 1 MONTH)), YEAR(CURDATE()), NULL, NULL, 'En attente'),
(2, 400.00, MONTH(CURDATE()), YEAR(CURDATE()), DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Espèces', 'Payé'),
(3, 400.00, MONTH(CURDATE()), YEAR(CURDATE()), DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'Carte bancaire', 'Payé'),
(4, 400.00, MONTH(CURDATE()), YEAR(CURDATE()), NULL, NULL, 'En attente');

-- ============================================
-- 12. CRÉER DES ACCIDENTS (pour tester)
-- ============================================
INSERT INTO accidents (date, heure, bus_id, chauffeur_id, description, degats, lieu, gravite, blesses, date_creation) VALUES
(DATE_SUB(CURDATE(), INTERVAL 30 DAY), '08:15:00', 2, 2, 'Collision mineure avec un poteau', 'Rétroviseur cassé', 'Boulevard Gauthier', 'Légère', FALSE, DATE_SUB(CURDATE(), INTERVAL 30 DAY)),
(DATE_SUB(CURDATE(), INTERVAL 90 DAY), '17:20:00', 1, 1, 'Accrochage avec un véhicule', 'Rayure sur la portière arrière', 'Avenue 2 Mars', 'Légère', FALSE, DATE_SUB(CURDATE(), INTERVAL 90 DAY));

-- ============================================
-- 13. CRÉER DES RELATIONS CONDUIRE (chauffeurs-trajets)
-- ============================================
INSERT INTO conduire (chauffeur_id, trajet_id, bus_id, date_debut, date_fin, statut) VALUES
(1, 1, 1, DATE_SUB(CURDATE(), INTERVAL 90 DAY), NULL, 'Actif'),  -- Ahmed conduit BUS-001 sur Trajet Centre
(2, 1, 2, DATE_SUB(CURDATE(), INTERVAL 60 DAY), NULL, 'Actif'),  -- Youssef conduit BUS-002 sur Trajet Centre
(3, 2, 3, DATE_SUB(CURDATE(), INTERVAL 30 DAY), NULL, 'Actif');  -- Karim conduit BUS-003 sur Trajet Nord

-- ============================================
-- RÉSUMÉ DES DONNÉES CRÉÉES
-- ============================================
SELECT '=== RÉSUMÉ DES DONNÉES DE TEST ===' as '';

SELECT 'Utilisateurs Admin:' as '', COUNT(*) as total FROM utilisateurs WHERE role = 'admin';
SELECT 'Utilisateurs Chauffeurs:' as '', COUNT(*) as total FROM utilisateurs WHERE role = 'chauffeur';
SELECT 'Utilisateurs Responsables:' as '', COUNT(*) as total FROM utilisateurs WHERE role = 'responsable';
SELECT 'Utilisateurs Tuteurs:' as '', COUNT(*) as total FROM utilisateurs WHERE role = 'tuteur';
SELECT 'Chauffeurs:' as '', COUNT(*) as total FROM chauffeurs;
SELECT 'Responsables:' as '', COUNT(*) as total FROM responsables_bus;
SELECT 'Bus:' as '', COUNT(*) as total FROM bus;
SELECT 'Trajets:' as '', COUNT(*) as total FROM trajets;
SELECT 'Élèves:' as '', COUNT(*) as total FROM eleves;
SELECT 'Inscriptions:' as '', COUNT(*) as total FROM inscriptions;
SELECT 'Paiements:' as '', COUNT(*) as total FROM paiements;
SELECT 'Notifications:' as '', COUNT(*) as total FROM notifications;
SELECT 'Accidents:' as '', COUNT(*) as total FROM accidents;

-- ============================================
-- IDENTIFIANTS DE CONNEXION
-- ============================================
SELECT '' as '';
SELECT '=== IDENTIFIANTS DE TEST ===' as '';
SELECT '' as '';
SELECT 
    CONCAT(prenom, ' ', nom) as 'Nom Complet',
    email as 'Email',
    'test123' as 'Mot de passe',
    role as 'Rôle',
    statut as 'Statut'
FROM utilisateurs
WHERE role IN ('admin', 'chauffeur', 'responsable')
ORDER BY 
    CASE role 
        WHEN 'admin' THEN 1 
        WHEN 'responsable' THEN 2 
        WHEN 'chauffeur' THEN 3 
    END,
    nom;

-- ============================================
-- INFORMATIONS DÉTAILLÉES PAR RÔLE
-- ============================================

SELECT '' as '';
SELECT '=== COMPTES ADMIN ===' as '';
SELECT 
    u.id,
    CONCAT(u.prenom, ' ', u.nom) as nom_complet,
    u.email,
    u.telephone,
    u.statut
FROM utilisateurs u
WHERE u.role = 'admin';

SELECT '' as '';
SELECT '=== COMPTES CHAUFFEURS ===' as '';
SELECT 
    u.id as user_id,
    CONCAT(u.prenom, ' ', u.nom) as nom_complet,
    u.email,
    u.telephone,
    c.numero_permis,
    c.date_expiration_permis,
    c.nombre_accidents,
    c.statut,
    GROUP_CONCAT(DISTINCT b.numero SEPARATOR ', ') as bus_assigne
FROM utilisateurs u
JOIN chauffeurs c ON c.utilisateur_id = u.id
LEFT JOIN bus b ON b.chauffeur_id = c.id
WHERE u.role = 'chauffeur'
GROUP BY u.id, u.prenom, u.nom, u.email, u.telephone, c.numero_permis, c.date_expiration_permis, c.nombre_accidents, c.statut;

SELECT '' as '';
SELECT '=== COMPTES RESPONSABLES ===' as '';
SELECT 
    u.id as user_id,
    CONCAT(u.prenom, ' ', u.nom) as nom_complet,
    u.email,
    u.telephone,
    r.zone_responsabilite,
    r.statut,
    COUNT(DISTINCT b.id) as nombre_bus
FROM utilisateurs u
JOIN responsables_bus r ON r.utilisateur_id = u.id
LEFT JOIN bus b ON b.responsable_id = r.id
WHERE u.role = 'responsable'
GROUP BY u.id, u.prenom, u.nom, u.email, u.telephone, r.zone_responsabilite, r.statut;

