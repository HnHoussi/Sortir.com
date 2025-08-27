-- Nettoyage des tables pour éviter les doublons lors des exécutions répétées
-- L'ordre est important en raison des clés étrangères
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE sortie;
TRUNCATE TABLE participant;
TRUNCATE TABLE place;
TRUNCATE TABLE city;
TRUNCATE TABLE campus;
TRUNCATE TABLE status;
SET FOREIGN_KEY_CHECKS = 1;

-- Table 'campus'
INSERT INTO campus (campus_name) VALUES
                              ('ENI Rennes'),
                              ('ENI Quimper'),
                              ('ENI Niort'),
                              ('ENI Saint-Herblain');

-- Table 'status'
INSERT INTO status (status_label) VALUES
                               ('Créée'),
                               ('Ouverte'),
                               ('Fermée'),
                               ('En cours'),
                               ('Terminée'),
                               ('Annulée');

-- Table 'city'
INSERT INTO city (city_name, postal_code) VALUES
                                         ('Nantes', '44000'),
                                         ('Rennes', '35000'),
                                         ('Quimper', '29000');

-- Table 'place'
-- 'city_id' correspond à l'ID de la ville (ex: 1 pour Nantes, 2 pour Rennes, 3 pour Quimper)
INSERT INTO place (place_name, street, latitude, longitude, city_id) VALUES
                                                                   ('Parc de Procé', 'Avenue du Parc', 47.218, -1.589, 1),
                                                                   ('Jardin des Plantes', 'Rue Stanislas Baudry', 47.218, -1.545, 1),
                                                                   ('Parc du Thabor', 'Rue de Paris', 48.113, -1.669, 2),
                                                                   ('Place des Lices', 'Place des Lices', 48.111, -1.681, 2),
                                                                   ('Jardin de la Retraite', 'Rue de la Retraite', 47.994, -4.102, 3);


-- Table 'participant'
-- 'campus_id' correspond à l'ID du campus
-- Les mots de passe ne sont pas hachés ici pour la simplicité, mais en production, ils devraient l'être.
INSERT INTO participant (pseudo, last_name, first_name, phone, email, password, administrator, is_active, campus_id) VALUES
                                                                                                                         ('jdupont', 'Dupont', 'Jean', '0612345678', 'jean.dupont@campus-eni.fr', '$2y$13$4gMcps9IpbRuW5YK.sPGJeUraGulsib0haOQbZneUDVw1cp7Q6ila', 1, 1, 1),
                                                                                                                         ('mlegrand', 'Legrand', 'Marie', '0712345678', 'marie.legrand@campus-eni.fr', '$2y$13$McNoJjQSlwGAZPKsR8qyHe66U9SwdZGJ7b.F3ETT0Rsy6Trv0ep/G', 0, 1, 2),
                                                                                                                         ('psimon', 'Simon', 'Pierre', '0698765432', 'pierre.simon@campus-eni.fr', '$2y$13$McNoJjQSlwGAZPKsR8qyHe66U9SwdZGJ7b.F3ETT0Rsy6Trv0ep/G', 0, 1, 3);


-- Table 'sortie'
-- 'organisateur_id', 'place_id' et 'status_id' doivent correspondre à des IDs existants
-- Le `state` de la sortie doit aussi correspondre à une valeur prédéfinie.
INSERT INTO sortie (name, start_datetime, duration, registration_deadline, max_registrations, description, event_state, photo_url, organizer, place, state, organisateur_id, status_id, cancellation_reason) VALUES
                                                                                                                                                                                                                 ('Visite du Jardin des Plantes', '2025-08-30 14:00:00', 120, '2025-08-28 12:00:00', 10, 'Visite guidée des serres.', 1, NULL, 1, 2, 1, 1, 2, NULL),
                                                                                                                                                                                                                 ('Footing au Parc du Thabor', '2025-09-05 18:00:00', 60, '2025-09-04 17:00:00', 5, 'Course amicale.', 1, NULL, 2, 3, 1, 2, 2, NULL),
                                                                                                                                                                                                                 ('Pique-nique à Procé', '2025-09-10 12:30:00', 90, '2025-09-08 10:00:00', 8, 'Pique-nique convivial.', 1, NULL, 1, 1, 1, 1, 2, NULL);
