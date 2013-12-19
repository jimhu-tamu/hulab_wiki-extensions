-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 30, 2013 at 11:22 AM
-- Server version: 5.5.22-log
-- PHP Version: 5.3.22

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `colipedia`
--

-- --------------------------------------------------------

--
-- Table structure for table `ext_TableEdit_box`
--

CREATE TABLE IF NOT EXISTS `ext_TableEdit_box` (
  `box_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template` varchar(255) NOT NULL,
  `page_name` varchar(255) NOT NULL,
  `page_uid` varchar(255) NOT NULL,
  `box_uid` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `headings` varchar(255) NOT NULL,
  `heading_style` varchar(255) NOT NULL,
  `box_style` varchar(255) NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY  (`box_id`),
  KEY `template` (`template`),
  KEY `page_name` (`page_name`),
  KEY `page_uid` (`page_uid`),
  KEY `box_uid` (`box_uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ext_TableEdit_box_metadata`
--

CREATE TABLE IF NOT EXISTS `ext_TableEdit_box_metadata` (
  `box_metadata_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `box_id` int(10) unsigned NOT NULL DEFAULT '0',
  `box_metadata` varchar(255) NOT NULL DEFAULT '',
  `timestamp` int(10) NOT NULL,
  PRIMARY KEY  (`box_metadata_id`),
  KEY `box_id` (`box_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ext_TableEdit_relations`
--

CREATE TABLE IF NOT EXISTS `ext_TableEdit_relations` (
  `rel_id` int(8) NOT NULL AUTO_INCREMENT,
  `from_row` int(10) NOT NULL,
  `from_field` int(4) NOT NULL,
  `to_row` int(10) NOT NULL,
  `to_field` int(4) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (`rel_id`),
  KEY `from_row` (`from_row`),
  KEY `to_row` (`to_row`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ext_TableEdit_row`
--

CREATE TABLE IF NOT EXISTS `ext_TableEdit_row` (
  `row_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `box_id` int(10) unsigned NOT NULL,
  `owner_uid` int(10) DEFAULT NULL,
  `row_data` text NOT NULL,
  `row_style` varchar(255) NOT NULL,
  `row_sort_order` int(11) NOT NULL,
  `row_locked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`row_id`),
  KEY `box_id` (`box_id`),
  FULLTEXT KEY `row_data` (`row_data`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ext_TableEdit_row_metadata`
--

CREATE TABLE IF NOT EXISTS `ext_TableEdit_row_metadata` (
  `row_metadata_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `row_id` int(10) unsigned NOT NULL DEFAULT '0',
  `row_metadata` varchar(255) NOT NULL DEFAULT '',
  `timestamp` int(10) NOT NULL,
  PRIMARY KEY  (`row_metadata_id`),
  KEY `row_id` (`row_id`),
  KEY `row_metadata` (`row_metadata`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
