-- 🔹 Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- 🔹 Truncate child tables first
TRUNCATE TABLE sortie_user;
TRUNCATE TABLE sortie;
TRUNCATE TABLE user;
TRUNCATE TABLE place;
TRUNCATE TABLE city;
TRUNCATE TABLE campus;
TRUNCATE TABLE status;

-- 🔹 Reset auto-increments
ALTER TABLE campus AUTO_INCREMENT = 1;
ALTER TABLE status AUTO_INCREMENT = 1;
ALTER TABLE city AUTO_INCREMENT = 1;
ALTER TABLE place AUTO_INCREMENT = 1;
ALTER TABLE user AUTO_INCREMENT = 1;
ALTER TABLE sortie AUTO_INCREMENT = 1;

-- 🔹 Enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- 🔹 Insert campuses
INSERT INTO campus (campus_name) VALUES
                                     ('ENI Rennes'),
                                     ('ENI Quimper'),
                                     ('ENI Niort'),
                                     ('ENI Saint-Herblain');

-- 🔹 Insert status
INSERT INTO status (status_label) VALUES
                                      ('Créée'),
                                      ('Ouverte'),
                                      ('Fermée'),
                                      ('En cours'),
                                      ('Terminée'),
                                      ('Annulée'),
                                      ('Archivée');

-- 🔹 Insert cities
INSERT INTO city (city_name, postal_code) VALUES
                                              ('Nantes', '44000'),
                                              ('Rennes', '35000'),
                                              ('Quimper', '29000');

-- 🔹 Insert places
INSERT INTO place (place_name, street, latitude, longitude, city_id) VALUES
                                                                         ('Parc de Procé', 'Avenue du Parc', 47.218, -1.589, 1),
                                                                         ('Jardin des Plantes', 'Rue Stanislas Baudry', 47.218, -1.545, 1),
                                                                         ('Parc du Thabor', 'Rue de Paris', 48.113, -1.669, 2),
                                                                         ('Place des Lices', 'Place des Lices', 48.111, -1.681, 2),
                                                                         ('Jardin de la Retraite', 'Rue de la Retraite', 47.994, -4.102, 3);

-- 🔹 Insert users
INSERT INTO user (pseudo, last_name, first_name, phone, email, password, is_active, campus_id, roles) VALUES
                                                                                                          ('jdupont', 'Dupont', 'Jean', '0612345678', 'jean.dupont@campus-eni.fr', '$2y$13$4gMcps9IpbRuW5YK.sPGJeUraGulsib0haOQbZneUDVw1cp7Q6ila', 1, 1, '["ROLE_ADMIN"]'),
                                                                                                          ('mlegrand', 'Legrand', 'Marie', '0712345678', 'marie.legrand@campus-eni.fr', '$2y$13$McNoJjQSlwGAZPKsR8qyHe66U9SwdZGJ7b.F3ETT0Rsy6Trv0ep/G', 1, 1, '["ROLE_USER"]'),
                                                                                                          ('psimon', 'Simon', 'Pierre', '0698765432', 'pierre.simon@campus-eni.fr', '$2y$13$McNoJjQSlwGAZPKsR8qyHe66U9SwdZGJ7b.F3ETT0Rsy6Trv0ep/G', 1, 3, '["ROLE_USER"]'),
                                                                                                          ('sleberre', 'Le Berre', 'Stève', '0600000000', 'steve.leberre2025@campus-eni.fr', '$2y$13$McNoJjQSlwGAZPKsR8qyHe66U9SwdZGJ7b.F3ETT0Rsy6Trv0ep/G', 1, 3, '["ROLE_ADMIN"]'),
                                                                                                          ('cleroy', 'Leroy', 'Camille', '0623456789', 'camille.leroy@campus-eni.fr', '$2y$13$McNoJjQSlwGAZPKsR8qyHe66U9SwdZGJ7b.F3ETT0Rsy6Trv0ep/G', 1, 2, '["ROLE_USER"]'),
                                                                                                          ('vmoreau', 'Moreau', 'Vincent', '0634567890', 'vincent.moreau@campus-eni.fr', '$2y$13$McNoJjQSlwGAZPKsR8qyHe66U9SwdZGJ7b.F3ETT0Rsy6Trv0ep/G', 1, 2, '["ROLE_ADMIN"]'),
                                                                                                          ('lfaure', 'Faure', 'Laura', '0645678901', 'laura.faure@campus-eni.fr', '$2y$13$McNoJjQSlwGAZPKsR8qyHe66U9SwdZGJ7b.F3ETT0Rsy6Trv0ep/G', 1, 1, '["ROLE_USER"]'),
                                                                                                          ('bmartin', 'Martin', 'Bruno', '0656789012', 'bruno.martin@campus-eni.fr', '$2y$13$McNoJjQSlwGAZPKsR8qyHe66U9SwdZGJ7b.F3ETT0Rsy6Trv0ep/G', 1, 3, '["ROLE_USER"]'),
                                                                                                          ('nblanc', 'Blanc', 'Nadia', '0667890123', 'nadia.blanc@campus-eni.fr', '$2y$13$McNoJjQSlwGAZPKsR8qyHe66U9SwdZGJ7b.F3ETT0Rsy6Trv0ep/G', 1, 2, '["ROLE_ADMIN"]'),
                                                                                                          ('jroux', 'Roux', 'Julien', '0678901234', 'julien.roux@campus-eni.fr', '$2y$13$McNoJjQSlwGAZPKsR8qyHe66U9SwdZGJ7b.F3ETT0Rsy6Trv0ep/G', 1, 1, '["ROLE_USER"]'),
                                                                                                          ('svernier', 'Vernier', 'Sophie', '0689012345', 'sophie.vernier@campus-eni.fr', '$2y$13$McNoJjQSlwGAZPKsR8qyHe66U9SwdZGJ7b.F3ETT0Rsy6Trv0ep/G', 1, 3, '["ROLE_USER"]'),
                                                                                                          ('thubert', 'Hubert', 'Thomas', '0690123456', 'thomas.hubert@campus-eni.fr', '$2y$13$McNoJjQSlwGAZPKsR8qyHe66U9SwdZGJ7b.F3ETT0Rsy6Trv0ep/G', 1, 2, '["ROLE_ADMIN"]');


-- 🔹 Insert sorties
INSERT INTO sortie (
    name, start_datetime, duration, registration_deadline, max_registrations,
    description, photo_url, organizer_id, place_id, status_id, cancellation_reason
) VALUES
      ('Visite du Jardin des Plantes', '2025-08-30 14:00:00', 120, '2025-08-28 12:00:00', 10,
       'Visite guidée des serres.', NULL, 1, 2, 2, NULL),
      ('Footing au Parc du Thabor', '2025-09-05 18:00:00', 60, '2025-09-04 17:00:00', 5,
       'Course amicale.', NULL, 2, 3, 2, NULL),
      ('Pique-nique à Procé', '2025-09-10 12:30:00', 90, '2025-09-08 10:00:00', 8,
       'Pique-nique convivial.', NULL, 1, 1, 2, NULL),
      ('Ancien évènement à Rennes', '2025-07-01 10:00:00', 180, '2025-06-28 10:00:00', 15,
       'Un évènement passé.', NULL, 2, 4, 5, NULL);

-- 🔹 Insert sortie_user (inscriptions)
INSERT INTO sortie_user (sortie_id, user_id) VALUES
                                                 (1, 2),
                                                 (1, 3),
                                                 (2, 1),
                                                 (3, 2);
