-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 10 mai 2025 à 18:16
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `clickngo`
--

-- --------------------------------------------------------

--
-- Structure de la table `annonce_covoiturage`
--

CREATE TABLE `annonce_covoiturage` (
  `id_conducteur` int(11) NOT NULL,
  `prenom_conducteur` varchar(255) NOT NULL,
  `nom_conducteur` varchar(255) NOT NULL,
  `tel_conducteur` varchar(255) NOT NULL,
  `date_depart` datetime NOT NULL,
  `lieu_depart` varchar(255) NOT NULL,
  `lieu_arrivee` varchar(255) NOT NULL,
  `nombre_places` int(11) NOT NULL,
  `prix_estime` float NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `date_modification` datetime DEFAULT NULL,
  `statut` enum('active','archivée') DEFAULT 'active',
  `image` varchar(255) DEFAULT NULL,
  `type_voiture` varchar(255) DEFAULT NULL,
  `likes` int(11) DEFAULT 0,
  `dislikes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `annonce_covoiturage`
--

INSERT INTO `annonce_covoiturage` (`id_conducteur`, `prenom_conducteur`, `nom_conducteur`, `tel_conducteur`, `date_depart`, `lieu_depart`, `lieu_arrivee`, `nombre_places`, `prix_estime`, `description`, `status`, `date_creation`, `date_modification`, `statut`, `image`, `type_voiture`, `likes`, `dislikes`) VALUES
(23, 'Joee', 'Goldberg', '55436637', '2025-05-15 20:00:00', 'Hammamet', 'Nabeul', 2, 10, 'Hello YOU ', 'disponible', '2025-05-08 18:23:32', NULL, 'active', NULL, 'Bmw', 0, 0),
(24, 'Berlin', 'Alonso', '23678943', '2025-05-16 12:05:00', 'Carthage', 'Le Bardo', 3, 5, '', 'disponible', '2025-05-08 20:55:59', NULL, 'active', NULL, 'Mercedes', 1, 2),
(25, 'Abir', 'Dhaker', '56603286', '2025-05-21 23:18:00', 'Ariana', 'Tabarka', 4, 20, '', 'disponible', '2025-05-08 21:19:20', NULL, 'active', NULL, 'Lamborghini', 0, 0),
(29, 'firas', 'haous', '55436637', '2025-05-23 21:34:00', 'Nabeul', 'Mahdia', 4, 15, '', 'disponible', '2025-05-09 16:34:54', NULL, 'active', NULL, 'CLIO', 5, 1);

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `id_avis` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `commentaire` text NOT NULL,
  `note` float NOT NULL,
  `auteur` varchar(255) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  `id_conducteur` int(11) NOT NULL,
  `id_passager` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demande_covoiturage`
--

CREATE TABLE `demande_covoiturage` (
  `id_passager` int(11) NOT NULL,
  `prenom_passager` varchar(255) NOT NULL,
  `nom_passager` varchar(255) NOT NULL,
  `tel_passager` varchar(255) NOT NULL,
  `id_conducteur` int(11) DEFAULT NULL,
  `date_demande` datetime NOT NULL,
  `status_demande` varchar(255) NOT NULL,
  `nbr_places_reservees` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `moyen_paiement` varchar(255) NOT NULL,
  `prix_total` float DEFAULT NULL,
  `date_creation` datetime NOT NULL,
  `date_modification` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demande_covoiturage`
--

INSERT INTO `demande_covoiturage` (`id_passager`, `prenom_passager`, `nom_passager`, `tel_passager`, `id_conducteur`, `date_demande`, `status_demande`, `nbr_places_reservees`, `message`, `moyen_paiement`, `prix_total`, `date_creation`, `date_modification`) VALUES
(35, 'lovee', 'Goldberg', '23074500', 23, '2025-05-08 20:55:15', 'approuvé', 2, 'I did it for love', 'espèces', NULL, '2025-05-08 20:55:15', '2025-05-09 22:26:48');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `annonce_covoiturage`
--
ALTER TABLE `annonce_covoiturage`
  ADD PRIMARY KEY (`id_conducteur`);

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`id_avis`),
  ADD KEY `fk_avis_conducteur` (`id_conducteur`),
  ADD KEY `fk_passager` (`id_passager`);

--
-- Index pour la table `demande_covoiturage`
--
ALTER TABLE `demande_covoiturage`
  ADD PRIMARY KEY (`id_passager`),
  ADD KEY `fk_conducteur` (`id_conducteur`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `annonce_covoiturage`
--
ALTER TABLE `annonce_covoiturage`
  MODIFY `id_conducteur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT pour la table `avis`
--
ALTER TABLE `avis`
  MODIFY `id_avis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `demande_covoiturage`
--
ALTER TABLE `demande_covoiturage`
  MODIFY `id_passager` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `fk_avis_conducteur` FOREIGN KEY (`id_conducteur`) REFERENCES `annonce_covoiturage` (`id_conducteur`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_passager` FOREIGN KEY (`id_passager`) REFERENCES `demande_covoiturage` (`id_passager`);

--
-- Contraintes pour la table `demande_covoiturage`
--
ALTER TABLE `demande_covoiturage`
  ADD CONSTRAINT `fk_conducteur` FOREIGN KEY (`id_conducteur`) REFERENCES `annonce_covoiturage` (`id_conducteur`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
