-- Création de la base de données
CREATE DATABASE IF NOT EXISTS `clickngo_db`;
USE `clickngo_db`;

-- Structure de la table `activities`
CREATE TABLE IF NOT EXISTS `activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `location` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `category` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Structure de la table `enterprise_activities`
CREATE TABLE IF NOT EXISTS `enterprise_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `price_type` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insertion de données d'exemple pour activities
INSERT INTO `activities` (`name`, `description`, `price`, `location`, `date`, `category`, `capacity`, `image`) VALUES
('Yoga en plein air', 'Séance de yoga en plein air dans un cadre naturel relaxant', 25.00, 'Parc Belvédère', '2025-06-15 09:00:00', 'bien-etre', 20, 'images/yoga.jpg'),
('Randonnée pédestre', 'Randonnée guidée à travers les plus beaux sentiers naturels', 15.00, 'Montagnes de Zaghouan', '2025-06-20 08:30:00', 'sport', 15, 'images/randonnee.jpg'),
('Visite guidée de Carthage', 'Découvrez l\'histoire fascinante de l\'ancienne Carthage', 35.00, 'Site archéologique de Carthage', '2025-06-25 10:00:00', 'culture', 25, 'images/carthage.jpg'),
('Cours de plongée', 'Initiation à la plongée sous-marine en Méditerranée', 75.00, 'Tabarka', '2025-07-05 14:00:00', 'Aquatique', 10, 'images/plongee.jpg');

-- Insertion de données d'exemple pour enterprise_activities
INSERT INTO `enterprise_activities` (`name`, `description`, `price`, `price_type`, `category`, `image`) VALUES
('Escape Game Entreprise', 'Résolvez des énigmes en équipe et développez vos compétences collaboratives dans notre escape game spécial entreprise.', 750.00, 'DT / groupe', 'team-building', 'images/escape.jpg'),
('Challenge Paintball', 'Stratégie, communication et esprit d\'équipe sont les clés de cette activité ludique en plein air.', 650.00, 'DT / groupe', 'team-building', 'images/paintb.jpg'),
('Atelier Cuisine d\'Équipe', 'Préparez un repas gastronomique en équipe sous la direction d\'un chef professionnel.', 850.00, 'DT / groupe', 'team-building', 'images/cuisine.jpg'),
('Magicien Professionnel', 'Surprenez vos invités avec les tours bluffants de notre magicien expert en close-up et mentalisme.', 500.00, 'DT / prestation', 'animation', 'images/magicien.jpg'),
('Séminaire Hôtel 5 étoiles', 'Salles de conférence entièrement équipées dans un cadre luxueux avec service de restauration inclus.', 3500.00, 'DT / jour', 'seminaire', 'images/hotel.jpg'),
('Salle de Réunion Premium', 'Espace moderne équipé de la dernière technologie audiovisuelle pour des réunions productives.', 350.00, 'DT / demi-journée', 'reunion', 'images/salle.jpg'),
('Soirée de Gala', 'Une soirée élégante avec dîner gastronomique et animations haut de gamme.', 7500.00, 'DT / soirée', 'soiree', 'images/gala.jpg'),
('Restaurant Gastronomique', 'Un repas raffiné dans un restaurant étoilé avec menu personnalisé pour votre entreprise.', 120.00, 'DT / personne', 'repas', 'images/restaurant.jpg'),
('Olympiades d\'Entreprise', 'Une journée de défis sportifs et ludiques pour stimuler l\'esprit d\'équipe et la compétition saine.', 3500.00, 'DT / journée', 'fundays', 'images/olympiades.jpg'),
('Voyage Incentive', 'Organisation de voyages de récompense ou de motivation pour vos équipes ou vos meilleurs clients.', 0.00, 'Sur devis', 'projets-sur-mesure', 'images/incentive.jpg'); 