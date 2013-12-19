--
-- Table structure for table `ext_TableEdit_box`
--

CREATE TABLE IF NOT EXISTS `ext_TableEdit_box` (
  `box_id` int(10) unsigned NOT NULL auto_increment,
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