-- Structure de la table `enterprise_activities`
CREATE TABLE `enterprise_activities` (
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

-- Insertion de données d'exemple
INSERT INTO `enterprise_activities` (`name`, `description`, `price`, `price_type`, `category`, `image`) VALUES
('Escape Game Entreprise', 'Résolvez des énigmes en équipe et développez vos compétences collaboratives dans notre escape game spécial entreprise.', 750.00, 'DT / groupe', 'team-building', 'images/escape.jpg'),
('Challenge Paintball', 'Stratégie, communication et esprit d\'équipe sont les clés de cette activité ludique en plein air.', 650.00, 'DT / groupe', 'team-building', 'images/paintb.jpg'),
('Atelier Cuisine d\'Équipe', 'Préparez un repas gastronomique en équipe sous la direction d\'un chef professionnel.', 850.00, 'DT / groupe', 'team-building', 'images/cuisine.jpg'),
('Magicien Professionnel', 'Surprenez vos invités avec les tours bluffants de notre magicien expert en close-up et mentalisme.', 500.00, 'DT / prestation', 'animation', 'images/magicien.jpg'); 