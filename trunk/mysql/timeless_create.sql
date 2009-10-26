-- MySQL dump 10.13  Distrib 5.1.24-rc, for apple-darwin9.0.0b5 (i686)
--
-- Host: kaimac.ath.cx    Database: timeless
-- ------------------------------------------------------
-- Server version	5.1.37

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
-- Table structure for table `bank`
--

DROP TABLE IF EXISTS `bank`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `bank` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'transaction id',
  `gil_change` int(11) NOT NULL COMMENT 'how much gil is added or removed from the bank',
  `reason` varchar(80) NOT NULL COMMENT 'what''s the reason for the change in gil',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='records of bank transactions';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id number for this person''s comment',
  `member_id` int(10) unsigned NOT NULL COMMENT 'member''s id from the members table',
  `run_id` int(10) unsigned NOT NULL COMMENT 'run id from the runs table',
  `early_point` int(2) unsigned NOT NULL DEFAULT '0' COMMENT 'did this person earn the point for being early? 1 for yes, 0 for no',
  `mb_point` int(2) unsigned NOT NULL DEFAULT '0' COMMENT 'did this person stay until the death of the megaboss? 1 or 0',
  `ab_points` int(2) unsigned NOT NULL DEFAULT '0' COMMENT 'how many after megaboss points did this person earn?',
  `comment` int(10) unsigned DEFAULT '0' COMMENT 'item id the person is spending points on. 0 = points',
  `fl_one` int(10) unsigned DEFAULT '0' COMMENT 'item id the person is free lotting',
  `fl_one_tier` int(10) unsigned DEFAULT '50' COMMENT 'lot tier for first free lot: 50, 55, 60, 65, 70, 75',
  `fl_two` int(10) unsigned DEFAULT '0' COMMENT 'item id the person is free lotting',
  `fl_two_tier` int(10) unsigned DEFAULT '50' COMMENT 'lot tier for second free lot: 50, 55, 60, 65, 70, 75',
  `excused` int(2) unsigned NOT NULL DEFAULT '0' COMMENT 'if absent not counted in restricted status',
  `comments` varchar(256) DEFAULT NULL COMMENT 'Anything of particular note for this person during this run',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1351 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC COMMENT='Player comments for each run';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `currency`
--

DROP TABLE IF EXISTS `currency`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `currency` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'tracking id for each transaction',
  `type` varchar(8) NOT NULL COMMENT 'before MB, after MB, or sale are the possible values',
  `run_id` int(10) unsigned DEFAULT NULL COMMENT 'id from the runs table',
  `time` datetime DEFAULT NULL COMMENT 'close approximation of the transaction time',
  `one_byne` int(4) DEFAULT '0' COMMENT '1 Byne Bills',
  `o_bronze` int(4) DEFAULT '0' COMMENT 'O. Bronzepieces',
  `t_shell` int(4) DEFAULT '0' COMMENT 'T. Whiteshells',
  `wootz` int(4) DEFAULT '0' COMMENT 'Wootz Ores',
  `one_hundred_byne` int(4) DEFAULT '0' COMMENT '100 Byne Bills',
  `m_silverpiece` int(4) DEFAULT '0' COMMENT 'Montiont Silvepieces',
  `l_jadeshell` int(4) DEFAULT '0' COMMENT 'Lungo-Nango Jadeshells',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `drops`
--

DROP TABLE IF EXISTS `drops`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `drops` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'record id',
  `run_id` int(10) unsigned NOT NULL COMMENT 'id for the run this item came from',
  `member_id` int(10) unsigned NOT NULL COMMENT 'id for the member that recieved this item',
  `item_id` int(10) unsigned NOT NULL COMMENT 'id for the item that was recieved',
  `pointed` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'were points spent on this item? 1 = yes, 0 = no',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=317 DEFAULT CHARSET=latin1 COMMENT='player won drops';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `dynamis_zones`
--

