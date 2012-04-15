-- phpMyAdmin SQL Dump
-- version 3.4.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 15, 2012 at 03:00 PM
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
-- Table structure for table `auth_sg_banned`
--

CREATE TABLE IF NOT EXISTS `auth_sg_banned` (
  `entry` varchar(100) NOT NULL,
  `type` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`entry`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `auth_sg_login_log`
--

CREATE TABLE IF NOT EXISTS `auth_sg_login_log` (
  `email` varchar(100) DEFAULT NULL,
  `date` varchar(20) DEFAULT NULL,
  `time` varchar(20) DEFAULT NULL,
  `ip_addr` varchar(20) DEFAULT NULL,
  `oper_sys` varchar(20) DEFAULT NULL,
  `brow` varchar(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `auth_sg_login_log`
--

INSERT INTO `auth_sg_login_log` (`email`, `date`, `time`, `ip_addr`, `oper_sys`, `brow`) VALUES
('adamgries@gmail.com', '2012-03-28', '21:19', '::1', 'MAC', 'FireFox'),
('adamgries@gmail.com', '2012-03-31', '07:59', '::1', 'MAC', 'FireFox'),
('adamgries@gmail.com', '2012-04-06', '20:30', '::1', 'MAC', 'unknown'),
('adamgries@gmail.com', '2012-04-06', '21:18', '::1', 'MAC', 'FireFox'),
('adamgries@gmail.com', '2012-04-06', '21:20', '::1', 'MAC', 'FireFox'),
('adamgries@gmail.com', '2012-04-07', '21:34', '::1', 'MAC', 'unknown'),
('adamgries@gmail.com', '2012-04-07', '21:34', '::1', 'MAC', 'unknown'),
('adamgries@gmail.com', '2012-04-07', '21:55', '::1', 'MAC', 'FireFox'),
('adamgries@gmail.com', '2012-04-08', '01:56', '::1', 'MAC', 'unknown'),
('adamgries@gmail.com', '2012-04-08', '09:05', '::1', 'MAC', 'FireFox'),
('adamgries@gmail.com', '2012-04-08', '16:19', '::1', 'MAC', 'unknown'),
('adamgries@gmail.com', '2012-04-09', '01:35', '::1', 'MAC', 'FireFox'),
('adamgries@gmail.com', '2012-04-09', '05:24', '::1', 'MAC', 'FireFox'),
('adamgries@gmail.com', '2012-04-09', '05:31', '::1', 'MAC', 'FireFox'),
('adamgries@gmail.com', '2012-04-09', '20:16', '::1', 'MAC', 'FireFox'),
('adamgries@gmail.com', '2012-04-09', '22:47', '::1', 'MAC', 'unknown'),
('roger@productx.co', '2012-04-10', '05:06', '192.168.1.213', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-10', '05:19', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-10', '05:30', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-10', '05:35', '127.0.0.1', 'Windows', 'unknown'),
('roger+test1@productx.co', '2012-04-10', '05:36', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-10', '05:43', '127.0.0.1', 'Windows', 'unknown'),
('roger+test2@productx.co', '2012-04-10', '05:44', '127.0.0.1', 'Windows', 'unknown'),
('roger+test4@productx.co', '2012-04-10', '05:47', '127.0.0.1', 'Windows', 'unknown'),
('roger+test4@productx.co', '2012-04-10', '05:53', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-10', '06:05', '192.168.1.213', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-10', '06:15', '192.168.1.213', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-10', '06:15', '192.168.1.213', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-10', '21:00', '192.168.1.213', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-10', '21:25', '192.168.1.213', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-10', '21:28', '192.168.1.213', 'Windows', 'unknown'),
('roger+test5@productx.co', '2012-04-10', '22:37', '127.0.0.1', 'Windows', 'unknown'),
('roger+test5@productx.co', '2012-04-10', '22:38', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-11', '05:10', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-11', '22:08', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-12', '01:05', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-13', '05:49', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-13', '05:49', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-13', '05:49', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-13', '06:56', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-13', '06:58', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-13', '06:59', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-13', '07:04', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-13', '20:52', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-13', '22:58', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-13', '22:58', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-13', '22:58', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-15', '01:59', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-15', '02:00', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-15', '02:00', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-15', '02:01', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-15', '02:02', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-15', '04:27', '127.0.0.1', 'Windows', 'unknown'),
('roger@productx.co', '2012-04-15', '04:39', '127.0.0.1', 'Windows', 'unknown');

-- --------------------------------------------------------

--
-- Table structure for table `auth_sg_trash`
--

CREATE TABLE IF NOT EXISTS `auth_sg_trash` (
  `firstname` varchar(20) DEFAULT NULL,
  `lastname` varchar(20) DEFAULT NULL,
  `username` varchar(20) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `group1` varchar(20) DEFAULT NULL,
  `group2` varchar(20) DEFAULT NULL,
  `group3` varchar(20) DEFAULT NULL,
  `pchange` varchar(1) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `redirect` varchar(100) DEFAULT NULL,
  `verified` varchar(1) DEFAULT NULL,
  `last_login` date DEFAULT NULL,
  `del_date` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `auth_sg_users`
--

CREATE TABLE IF NOT EXISTS `auth_sg_users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(20) NOT NULL,
  `lastname` varchar(20) DEFAULT NULL,
  `extradata` blob,
  `password` varchar(50) NOT NULL,
  `group1` varchar(20) DEFAULT NULL,
  `group2` varchar(20) DEFAULT NULL,
  `group3` varchar(20) DEFAULT NULL,
  `pchange` tinyint(1) NOT NULL,
  `email` varchar(100) NOT NULL,
  `verified` tinyint(1) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `auth_sg_users`
--

INSERT INTO `auth_sg_users` (`id`, `firstname`, `lastname`, `extradata`, `password`, `group1`, `group2`, `group3`, `pchange`, `email`, `verified`, `last_login`) VALUES
(4, 'Adam', 'Gries', 0x613a313a7b733a31303a227069637475726555524c223b733a34313a22687474703a2f2f646c2e64726f70626f782e636f6d2f752f373632393430302f66627069632e6a7067223b7d, '*281BE344E7A71A8C881968E5DB935E384FB9B279', 'Users', '', '', 0, 'adamgries@gmail.com', 1, '2012-04-09 15:47:59'),
(7, 'Roger', 'Dickey', 0x613a313a7b733a31303a227069637475726555524c223b733a34313a22687474703a2f2f646c2e64726f70626f782e636f6d2f752f373632393430302f66627069632e6a7067223b7d, '*1BF3116A5372A85B80F3769F62A5162B482C00EE', 'Users', '', '', 0, 'roger@productx.co', 1, '2012-04-14 21:39:50'),
(8, 'Roger', 'D', 0x613a313a7b733a31303a227069637475726555524c223b733a34313a22687474703a2f2f646c2e64726f70626f782e636f6d2f752f373632393430302f66627069632e6a7067223b7d, '*1BF3116A5372A85B80F3769F62A5162B482C00EE', 'Users', '', '', 0, 'roger+test1@productx.co', 1, '2012-04-09 22:36:18'),
(9, 'Roger', 'Dickey', 0x613a313a7b733a31303a227069637475726555524c223b733a34313a22687474703a2f2f646c2e64726f70626f782e636f6d2f752f373632393430302f66627069632e6a7067223b7d, '*1BF3116A5372A85B80F3769F62A5162B482C00EE', 'Users', '', '', 0, 'roger+test2@productx.co', 1, '2012-04-09 22:44:35'),
(10, 'Roger', 'D', 0x613a313a7b733a31303a227069637475726555524c223b733a34313a22687474703a2f2f646c2e64726f70626f782e636f6d2f752f373632393430302f66627069632e6a7067223b7d, '*1BF3116A5372A85B80F3769F62A5162B482C00EE', 'Users', '', '', 0, 'roger+test4@productx.co', 1, '2012-04-09 22:53:57'),
(11, 'Roger', 'D5', 0x613a313a7b733a31303a227069637475726555524c223b733a34313a22687474703a2f2f646c2e64726f70626f782e636f6d2f752f373632393430302f66627069632e6a7067223b7d, '*1BF3116A5372A85B80F3769F62A5162B482C00EE', 'Users', '', '', 0, 'roger+test5@productx.co', 1, '2012-04-10 15:38:09');

-- --------------------------------------------------------

--
-- Table structure for table `goals`
--

CREATE TABLE IF NOT EXISTS `goals` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=42 ;

--
-- Dumping data for table `goals`
--

INSERT INTO `goals` (`id`, `name`, `description`, `created_by`, `is_active`, `date_created`) VALUES
(1, 'Sleep', 'Achieve ultimate sleep. ', 9, 0, '2012-03-26 00:42:49'),
(2, 'Have a great body', 'be the one girls like to look at', 9, 0, '2012-03-26 00:42:49'),
(4, 'Improve at Meditation (Quantity and Quality)', 'Achieve ultimate mindfulness through consistent meditation practice. Increase focus, reduce reflexive cognitive/emotional reactions, reduce decline in pre-frontal cortex volume.', 9, 0, '2012-03-26 00:42:49'),
(11, 'Money', 'Anything relating to finances, saving, investments, net worth or money.', 9, 1, '2012-04-02 22:16:10'),
(12, 'Career', 'Anything to do with work, professional development, jobs, management.', 9, 1, '2012-04-02 22:21:03'),
(13, 'Productivity', 'Anything that raises you effectiveness / efficiency.', 9, 1, '2012-04-02 22:28:21'),
(14, 'Lifestyle', 'Goals that have to do with achieving your ultimate lifestyle.', 9, 1, '2012-04-02 22:28:46'),
(15, 'Mood', 'Anything that relates to how you feel day to day and overall.', 9, 1, '2012-04-02 22:29:36'),
(16, 'Learning', 'Anything related to education, gaining mastery, intellectual improvement.', 9, 1, '2012-04-02 22:44:46'),
(17, 'Skills', 'Mastery in a particular practical realm.', 9, 1, '2012-04-02 22:45:22'),
(18, 'Leadership', 'Building charisma, influence and leadership skills.', 9, 1, '2012-04-02 23:00:44'),
(19, 'Relationships', 'Interpersonal communication, social skills, social understanding, emotional intelligence.', 9, 1, '2012-04-02 23:01:29'),
(20, 'Personal Brand', 'Present yourself and communicate with the world in the best way possible.', 9, 1, '2012-04-02 23:02:07'),
(21, 'Fashion / Looks', 'Be really really good looking :)', 9, 1, '2012-04-02 23:03:28'),
(22, 'Meditation', 'Achieve better balance, focus and liberation from suffering.', 9, 1, '2012-04-02 23:04:12'),
(23, 'Weightloss', 'Lose the weight you want to reach the body and feeling you desire.', 9, 1, '2012-04-02 23:04:54'),
(24, 'Nutrition', 'Devise the optimal nutrition for your body type.', 9, 1, '2012-04-02 23:05:13'),
(25, 'Fitness', 'Running, sports, building muscles, endurance, constitution, optimal physical performance and health.', 9, 1, '2012-04-02 23:05:53'),
(26, 'Energy', 'Have the energy level you desire as much of the time as possible.', 9, 1, '2012-04-02 23:06:19'),
(27, 'Health', 'Be healthful in any way you desire.', 9, 1, '2012-04-02 23:06:52'),
(28, 'Sleep', 'Achieve optimal sleep. Be refreshed and rejuvenated while spending the amount of time you''d like on sleep.', 9, 1, '2012-04-02 23:07:22'),
(29, 'Experiences', 'Tasting the richness of what life has to offer in any way.', 9, 1, '2012-04-02 23:07:45'),
(30, 'Happiness', 'Achieve your happiness goals.', 9, 1, '2012-04-02 23:08:23'),
(31, 'Other', 'Any other type of goal', 9, 1, '2012-04-02 23:08:39'),
(32, 'Reading and Writing', 'Anything to do with reading and writing', 9, 1, '2012-04-04 16:18:15'),
(33, 'Focus / Self Control', '', 9, 1, '2012-04-04 16:27:33'),
(34, 'Confidence', '', 10, 1, '2012-04-13 14:53:06'),
(35, 'Entrepreneurship', 'Grow as an entrepreneur in every way', 10, 1, '2012-04-13 14:57:08'),
(36, 'Angel Investing', '', 10, 1, '2012-04-13 14:59:42'),
(37, 'Professional Network', '', 10, 1, '2012-04-13 15:03:02'),
(38, 'Learn Chinese', '', 10, 1, '2012-04-13 15:22:12'),
(39, 'Does This WOrk', 'yo bro', 10, 1, '2012-04-14 01:49:22'),
(40, 'test2', '', 10, 1, '2012-04-14 17:43:35'),
(41, 'test3', '', 10, 1, '2012-04-15 04:54:27');

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
  `description` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL,
  `is_public` tinyint(1) NOT NULL,
  `position_index` int(11) NOT NULL,
  `goal_group_id` bigint(20) DEFAULT NULL,
  `display_style` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `latest_change` datetime DEFAULT NULL,
  UNIQUE KEY `goal_id` (`goal_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `goals_status`
--

INSERT INTO `goals_status` (`goal_id`, `user_id`, `level`, `description`, `is_active`, `is_public`, `position_index`, `goal_group_id`, `display_style`, `date_created`, `latest_change`) VALUES
(1, 9, 5, 'wake up refreshed', 0, 1, 0, NULL, 0, '2012-03-26 00:43:27', '2012-04-02 21:54:01'),
(4, 9, 5, 'have a body like zach efrons', 0, 1, 0, NULL, 0, '2012-03-26 01:48:59', '2012-04-02 18:48:00'),
(2, 9, 5, 'have a body like zach efrons', 0, 1, 0, NULL, 1, '2012-03-26 01:25:20', '2012-04-02 22:11:34'),
(11, 9, 5, 'Net worth $5 Million', 1, 1, 0, NULL, 0, '2012-04-02 22:46:17', '2012-04-09 15:51:15'),
(12, 9, 5, '', 0, 1, 0, NULL, 0, '2012-04-02 22:54:04', '2012-04-02 23:08:54'),
(25, 9, 5, 'have epicnessss''s', 1, 1, 0, NULL, 0, '2012-04-02 23:14:38', '2012-04-05 15:53:53'),
(32, 9, 5, 'Improve my reading and writing', 1, 1, 0, NULL, 0, '2012-04-04 16:18:33', '2012-04-04 16:44:43'),
(28, 9, 5, 'Wake up refreshed and stay energized throughout the day on as little sleep as sustainable.', 1, 1, 0, NULL, 0, '2012-04-04 16:24:26', '2012-04-04 16:25:15'),
(33, 9, 5, '', 1, 1, 0, NULL, 0, '2012-04-04 16:27:38', '2012-04-04 16:27:38'),
(22, 9, 5, '', 1, 1, 0, NULL, 0, '2012-04-04 16:30:55', '2012-04-04 16:30:55'),
(13, 9, 5, 'Plan better', 1, 1, 0, NULL, 0, '2012-04-04 16:33:50', '2012-04-08 18:44:30'),
(17, 9, 5, '', 1, 1, 0, NULL, 0, '2012-04-04 16:36:40', '2012-04-04 16:36:40'),
(31, 9, 5, '', 1, 1, 0, NULL, 0, '2012-04-04 16:38:09', '2012-04-04 16:38:09'),
(20, 9, 5, '', 1, 1, 0, NULL, 0, '2012-04-04 16:39:57', '2012-04-04 16:39:57'),
(13, 10, 7, '', 1, 1, 0, NULL, 0, '2012-04-10 15:43:05', '2012-04-13 00:29:31'),
(19, 10, 6, '', 1, 1, 0, NULL, 0, '2012-04-10 15:44:03', '2012-04-15 03:40:29'),
(20, 10, 3, '', 1, 1, 0, NULL, 0, '2012-04-10 15:44:09', '2012-04-15 00:41:47'),
(21, 10, 3, '', 1, 1, 0, NULL, 0, '2012-04-10 15:44:14', '2012-04-15 01:37:01'),
(25, 10, 5, '', 1, 1, 0, NULL, 0, '2012-04-10 15:44:22', '2012-04-10 15:44:22'),
(26, 10, 5, '', 1, 1, 0, NULL, 0, '2012-04-10 15:44:30', '2012-04-10 15:44:30'),
(32, 10, 5, '', 1, 1, 0, NULL, 0, '2012-04-10 15:44:51', '2012-04-10 15:44:51'),
(33, 10, 6, '', 1, 1, 0, NULL, 0, '2012-04-10 15:44:58', '2012-04-15 00:44:04'),
(12, 10, 5, '', 0, 1, 0, NULL, 0, '2012-04-11 18:12:24', '2012-04-13 15:43:17'),
(38, 10, 5, '', 1, 1, 0, NULL, 0, '2012-04-14 01:20:56', '2012-04-14 01:20:56'),
(37, 10, 5, '', 1, 1, 0, NULL, 0, '2012-04-14 01:21:03', '2012-04-14 01:21:03'),
(36, 10, 5, '', 1, 1, 0, NULL, 0, '2012-04-14 01:21:06', '2012-04-14 01:21:06'),
(35, 10, 5, '', 1, 1, 0, NULL, 0, '2012-04-14 01:21:09', '2012-04-14 01:21:09'),
(34, 10, 5, '', 1, 1, 0, NULL, 0, '2012-04-14 01:21:16', '2012-04-14 01:21:16'),
(28, 10, 5, '', 1, 1, 0, NULL, 0, '2012-04-14 01:21:24', '2012-04-14 01:21:24'),
(18, 10, 5, '', 1, 1, 0, NULL, 0, '2012-04-14 01:21:31', '2012-04-14 01:21:31'),
(39, 10, 5, '', 1, 1, 0, NULL, 0, '2012-04-14 01:49:28', '2012-04-14 01:49:28'),
(40, 10, 5, '', 0, 1, 0, NULL, 0, '2012-04-14 17:43:41', '2012-04-14 17:43:43'),
(41, 10, 5, '', 0, 1, 0, NULL, 0, '2012-04-15 04:55:00', '2012-04-15 04:55:17');

-- --------------------------------------------------------

--
-- Table structure for table `goals_to_kpis`
--

CREATE TABLE IF NOT EXISTS `goals_to_kpis` (
  `goal_id` bigint(20) NOT NULL DEFAULT '0',
  `kpi_id` bigint(20) DEFAULT NULL,
  `associated_by` bigint(20) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  UNIQUE KEY `goal_id` (`goal_id`,`kpi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `goals_to_kpis`
--

INSERT INTO `goals_to_kpis` (`goal_id`, `kpi_id`, `associated_by`, `date_created`) VALUES
(1, 1, 9, '2012-03-26 00:58:59'),
(2, 2, 9, '2012-03-26 00:58:59'),
(3, 3, 9, '2012-03-26 00:58:59'),
(4, 4, 9, '2012-03-26 00:58:59'),
(4, 29, 9, '2012-03-26 00:58:59'),
(7, 62, 9, '2012-03-30 19:18:15'),
(25, 63, 9, '2012-04-02 23:16:01'),
(25, 64, 9, '2012-04-02 23:16:53'),
(25, 65, 9, '2012-04-02 23:17:41'),
(25, 66, 9, '2012-04-02 23:18:47'),
(25, 67, 9, '2012-04-02 23:20:05'),
(25, 68, 9, '2012-04-02 23:21:33'),
(25, 69, 9, '2012-04-02 23:23:10'),
(25, 70, 9, '2012-04-02 23:25:17'),
(25, 71, 9, '2012-04-02 23:37:44'),
(32, 72, 9, '2012-04-04 16:20:28'),
(32, 73, 9, '2012-04-04 16:21:59'),
(28, 74, 9, '2012-04-04 16:25:41'),
(33, 75, 9, '2012-04-04 16:28:20'),
(22, 76, 9, '2012-04-04 16:31:16'),
(13, 77, 9, '2012-04-04 16:34:59'),
(17, 78, 9, '2012-04-04 16:37:22'),
(31, 79, 9, '2012-04-04 16:38:51'),
(20, 80, 9, '2012-04-04 16:40:20'),
(20, 81, 9, '2012-04-04 16:40:42'),
(20, 82, 9, '2012-04-04 16:40:52'),
(20, 83, 9, '2012-04-04 16:41:15'),
(20, 84, 9, '2012-04-04 16:41:37'),
(20, 85, 9, '2012-04-04 16:41:47'),
(20, 86, 9, '2012-04-04 16:42:08'),
(32, 87, 9, '2012-04-04 16:45:06');

-- --------------------------------------------------------

--
-- Table structure for table `kpis`
--

CREATE TABLE IF NOT EXISTS `kpis` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `kpi_name` varchar(256) DEFAULT NULL,
  `kpi_desc` varchar(256) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=88 ;

--
-- Dumping data for table `kpis`
--

INSERT INTO `kpis` (`id`, `kpi_name`, `kpi_desc`, `created_by`, `date_created`) VALUES
(1, 'Wake up refreshed 80% of days', 'Feel like a champ when you wake up', 9, '2012-03-08 12:01:17'),
(2, '13% Body Fat', 'Get them fats off', 9, '2012-03-08 12:19:19'),
(3, '13% Body Fat', 'Get them fats off', 9, '2012-03-08 12:20:50'),
(4, '80% Performance on Daily Meditation Adherence Test', 'Make sure I verify I have been meditating every day and hold myself accountable if I do not', 9, '2012-03-08 12:45:29'),
(29, 'Ability to sit for 60 minutes straight', 'Self Explanatory', 9, '2012-03-21 09:41:06'),
(63, '13% Body Fat', '', 9, '2012-04-02 23:16:01'),
(64, '8 x Bench Press 270lbs', '', 9, '2012-04-02 23:16:53'),
(65, '8 x Squat 360lbs', '', 9, '2012-04-02 23:17:41'),
(66, '8 x Deadlift 270lbs', '', 9, '2012-04-02 23:18:47'),
(67, '100 Push ups straight', '', 9, '2012-04-02 23:20:05'),
(68, 'Run 10K in 45min', '', 9, '2012-04-02 23:21:33'),
(69, 'Great bloodwork indicators', '', 9, '2012-04-02 23:23:10'),
(70, '80% High Energy Days', '', 9, '2012-04-02 23:25:17'),
(71, 'Achieve 185lbs bodyweight', '', 9, '2012-04-02 23:37:44'),
(72, 'Create 24 posts on a blog and get an average of 3 comments per post', '', 9, '2012-04-04 16:20:28'),
(73, 'Reading speed above 1000/w/minute', '', 9, '2012-04-04 16:21:59'),
(74, '80% performance on the my wake up refreshed habit', '', 9, '2012-04-04 16:25:41'),
(75, '80% performance on the question: Did I procrastinate today?', '', 9, '2012-04-04 16:28:20'),
(76, 'Log at least 300 hours of vipassana in 2012', '', 9, '2012-04-04 16:31:16'),
(77, '80% performance on the Plan Day in the Morning habit', '', 9, '2012-04-04 16:34:59'),
(78, 'Improve typing speed by 50%', '', 9, '2012-04-04 16:37:22'),
(79, 'Have a comprehensive document outlining citizenship/permanent-residence information for all relevant countries', '', 9, '2012-04-04 16:38:51'),
(80, 'Be happy with all content up on my public profiles', '', 9, '2012-04-04 16:40:20'),
(81, 'Lower unfriends/month by 50% comparing first to last quarter of 2012', '', 9, '2012-04-04 16:40:42'),
(82, 'Be invited to speak at two major events', '', 9, '2012-04-04 16:40:52'),
(83, 'Be invited to participate as judge or speaker at 4 events', '', 9, '2012-04-04 16:41:15'),
(84, 'Receive advisory shares for 3 companies I believe in', '', 9, '2012-04-04 16:41:37'),
(85, 'Lower unanswered emails by 50% comparing first and last quarters 2012', '', 9, '2012-04-04 16:41:47'),
(86, 'Have a beautiful blog', '', 9, '2012-04-04 16:42:08'),
(87, 'Complete 2012 media list', '', 9, '2012-04-04 16:45:06');

-- --------------------------------------------------------

--
-- Table structure for table `kpi_log`
--

CREATE TABLE IF NOT EXISTS `kpi_log` (
  `user_id` bigint(20) NOT NULL DEFAULT '0',
  `kpi_test_id` bigint(20) DEFAULT NULL,
  `goal_id` bigint(20) DEFAULT NULL,
  `kpi_id` bigint(20) DEFAULT NULL,
  `performance` bigint(20) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `kpi_tests`
--

CREATE TABLE IF NOT EXISTS `kpi_tests` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `test_description` varchar(256) DEFAULT NULL,
  `test_name` varchar(256) DEFAULT NULL,
  `kpi_id` bigint(20) DEFAULT NULL,
  `test_frequency` bigint(20) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=100 ;

--
-- Dumping data for table `kpi_tests`
--

INSERT INTO `kpi_tests` (`id`, `test_description`, `test_name`, `kpi_id`, `test_frequency`, `created_by`, `date_created`) VALUES
(1, 'DEXA body scan', NULL, 0, 90, 9, '2012-03-26 01:09:06'),
(2, 'Review my log for the aggregate reporting on the refreshedness I had upon waking', NULL, 1, 30, 9, '2012-03-26 01:09:06'),
(4, 'use the best tool to evaluate body fat %', 'DEXA Scan', 3, 90, 9, '2012-03-26 01:09:06'),
(5, 'check my adherence stats', 'Adherence test review', 4, 30, 9, '2012-03-26 01:09:06'),
(6, 'Bench press 200lbs', 'Bench press 200lbs', 3, 30, 9, '2012-03-26 01:09:06'),
(41, 'self explanatory', 'sit motionless for 60 minutes', 29, 30, 9, '2012-03-26 01:09:06'),
(42, 'look at the logs and verify sitting', 'Check insight timer logs', 4, 30, 9, '2012-03-26 01:09:06'),
(75, 'A scan exposing bodymass by tissue type', 'DEXA', 63, 90, 9, '2012-04-02 23:16:01'),
(76, '', '', 64, 30, 9, '2012-04-02 23:16:53'),
(77, '', '', 65, 30, 9, '2012-04-02 23:17:41'),
(78, '', '', 66, 30, 9, '2012-04-02 23:18:47'),
(79, '', '', 67, 30, 9, '2012-04-02 23:20:05'),
(80, '', '', 68, 30, 9, '2012-04-02 23:21:33'),
(81, '', 'Spectrocell', 69, 90, 9, '2012-04-02 23:23:10'),
(82, '', 'Daily report', 70, 30, 9, '2012-04-02 23:25:17'),
(83, '', 'Withings scale', 71, 90, 9, '2012-04-02 23:37:44'),
(84, '', '', 72, 30, 9, '2012-04-04 16:20:28'),
(85, '', 'Test myself on the quick reader ipad app', 73, 30, 9, '2012-04-04 16:21:59'),
(86, '', '', 74, 30, 9, '2012-04-04 16:25:41'),
(87, '', '', 75, 30, 9, '2012-04-04 16:28:20'),
(88, '', '', 76, 30, 9, '2012-04-04 16:31:16'),
(89, '', '', 77, 30, 9, '2012-04-04 16:34:59'),
(90, '', 'Test typing speed on ', 78, 30, 9, '2012-04-04 16:37:22'),
(91, '', '', 79, 30, 9, '2012-04-04 16:38:51'),
(92, '', '', 80, 30, 9, '2012-04-04 16:40:20'),
(93, '', '', 81, 30, 9, '2012-04-04 16:40:42'),
(94, '', '', 82, 30, 9, '2012-04-04 16:40:52'),
(95, '', '', 83, 30, 9, '2012-04-04 16:41:15'),
(96, '', '', 84, 30, 9, '2012-04-04 16:41:37'),
(97, '', '', 85, 30, 9, '2012-04-04 16:41:47'),
(98, '', '', 86, 30, 9, '2012-04-04 16:42:08'),
(99, '', '', 87, 30, 9, '2012-04-04 16:45:06');

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

--
-- Dumping data for table `stories`
--

INSERT INTO `stories` (`id`, `user_id`, `type`, `event_goal_id`, `is_public`, `event_new_level`, `entered_at`, `event_letter_score`, `event_description`, `event_old_level`, `dailyscore_progress`, `entered_at_day`) VALUES
(4, 9, 'event', 4, 1, 6, '2012-02-27 19:38:09', 'D', 'john ', 2.1, NULL, '2012-02-27'),
(5, 9, 'event', 2, 1, 6.5, '2012-02-27 19:40:58', 'B', 'i''m awesome', 5, NULL, '2012-02-27'),
(6, 9, 'event', 1, 1, 8, '2012-02-27 19:41:44', 'A', 'because i cool', 5, NULL, '2012-02-27'),
(7, 10, 'event', 19, 1, 8, '2012-04-10 15:48:30', 'A', 'figured it out bro!', 5, NULL, '2012-04-10'),
(8, 10, 'event', 13, 1, 7, '2012-04-12 23:28:17', 'A', 'KILLING IT BRO!', 5, NULL, '2012-04-12'),
(9, 10, 'event', 19, 1, 9, '2012-04-12 23:30:45', 'A', 'jimmy', 8, NULL, '2012-04-12'),
(10, 10, 'event', 13, 1, 7, '2012-04-13 00:29:31', 'A', 'jimbob the 3rd', 7, NULL, '2012-04-13'),
(11, 10, 'event', 19, 1, 8, '2012-04-13 00:29:20', 'A', '8 is still pretty good ''bro'';', 9, NULL, '2012-04-13'),
(12, 10, 'event', 19, 1, 10, '2012-04-14 23:37:46', 'A', 'testing the buttons ;)', 8, NULL, '2012-04-14'),
(13, 10, 'event', 21, 1, 2, '2012-04-14 23:39:24', 'F', 'random KG doesn''t like my glasses', 5, NULL, '2012-04-14'),
(14, 10, 'event', 33, 1, 6, '2012-04-15 00:44:04', 'A', 'focussss', 5, NULL, '2012-04-15'),
(15, 10, 'event', 19, 1, 6, '2012-04-15 03:40:29', 'A', 'cause things go places', 9, NULL, '2012-04-15');

-- --------------------------------------------------------

--
-- Table structure for table `strategies`
--

CREATE TABLE IF NOT EXISTS `strategies` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `goal_id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL,
  `strategy_type` varchar(256) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `goal_id` (`goal_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=118 ;

--
-- Dumping data for table `strategies`
--

INSERT INTO `strategies` (`id`, `goal_id`, `name`, `description`, `strategy_type`, `created_by`, `date_created`) VALUES
(1, 1, 'Buy Philips Lamp', 'this lamp mimics the sunrise and supposedly wakes you up refreshed', 'todo', 9, '2012-03-08 12:05:56'),
(2, 1, 'Get shades that can make my room entirely dark', 'full darkness is said to help with quality sleep', 'todo', 9, '2012-03-08 12:05:56'),
(3, 1, 'Report whether I woke up refreshed', 'a daily test to verify whether I am making progress', 'adherence', 9, '2012-03-08 12:05:56'),
(4, 2, 'Exercise 4 days a week', '4 intense 1 hour sessions should suffice for optimal progress', 'adherence', 9, '2012-03-08 12:19:19'),
(6, 4, 'Meditate at least 20 minutes every day', 'self explanatory', 'adherence', 9, '2012-03-08 12:45:29'),
(7, 4, 'meditate with friends', 'connect with at least one friend to meditate once a week', 'adherence', 9, '2012-03-11 00:03:12'),
(74, 25, 'Exercise with a trainer 3 times a week', '', 'adherence', 9, '2012-04-02 23:35:36'),
(75, 25, '2x high intensity cardio workout per week', '', 'adherence', 9, '2012-04-02 23:39:29'),
(76, 25, 'Log high energy days', '', 'adherence', 9, '2012-04-02 23:39:55'),
(77, 25, 'Spend 30min/week learning about nutrition/fitness', '', 'adherence', 9, '2012-04-02 23:40:51'),
(78, 25, 'Compile reading list for nutrition/fitness', '', 'todo', 9, '2012-04-02 23:41:02'),
(79, 25, 'Find yoga studio near my place', '', 'todo', 9, '2012-04-02 23:41:28'),
(80, 25, 'Use brown rice in pot for grip improvent while reading', '', 'tactic', 9, '2012-04-02 23:42:09'),
(81, 32, 'Read for 30 min / day', '', 'adherence', 9, '2012-04-04 16:22:47'),
(82, 32, 'Do my reading using quick reader app 3 times a week', '', 'adherence', 9, '2012-04-04 16:23:19'),
(83, 32, 'Write public blog entry once a month', '', 'adherence', 9, '2012-04-04 16:23:32'),
(84, 32, 'Write for 10 min on any topic 4 times a week', '', 'adherence', 9, '2012-04-04 16:24:02'),
(85, 28, 'Wake up refreshed', '', 'adherence', 9, '2012-04-04 16:26:05'),
(86, 28, 'Research ways to make my room completely dark', '', 'todo', 9, '2012-04-04 16:26:29'),
(87, 28, 'Buy products required to make my room fully dark', '', 'todo', 9, '2012-04-04 16:26:42'),
(88, 22, 'Meditate at least 15 minutes in the morning', '', 'adherence', 9, '2012-04-04 16:31:37'),
(89, 22, 'Meditate at least 15 minutes before bed', '', 'adherence', 9, '2012-04-04 16:31:48'),
(90, 13, 'Spend 10 min planning day in the morning', '', 'adherence', 9, '2012-04-04 16:35:06'),
(91, 17, 'Find a good tool to improve my speed typing', '', 'todo', 9, '2012-04-04 16:37:49'),
(92, 31, 'Make sure Liz completes the task', '', 'todo', 9, '2012-04-04 16:39:22'),
(93, 20, 'Check my facebook photos and curate as necessary once a week', '', 'adherence', 9, '2012-04-04 16:42:55'),
(94, 20, 'Share at least 3 pieces of content I think is cool on facebook per week', '', 'adherence', 9, '2012-04-04 16:44:36'),
(95, 32, 'Spend at least 3 hours a week consuming content from 2012 media list', '', 'adherence', 9, '2012-04-04 16:45:40'),
(96, 19, 'Be nice to people', '', 'adherence', 10, '2012-04-12 23:20:54'),
(97, 19, 'Give folks random gifts!', '', 'adherence', 10, '2012-04-12 23:21:05'),
(98, 34, 'Build a strong self-identity', '', 'tactic', 10, '2012-04-13 14:53:06'),
(99, 34, 'Be yourself - not a chamelion', '', 'tactic', 10, '2012-04-13 14:53:06'),
(100, 34, 'Did I excel today?', '', 'adherence', 10, '2012-04-13 14:53:06'),
(101, 35, 'Did I hustle today?', '', 'adherence', 10, '2012-04-13 14:57:08'),
(102, 35, 'Ask yourself - would you invest in your own business?', '', 'tactic', 10, '2012-04-13 14:57:08'),
(103, 36, 'Don''t go in if you aren''t 100% - can always come in later', '', 'tactic', 10, '2012-04-13 14:59:42'),
(104, 37, 'Provide value to others to grow your network', '', 'tactic', 10, '2012-04-13 15:03:02'),
(105, 37, 'Did I meet someone new & awesome today?', '', 'adherence', 10, '2012-04-13 15:03:02'),
(106, 38, 'Speak with folks who don''t know your 1st language', '', 'tactic', 10, '2012-04-13 15:22:12'),
(107, 38, 'Speak Chinese with someone for 5 minutes', '', 'adherence', 10, '2012-04-13 15:22:12'),
(108, 39, 'every day do it!', '', 'todo', 10, '2012-04-14 01:49:22'),
(109, 39, 'check that it works', '', 'todo', 10, '2012-04-14 01:49:52'),
(110, 40, 'hello', '', 'adherence', 10, '2012-04-14 17:43:35'),
(111, 19, 'Be nice to people', '', 'adherence', 10, '2012-04-14 21:20:43'),
(112, 19, 'Be nice to people', '', 'tactic', 10, '2012-04-14 21:21:19'),
(113, 19, 'Get to know a stranger', '', 'adherence', 10, '2012-04-14 21:21:41'),
(114, 19, 'Buy a bunch of gifts to give ppl', '', 'todo', 10, '2012-04-14 21:43:25'),
(115, 19, 'Get a list of events to go to', '', 'todo', 10, '2012-04-14 22:28:49'),
(116, 19, 'Remember names better', '', 'tactic', 10, '2012-04-14 22:29:01'),
(117, 41, 'herer', '', 'adherence', 10, '2012-04-15 04:54:27');

-- --------------------------------------------------------

--
-- Table structure for table `strategies_log`
--

CREATE TABLE IF NOT EXISTS `strategies_log` (
  `strategy_id` bigint(20) NOT NULL DEFAULT '0',
  `user_id` bigint(20) NOT NULL,
  `input` int(11) DEFAULT NULL,
  `input_text` varchar(500) DEFAULT NULL,
  `entered_at` datetime NOT NULL,
  `entered_at_day` varchar(10) NOT NULL,
  PRIMARY KEY (`strategy_id`,`user_id`,`entered_at_day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `strategies_log`
--

INSERT INTO `strategies_log` (`strategy_id`, `user_id`, `input`, `input_text`, `entered_at`, `entered_at_day`) VALUES
(3, 9, 1, NULL, '2012-02-27 19:55:36', '2012-02-27'),
(4, 9, 1, NULL, '2012-02-26 00:00:00', '2012-02-26'),
(4, 9, 1, NULL, '2012-02-24 00:00:00', '2012-02-24'),
(4, 9, 1, NULL, '2012-02-22 00:00:00', '2012-02-22'),
(3, 9, 1, NULL, '2012-02-29 23:48:58', '2012-02-29'),
(4, 9, 1, NULL, '2012-02-29 23:48:57', '2012-02-29'),
(4, 9, 1, NULL, '2012-03-29 17:15:44', '2012-03-29'),
(1, 9, 1, NULL, '2012-03-20 21:57:19', '2012-03-20'),
(2, 9, 1, NULL, '2012-03-20 21:57:19', '2012-03-20'),
(3, 9, 1, NULL, '2012-03-20 21:57:20', '2012-03-20'),
(6, 9, 1, NULL, '2012-04-02 17:17:47', '2012-04-02'),
(7, 9, 1, NULL, '2012-04-02 17:17:48', '2012-04-02'),
(7, 9, 1, NULL, '2012-04-05 15:35:20', '2012-04-05'),
(93, 10, 1, NULL, '2012-04-10 15:47:37', '2012-04-10'),
(90, 10, 1, NULL, '2012-04-10 15:47:38', '2012-04-10'),
(96, 10, 1, NULL, '2012-04-13 15:52:35', '2012-04-12'),
(97, 10, 1, NULL, '2012-04-13 00:14:18', '2012-04-13'),
(113, 10, 1, NULL, '2012-04-14 21:22:44', '2012-04-14'),
(113, 10, 1, NULL, '2012-04-15 03:15:37', '2012-04-15');

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
  `permissions` int(11) NOT NULL DEFAULT '0',
  `last_daily_entry` datetime DEFAULT NULL,
  `daily_entry_story_posted` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `auth_id`, `picture_url`, `visit_history`, `full_name`, `permissions`, `last_daily_entry`, `daily_entry_story_posted`) VALUES
(9, 4, 'https://fbcdn-profile-a.akamaihd.net/hprofile-ak-snc4/276034_9805008_1929439820_q.jpg', 0x613a32303a7b693a303b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031343532323b7d693a313b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031343531313b7d693a323b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031323533383b7d693a333b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031323530383b7d693a343b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031323530363b7d693a353b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031323530323b7d693a363b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031323530303b7d693a373b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031323335373b7d693a383b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031323036323b7d693a393b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031323035353b7d693a31303b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031323032303b7d693a31313b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031323031393b7d693a31323b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031323031383b7d693a31333b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031323031353b7d693a31343b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031313837383b7d693a31353b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031313837373b7d693a31363b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031313837353b7d693a31373b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031313837313b7d693a31383b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031313831343b7d693a31393b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343031313739323b7d7d, 'Adam Gries', 0, '2012-04-05 21:24:22', 0),
(10, 7, 'https://fbcdn-profile-a.akamaihd.net/hprofile-ak-snc4/276034_9805008_1929439820_q.jpg', 0x613a32303a7b693a303b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530323033303b7d693a313b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530323032393b7d693a323b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530323032383b7d693a333b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530323032373b7d693a343b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530313935353b7d693a353b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530313935303b7d693a363b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530313934393b7d693a373b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530313838393b7d693a383b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530313838363b7d693a393b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530313838333b7d693a31303b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530313837373b7d693a31313b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530313837333b7d693a31323b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530313836353b7d693a31333b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530313836343b7d693a31343b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530313836333b7d693a31353b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530313836313b7d693a31363b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530313835383b7d693a31373b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530313835373b7d693a31383b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530313835323b7d693a31393b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343530313735303b7d7d, 'Roger Dickey', 0, '2012-04-15 03:40:29', 0),
(11, 8, 'https://fbcdn-profile-a.akamaihd.net/hprofile-ak-snc4/276034_9805008_1929439820_q.jpg', 0x613a31313a7b693a303b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033363539393b7d693a313b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033363539373b7d693a323b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033363539363b7d693a333b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033363539353b7d693a343b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033363539323b7d693a353b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033363534303b7d693a363b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033363533383b7d693a373b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033363533373b7d693a383b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033363533363b7d693a393b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033363533343b7d693a31303b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033363137383b7d7d, 'Roger D', 0, '1969-12-31 16:00:00', 0),
(12, 9, 'https://fbcdn-profile-a.akamaihd.net/hprofile-ak-snc4/276034_9805008_1929439820_q.jpg', 0x613a333a7b693a303b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033363731333b7d693a313b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033363731323b7d693a323b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033363637353b7d7d, 'Roger Dickey', 0, '1969-12-31 16:00:00', 0),
(13, 10, 'https://fbcdn-profile-a.akamaihd.net/hprofile-ak-snc4/276034_9805008_1929439820_q.jpg', 0x613a32303a7b693a303b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033383439313b7d693a313b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033383437353b7d693a323b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033383437343b7d693a333b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033383436373b7d693a343b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033383436363b7d693a353b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033383131313b7d693a363b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033383131303b7d693a373b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033383130373b7d693a383b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033383130343b7d693a393b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033383130333b7d693a31303b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033383039393b7d693a31313b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033383039373b7d693a31323b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033383039363b7d693a31333b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033383039323b7d693a31343b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033383030363b7d693a31353b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033373934373b7d693a31363b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033373934363b7d693a31373b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033373934333b7d693a31383b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033373934313b7d693a31393b4f3a343a2244617465223a313a7b733a383a220044617465007574223b693a313333343033373933393b7d7d, 'Roger D', 0, '1969-12-31 16:00:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_kpis`
--

CREATE TABLE IF NOT EXISTS `user_kpis` (
  `user_id` bigint(20) NOT NULL DEFAULT '0',
  `kpi_id` bigint(20) DEFAULT NULL,
  `goal_id` bigint(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `comment` varchar(256) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  UNIQUE KEY `user_id` (`user_id`,`kpi_id`,`goal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_kpis`
--

INSERT INTO `user_kpis` (`user_id`, `kpi_id`, `goal_id`, `is_active`, `comment`, `date_created`) VALUES
(9, 29, 4, 0, NULL, '2012-03-29 17:13:23'),
(9, 4, 4, 1, NULL, '2012-04-02 17:10:00'),
(9, 1, 1, 0, NULL, '2012-03-29 17:14:26'),
(9, 2, 2, 1, NULL, '2012-03-29 17:14:39'),
(9, 63, 25, 1, NULL, '2012-04-02 23:16:01'),
(9, 64, 25, 1, NULL, '2012-04-02 23:16:53'),
(9, 65, 25, 1, NULL, '2012-04-02 23:17:41'),
(9, 66, 25, 1, NULL, '2012-04-02 23:18:47'),
(9, 67, 25, 1, NULL, '2012-04-02 23:20:05'),
(9, 68, 25, 1, NULL, '2012-04-02 23:21:33'),
(9, 69, 25, 1, NULL, '2012-04-02 23:23:10'),
(9, 70, 25, 1, NULL, '2012-04-02 23:25:17'),
(9, 71, 25, 1, NULL, '2012-04-02 23:37:44'),
(9, 72, 32, 1, NULL, '2012-04-04 16:20:28'),
(9, 73, 32, 1, NULL, '2012-04-04 16:21:59'),
(9, 74, 28, 1, NULL, '2012-04-04 16:25:41'),
(9, 75, 33, 1, NULL, '2012-04-04 16:28:20'),
(9, 76, 22, 1, NULL, '2012-04-04 16:31:16'),
(9, 77, 13, 1, NULL, '2012-04-04 16:34:59'),
(9, 78, 17, 1, NULL, '2012-04-04 16:37:22'),
(9, 79, 31, 1, NULL, '2012-04-04 16:38:51'),
(9, 80, 20, 1, NULL, '2012-04-04 16:40:20'),
(9, 81, 20, 1, NULL, '2012-04-04 16:40:42'),
(9, 82, 20, 1, NULL, '2012-04-04 16:40:52'),
(9, 83, 20, 1, NULL, '2012-04-04 16:41:15'),
(9, 84, 20, 1, NULL, '2012-04-04 16:41:37'),
(9, 85, 20, 1, NULL, '2012-04-04 16:41:47'),
(9, 86, 20, 1, NULL, '2012-04-04 16:42:08'),
(9, 87, 32, 1, NULL, '2012-04-04 16:45:06');

-- --------------------------------------------------------

--
-- Table structure for table `user_strategies`
--

CREATE TABLE IF NOT EXISTS `user_strategies` (
  `user_id` bigint(20) DEFAULT NULL,
  `strategy_id` bigint(20) DEFAULT NULL,
  `goal_id` bigint(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `comment` varchar(256) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `user_id` (`user_id`,`strategy_id`,`goal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_strategies`
--

INSERT INTO `user_strategies` (`user_id`, `strategy_id`, `goal_id`, `is_active`, `comment`, `date_created`, `is_completed`) VALUES
(9, 6, 4, 0, NULL, '2012-04-02 17:10:12', 0),
(9, 7, 4, 0, NULL, '2012-03-26 19:02:49', 0),
(9, 4, 2, 1, NULL, '2012-03-29 17:14:48', 0),
(9, 74, 25, 1, NULL, '2012-04-02 23:35:36', 0),
(9, 75, 25, 1, NULL, '2012-04-02 23:39:29', 0),
(9, 76, 25, 1, NULL, '2012-04-02 23:39:55', 0),
(9, 77, 25, 1, NULL, '2012-04-02 23:40:51', 0),
(9, 78, 25, 1, NULL, '2012-04-04 16:47:00', 0),
(9, 79, 25, 1, NULL, '2012-04-02 23:41:28', 0),
(9, 80, 25, 1, NULL, '2012-04-02 23:42:09', 0),
(9, 81, 32, 1, NULL, '2012-04-04 16:22:47', 0),
(9, 82, 32, 1, NULL, '2012-04-04 16:23:19', 0),
(9, 83, 32, 1, NULL, '2012-04-04 16:23:32', 0),
(9, 84, 32, 1, NULL, '2012-04-04 16:24:02', 0),
(9, 85, 28, 1, NULL, '2012-04-04 16:26:05', 0),
(9, 86, 28, 1, NULL, '2012-04-04 16:26:29', 0),
(9, 87, 28, 1, NULL, '2012-04-04 16:26:42', 0),
(9, 88, 22, 1, NULL, '2012-04-04 16:31:37', 0),
(9, 89, 22, 1, NULL, '2012-04-04 16:31:48', 0),
(9, 90, 13, 1, NULL, '2012-04-04 16:35:06', 0),
(9, 91, 17, 1, NULL, '2012-04-04 16:37:49', 0),
(9, 92, 31, 1, NULL, '2012-04-04 16:39:22', 0),
(9, 93, 20, 1, NULL, '2012-04-04 16:42:55', 0),
(9, 94, 20, 1, NULL, '2012-04-04 16:44:36', 0),
(9, 95, 32, 1, NULL, '2012-04-04 16:45:40', 0),
(10, 96, 19, 0, NULL, '2012-04-14 21:20:29', 0),
(10, 97, 19, 0, NULL, '2012-04-14 21:20:35', 0),
(10, 109, 39, 1, NULL, '2012-04-14 01:49:52', 0),
(10, 111, 19, 0, NULL, '2012-04-14 21:22:06', 0),
(10, 112, 19, 1, NULL, '2012-04-14 21:21:19', 0),
(10, 113, 19, 1, NULL, '2012-04-14 21:21:41', 0),
(10, 114, 19, 1, NULL, '2012-04-14 21:43:25', 0),
(10, 115, 19, 1, NULL, '2012-04-14 22:28:49', 1),
(10, 116, 19, 1, NULL, '2012-04-14 22:29:01', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_tests`
--

CREATE TABLE IF NOT EXISTS `user_tests` (
  `user_id` bigint(20) DEFAULT NULL,
  `goal_id` bigint(20) DEFAULT NULL,
  `kpi_id` bigint(20) DEFAULT NULL,
  `test_id` bigint(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `comment` varchar(256) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  UNIQUE KEY `user_id` (`user_id`,`goal_id`,`kpi_id`,`test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_tests`
--

INSERT INTO `user_tests` (`user_id`, `goal_id`, `kpi_id`, `test_id`, `is_active`, `comment`, `date_created`) VALUES
(9, 4, 4, 5, 0, NULL, '2012-03-29 17:13:25'),
(9, 4, 4, 42, 0, NULL, '2012-03-29 17:13:25'),
(9, 25, 63, 75, 1, NULL, '2012-04-02 23:16:01'),
(9, 25, 64, 76, 1, NULL, '2012-04-02 23:16:53'),
(9, 25, 65, 77, 1, NULL, '2012-04-02 23:17:41'),
(9, 25, 66, 78, 1, NULL, '2012-04-02 23:18:47'),
(9, 25, 67, 79, 1, NULL, '2012-04-02 23:20:05'),
(9, 25, 68, 80, 1, NULL, '2012-04-02 23:21:33'),
(9, 25, 69, 81, 1, NULL, '2012-04-02 23:23:10'),
(9, 25, 70, 82, 1, NULL, '2012-04-02 23:25:17'),
(9, 25, 71, 83, 1, NULL, '2012-04-02 23:37:44'),
(9, 32, 72, 84, 1, NULL, '2012-04-04 16:20:28'),
(9, 32, 73, 85, 1, NULL, '2012-04-04 16:21:59'),
(9, 28, 74, 86, 1, NULL, '2012-04-04 16:25:41'),
(9, 33, 75, 87, 1, NULL, '2012-04-04 16:28:20'),
(9, 22, 76, 88, 1, NULL, '2012-04-04 16:31:16'),
(9, 13, 77, 89, 1, NULL, '2012-04-04 16:34:59'),
(9, 17, 78, 90, 1, NULL, '2012-04-04 16:37:22'),
(9, 31, 79, 91, 1, NULL, '2012-04-04 16:38:51'),
(9, 20, 80, 92, 1, NULL, '2012-04-04 16:40:20'),
(9, 20, 81, 93, 1, NULL, '2012-04-04 16:40:42'),
(9, 20, 82, 94, 1, NULL, '2012-04-04 16:40:52'),
(9, 20, 83, 95, 1, NULL, '2012-04-04 16:41:15'),
(9, 20, 84, 96, 1, NULL, '2012-04-04 16:41:37'),
(9, 20, 85, 97, 1, NULL, '2012-04-04 16:41:47'),
(9, 20, 86, 98, 1, NULL, '2012-04-04 16:42:08'),
(9, 32, 87, 99, 1, NULL, '2012-04-04 16:45:06');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
