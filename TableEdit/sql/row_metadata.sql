--
-- Table structure for table `ext_TableEdit_row_metadata`
--

CREATE TABLE IF NOT EXISTS `ext_TableEdit_row_metadata` (
  `row_metadata_id` int(10) unsigned NOT NULL auto_increment,
  `row_id` int(10) unsigned NOT NULL default '0',
  `row_metadata` varchar(255) NOT NULL default '',
  `timestamp` int(10) NOT NULL,
  PRIMARY KEY  (`row_metadata_id`),
  KEY `row_id` (`row_id`),
  KEY `row_metadata` (`row_metadata`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;