-- transport_scolaire.sql - Base de données pour le système de transport scolaire

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS transport_scolaire;
USE transport_scolaire;

-- Table utilisateurs (admins, chauffeurs, responsables, tuteurs)
CREATE TABLE utilisateurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    role ENUM('admin', 'chauffeur', 'responsable', 'tuteur') NOT NULL,
    statut ENUM('Actif', 'Inactif') DEFAULT 'Actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table eleves
CREATE TABLE eleves (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_naissance DATE,
    adresse TEXT,
    telephone_parent VARCHAR(20),
    email_parent VARCHAR(150),
    classe VARCHAR(50),
    tuteur_id INT,
    statut ENUM('Actif', 'Inactif') DEFAULT 'Actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tuteur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- Table chauffeurs
CREATE TABLE chauffeurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT UNIQUE,
    numero_permis VARCHAR(50) UNIQUE NOT NULL,
    date_expiration_permis DATE,
    nombre_accidents INT DEFAULT 0,
    statut ENUM('Actif', 'Licencié', 'Suspendu') DEFAULT 'Actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Table responsables_bus
CREATE TABLE responsables_bus (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT UNIQUE,
    zone_responsabilite VARCHAR(100),
    statut ENUM('Actif', 'Inactif') DEFAULT 'Actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Table bus
CREATE TABLE bus (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero VARCHAR(20) UNIQUE NOT NULL,
    marque VARCHAR(50),
    modele VARCHAR(50),
    annee_fabrication YEAR,
    capacite INT NOT NULL,
    chauffeur_id INT,
    responsable_id INT,
    statut ENUM('Actif', 'En maintenance', 'Hors service') DEFAULT 'Actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chauffeur_id) REFERENCES chauffeurs(id) ON DELETE SET NULL,
    FOREIGN KEY (responsable_id) REFERENCES responsables_bus(id) ON DELETE SET NULL
);

-- Table accidents
CREATE TABLE accidents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    date DATE NOT NULL,
    heure TIME,
    bus_id INT,
    chauffeur_id INT,
    description TEXT NOT NULL,
    degats TEXT,
    lieu VARCHAR(255),
    gravite ENUM('Légère', 'Moyenne', 'Grave') NOT NULL,
    blesses BOOLEAN DEFAULT FALSE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bus_id) REFERENCES bus(id) ON DELETE SET NULL,
    FOREIGN KEY (chauffeur_id) REFERENCES chauffeurs(id) ON DELETE SET NULL
);

-- Table notifications
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    destinataire_id INT NOT NULL,
    destinataire_type ENUM('chauffeur', 'responsable', 'tuteur', 'admin') NOT NULL,
    titre VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'alerte', 'avertissement') DEFAULT 'info',
    lue BOOLEAN DEFAULT FALSE,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table demandes (demandes d'inscription, modifications, etc.)
CREATE TABLE demandes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    eleve_id INT,
    tuteur_id INT NOT NULL,
    type_demande ENUM('inscription', 'modification', 'desinscription') NOT NULL,
    description TEXT,
    statut ENUM('En attente', 'Approuvée', 'Rejetée') DEFAULT 'En attente',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_traitement TIMESTAMP NULL,
    traite_par INT,
    FOREIGN KEY (eleve_id) REFERENCES eleves(id) ON DELETE CASCADE,
    FOREIGN KEY (tuteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (traite_par) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- Table inscriptions
CREATE TABLE inscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    eleve_id INT NOT NULL,
    bus_id INT,
    date_inscription DATE NOT NULL,
    date_debut DATE,
    date_fin DATE,
    statut ENUM('Active', 'Suspendue', 'Terminée') DEFAULT 'Active',
    montant_mensuel DECIMAL(10,2),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (eleve_id) REFERENCES eleves(id) ON DELETE CASCADE,
    FOREIGN KEY (bus_id) REFERENCES bus(id) ON DELETE SET NULL
);

-- Table paiements
CREATE TABLE paiements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    inscription_id INT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    mois INT NOT NULL,
    annee YEAR NOT NULL,
    date_paiement DATE NOT NULL,
    mode_paiement ENUM('Espèces', 'Virement', 'Carte bancaire', 'Chèque') DEFAULT 'Espèces',
    statut ENUM('Payé', 'En attente', 'Échoué') DEFAULT 'Payé',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inscription_id) REFERENCES inscriptions(id) ON DELETE CASCADE
);

-- Insérer des données d'exemple
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, statut) VALUES
('Admin', 'Principal', 'admin@transport-scolaire.ma', '$2y$10$hashedpassword', '0612345678', 'admin', 'Actif'),
('Dupont', 'Jean', 'jean.dupont@transport-scolaire.ma', '$2y$10$hashedpassword', '0612345679', 'chauffeur', 'Actif'),
('Martin', 'Marie', 'marie.martin@transport-scolaire.ma', '$2y$10$hashedpassword', '0612345680', 'responsable', 'Actif'),
('Dubois', 'Pierre', 'pierre.dubois@email.com', '$2y$10$hashedpassword', '0612345681', 'tuteur', 'Actif');

INSERT INTO chauffeurs (utilisateur_id, numero_permis, date_expiration_permis, nombre_accidents, statut) VALUES
(2, 'PERMIS123456', '2025-12-31', 0, 'Actif');

INSERT INTO responsables_bus (utilisateur_id, zone_responsabilite, statut) VALUES
(3, 'Zone Centre', 'Actif');

INSERT INTO bus (numero, marque, modele, annee_fabrication, capacite, chauffeur_id, responsable_id, statut) VALUES
('BUS-001', 'Mercedes', 'Sprinter', 2020, 50, 1, 1, 'Actif'),
('BUS-002', 'Volvo', '9700', 2019, 45, NULL, 1, 'Actif');

INSERT INTO eleves (nom, prenom, date_naissance, adresse, telephone_parent, email_parent, classe, tuteur_id, statut) VALUES
('Dubois', 'Alice', '2010-05-15', '123 Rue de la Paix, Casablanca', '0612345681', 'pierre.dubois@email.com', 'CM2', 4, 'Actif');

INSERT INTO inscriptions (eleve_id, bus_id, date_inscription, date_debut, date_fin, statut, montant_mensuel) VALUES
(1, 1, '2024-01-01', '2024-01-15', '2024-06-30', 'Active', 500.00);

-- Créer des index pour améliorer les performances
CREATE INDEX idx_utilisateurs_email ON utilisateurs(email);
CREATE INDEX idx_utilisateurs_role ON utilisateurs(role);
CREATE INDEX idx_eleves_tuteur ON eleves(tuteur_id);
CREATE INDEX idx_bus_chauffeur ON bus(chauffeur_id);
CREATE INDEX idx_bus_responsable ON bus(responsable_id);
CREATE INDEX idx_accidents_bus ON accidents(bus_id);
CREATE INDEX idx_accidents_chauffeur ON accidents(chauffeur_id);
CREATE INDEX idx_notifications_destinataire ON notifications(destinataire_id, destinataire_type);
CREATE INDEX idx_demandes_statut ON demandes(statut);
CREATE INDEX idx_inscriptions_bus ON inscriptions(bus_id);
CREATE INDEX idx_paiements_inscription ON paiements(inscription_id);