-- MySQL dump 10.13  Distrib 5.5.21, for osx10.6 (i386)
--
-- Host: localhost    Database: superhuman_goals
-- ------------------------------------------------------
-- Server version	5.5.21

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `auth_sg_banned`
--

DROP TABLE IF EXISTS `auth_sg_banned`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_sg_banned` (
  `entry` varchar(100) NOT NULL,
  `type` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`entry`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_sg_banned`
--

LOCK TABLES `auth_sg_banned` WRITE;
/*!40000 ALTER TABLE `auth_sg_banned` DISABLE KEYS */;
/*!40000 ALTER TABLE `auth_sg_banned` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auth_sg_login_log`
--

DROP TABLE IF EXISTS `auth_sg_login_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_sg_login_log` (
  `email` varchar(100) DEFAULT NULL,
  `date` varchar(20) DEFAULT NULL,
  `time` varchar(20) DEFAULT NULL,
  `ip_addr` varchar(20) DEFAULT NULL,
  `oper_sys` varchar(20) DEFAULT NULL,
  `brow` varchar(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_sg_login_log`
--

LOCK TABLES `auth_sg_login_log` WRITE;
/*!40000 ALTER TABLE `auth_sg_login_log` DISABLE KEYS */;
INSERT INTO `auth_sg_login_log` VALUES ('adamgries@gmail.com','2012-03-28','21:19','::1','MAC','FireFox');
/*!40000 ALTER TABLE `auth_sg_login_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auth_sg_trash`
--

DROP TABLE IF EXISTS `auth_sg_trash`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_sg_trash` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_sg_trash`
--

LOCK TABLES `auth_sg_trash` WRITE;
/*!40000 ALTER TABLE `auth_sg_trash` DISABLE KEYS */;
/*!40000 ALTER TABLE `auth_sg_trash` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auth_sg_users`
--

DROP TABLE IF EXISTS `auth_sg_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_sg_users` (
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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_sg_users`
--

LOCK TABLES `auth_sg_users` WRITE;
/*!40000 ALTER TABLE `auth_sg_users` DISABLE KEYS */;
INSERT INTO `auth_sg_users` VALUES (4,'Adam','Gries','a:1:{s:10:\"pictureURL\";s:0:\"\";}','*281BE344E7A71A8C881968E5DB935E384FB9B279','Users','','',0,'adamgries@gmail.com',1,'2012-03-28 14:19:10');
/*!40000 ALTER TABLE `auth_sg_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `goals`
--

DROP TABLE IF EXISTS `goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `goals` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `goals`
--

LOCK TABLES `goals` WRITE;
/*!40000 ALTER TABLE `goals` DISABLE KEYS */;
INSERT INTO `goals` VALUES (1,'Sleep','Achieve ultimate sleep. ',9,'2012-03-26 00:42:49'),(2,'Have a great body','be the one girls like to look at',9,'2012-03-26 00:42:49'),(4,'Improve at Meditation (Quantity and Quality)','Achieve ultimate mindfulness through consistent meditation practice. Increase focus, reduce reflexive cognitive/emotional reactions, reduce decline in pre-frontal cortex volume.',9,'2012-03-26 00:42:49');
/*!40000 ALTER TABLE `goals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `goals_group`
--

DROP TABLE IF EXISTS `goals_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `goals_group` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `position_index` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `goals_group`
--

LOCK TABLES `goals_group` WRITE;
/*!40000 ALTER TABLE `goals_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `goals_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `goals_status`
--

DROP TABLE IF EXISTS `goals_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `goals_status` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `goals_status`
--

LOCK TABLES `goals_status` WRITE;
/*!40000 ALTER TABLE `goals_status` DISABLE KEYS */;
INSERT INTO `goals_status` VALUES (1,9,5,NULL,1,1,0,NULL,0,'2012-03-26 00:43:27','2012-03-26 01:22:47'),(4,9,5,'',1,1,0,NULL,1,'2012-03-26 01:48:59','2012-03-28 14:20:52'),(2,9,5,'',0,1,0,NULL,0,'2012-03-26 01:25:20','2012-03-26 01:25:22');
/*!40000 ALTER TABLE `goals_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `goals_to_kpis`
--

DROP TABLE IF EXISTS `goals_to_kpis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `goals_to_kpis` (
  `goal_id` bigint(20) NOT NULL DEFAULT '0',
  `kpi_id` bigint(20) DEFAULT NULL,
  `associated_by` bigint(20) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  UNIQUE KEY `goal_id` (`goal_id`,`kpi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `goals_to_kpis`
--

LOCK TABLES `goals_to_kpis` WRITE;
/*!40000 ALTER TABLE `goals_to_kpis` DISABLE KEYS */;
INSERT INTO `goals_to_kpis` VALUES (1,1,9,'2012-03-26 00:58:59'),(2,2,9,'2012-03-26 00:58:59'),(3,3,9,'2012-03-26 00:58:59'),(4,4,9,'2012-03-26 00:58:59'),(4,29,9,'2012-03-26 00:58:59');
/*!40000 ALTER TABLE `goals_to_kpis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kpi_log`
--

DROP TABLE IF EXISTS `kpi_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kpi_log` (
  `user_id` bigint(20) NOT NULL DEFAULT '0',
  `kpi_test_id` bigint(20) DEFAULT NULL,
  `goal_id` bigint(20) DEFAULT NULL,
  `kpi_id` bigint(20) DEFAULT NULL,
  `performance` bigint(20) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kpi_log`
--

LOCK TABLES `kpi_log` WRITE;
/*!40000 ALTER TABLE `kpi_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `kpi_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kpi_tests`
--

DROP TABLE IF EXISTS `kpi_tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kpi_tests` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `test_description` varchar(256) DEFAULT NULL,
  `test_name` varchar(256) DEFAULT NULL,
  `kpi_id` bigint(20) DEFAULT NULL,
  `test_frequency` bigint(20) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kpi_tests`
--

LOCK TABLES `kpi_tests` WRITE;
/*!40000 ALTER TABLE `kpi_tests` DISABLE KEYS */;
INSERT INTO `kpi_tests` VALUES (1,'DEXA body scan',NULL,0,90,9,'2012-03-26 01:09:06'),(2,'Review my log for the aggregate reporting on the refreshedness I had upon waking',NULL,1,30,9,'2012-03-26 01:09:06'),(4,'use the best tool to evaluate body fat %','DEXA Scan',3,90,9,'2012-03-26 01:09:06'),(5,'check my adherence stats','Adherence test review',4,30,9,'2012-03-26 01:09:06'),(6,'Bench press 200lbs','Bench press 200lbs',3,30,9,'2012-03-26 01:09:06'),(41,'self explanatory','sit motionless for 60 minutes',29,30,9,'2012-03-26 01:09:06'),(42,'look at the logs and verify sitting','Check insight timer logs',4,30,9,'2012-03-26 01:09:06');
/*!40000 ALTER TABLE `kpi_tests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kpis`
--

DROP TABLE IF EXISTS `kpis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kpis` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `kpi_name` varchar(256) DEFAULT NULL,
  `kpi_desc` varchar(256) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kpis`
--

LOCK TABLES `kpis` WRITE;
/*!40000 ALTER TABLE `kpis` DISABLE KEYS */;
INSERT INTO `kpis` VALUES (1,'Wake up refreshed 80% of days','Feel like a champ when you wake up',9,'2012-03-08 12:01:17'),(2,'13% Body Fat','Get them fats off',9,'2012-03-08 12:19:19'),(3,'13% Body Fat','Get them fats off',9,'2012-03-08 12:20:50'),(4,'80% Performance on Daily Meditation Adherence Test','Make sure I verify I have been meditating every day and hold myself accountable if I do not',9,'2012-03-08 12:45:29'),(29,'Ability to sit for 60 minutes straight','Self Explanatory',9,'2012-03-21 09:41:06');
/*!40000 ALTER TABLE `kpis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stories`
--

DROP TABLE IF EXISTS `stories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stories` (
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
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stories`
--

LOCK TABLES `stories` WRITE;
/*!40000 ALTER TABLE `stories` DISABLE KEYS */;
INSERT INTO `stories` VALUES (4,9,'event',4,1,6,'2012-02-27 19:38:09','D','john ',2.1,NULL,'2012-02-27'),(5,9,'event',2,1,6.5,'2012-02-27 19:40:58','B','i\'m awesome',5,NULL,'2012-02-27'),(6,9,'event',1,1,8,'2012-02-27 19:41:44','A','because i cool',5,NULL,'2012-02-27');
/*!40000 ALTER TABLE `stories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `strategies`
--

DROP TABLE IF EXISTS `strategies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `strategies` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `goal_id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL,
  `strategy_type` varchar(256) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `goal_id` (`goal_id`)
) ENGINE=MyISAM AUTO_INCREMENT=74 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `strategies`
--

LOCK TABLES `strategies` WRITE;
/*!40000 ALTER TABLE `strategies` DISABLE KEYS */;
INSERT INTO `strategies` VALUES (1,1,'Buy Philips Lamp','this lamp mimics the sunrise and supposedly wakes you up refreshed','todo',9,'2012-03-08 12:05:56'),(2,1,'Get shades that can make my room entirely dark','full darkness is said to help with quality sleep','todo',9,'2012-03-08 12:05:56'),(3,1,'Report whether I woke up refreshed','a daily test to verify whether I am making progress','adherence',9,'2012-03-08 12:05:56'),(4,2,'Exercise 4 days a week','4 intense 1 hour sessions should suffice for optimal progress','adherence',9,'2012-03-08 12:19:19'),(6,4,'Meditate at least 20 minutes every day','self explanatory','adherence',9,'2012-03-08 12:45:29'),(7,4,'meditate with friends','connect with at least one friend to meditate once a week','adherence',9,'2012-03-11 00:03:12');
/*!40000 ALTER TABLE `strategies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `strategies_log`
--

DROP TABLE IF EXISTS `strategies_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `strategies_log` (
  `strategy_id` bigint(20) NOT NULL DEFAULT '0',
  `user_id` bigint(20) NOT NULL,
  `input` int(11) DEFAULT NULL,
  `input_text` varchar(500) DEFAULT NULL,
  `entered_at` datetime NOT NULL,
  `entered_at_day` varchar(10) NOT NULL,
  PRIMARY KEY (`strategy_id`,`user_id`,`entered_at_day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `strategies_log`
--

LOCK TABLES `strategies_log` WRITE;
/*!40000 ALTER TABLE `strategies_log` DISABLE KEYS */;
INSERT INTO `strategies_log` VALUES (3,9,1,NULL,'2012-02-27 19:55:36','2012-02-27'),(4,9,1,NULL,'2012-02-26 00:00:00','2012-02-26'),(4,9,1,NULL,'2012-02-24 00:00:00','2012-02-24'),(4,9,1,NULL,'2012-02-22 00:00:00','2012-02-22'),(1,9,1,NULL,'2012-03-28 14:21:45','2012-03-28'),(3,9,1,NULL,'2012-02-29 23:48:58','2012-02-29'),(4,9,1,NULL,'2012-02-29 23:48:57','2012-02-29'),(1,9,1,NULL,'2012-03-20 21:57:19','2012-03-20'),(2,9,1,NULL,'2012-03-20 21:57:19','2012-03-20'),(3,9,1,NULL,'2012-03-20 21:57:20','2012-03-20');
/*!40000 ALTER TABLE `strategies_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_kpis`
--

DROP TABLE IF EXISTS `user_kpis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_kpis` (
  `user_id` bigint(20) NOT NULL DEFAULT '0',
  `kpi_id` bigint(20) DEFAULT NULL,
  `goal_id` bigint(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `comment` varchar(256) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  UNIQUE KEY `user_id` (`user_id`,`kpi_id`,`goal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_kpis`
--

LOCK TABLES `user_kpis` WRITE;
/*!40000 ALTER TABLE `user_kpis` DISABLE KEYS */;
INSERT INTO `user_kpis` VALUES (9,29,4,0,NULL,'2012-03-26 19:01:17'),(9,4,4,0,NULL,'2012-03-26 19:01:23');
/*!40000 ALTER TABLE `user_kpis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_strategies`
--

DROP TABLE IF EXISTS `user_strategies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_strategies` (
  `user_id` bigint(20) DEFAULT NULL,
  `strategy_id` bigint(20) DEFAULT NULL,
  `goal_id` bigint(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `comment` varchar(256) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  UNIQUE KEY `user_id` (`user_id`,`strategy_id`,`goal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_strategies`
--

LOCK TABLES `user_strategies` WRITE;
/*!40000 ALTER TABLE `user_strategies` DISABLE KEYS */;
INSERT INTO `user_strategies` VALUES (9,6,4,0,NULL,'2012-03-26 19:01:27'),(9,7,4,0,NULL,'2012-03-26 19:02:49');
/*!40000 ALTER TABLE `user_strategies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_tests`
--

DROP TABLE IF EXISTS `user_tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_tests` (
  `user_id` bigint(20) DEFAULT NULL,
  `goal_id` bigint(20) DEFAULT NULL,
  `kpi_id` bigint(20) DEFAULT NULL,
  `test_id` bigint(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `comment` varchar(256) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  UNIQUE KEY `user_id` (`user_id`,`goal_id`,`kpi_id`,`test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_tests`
--

LOCK TABLES `user_tests` WRITE;
/*!40000 ALTER TABLE `user_tests` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_tests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `auth_id` bigint(20) NOT NULL,
  `picture_url` varchar(300) NOT NULL,
  `visit_history` blob,
  `full_name` varchar(100) NOT NULL,
  `last_daily_entry` datetime DEFAULT NULL,
  `daily_entry_story_posted` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (9,4,'http://2.bp.blogspot.com/_xjP3IbeLPac/SwrzRpxr5-I/AAAAAAAAAQk/bNG38azRVnc/S45/very%2Bsmall%2Bface.jpg','a:20:{i:0;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969851;}i:1;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969849;}i:2;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969840;}i:3;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969839;}i:4;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969833;}i:5;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969831;}i:6;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969806;}i:7;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969805;}i:8;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969804;}i:9;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969803;}i:10;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969782;}i:11;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969781;}i:12;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969761;}i:13;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969739;}i:14;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969738;}i:15;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969737;}i:16;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969736;}i:17;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969735;}i:18;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969728;}i:19;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1332969727;}}','Adam Gries','2012-03-28 14:22:19',0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-03-28 14:36:13
