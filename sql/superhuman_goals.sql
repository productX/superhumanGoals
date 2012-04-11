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
INSERT INTO `auth_sg_login_log` VALUES ('adamgries@gmail.com','2012-03-28','21:19','::1','MAC','FireFox'),('adamgries@gmail.com','2012-03-31','07:59','::1','MAC','FireFox'),('adamgries@gmail.com','2012-04-06','20:30','::1','MAC','unknown'),('adamgries@gmail.com','2012-04-06','21:18','::1','MAC','FireFox'),('adamgries@gmail.com','2012-04-06','21:20','::1','MAC','FireFox'),('adamgries@gmail.com','2012-04-07','21:34','::1','MAC','unknown'),('adamgries@gmail.com','2012-04-07','21:34','::1','MAC','unknown'),('adamgries@gmail.com','2012-04-07','21:55','::1','MAC','FireFox'),('adamgries@gmail.com','2012-04-08','01:56','::1','MAC','unknown'),('adamgries@gmail.com','2012-04-08','09:05','::1','MAC','FireFox'),('adamgries@gmail.com','2012-04-08','16:19','::1','MAC','unknown'),('adamgries@gmail.com','2012-04-09','01:35','::1','MAC','FireFox'),('adamgries@gmail.com','2012-04-09','05:24','::1','MAC','FireFox'),('adamgries@gmail.com','2012-04-09','05:31','::1','MAC','FireFox'),('adamgries@gmail.com','2012-04-09','20:16','::1','MAC','FireFox'),('adamgries@gmail.com','2012-04-09','22:47','::1','MAC','unknown'),('adamgries@gmail.com','2012-04-09','23:58','::1','MAC','unknown'),('adamgries@gmail.com','2012-04-10','00:02','::1','MAC','unknown'),('adamgries@gmail.com','2012-04-10','00:56','::1','MAC','unknown');
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
INSERT INTO `auth_sg_users` VALUES (4,'Adam','Gries','a:1:{s:10:\"pictureURL\";s:0:\"\";}','*281BE344E7A71A8C881968E5DB935E384FB9B279','Users','','',0,'adamgries@gmail.com',1,'2012-04-09 17:56:35');
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
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `goals`
--

