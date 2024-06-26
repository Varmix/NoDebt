-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Client :  localhost
-- Généré le :  Lun 23 Mai 2022 à 20:24
-- Version du serveur :  5.7.29
-- Version de PHP :  5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `in21b10054`
--

-- --------------------------------------------------------

--
-- Structure de la table `nodebt_caracteriser`
--

CREATE TABLE `nodebt_caracteriser` (
  `did` int(11) NOT NULL,
  `tid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `nodebt_caracteriser`
--

INSERT INTO `nodebt_caracteriser` (`did`, `tid`) VALUES
(134, 131),
(135, 132),
(137, 133),
(138, 134),
(139, 135),
(140, 136),
(141, 137),
(142, 138);

-- --------------------------------------------------------

--
-- Structure de la table `nodebt_depense`
--

CREATE TABLE `nodebt_depense` (
  `did` int(11) NOT NULL,
  `dateHeure` datetime NOT NULL,
  `montant` double NOT NULL DEFAULT '0',
  `libelle` varchar(255) NOT NULL,
  `gid` int(11) NOT NULL,
  `uid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `nodebt_depense`
--

INSERT INTO `nodebt_depense` (`did`, `dateHeure`, `montant`, `libelle`, `gid`, `uid`) VALUES
(134, '2022-05-23 18:48:00', 1, 'Glace', 42, 60),
(135, '2022-05-23 19:08:00', 5, 'Barbarossa', 43, 62),
(136, '2022-05-23 19:08:00', 3, 'Frite', 43, 62),
(137, '2022-05-23 19:08:00', 650, 'Audi RS6', 43, 62),
(138, '2022-05-23 19:11:00', 150, 'Plongée', 43, 62),
(139, '2022-05-23 19:22:00', 125, 'Salomon chaussure', 44, 60),
(140, '2022-05-23 19:22:00', 5, 'Foulard', 44, 60),
(141, '2022-05-23 19:23:00', 15, 'Lampe frontale', 44, 60),
(142, '2022-05-23 19:23:00', 15, 'Fromage', 44, 60),
(143, '2022-05-23 19:24:00', 55, 'Sac', 44, 60);

-- --------------------------------------------------------

--
-- Structure de la table `nodebt_facture`
--

CREATE TABLE `nodebt_facture` (
  `fid` int(11) NOT NULL,
  `scan` text NOT NULL,
  `did` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `nodebt_facture`
--

INSERT INTO `nodebt_facture` (`fid`, `scan`, `did`) VALUES
(27, '628bd02b0f2bb1.39052107.jpg', 137),
(28, '628bd05c583a44.47120712.jpg', 138),
(29, '628bd08c2e8ef2.24804932.jpg', 134);

-- --------------------------------------------------------

--
-- Structure de la table `nodebt_groupe`
--

CREATE TABLE `nodebt_groupe` (
  `gid` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `devise` varchar(255) NOT NULL,
  `uid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `nodebt_groupe`
--

INSERT INTO `nodebt_groupe` (`gid`, `nom`, `devise`, `uid`) VALUES
(42, 'TotoSurSonVelo', 'Euro (€)', 60),
(43, 'GroupeDuRoger', 'Euro (€)', 62),
(44, 'Randonnée', 'Euro (€)', 60);

-- --------------------------------------------------------

--
-- Structure de la table `nodebt_participer`
--

CREATE TABLE `nodebt_participer` (
  `uid` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `estConfirme` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `nodebt_participer`
--

INSERT INTO `nodebt_participer` (`uid`, `gid`, `estConfirme`) VALUES
(60, 42, 1),
(60, 43, 1),
(60, 44, 1),
(62, 42, 0),
(62, 43, 1),
(62, 44, 1);

-- --------------------------------------------------------

--
-- Structure de la table `nodebt_tag`
--

CREATE TABLE `nodebt_tag` (
  `tid` int(11) NOT NULL,
  `tag` varchar(255) DEFAULT NULL,
  `gid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `nodebt_tag`
--

INSERT INTO `nodebt_tag` (`tid`, `tag`, `gid`) VALUES
(131, 'Plage', 42),
(132, 'Cocktail', 43),
(133, 'Location', 43),
(134, 'Activité sportive', 43),
(135, 'Course', 44),
(136, 'Protection', 44),
(137, 'Matériel', 44),
(138, 'Gouda', 44);

-- --------------------------------------------------------

--
-- Structure de la table `nodebt_utilisateur`
--

CREATE TABLE `nodebt_utilisateur` (
  `uid` int(11) NOT NULL,
  `courriel` varchar(255) NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `motPasse` varchar(255) DEFAULT NULL,
  `estActif` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `nodebt_utilisateur`
--

INSERT INTO `nodebt_utilisateur` (`uid`, `courriel`, `nom`, `prenom`, `motPasse`, `estActif`) VALUES
(60, 'toto.laps@yahoo.fr', 'Laps', 'Toto', '$2y$10$k7FCeoTFdMI8.bsuZYaAw.Pg1RmhZe7E5oPSdxz1sFxP9RjchSlTa', 1),
(61, 'e.devlegelaer@student.helmo.be', 'De Vlegelaer', 'Edwin', '$2y$10$thfEqT6cyin9ahvfaAWequP.7O7RVv.Ufub0TWO.8wxb3dogxO3/W', 1),
(62, 'roger.dupont@test.be', 'Dupont', 'Roger', '$2y$10$f3e5sY0yXS36QfmJzkizcuT0KiW6TeLJxrav/CK3fzt38KUmXl0sy', 1);

-- --------------------------------------------------------

--
-- Structure de la table `nodebt_versement`
--

CREATE TABLE `nodebt_versement` (
  `gid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `uid_1` int(11) NOT NULL,
  `dateHeure` datetime NOT NULL,
  `montant` double NOT NULL,
  `estConfirme` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `nodebt_caracteriser`
--
ALTER TABLE `nodebt_caracteriser`
  ADD PRIMARY KEY (`did`,`tid`),
  ADD KEY `tid` (`tid`);

--
-- Index pour la table `nodebt_depense`
--
ALTER TABLE `nodebt_depense`
  ADD PRIMARY KEY (`did`),
  ADD KEY `gid` (`gid`),
  ADD KEY `uid` (`uid`);

--
-- Index pour la table `nodebt_facture`
--
ALTER TABLE `nodebt_facture`
  ADD PRIMARY KEY (`fid`),
  ADD KEY `did` (`did`);

--
-- Index pour la table `nodebt_groupe`
--
ALTER TABLE `nodebt_groupe`
  ADD PRIMARY KEY (`gid`),
  ADD KEY `uid` (`uid`);

--
-- Index pour la table `nodebt_participer`
--
ALTER TABLE `nodebt_participer`
  ADD PRIMARY KEY (`uid`,`gid`),
  ADD KEY `gid` (`gid`);

--
-- Index pour la table `nodebt_tag`
--
ALTER TABLE `nodebt_tag`
  ADD PRIMARY KEY (`tid`),
  ADD KEY `gid` (`gid`);

--
-- Index pour la table `nodebt_utilisateur`
--
ALTER TABLE `nodebt_utilisateur`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `courriel` (`courriel`);

--
-- Index pour la table `nodebt_versement`
--
ALTER TABLE `nodebt_versement`
  ADD PRIMARY KEY (`gid`,`uid`,`uid_1`,`dateHeure`),
  ADD KEY `uid` (`uid`),
  ADD KEY `uid_1` (`uid_1`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `nodebt_depense`
--
ALTER TABLE `nodebt_depense`
  MODIFY `did` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;
--
-- AUTO_INCREMENT pour la table `nodebt_facture`
--
ALTER TABLE `nodebt_facture`
  MODIFY `fid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
--
-- AUTO_INCREMENT pour la table `nodebt_groupe`
--
ALTER TABLE `nodebt_groupe`
  MODIFY `gid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
--
-- AUTO_INCREMENT pour la table `nodebt_tag`
--
ALTER TABLE `nodebt_tag`
  MODIFY `tid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;
--
-- AUTO_INCREMENT pour la table `nodebt_utilisateur`
--
ALTER TABLE `nodebt_utilisateur`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;
--
-- AUTO_INCREMENT pour la table `nodebt_versement`
--
ALTER TABLE `nodebt_versement`
  MODIFY `gid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;
--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `nodebt_caracteriser`
--
ALTER TABLE `nodebt_caracteriser`
  ADD CONSTRAINT `nodebt_caracteriser_ibfk_1` FOREIGN KEY (`did`) REFERENCES `nodebt_depense` (`did`),
  ADD CONSTRAINT `nodebt_caracteriser_ibfk_2` FOREIGN KEY (`tid`) REFERENCES `nodebt_tag` (`tid`);

--
-- Contraintes pour la table `nodebt_depense`
--
ALTER TABLE `nodebt_depense`
  ADD CONSTRAINT `nodebt_depense_ibfk_1` FOREIGN KEY (`gid`) REFERENCES `nodebt_groupe` (`gid`),
  ADD CONSTRAINT `nodebt_depense_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `nodebt_utilisateur` (`uid`);

--
-- Contraintes pour la table `nodebt_facture`
--
ALTER TABLE `nodebt_facture`
  ADD CONSTRAINT `nodebt_facture_ibfk_1` FOREIGN KEY (`did`) REFERENCES `nodebt_depense` (`did`);

--
-- Contraintes pour la table `nodebt_groupe`
--
ALTER TABLE `nodebt_groupe`
  ADD CONSTRAINT `nodebt_groupe_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `nodebt_utilisateur` (`uid`);

--
-- Contraintes pour la table `nodebt_participer`
--
ALTER TABLE `nodebt_participer`
  ADD CONSTRAINT `nodebt_participer_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `nodebt_utilisateur` (`uid`),
  ADD CONSTRAINT `nodebt_participer_ibfk_2` FOREIGN KEY (`gid`) REFERENCES `nodebt_groupe` (`gid`);

--
-- Contraintes pour la table `nodebt_tag`
--
ALTER TABLE `nodebt_tag`
  ADD CONSTRAINT `nodebt_tag_ibfk_1` FOREIGN KEY (`gid`) REFERENCES `nodebt_groupe` (`gid`);

--
-- Contraintes pour la table `nodebt_versement`
--
ALTER TABLE `nodebt_versement`
  ADD CONSTRAINT `nodebt_versement_ibfk_1` FOREIGN KEY (`gid`) REFERENCES `nodebt_groupe` (`gid`),
  ADD CONSTRAINT `nodebt_versement_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `nodebt_utilisateur` (`uid`),
  ADD CONSTRAINT `nodebt_versement_ibfk_3` FOREIGN KEY (`uid_1`) REFERENCES `nodebt_utilisateur` (`uid`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
