-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 26 août 2025 à 22:02
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
-- Base de données : `aide_fianance`
--

-- --------------------------------------------------------

--
-- Structure de la table `contact`
--

CREATE TABLE `contact` (
  `id_contact` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenoms` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(25) NOT NULL,
  `sujet` varchar(150) NOT NULL,
  `message` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `contact`
--

INSERT INTO `contact` (`id_contact`, `nom`, `prenoms`, `email`, `telephone`, `sujet`, `message`) VALUES
(0, 'SAÏZONOU', 'MAHUDJRO SEGLA EMELIN BRIAND', 'briandsaizonou0@gmail.com', '62910685', 'financement', 'dff');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_financement`
--

CREATE TABLE `demandes_financement` (
  `id` int(11) NOT NULL,
  `type_financement` varchar(70) NOT NULL,
  `civilite` varchar(30) NOT NULL,
  `nom_complet` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `adresse` text NOT NULL,
  `code_postal` varchar(10) NOT NULL,
  `ville` varchar(50) NOT NULL,
  `titre_projet` varchar(200) NOT NULL,
  `description_projet` text NOT NULL,
  `montant_demande` float NOT NULL,
  `duree_projet` varchar(30) NOT NULL,
  `cv_filename` varchar(255) DEFAULT NULL,
  `projet_filename` varchar(255) DEFAULT NULL,
  `statut` varchar(70) DEFAULT 'en_attente',
  `date_soumission` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_mise_a_jour` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id_contact`);

--
-- Index pour la table `demandes_financement`
--
ALTER TABLE `demandes_financement`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email_projet` (`email`,`titre_projet`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `demandes_financement`
--
ALTER TABLE `demandes_financement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