DROP TABLE IF EXISTS `dynamis_zones`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `dynamis_zones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'record id',
  `zone_name_short` varchar(8) NOT NULL COMMENT 'shortname',
  `zone_name_search` varchar(16) NOT NULL COMMENT 'search name',
  `zone_name_long` varchar(45) NOT NULL COMMENT 'longname',
  `location` varchar(45) NOT NULL COMMENT 'entrance location',
  `boss` varchar(45) NOT NULL COMMENT 'boss name',
  `players_max` int(2) unsigned NOT NULL COMMENT 'max number of players that can enter',
  `key_item` varchar(45) NOT NULL COMMENT 'name of the key item after you get the win',
  `relic_not_dropped` varchar(45) DEFAULT NULL COMMENT 'jobs that do not have relic in this zone',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COMMENT='default information on dynamis zones';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'record id',
  `item` varchar(45) NOT NULL COMMENT 'name of the item',
  `job` varchar(5) DEFAULT NULL COMMENT 'job description of the item',
  `piece` varchar(8) DEFAULT NULL COMMENT 'piece the item equips to',
  `level` int(2) unsigned DEFAULT NULL COMMENT 'level needed to equip the item',
  `value` int(2) unsigned NOT NULL COMMENT 'point value of the item',
  `zones` varchar(8) DEFAULT NULL COMMENT 'zones this piece drops in seperated by a pipe',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=224 DEFAULT CHARSET=latin1 COMMENT='pointed drops';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `member_af`
--

DROP TABLE IF EXISTS `member_af`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `member_af` (
  `member_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'member_id, same as members.id',
  PRIMARY KEY (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='table of each member and the items they''ve received. NOTE: S';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'character id',
  `name` varchar(45) NOT NULL COMMENT 'character name',
  `main_char` varchar(45) DEFAULT NULL COMMENT 'name of the main character for this account if this is a mule',
  `title` varchar(45) NOT NULL DEFAULT 'member' COMMENT 'role in shell',
  `restricted` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '0=no, 1=yes',
  `trial` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '0=no, 1=yes',
  `shared` int(1) unsigned NOT NULL DEFAULT '0' COMMENT 'does this mule share poitns with main? 0=no, 1=yes',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=latin1 COMMENT='timeless members list';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `pay_temp`
--

DROP TABLE IF EXISTS `pay_temp`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `pay_temp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `month` varchar(8) DEFAULT NULL COMMENT 'the mm-yy value used to identify where these funds are to be distributed',
  `amount` int(16) DEFAULT NULL COMMENT 'the total sales for this month',
  `override_points` int(16) DEFAULT NULL COMMENT 'If there is some adjustment needed for the monthly points, the altered total is here',
  `override_reason` varchar(256) DEFAULT NULL COMMENT 'the reason for entering an override',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='temp table holding values for each monthly payout totals';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `payouts`
--

DROP TABLE IF EXISTS `payouts`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `payouts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL COMMENT 'members.id for who''s getting paid',
  `pay_type` varchar(45) NOT NULL COMMENT 'monthly, monthly_ab, specialty_fee, misc',
  `pay_value` int(10) unsigned DEFAULT NULL COMMENT 'amount of gil owed to member',
  `paid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '1 = yes, 0 = no',
  `paid_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='member payouts';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `runs`
--

DROP TABLE IF EXISTS `runs`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `runs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'generic tracking number',
  `run_date` varchar(8) NOT NULL COMMENT 'date of the run MMDDYY',
  `zone_id` int(2) NOT NULL COMMENT 'zone id',
  `zone` varchar(8) NOT NULL COMMENT 'short zone name',
  `enter_time` int(4) unsigned NOT NULL COMMENT 'entry time',
  `exit_time` int(4) unsigned NOT NULL COMMENT 'time we got kicked out',
  `mb_tod` int(4) unsigned NOT NULL DEFAULT '0' COMMENT 'tod for megaboss or 0000 in case of farming/loss',
  `killshot` varchar(16) DEFAULT NULL COMMENT 'name of the character to get the killshot',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=latin1 COMMENT='dynamis runs';
SET character_set_client = @saved_cs_client;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-10-26 22:06:52
