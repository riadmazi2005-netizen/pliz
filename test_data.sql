-- Données de test pour le système de transport scolaire
-- À exécuter APRÈS transport_scolaire.sql

USE transport_scolaire;

-- Nettoyer les données existantes (optionnel)
-- TRUNCATE TABLE notifications;
-- TRUNCATE TABLE demandes;
-- TRUNCATE TABLE paiements;
-- TRUNCATE TABLE inscriptions;
-- TRUNCATE TABLE eleves;
-- DELETE FROM utilisateurs WHERE role != 'admin';

-- 1. Créer des utilisateurs de test
-- Mot de passe pour tous : "test123" (haché)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut) VALUES
-- Admin de test
('Admin', 'Système', 'admin@transport.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612345678', 'admin', 'Actif'),

-- Tuteurs de test
('Alami', 'Mohammed', 'mohammed.alami@email.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612345679', 'tuteur', 'Actif'),
('Benjelloun', 'Fatima', 'fatima.benjelloun@email.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612345680', 'tuteur', 'Actif'),
('El Amrani', 'Hassan', 'hassan.elamrani@email.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612345681', 'tuteur', 'Actif'),

-- Chauffeurs de test
('Idrissi', 'Ahmed', 'ahmed.idrissi@transport.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612345682', 'chauffeur', 'Actif'),
('Tazi', 'Youssef', 'youssef.tazi@transport.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612345683', 'chauffeur', 'Actif'),

-- Responsables de test
('Kettani', 'Nadia', 'nadia.kettani@transport.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612345684', 'responsable', 'Actif'),
('Fassi', 'Omar', 'omar.fassi@transport.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612345685', 'responsable', 'Actif');

-- 2. Créer les chauffeurs
INSERT INTO chauffeurs (utilisateur_id, numero_permis, date_expiration_permis, nombre_accidents, statut) VALUES
(5, 'CH-123456', '2026-12-31', 0, 'Actif'),
(6, 'CH-789012', '2027-06-30', 1, 'Actif');

-- 3. Créer les responsables bus
INSERT INTO responsables_bus (utilisateur_id, zone_responsabilite, statut) VALUES
(7, 'Zone Centre', 'Actif'),
(8, 'Zone Nord', 'Actif');

-- 4. Créer des trajets
INSERT INTO trajets (nom, zones, heure_depart_matin_a, heure_arrivee_matin_a, heure_depart_soir_a, heure_arrivee_soir_a, heure_depart_matin_b, heure_arrivee_matin_b, heure_depart_soir_b, heure_arrivee_soir_b) VALUES
('Trajet Centre', '["Maarif", "Gauthier", "2 Mars"]', '07:30:00', '08:00:00', '17:00:00', '17:30:00', '08:00:00', '08:30:00', '17:30:00', '18:00:00'),
('Trajet Nord', '["Sidi Maarouf", "Californie", "Oasis"]', '07:30:00', '08:00:00', '17:00:00', '17:30:00', '08:00:00', '08:30:00', '17:30:00', '18:00:00'),
('Trajet Sud', '["Hay Hassani", "Oulfa", "Sbata"]', '07:30:00', '08:00:00', '17:00:00', '17:30:00', '08:00:00', '08:30:00', '17:30:00', '18:00:00');

-- 5. Créer des bus
INSERT INTO bus (numero, marque, modele, annee_fabrication, capacite, chauffeur_id, responsable_id, statut) VALUES
('BUS-001', 'Mercedes', 'Sprinter', 2020, 50, 1, 1, 'Actif'),
('BUS-002', 'Volvo', '9700', 2019, 45, 2, 1, 'Actif'),
('BUS-003', 'Iveco', 'Daily', 2021, 35, NULL, 2, 'Actif');

