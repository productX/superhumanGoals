-- phpMyAdmin SQL Dump
-- version 3.4.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 05, 2012 at 08:02 AM
-- Server version: 5.5.8
-- PHP Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `intranet`
--

-- --------------------------------------------------------

--
-- Table structure for table `auth_intranet_banned`
--

CREATE TABLE IF NOT EXISTS `auth_sg_banned` (
  `entry` varchar(100) NOT NULL,
  `type` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`entry`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `auth_intranet_login_log`
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
-- Table structure for table `auth_intranet_trash`
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
-- Table structure for table `auth_intranet_users`
--

CREATE TABLE IF NOT EXISTS `auth_sg_users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(20) NOT NULL,
  `lastname` varchar(20) DEFAULT NULL,
  `extradata` blob DEFAULT NULL,
  `password` varchar(50) NOT NULL,
  `group1` varchar(20) DEFAULT NULL,
  `group2` varchar(20) DEFAULT NULL,
  `group3` varchar(20) DEFAULT NULL,
  `pchange` tinyint(1) NOT NULL,
  `email` varchar(100) NOT NULL,
  `verified` tinyint(1) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
