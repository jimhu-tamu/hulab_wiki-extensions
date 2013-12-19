--
-- Table structure for table `ext_TableEdit_box_metadata`
--

CREATE TABLE IF NOT EXISTS `ext_TableEdit_box_metadata` (
  `box_metadata_id` int(10) unsigned NOT NULL auto_increment,
  `box_id` int(10) unsigned NOT NULL default '0',
  `box_metadata` varchar(255) NOT NULL default '',
  `timestamp` int(10) NOT NULL,
  PRIMARY KEY  (`box_metadata_id`),
  KEY `box_id` (`box_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;