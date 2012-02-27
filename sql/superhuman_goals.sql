-- phpMyAdmin SQL Dump
-- version 3.4.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 27, 2012 at 08:15 PM
-- Server version: 5.5.8
-- PHP Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `superhuman_goals`
--

-- --------------------------------------------------------

--
-- Table structure for table `dailytests`
--

CREATE TABLE IF NOT EXISTS `dailytests` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `goal_id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `goal_id` (`goal_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `dailytests`
--

INSERT INTO `dailytests` (`id`, `goal_id`, `name`, `description`) VALUES
(3, 15, 'girl likes your shit', 'she''s like "whoa"'),
(4, 16, 'eat everything on SS', '');

-- --------------------------------------------------------

--
-- Table structure for table `dailytests_status`
--

CREATE TABLE IF NOT EXISTS `dailytests_status` (
  `dailytest_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `result` int(11) NOT NULL,
  `entered_at` datetime NOT NULL,
  `entered_at_day` varchar(10) NOT NULL,
  PRIMARY KEY (`dailytest_id`,`user_id`,`entered_at_day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `dailytests_status`
--

INSERT INTO `dailytests_status` (`dailytest_id`, `user_id`, `result`, `entered_at`, `entered_at_day`) VALUES
(3, 8, 1, '2012-02-27 19:55:36', '2012-02-27'),
(4, 8, 1, '2012-02-26 00:00:00', '2012-02-26'),
(4, 8, 1, '2012-02-24 00:00:00', '2012-02-24'),
(4, 8, 1, '2012-02-22 00:00:00', '2012-02-22');

-- --------------------------------------------------------

--
-- Table structure for table `goals`
--

CREATE TABLE IF NOT EXISTS `goals` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

--
-- Dumping data for table `goals`
--

INSERT INTO `goals` (`id`, `name`, `description`) VALUES
(18, 'dfsd', ''),
(4, 'procrastination', ''),
(6, 'sleep', 'get the best sleep you can!'),
(17, 'asd', ''),
(16, 'diet', 'eat right, yo! "ok"'),
(15, 'fashion', 'you not fancy yet, huh?');

-- --------------------------------------------------------

--
-- Table structure for table `goals_group`
--

CREATE TABLE IF NOT EXISTS `goals_group` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `position_index` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `goals_status`
--

CREATE TABLE IF NOT EXISTS `goals_status` (
  `goal_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `level` float NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `is_public` tinyint(1) NOT NULL,
  `position_index` int(11) NOT NULL,
  `goal_group_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`goal_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `goals_status`
--

INSERT INTO `goals_status` (`goal_id`, `user_id`, `level`, `is_active`, `is_public`, `position_index`, `goal_group_id`) VALUES
(4, 8, 6, 1, 1, 0, NULL),
(15, 8, 6.5, 1, 1, 0, NULL),
(16, 8, 8, 1, 1, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stories`
--

CREATE TABLE IF NOT EXISTS `stories` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `type` varchar(20) NOT NULL,
  `event_goal_id` bigint(20) DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL,
  `event_new_level` float DEFAULT NULL,
  `entered_at` datetime NOT NULL,
  `event_letter_score` char(3) DEFAULT NULL,
  `event_description` varchar(500) DEFAULT NULL,
  `event_old_level` float DEFAULT NULL,
  `dailyscore_progress` blob,
  `entered_at_day` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`event_goal_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `stories`
--

INSERT INTO `stories` (`id`, `user_id`, `type`, `event_goal_id`, `is_public`, `event_new_level`, `entered_at`, `event_letter_score`, `event_description`, `event_old_level`, `dailyscore_progress`, `entered_at_day`) VALUES
(4, 8, 'event', 4, 1, 6, '2012-02-27 19:38:09', 'D', 'john ', 2.1, NULL, '2012-02-27'),
(5, 8, 'event', 15, 1, 6.5, '2012-02-27 19:40:58', 'B', 'i''m awesome', 5, NULL, '2012-02-27'),
(6, 8, 'event', 16, 1, 8, '2012-02-27 19:41:44', 'A', 'because i cool', 5, NULL, '2012-02-27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `auth_id` bigint(20) NOT NULL,
  `picture_url` varchar(300) NOT NULL,
  `visit_history` blob,
  `full_name` varchar(100) NOT NULL,
  `last_daily_entry` datetime DEFAULT NULL,
  `daily_entry_story_posted` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `auth_id`, `picture_url`, `visit_history`, `full_name`, `last_daily_entry`, `daily_entry_story_posted`) VALUES
(8, 1, 'http://profile.ak.fbcdn.net/hprofile-ak-snc4/70452_1934291_640441179_q.jpg', 0x613a32303a7b693a303b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337333337323b7d693a313b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337333132333b7d693a323b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337333131313b7d693a333b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337333131303b7d693a343b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337333130393b7d693a353b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337333130383b7d693a363b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337333130373b7d693a373b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337333130363b7d693a383b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337333130353b7d693a393b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337333130343b7d693a31303b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337333130323b7d693a31313b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337333035373b7d693a31323b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337333031363b7d693a31333b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337323838393b7d693a31343b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337323838383b7d693a31353b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337323636393b7d693a31363b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337323636383b7d693a31373b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337323531313b7d693a31383b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337323132363b7d693a31393b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333303337323132323b7d7d, 'Roger Dickey', '2012-02-27 20:05:17', 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
