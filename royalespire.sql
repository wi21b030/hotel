-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 09. Jan 2023 um 19:53
-- Server-Version: 10.4.27-MariaDB
-- PHP-Version: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `royalespire`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(260) NOT NULL,
  `uploadtime` int(50) NOT NULL,
  `text` text NOT NULL,
  `path` varchar(260) NOT NULL,
  `keyword` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Daten für Tabelle `news`
--

INSERT INTO `news` (`id`, `title`, `uploadtime`, `text`, `path`, `keyword`) VALUES
(58, 'Erster Beitrag', 1673224399, 'Unser Team!', 'uploads/news/pic/Erster Beitrag.jpg', 'Team'),
(59, 'Zweiter Beitrag', 1673224871, 'Die besten Sehenswürdigkeiten Wien', 'uploads/news/pic/Zweiter Beitrag.jpg', 'Sightseeing Wien'),
(60, 'Dritter Post', 1673225005, 'Wien bietet eine Vielzahl an ästhetischen Bars!', 'uploads/news/pic/Dritter Post.jpg', 'Bars Wien'),
(61, 'Vierter Post', 1673289829, 'Hier sind gute Restaurants!', 'uploads/news/pic/Vierter Post.jpg', 'Restaurant Wien');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `reservation`
--

CREATE TABLE `reservation` (
  `id` int(10) NOT NULL,
  `checkin` date NOT NULL,
  `checkout` date NOT NULL,
  `breakfast` varchar(100) NOT NULL,
  `parking` varchar(100) NOT NULL,
  `pet` varchar(100) NOT NULL,
  `time` int(20) NOT NULL,
  `user_id` int(10) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Neu',
  `total` int(10) NOT NULL,
  `nights` int(4) NOT NULL,
  `room` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Daten für Tabelle `reservation`
--

INSERT INTO `reservation` (`id`, `checkin`, `checkout`, `breakfast`, `parking`, `pet`, `time`, `user_id`, `status`, `total`, `nights`, `room`) VALUES
(155, '2023-01-01', '2023-01-02', 'Nein', 'Nein', 'Kein', 1673289599, 34, 'Neu', 50, 1, 11),
(156, '2023-01-01', '2023-01-02', 'Nein', 'Nein', 'Kein', 1673289608, 34, 'Neu', 50, 1, 12),
(157, '2023-01-01', '2023-01-02', 'Nein', 'Nein', 'Kein', 1673289616, 34, 'Neu', 50, 1, 13),
(158, '2023-01-01', '2023-01-02', 'Nein', 'Nein', 'Kein', 1673289628, 34, 'Neu', 50, 1, 14),
(159, '2023-01-01', '2023-01-02', 'Nein', 'Nein', 'Kein', 1673289638, 34, 'Neu', 50, 1, 15);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rooms`
--

CREATE TABLE `rooms` (
  `room_number` int(3) NOT NULL,
  `type` varchar(10) NOT NULL,
  `rate` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Daten für Tabelle `rooms`
--

INSERT INTO `rooms` (`room_number`, `type`, `rate`) VALUES
(11, 'Single', 50),
(12, 'Single', 50),
(13, 'Single', 50),
(14, 'Single', 50),
(15, 'Single', 50),
(16, 'Double', 80),
(17, 'Double', 80),
(18, 'Double', 80),
(19, 'Double', 80),
(20, 'Double', 80);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `username` varchar(50) NOT NULL,
  `password` varchar(260) NOT NULL,
  `useremail` varchar(100) NOT NULL,
  `formofadress` varchar(30) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `secondname` varchar(50) NOT NULL,
  `path` varchar(260) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`id`, `admin`, `active`, `username`, `password`, `useremail`, `formofadress`, `firstname`, `secondname`, `path`) VALUES
(23, 1, 1, 'admin', '$2y$10$Pgf7Rd4eqE1l3A8sdQAE2.USLzQFsj36/4GuBakz6dALR1WYxKPvO', 'wi21b030@technikum-wien.at', '1', 'Safwan', 'Zullash', 'uploads/profilepics/admin.jpg'),
(34, 0, 1, 'hadi', '$2y$10$GDEPE51C/4LzEbAoZVH8QuOSWWfSJIHPaIPgV4/PpTgzzER7ixQ0i', 'hadi@gmail.com', '1', 'Hadi', 'Heydari', 'uploads/profilepics/hadi.jpg'),
(37, 0, 1, 'kevin', '$2y$10$QxoriboPg0kq8V9FbMWTUOAtt9E70UrRJpk1UBsU.MXnuTdiCVN4.', 'kevin@xhunga.at', '2', 'Kevin', 'Xhunga', 'uploads/profilepics/kevin.jpg');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `title` (`title`);

--
-- Indizes für die Tabelle `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_number`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT für Tabelle `reservation`
--
ALTER TABLE `reservation`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