LOCK TABLES `goals` WRITE;
/*!40000 ALTER TABLE `goals` DISABLE KEYS */;
INSERT INTO `goals` VALUES (1,'Sleep','Achieve ultimate sleep. ',9,0,'2012-03-26 00:42:49'),(2,'Have a great body','be the one girls like to look at',9,0,'2012-03-26 00:42:49'),(4,'Improve at Meditation (Quantity and Quality)','Achieve ultimate mindfulness through consistent meditation practice. Increase focus, reduce reflexive cognitive/emotional reactions, reduce decline in pre-frontal cortex volume.',9,0,'2012-03-26 00:42:49'),(11,'Money','Anything relating to finances, saving, investments, net worth or money.',9,1,'2012-04-02 22:16:10'),(12,'Career','Anything to do with work, professional development, jobs, management.',9,1,'2012-04-02 22:21:03'),(13,'Productivity','Anything that raises you effectiveness / efficiency.',9,1,'2012-04-02 22:28:21'),(14,'Lifestyle','Goals that have to do with achieving your ultimate lifestyle.',9,1,'2012-04-02 22:28:46'),(15,'Mood','Anything that relates to how you feel day to day and overall.',9,1,'2012-04-02 22:29:36'),(16,'Learning','Anything related to education, gaining mastery, intellectual improvement.',9,1,'2012-04-02 22:44:46'),(17,'Skills','Mastery in a particular practical realm.',9,1,'2012-04-02 22:45:22'),(18,'Leadership','Building charisma, influence and leadership skills.',9,1,'2012-04-02 23:00:44'),(19,'Relationships','Interpersonal communication, social skills, social understanding, emotional intelligence.',9,1,'2012-04-02 23:01:29'),(20,'Personal Brand','Present yourself and communicate with the world in the best way possible.',9,1,'2012-04-02 23:02:07'),(21,'Fashion / Looks','Be really really good looking :)',9,1,'2012-04-02 23:03:28'),(22,'Meditation','Achieve better balance, focus and liberation from suffering.',9,1,'2012-04-02 23:04:12'),(23,'Weightloss','Lose the weight you want to reach the body and feeling you desire.',9,1,'2012-04-02 23:04:54'),(24,'Nutrition','Devise the optimal nutrition for your body type.',9,1,'2012-04-02 23:05:13'),(25,'Fitness','Running, sports, building muscles, endurance, constitution, optimal physical performance and health.',9,1,'2012-04-02 23:05:53'),(26,'Energy','Have the energy level you desire as much of the time as possible.',9,1,'2012-04-02 23:06:19'),(27,'Health','Be healthful in any way you desire.',9,1,'2012-04-02 23:06:52'),(28,'Sleep','Achieve optimal sleep. Be refreshed and rejuvenated while spending the amount of time you\'d like on sleep.',9,1,'2012-04-02 23:07:22'),(29,'Experiences','Tasting the richness of what life has to offer in any way.',9,1,'2012-04-02 23:07:45'),(30,'Happiness','Achieve your happiness goals.',9,1,'2012-04-02 23:08:23'),(31,'Other','Any other type of goal',9,1,'2012-04-02 23:08:39'),(32,'Reading and Writing','Anything to do with reading and writing',9,1,'2012-04-04 16:18:15'),(33,'Focus / Self Control','',9,1,'2012-04-04 16:27:33');
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
INSERT INTO `goals_status` VALUES (1,9,5,'wake up refreshed',0,1,0,NULL,0,'2012-03-26 00:43:27','2012-04-02 21:54:01'),(4,9,5,'have a body like zach efrons',0,1,0,NULL,0,'2012-03-26 01:48:59','2012-04-02 18:48:00'),(2,9,5,'have a body like zach efrons',0,1,0,NULL,1,'2012-03-26 01:25:20','2012-04-02 22:11:34'),(11,9,4,'Net worth $5 Million',1,1,0,NULL,0,'2012-04-02 22:46:17','2012-04-10 21:58:08'),(12,9,5,'',0,1,0,NULL,0,'2012-04-02 22:54:04','2012-04-02 23:08:54'),(25,9,8,'have epicnessss\'s',1,1,0,NULL,0,'2012-04-02 23:14:38','2012-04-10 23:17:18'),(32,9,5,'Improve my reading and writing',1,1,0,NULL,0,'2012-04-04 16:18:33','2012-04-04 16:44:43'),(28,9,5,'Wake up refreshed and stay energized throughout the day on as little sleep as sustainable.',1,1,0,NULL,0,'2012-04-04 16:24:26','2012-04-04 16:25:15'),(33,9,5,'',1,1,0,NULL,0,'2012-04-04 16:27:38','2012-04-04 16:27:38'),(22,9,5,'',1,1,0,NULL,0,'2012-04-04 16:30:55','2012-04-04 16:30:55'),(13,9,5,'Plan better',1,1,0,NULL,0,'2012-04-04 16:33:50','2012-04-10 17:00:51'),(17,9,5,'',1,1,0,NULL,0,'2012-04-04 16:36:40','2012-04-04 16:36:40'),(31,9,5,'',1,1,0,NULL,0,'2012-04-04 16:38:09','2012-04-04 16:38:09'),(20,9,5,'',1,1,0,NULL,0,'2012-04-04 16:39:57','2012-04-04 16:39:57'),(27,9,5,'Eat Healthfully',1,1,0,NULL,0,'2012-04-10 17:12:49','2012-04-10 17:12:57'),(14,9,5,'',1,1,0,NULL,0,'2012-04-10 17:14:14','2012-04-10 17:14:14');
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
INSERT INTO `goals_to_kpis` VALUES (1,1,9,'2012-03-26 00:58:59'),(2,2,9,'2012-03-26 00:58:59'),(3,3,9,'2012-03-26 00:58:59'),(4,4,9,'2012-03-26 00:58:59'),(4,29,9,'2012-03-26 00:58:59'),(7,62,9,'2012-03-30 19:18:15'),(25,63,9,'2012-04-02 23:16:01'),(25,64,9,'2012-04-02 23:16:53'),(25,65,9,'2012-04-02 23:17:41'),(25,66,9,'2012-04-02 23:18:47'),(25,67,9,'2012-04-02 23:20:05'),(25,68,9,'2012-04-02 23:21:33'),(25,69,9,'2012-04-02 23:23:10'),(25,70,9,'2012-04-02 23:25:17'),(25,71,9,'2012-04-02 23:37:44'),(32,72,9,'2012-04-04 16:20:28'),(32,73,9,'2012-04-04 16:21:59'),(28,74,9,'2012-04-04 16:25:41'),(33,75,9,'2012-04-04 16:28:20'),(22,76,9,'2012-04-04 16:31:16'),(13,77,9,'2012-04-04 16:34:59'),(17,78,9,'2012-04-04 16:37:22'),(31,79,9,'2012-04-04 16:38:51'),(20,80,9,'2012-04-04 16:40:20'),(20,81,9,'2012-04-04 16:40:42'),(20,82,9,'2012-04-04 16:40:52'),(20,83,9,'2012-04-04 16:41:15'),(20,84,9,'2012-04-04 16:41:37'),(20,85,9,'2012-04-04 16:41:47'),(20,86,9,'2012-04-04 16:42:08'),(32,87,9,'2012-04-04 16:45:06');
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
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kpi_tests`
--

LOCK TABLES `kpi_tests` WRITE;
/*!40000 ALTER TABLE `kpi_tests` DISABLE KEYS */;
INSERT INTO `kpi_tests` VALUES (1,'DEXA body scan',NULL,0,90,9,'2012-03-26 01:09:06'),(2,'Review my log for the aggregate reporting on the refreshedness I had upon waking',NULL,1,30,9,'2012-03-26 01:09:06'),(4,'use the best tool to evaluate body fat %','DEXA Scan',3,90,9,'2012-03-26 01:09:06'),(5,'check my adherence stats','Adherence test review',4,30,9,'2012-03-26 01:09:06'),(6,'Bench press 200lbs','Bench press 200lbs',3,30,9,'2012-03-26 01:09:06'),(41,'self explanatory','sit motionless for 60 minutes',29,30,9,'2012-03-26 01:09:06'),(42,'look at the logs and verify sitting','Check insight timer logs',4,30,9,'2012-03-26 01:09:06'),(75,'A scan exposing bodymass by tissue type','DEXA',63,90,9,'2012-04-02 23:16:01'),(76,'','',64,30,9,'2012-04-02 23:16:53'),(77,'','',65,30,9,'2012-04-02 23:17:41'),(78,'','',66,30,9,'2012-04-02 23:18:47'),(79,'','',67,30,9,'2012-04-02 23:20:05'),(80,'','',68,30,9,'2012-04-02 23:21:33'),(81,'','Spectrocell',69,90,9,'2012-04-02 23:23:10'),(82,'','Daily report',70,30,9,'2012-04-02 23:25:17'),(83,'','Withings scale',71,90,9,'2012-04-02 23:37:44'),(84,'','',72,30,9,'2012-04-04 16:20:28'),(85,'','Test myself on the quick reader ipad app',73,30,9,'2012-04-04 16:21:59'),(86,'','',74,30,9,'2012-04-04 16:25:41'),(87,'','',75,30,9,'2012-04-04 16:28:20'),(88,'','',76,30,9,'2012-04-04 16:31:16'),(89,'','',77,30,9,'2012-04-04 16:34:59'),(90,'','Test typing speed on ',78,30,9,'2012-04-04 16:37:22'),(91,'','',79,30,9,'2012-04-04 16:38:51'),(92,'','',80,30,9,'2012-04-04 16:40:20'),(93,'','',81,30,9,'2012-04-04 16:40:42'),(94,'','',82,30,9,'2012-04-04 16:40:52'),(95,'','',83,30,9,'2012-04-04 16:41:15'),(96,'','',84,30,9,'2012-04-04 16:41:37'),(97,'','',85,30,9,'2012-04-04 16:41:47'),(98,'','',86,30,9,'2012-04-04 16:42:08'),(99,'','',87,30,9,'2012-04-04 16:45:06');
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
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kpis`
--

