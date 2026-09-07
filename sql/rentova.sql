-- =====================================================================
--  ÉQUIPLOC — Base de données (MySQL / MariaDB, encodage UTF-8)
--  Structure + données de démonstration
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS equipements;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS utilisateurs;

-- ------------------------- UTILISATEURS -------------------------
CREATE TABLE utilisateurs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nom        VARCHAR(100) NOT NULL,
    prenom     VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    telephone  VARCHAR(30)  NULL,
    adresse    VARCHAR(255) NULL,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('client','agent','responsable') NOT NULL DEFAULT 'client',
    photo      VARCHAR(255) NULL,
    cree_le    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------- CATEGORIES -------------------------
CREATE TABLE categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    image       VARCHAR(255) NULL,
    cree_le     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------- EQUIPEMENTS -------------------------
CREATE TABLE equipements (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    categorie_id     INT NOT NULL,
    nom              VARCHAR(150) NOT NULL,
    description      TEXT NULL,
    marque           VARCHAR(100) NULL,
    modele           VARCHAR(100) NULL,
    prix_jour        DECIMAL(10,2) NOT NULL DEFAULT 0,
    en_promo         TINYINT(1) NOT NULL DEFAULT 0,
    prix_promo       DECIMAL(10,2) NULL DEFAULT NULL,
    stock_total      INT NOT NULL DEFAULT 0,
    stock_disponible INT NOT NULL DEFAULT 0,
    seuil_alerte     INT NOT NULL DEFAULT 0,
    image            VARCHAR(255) NULL,
    etat             ENUM('disponible','en_location','maintenance','endommage') NOT NULL DEFAULT 'disponible',
    cree_le          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_equip_cat FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_equip_cat (categorie_id),
    INDEX idx_equip_stock (stock_disponible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------- LOCATIONS -------------------------
CREATE TABLE locations (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    reference            VARCHAR(20) NOT NULL UNIQUE,
    client_id            INT NOT NULL,
    equipement_id        INT NOT NULL,
    agent_id             INT NULL,
    date_debut           DATE NOT NULL,
    date_fin             DATE NOT NULL,
    duree                INT NOT NULL,
    quantite             INT NOT NULL DEFAULT 1,
    montant_base         DECIMAL(10,2) NOT NULL DEFAULT 0,
    montant_frais        DECIMAL(10,2) NOT NULL DEFAULT 0,
    montant_total        DECIMAL(10,2) NOT NULL DEFAULT 0,
    statut               ENUM('en_attente','confirmee','en_cours','terminee','annulee','refusee') NOT NULL DEFAULT 'en_attente',
    date_retour_effectif DATE NULL,
    condition_retour     VARCHAR(30) NULL,
    commentaire_retour   TEXT NULL,
    note                 VARCHAR(255) NULL,
    cree_le              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_loc_client  FOREIGN KEY (client_id)     REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT fk_loc_equip   FOREIGN KEY (equipement_id) REFERENCES equipements(id) ON DELETE RESTRICT,
    CONSTRAINT fk_loc_agent   FOREIGN KEY (agent_id)      REFERENCES utilisateurs(id) ON DELETE SET NULL,
    INDEX idx_loc_statut (statut),
    INDEX idx_loc_periode (date_debut, date_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
--  DONNÉES DE DÉMONSTRATION
--  Mots de passe par défaut :  password123  (pour tous les comptes)
-- =====================================================================

INSERT INTO utilisateurs (nom, prenom, email, telephone, adresse, password, role) VALUES
('Diallo',    'Amadou',  'admin@Rentova.com',     '06 12 34 56 78', '12 rue de la République, Paris',   '$2y$10$5hgND0D6tDZNzVm5C7mcP.Z.3ldAiZQPq/.KI4D0saXDnmxB5vCke', 'responsable'),
('Martin',    'Claire',  'agent@Rentova.com',     '06 98 76 54 32', '8 avenue des Champs, Lyon',       '$2y$10$5hgND0D6tDZNzVm5C7mcP.Z.3ldAiZQPq/.KI4D0saXDnmxB5vCke', 'agent'),
('Bernard',   'Sophie',  'client@Rentova.com',    '07 11 22 33 44', '23 boulevard Victor Hugo, Lille', '$2y$10$5hgND0D6tDZNzVm5C7mcP.Z.3ldAiZQPq/.KI4D0saXDnmxB5vCke', 'client'),
('Dubois',    'Marc',    'marc.dubois@gmail.com',  '06 55 44 33 22', '15 rue Pasteur, Nantes',          '$2y$10$5hgND0D6tDZNzVm5C7mcP.Z.3ldAiZQPq/.KI4D0saXDnmxB5vCke', 'client'),
('Petit',     'Julie',   'julie.petit@gmail.com',  '07 88 99 00 11', '4 place du Marché, Bordeaux',     '$2y$10$5hgND0D6tDZNzVm5C7mcP.Z.3ldAiZQPq/.KI4D0saXDnmxB5vCke', 'client'),
('Moreau',    'Thomas',  'thomas.moreau@gmail.com','06 23 45 67 89', '31 rue des Lilas, Toulouse',      '$2y$10$5hgND0D6tDZNzVm5C7mcP.Z.3ldAiZQPq/.KI4D0saXDnmxB5vCke', 'client');

INSERT INTO categories (nom, description, image) VALUES
('Bâtiment & Construction',      'Échafaudages, bétonnières, marteaux-piqueurs et tout le matériel nécessaire à vos chantiers.', 'categories/cat_batiment.jpg'),
('Outillage Électroportatif',    'Perceuses, meuleuses, scies : l\'outillage de précision pour vos travaux.',                       'categories/cat_outillage.jpg'),
('Espaces Verts & Jardinage',    'Tondeuses, débroussailleuses, taille-haies pour l\'entretien de vos extérieurs.',               'categories/cat_espaces_verts.jpg'),
('Événementiel & Sonorisation',  'Sono, éclairage de scène et structures pour vos événements et animations.',                     'categories/cat_evenementiel.jpg'),
('Nettoyage Professionnel',      'Nettoyeurs haute pression, monobrosses : l\'hygiène et la propreté au rendez-vous.',            'categories/cat_nettoyage.jpg'),
('Engins & Manutention',         'Mini-pelles, transpalettes, nacelles pour porter et déplacer lourd en toute sécurité.',          'categories/cat_engins.jpg');

INSERT INTO equipements (categorie_id, nom, description, marque, modele, prix_jour, stock_total, stock_disponible, seuil_alerte, image, etat) VALUES
(1, 'Échafaudage roulant',   'Échafaudage roulant 2,50 m en aluminium, stabilisateurs intégrés. Idéal pour les travaux en hauteur en toute sécurité.', 'ALTRAD', 'TS60',         45.00, 6, 6, 1, 'equipements/eq_echafaudage.webp', 'disponible'),
(1, 'Bétonnière 350 L',      'Bétonnière thermique de 350 litres avec cuve en acier, démarrage électrique et roue de secours.',                      'BETONMAST', 'STAR 350',  75.00, 4, 4, 1, 'equipements/eq_betonniere.jpg', 'disponible'),
(1, 'Marteau-piqueur',       'Marteau-piqueur électrique 1500 W, poignée antivibratoire et burins fournis. Puissance et endurance.',                'MAKITA', 'HM1501',       65.00, 5, 5, 1, 'equipements/eq_marteau.jpg', 'disponible'),
(2, 'Perceuse-visseuse sans fil', 'Perceuse-visseuse 20 V 2 batteries, coffret 50 accessoires. Polyvalente pour percer et visser partout.',            'BOSCH', 'GSR 18V-60',   25.00, 10, 10, 2, 'equipements/eq_perceuse.jpg', 'disponible'),
(2, 'Meuleuse d\'angle',     'Meuleuse d\'angle 230 mm, 2200 W, démarrage progressif, disques diamant fournis.',                                     'BOSCH', 'GWS 22-230',    30.00, 8, 8, 2, 'equipements/eq_meuleuse.webp', 'disponible'),
(2, 'Scie circulaire',       'Scie circulaire 1650 W, 190 mm, guide de coupe laser pour des coupes parfaites.',                                      'MAKITA', '5604R',        28.00, 7, 7, 2, 'equipements/eq_scie.avif', 'disponible'),
(3, 'Tondeuse à gazon thermique', 'Tondeuse autoportée 100 cm, 20 CV, bac de ramassage 200 L. Parfaite pour les grandes surfaces.',                    'JOHN DEERE', 'Z335E',    120.00, 3, 3, 1, 'equipements/eq_tondeuse.webp', 'disponible'),
(3, 'Débroussailleuse',      'Débroussailleuse thermique 42 cm, tête nylon + lame 3 dents, bretelles de portage.',                                    'STIHL', 'FS 460 C-EM',  35.00, 9, 9, 2, 'equipements/eq_debroussailleuse.jpg', 'disponible'),
(3, 'Taille-haie',           'Taille-haie thermique 70 cm, double lame, ergonomie renforcée pour une coupe nette.',                                   'STIHL', 'HS 82 R',       32.00, 8, 8, 2, 'equipements/eq_taillehaie.jpg', 'disponible'),
(4, 'Sonorisation 400 W',    'Kit sono complet 2 enceintes + table de mixage + micros, parfait pour réunions et soirées.',                              'YAMAHA', 'StagePas 400i', 90.00, 4, 4, 1, 'equipements/eq_sono.jpg', 'disponible'),
(4, 'Éclairage de scène LED','6 projecteurs LED 12 W, commande DMX, fumigène inclus. Habillez vos soirées d\'ambiances lumineuses.',                   'SHOWTEC', 'LED 12x12',   70.00, 5, 5, 1, 'equipements/eq_eclairage.jpg', 'disponible'),
(4, 'Barnum 3x6 m',          'Barnum professionnel 3x6 m, montage rapide, résistant au vent, inclut les bâches latérales.',                           'ABRIEVENT', 'FESTIVAL 3X6', 40.00, 6, 6, 1, 'equipements/eq_barnum.webp', 'disponible'),
(5, 'Nettoyeur haute pression','Nettoyeur haute pression 160 bar, 2100 W, lance turbo et détergent inclus.',                                           'KARCHER', 'K7 Premium',   55.00, 6, 6, 1, 'equipements/eq_nettoyeur.jpg', 'disponible'),
(5, 'Monobrosse',            'Monobrosse 600 W, 43 cm, 3 brosses, idéale pour sols durs, moquettes et locaux professionnels.',                      'NILFISK', 'SC 320',       48.00, 5, 5, 1, 'equipements/eq_monobrosse.webp', 'disponible'),
(6, 'Transpalette manuel',   'Transpalette manuel 2500 kg, fourches 1,15 m, roues nylon, manœuvre précise en entrepôt.',                              'HYSTER', 'P2.5',         22.00, 8, 8, 2, 'equipements/eq_transpalette.png', 'disponible'),
(6, 'Mini-pelle',            'Mini-pelle 1,5 t, godet + brise-roche, chenilles caoutchouc, sans permis requis (CC).',                                 'KUBOTA', 'U17-3',        190.00, 2, 2, 1, 'equipements/eq_minipelle.jpg', 'disponible');

-- Locations de démonstration (statuts variés)
INSERT INTO locations (reference, client_id, equipement_id, agent_id, date_debut, date_fin, duree, quantite, montant_base, montant_frais, montant_total, statut, date_retour_effectif, condition_retour, note) VALUES
(CONCAT('LOC-', DATE_FORMAT(NOW(), '%y%m'), '-0001'), 3, 4,  2, DATE_ADD(CURDATE(), INTERVAL -6 DAY),  DATE_ADD(CURDATE(), INTERVAL -2 DAY),  5, 1, 125.00, 0,    125.00, 'terminee', DATE_ADD(CURDATE(), INTERVAL -2 DAY), 'bon',       'Demande traitée rapidement.'),
(CONCAT('LOC-', DATE_FORMAT(NOW(), '%y%m'), '-0002'), 4, 1,  2, DATE_ADD(CURDATE(), INTERVAL -4 DAY),  DATE_ADD(CURDATE(), INTERVAL 3 DAY),   8, 1, 360.00, 0,    360.00, 'confirmee', NULL, NULL, 'Chantier façade.'),
(CONCAT('LOC-', DATE_FORMAT(NOW(), '%y%m'), '-0003'), 5, 10, 2, DATE_ADD(CURDATE(), INTERVAL -1 DAY),  DATE_ADD(CURDATE(), INTERVAL 2 DAY),   4, 1, 360.00, 0,    360.00, 'en_cours', NULL, NULL, 'Soirée d\'entreprise.'),
(CONCAT('LOC-', DATE_FORMAT(NOW(), '%y%m'), '-0004'), 6, 7,  NULL, DATE_ADD(CURDATE(), INTERVAL 3 DAY),  DATE_ADD(CURDATE(), INTERVAL 6 DAY), 4, 1, 480.00, 0,    480.00, 'en_attente', NULL, NULL, NULL),
(CONCAT('LOC-', DATE_FORMAT(NOW(), '%y%m'), '-0005'), 3, 13, 2, DATE_ADD(CURDATE(), INTERVAL -15 DAY), DATE_ADD(CURDATE(), INTERVAL -8 DAY),  8, 1, 440.00, 45,  485.00, 'terminee', DATE_ADD(CURDATE(), INTERVAL -6 DAY), 'use',        'Retour avec 2 jours de retard, frais appliqués.'),
(CONCAT('LOC-', DATE_FORMAT(NOW(), '%y%m'), '-0006'), 4, 16, 2, DATE_ADD(CURDATE(), INTERVAL -3 DAY),  DATE_ADD(CURDATE(), INTERVAL 4 DAY),   8, 1, 1520.00, 0,   1520.00, 'en_cours', NULL, NULL, 'Terrassement allée.');