-- Associer les bus aux trajets (ajouter colonne trajet_id si elle n'existe pas)
-- ALTER TABLE bus ADD COLUMN trajet_id INT;
-- UPDATE bus SET trajet_id = 1 WHERE id = 1;
-- UPDATE bus SET trajet_id = 2 WHERE id = 2;
-- UPDATE bus SET trajet_id = 3 WHERE id = 3;

-- 6. Créer des élèves pour les tuteurs
INSERT INTO eleves (nom, prenom, date_naissance, adresse, telephone_parent, email_parent, classe, tuteur_id, statut) VALUES
-- Élèves de Mohammed Alami (tuteur_id = 2)
('Alami', 'Yasmine', '2012-03-15', '123 Rue Maarif, Casablanca', '0612345679', 'mohammed.alami@email.ma', 'CE2', 2, 'Actif'),
('Alami', 'Karim', '2014-09-22', '123 Rue Maarif, Casablanca', '0612345679', 'mohammed.alami@email.ma', 'CP', 2, 'Actif'),

-- Élèves de Fatima Benjelloun (tuteur_id = 3)
('Benjelloun', 'Salma', '2011-06-10', '45 Boulevard Gauthier, Casablanca', '0612345680', 'fatima.benjelloun@email.ma', 'CM1', 3, 'Actif'),

-- Élèves de Hassan El Amrani (tuteur_id = 4)
('El Amrani', 'Amine', '2013-01-20', '78 Avenue 2 Mars, Casablanca', '0612345681', 'hassan.elamrani@email.ma', 'CE1', 4, 'Inactif');

-- 7. Créer des inscriptions pour les élèves actifs
INSERT INTO inscriptions (eleve_id, bus_id, date_inscription, date_debut, date_fin, statut, montant_mensuel) VALUES
(1, 1, '2024-09-01', '2024-09-15', '2025-06-30', 'Active', 400.00),
(2, 1, '2024-09-01', '2024-09-15', '2025-06-30', 'Active', 400.00),
(3, 2, '2024-09-01', '2024-09-15', '2025-06-30', 'Active', 400.00);

-- 8. Créer quelques demandes
INSERT INTO demandes (eleve_id, tuteur_id, type_demande, description, statut) VALUES
(4, 4, 'inscription', 'Demande d\'inscription pour Amine El Amrani', 'En attente'),
(1, 2, 'modification', 'Changement de zone pour Yasmine', 'En attente');

-- 9. Créer des notifications
INSERT INTO notifications (destinataire_id, destinataire_type, titre, message, type, lue) VALUES
-- Pour les tuteurs
(2, 'tuteur', 'Bienvenue', 'Bienvenue sur la plateforme de transport scolaire Mohammed 5', 'info', FALSE),
(2, 'tuteur', 'Inscription validée', 'L\'inscription de Yasmine a été validée', 'info', TRUE),
(3, 'tuteur', 'Paiement reçu', 'Votre paiement de 400 DH a été reçu', 'info', FALSE),
(4, 'tuteur', 'Demande en cours', 'Votre demande d\'inscription est en cours de traitement', 'info', FALSE),

-- Pour l'admin
(1, 'admin', 'Nouvelle inscription', 'Nouvelle demande d\'inscription reçue', 'info', FALSE),
(1, 'admin', 'Paiement en attente', '3 paiements en attente de validation', 'alerte', FALSE);

-- 10. Créer quelques paiements
INSERT INTO paiements (inscription_id, montant, mois, annee, date_paiement, mode_paiement, statut) VALUES
(1, 400.00, 9, 2024, '2024-09-05', 'Virement', 'Payé'),
(1, 400.00, 10, 2024, '2024-10-05', 'Virement', 'Payé'),
(2, 400.00, 9, 2024, '2024-09-05', 'Espèces', 'Payé'),
(3, 400.00, 9, 2024, '2024-09-07', 'Carte bancaire', 'Payé'),
(1, 400.00, 11, 2024, '2024-11-05', 'Virement', 'En attente');

-- Afficher un résumé
SELECT '=== RÉSUMÉ DES DONNÉES DE TEST ===' as '';
SELECT 'Utilisateurs:' as '', COUNT(*) as total FROM utilisateurs;
SELECT 'Tuteurs:' as '', COUNT(*) as total FROM utilisateurs WHERE role = 'tuteur';
SELECT 'Chauffeurs:' as '', COUNT(*) as total FROM chauffeurs;
SELECT 'Responsables:' as '', COUNT(*) as total FROM responsables_bus;
SELECT 'Élèves:' as '', COUNT(*) as total FROM eleves;
SELECT 'Bus:' as '', COUNT(*) as total FROM bus;
SELECT 'Trajets:' as '', COUNT(*) as total FROM trajets;
SELECT 'Inscriptions:' as '', COUNT(*) as total FROM inscriptions;
SELECT 'Demandes:' as '', COUNT(*) as total FROM demandes;

-- Afficher les identifiants de connexion
SELECT '=== IDENTIFIANTS DE TEST ===' as '';
SELECT 
    CONCAT(prenom, ' ', nom) as Nom,
    email,
    'test123' as mot_de_passe,
    role
FROM utilisateurs
WHERE role IN ('tuteur', 'chauffeur', 'responsable', 'admin')
ORDER BY role, nom;