LOCK TABLES `kpis` WRITE;
/*!40000 ALTER TABLE `kpis` DISABLE KEYS */;
INSERT INTO `kpis` VALUES (1,'Wake up refreshed 80% of days','Feel like a champ when you wake up',9,'2012-03-08 12:01:17'),(2,'13% Body Fat','Get them fats off',9,'2012-03-08 12:19:19'),(3,'13% Body Fat','Get them fats off',9,'2012-03-08 12:20:50'),(4,'80% Performance on Daily Meditation Adherence Test','Make sure I verify I have been meditating every day and hold myself accountable if I do not',9,'2012-03-08 12:45:29'),(29,'Ability to sit for 60 minutes straight','Self Explanatory',9,'2012-03-21 09:41:06'),(63,'13% Body Fat','',9,'2012-04-02 23:16:01'),(64,'8 x Bench Press 270lbs','',9,'2012-04-02 23:16:53'),(65,'8 x Squat 360lbs','',9,'2012-04-02 23:17:41'),(66,'8 x Deadlift 270lbs','',9,'2012-04-02 23:18:47'),(67,'100 Push ups straight','',9,'2012-04-02 23:20:05'),(68,'Run 10K in 45min','',9,'2012-04-02 23:21:33'),(69,'Great bloodwork indicators','',9,'2012-04-02 23:23:10'),(70,'80% High Energy Days','',9,'2012-04-02 23:25:17'),(71,'Achieve 185lbs bodyweight','',9,'2012-04-02 23:37:44'),(72,'Create 24 posts on a blog and get an average of 3 comments per post','',9,'2012-04-04 16:20:28'),(73,'Reading speed above 1000/w/minute','',9,'2012-04-04 16:21:59'),(74,'80% performance on the my wake up refreshed habit','',9,'2012-04-04 16:25:41'),(75,'80% performance on the question: Did I procrastinate today?','',9,'2012-04-04 16:28:20'),(76,'Log at least 300 hours of vipassana in 2012','',9,'2012-04-04 16:31:16'),(77,'80% performance on the Plan Day in the Morning habit','',9,'2012-04-04 16:34:59'),(78,'Improve typing speed by 50%','',9,'2012-04-04 16:37:22'),(79,'Have a comprehensive document outlining citizenship/permanent-residence information for all relevant countries','',9,'2012-04-04 16:38:51'),(80,'Be happy with all content up on my public profiles','',9,'2012-04-04 16:40:20'),(81,'Lower unfriends/month by 50% comparing first to last quarter of 2012','',9,'2012-04-04 16:40:42'),(82,'Be invited to speak at two major events','',9,'2012-04-04 16:40:52'),(83,'Be invited to participate as judge or speaker at 4 events','',9,'2012-04-04 16:41:15'),(84,'Receive advisory shares for 3 companies I believe in','',9,'2012-04-04 16:41:37'),(85,'Lower unanswered emails by 50% comparing first and last quarters 2012','',9,'2012-04-04 16:41:47'),(86,'Have a beautiful blog','',9,'2012-04-04 16:42:08'),(87,'Complete 2012 media list','',9,'2012-04-04 16:45:06');
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
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stories`
--

LOCK TABLES `stories` WRITE;
/*!40000 ALTER TABLE `stories` DISABLE KEYS */;
INSERT INTO `stories` VALUES (4,9,'event',4,1,6,'2012-02-27 19:38:09','D','john ',2.1,NULL,'2012-02-27'),(5,9,'event',2,1,6.5,'2012-02-27 19:40:58','B','i\'m awesome',5,NULL,'2012-02-27'),(6,9,'event',1,1,8,'2012-02-27 19:41:44','A','because i cool',5,NULL,'2012-02-27'),(7,9,'event',25,1,8,'2012-04-10 16:47:42','A','hotties..',5,NULL,'2012-04-10'),(8,9,'event',11,1,4,'2012-04-10 21:58:08','C','lost $300 at poker',5,NULL,'2012-04-10');
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
) ENGINE=MyISAM AUTO_INCREMENT=109 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `strategies`
--

LOCK TABLES `strategies` WRITE;
/*!40000 ALTER TABLE `strategies` DISABLE KEYS */;
INSERT INTO `strategies` VALUES (1,1,'Buy Philips Lamp','this lamp mimics the sunrise and supposedly wakes you up refreshed','todo',9,'2012-03-08 12:05:56'),(2,1,'Get shades that can make my room entirely dark','full darkness is said to help with quality sleep','todo',9,'2012-03-08 12:05:56'),(3,1,'Report whether I woke up refreshed','a daily test to verify whether I am making progress','adherence',9,'2012-03-08 12:05:56'),(4,2,'Exercise 4 days a week','4 intense 1 hour sessions should suffice for optimal progress','adherence',9,'2012-03-08 12:19:19'),(6,4,'Meditate at least 20 minutes every day','self explanatory','adherence',9,'2012-03-08 12:45:29'),(7,4,'meditate with friends','connect with at least one friend to meditate once a week','adherence',9,'2012-03-11 00:03:12'),(74,25,'Exercise with a trainer 3 times a week','','adherence',9,'2012-04-02 23:35:36'),(75,25,'2x high intensity cardio workout per week','','adherence',9,'2012-04-02 23:39:29'),(76,25,'Log high energy days','','adherence',9,'2012-04-02 23:39:55'),(77,25,'Spend 30min/week learning about nutrition/fitness','','adherence',9,'2012-04-02 23:40:51'),(78,25,'Compile reading list for nutrition/fitness','','todo',9,'2012-04-02 23:41:02'),(79,25,'Find yoga studio near my place','','todo',9,'2012-04-02 23:41:28'),(80,25,'Use brown rice in pot for grip improvent while reading','','tactic',9,'2012-04-02 23:42:09'),(81,32,'Read for 30 min / day','','adherence',9,'2012-04-04 16:22:47'),(82,32,'Do my reading using quick reader app 3 times a week','','adherence',9,'2012-04-04 16:23:19'),(83,32,'Write public blog entry once a month','','adherence',9,'2012-04-04 16:23:32'),(84,32,'Write for 10 min on any topic 4 times a week','','adherence',9,'2012-04-04 16:24:02'),(85,28,'Wake up refreshed','','adherence',9,'2012-04-04 16:26:05'),(86,28,'Research ways to make my room completely dark','','todo',9,'2012-04-04 16:26:29'),(87,28,'Buy products required to make my room fully dark','','todo',9,'2012-04-04 16:26:42'),(88,22,'Meditate at least 15 minutes in the morning','','adherence',9,'2012-04-04 16:31:37'),(89,22,'Meditate at least 15 minutes before bed','','adherence',9,'2012-04-04 16:31:48'),(90,13,'Spend 10 min planning day in the morning','','adherence',9,'2012-04-04 16:35:06'),(91,17,'Find a good tool to improve my speed typing','','todo',9,'2012-04-04 16:37:49'),(92,31,'Make sure Liz completes the task','','todo',9,'2012-04-04 16:39:22'),(93,20,'Check my facebook photos and curate as necessary once a week','','adherence',9,'2012-04-04 16:42:55'),(94,20,'Share at least 3 pieces of content I think is cool on facebook per week','','adherence',9,'2012-04-04 16:44:36'),(95,32,'Spend at least 3 hours a week consuming content from 2012 media list','','adherence',9,'2012-04-04 16:45:40'),(96,13,'Avoid Procrastination','','adherence',9,'2012-04-10 17:00:09'),(97,27,'Eat healthfully','','adherence',9,'2012-04-10 17:13:07'),(98,14,'Avoid Porn','','adherence',9,'2012-04-10 17:14:20'),(99,25,'Remember rest is critical to recovery which is critical to muscle building','','tactic',9,'2012-04-10 21:48:24'),(100,25,'If I can\'t sit up straight I should take a break until I can sit straight again.','','tactic',9,'2012-04-10 21:49:15');
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
INSERT INTO `strategies_log` VALUES (3,9,1,NULL,'2012-02-27 19:55:36','2012-02-27'),(4,9,1,NULL,'2012-02-26 00:00:00','2012-02-26'),(4,9,1,NULL,'2012-02-24 00:00:00','2012-02-24'),(4,9,1,NULL,'2012-02-22 00:00:00','2012-02-22'),(85,9,1,NULL,'2012-04-10 16:58:02','2012-04-10'),(3,9,1,NULL,'2012-02-29 23:48:58','2012-02-29'),(4,9,1,NULL,'2012-02-29 23:48:57','2012-02-29'),(4,9,1,NULL,'2012-03-29 17:15:44','2012-03-29'),(1,9,1,NULL,'2012-03-20 21:57:19','2012-03-20'),(2,9,1,NULL,'2012-03-20 21:57:19','2012-03-20'),(3,9,1,NULL,'2012-03-20 21:57:20','2012-03-20'),(85,9,1,NULL,'2012-04-10 16:58:01','2012-04-09'),(6,9,1,NULL,'2012-04-02 17:17:47','2012-04-02'),(7,9,1,NULL,'2012-04-02 17:17:48','2012-04-02'),(7,9,1,NULL,'2012-04-05 15:35:20','2012-04-05'),(75,9,1,NULL,'2012-04-10 16:57:21','2012-04-05'),(74,9,1,NULL,'2012-04-10 16:57:10','2012-04-10'),(74,9,1,NULL,'2012-04-10 16:57:12','2012-04-05'),(74,9,1,NULL,'2012-04-10 16:57:15','2012-04-06'),(85,9,1,NULL,'2012-04-10 16:58:03','2012-04-08'),(85,9,1,NULL,'2012-04-10 16:58:04','2012-04-07'),(85,9,1,NULL,'2012-04-10 16:58:04','2012-04-06'),(85,9,1,NULL,'2012-04-10 16:58:05','2012-04-05'),(85,9,1,NULL,'2012-04-10 16:58:05','2012-04-04'),(89,9,1,NULL,'2012-04-10 16:58:50','2012-04-09'),(88,9,1,NULL,'2012-04-10 16:58:20','2012-04-09'),(88,9,1,NULL,'2012-04-10 16:58:20','2012-04-08'),(88,9,1,NULL,'2012-04-10 16:58:21','2012-04-07'),(88,9,1,NULL,'2012-04-10 16:58:24','2012-04-04'),(89,9,1,NULL,'2012-04-10 16:58:51','2012-04-07'),(89,9,1,NULL,'2012-04-10 16:58:51','2012-04-06'),(89,9,1,NULL,'2012-04-10 16:58:52','2012-04-04'),(96,9,1,NULL,'2012-04-10 17:01:05','2012-04-09'),(96,9,1,NULL,'2012-04-10 17:01:06','2012-04-07'),(96,9,1,NULL,'2012-04-10 17:01:06','2012-04-06'),(96,9,1,NULL,'2012-04-10 17:01:07','2012-04-04'),(97,9,1,NULL,'2012-04-10 17:13:21','2012-04-10'),(97,9,1,NULL,'2012-04-10 17:13:22','2012-04-09'),(97,9,1,NULL,'2012-04-10 17:13:22','2012-04-08'),(97,9,1,NULL,'2012-04-10 17:13:23','2012-04-04'),(97,9,1,NULL,'2012-04-10 17:13:24','2012-04-05'),(97,9,1,NULL,'2012-04-10 17:13:24','2012-04-06'),(98,9,1,NULL,'2012-04-10 17:14:37','2012-04-09'),(98,9,1,NULL,'2012-04-10 17:14:38','2012-04-07'),(98,9,1,NULL,'2012-04-10 17:14:38','2012-04-04');
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
INSERT INTO `user_kpis` VALUES (9,29,4,0,NULL,'2012-03-29 17:13:23'),(9,4,4,1,NULL,'2012-04-02 17:10:00'),(9,1,1,0,NULL,'2012-03-29 17:14:26'),(9,2,2,1,NULL,'2012-03-29 17:14:39'),(9,63,25,1,NULL,'2012-04-02 23:16:01'),(9,64,25,1,NULL,'2012-04-02 23:16:53'),(9,65,25,1,NULL,'2012-04-02 23:17:41'),(9,66,25,1,NULL,'2012-04-02 23:18:47'),(9,67,25,1,NULL,'2012-04-02 23:20:05'),(9,68,25,1,NULL,'2012-04-02 23:21:33'),(9,69,25,1,NULL,'2012-04-02 23:23:10'),(9,70,25,1,NULL,'2012-04-02 23:25:17'),(9,71,25,1,NULL,'2012-04-02 23:37:44'),(9,72,32,1,NULL,'2012-04-04 16:20:28'),(9,73,32,1,NULL,'2012-04-04 16:21:59'),(9,74,28,1,NULL,'2012-04-04 16:25:41'),(9,75,33,1,NULL,'2012-04-04 16:28:20'),(9,76,22,1,NULL,'2012-04-04 16:31:16'),(9,77,13,1,NULL,'2012-04-04 16:34:59'),(9,78,17,1,NULL,'2012-04-04 16:37:22'),(9,79,31,1,NULL,'2012-04-04 16:38:51'),(9,80,20,1,NULL,'2012-04-04 16:40:20'),(9,81,20,1,NULL,'2012-04-04 16:40:42'),(9,82,20,1,NULL,'2012-04-04 16:40:52'),(9,83,20,1,NULL,'2012-04-04 16:41:15'),(9,84,20,1,NULL,'2012-04-04 16:41:37'),(9,85,20,1,NULL,'2012-04-04 16:41:47'),(9,86,20,1,NULL,'2012-04-04 16:42:08'),(9,87,32,1,NULL,'2012-04-04 16:45:06');
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
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
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
INSERT INTO `user_strategies` VALUES (9,6,4,0,0,NULL,'2012-04-02 17:10:12'),(9,7,4,0,0,NULL,'2012-03-26 19:02:49'),(9,4,2,1,0,NULL,'2012-03-29 17:14:48'),(9,74,25,1,0,NULL,'2012-04-02 23:35:36'),(9,75,25,1,0,NULL,'2012-04-02 23:39:29'),(9,76,25,1,0,NULL,'2012-04-02 23:39:55'),(9,77,25,1,0,NULL,'2012-04-02 23:40:51'),(9,78,25,1,0,NULL,'2012-04-11 15:24:31'),(9,79,25,1,0,NULL,'2012-04-02 23:41:28'),(9,80,25,1,0,NULL,'2012-04-11 16:19:21'),(9,81,32,1,0,NULL,'2012-04-04 16:22:47'),(9,82,32,1,0,NULL,'2012-04-04 16:23:19'),(9,83,32,1,0,NULL,'2012-04-04 16:23:32'),(9,84,32,1,0,NULL,'2012-04-04 16:24:02'),(9,85,28,1,0,NULL,'2012-04-04 16:26:05'),(9,86,28,1,0,NULL,'2012-04-04 16:26:29'),(9,87,28,1,0,NULL,'2012-04-04 16:26:42'),(9,88,22,1,0,NULL,'2012-04-04 16:31:37'),(9,89,22,1,0,NULL,'2012-04-04 16:31:48'),(9,90,13,1,0,NULL,'2012-04-10 17:00:30'),(9,91,17,1,0,NULL,'2012-04-04 16:37:49'),(9,92,31,1,0,NULL,'2012-04-04 16:39:22'),(9,93,20,1,0,NULL,'2012-04-04 16:42:55'),(9,94,20,1,0,NULL,'2012-04-04 16:44:36'),(9,95,32,1,0,NULL,'2012-04-04 16:45:40'),(9,96,13,1,0,NULL,'2012-04-10 17:00:09'),(9,97,27,1,0,NULL,'2012-04-10 17:13:07'),(9,98,14,1,0,NULL,'2012-04-10 17:14:20'),(9,99,25,1,0,NULL,'2012-04-10 21:48:24'),(9,100,25,1,0,NULL,'2012-04-10 21:49:15');
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
INSERT INTO `user_tests` VALUES (9,4,4,5,0,NULL,'2012-03-29 17:13:25'),(9,4,4,42,0,NULL,'2012-03-29 17:13:25'),(9,25,63,75,1,NULL,'2012-04-02 23:16:01'),(9,25,64,76,1,NULL,'2012-04-02 23:16:53'),(9,25,65,77,1,NULL,'2012-04-02 23:17:41'),(9,25,66,78,1,NULL,'2012-04-02 23:18:47'),(9,25,67,79,1,NULL,'2012-04-02 23:20:05'),(9,25,68,80,1,NULL,'2012-04-02 23:21:33'),(9,25,69,81,1,NULL,'2012-04-02 23:23:10'),(9,25,70,82,1,NULL,'2012-04-02 23:25:17'),(9,25,71,83,1,NULL,'2012-04-02 23:37:44'),(9,32,72,84,1,NULL,'2012-04-04 16:20:28'),(9,32,73,85,1,NULL,'2012-04-04 16:21:59'),(9,28,74,86,1,NULL,'2012-04-04 16:25:41'),(9,33,75,87,1,NULL,'2012-04-04 16:28:20'),(9,22,76,88,1,NULL,'2012-04-04 16:31:16'),(9,13,77,89,1,NULL,'2012-04-04 16:34:59'),(9,17,78,90,1,NULL,'2012-04-04 16:37:22'),(9,31,79,91,1,NULL,'2012-04-04 16:38:51'),(9,20,80,92,1,NULL,'2012-04-04 16:40:20'),(9,20,81,93,1,NULL,'2012-04-04 16:40:42'),(9,20,82,94,1,NULL,'2012-04-04 16:40:52'),(9,20,83,95,1,NULL,'2012-04-04 16:41:15'),(9,20,84,96,1,NULL,'2012-04-04 16:41:37'),(9,20,85,97,1,NULL,'2012-04-04 16:41:47'),(9,20,86,98,1,NULL,'2012-04-04 16:42:08'),(9,32,87,99,1,NULL,'2012-04-04 16:45:06');
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
  `permissions` int(11) NOT NULL DEFAULT '0',
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
INSERT INTO `users` VALUES (9,4,'http://2.bp.blogspot.com/_xjP3IbeLPac/SwrzRpxr5-I/AAAAAAAAAQk/bNG38azRVnc/S45/very%2Bsmall%2Bface.jpg','a:20:{i:0;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334186269;}i:1;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334186267;}i:2;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334186243;}i:3;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334186240;}i:4;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334186178;}i:5;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334186175;}i:6;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334186138;}i:7;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334186136;}i:8;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334185937;}i:9;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334185934;}i:10;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334185069;}i:11;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334185067;}i:12;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334183180;}i:13;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334183178;}i:14;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334183131;}i:15;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334183117;}i:16;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334183114;}i:17;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334183087;}i:18;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334183084;}i:19;O:4:\"Date\":1:{s:8:\"\0Date\0ut\";i:1334183065;}}','Adam Gries',1,'2012-04-11 15:57:49',0);
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

-- Dump completed on 2012-04-11 16:24:58
