--
-- Table structure for table `ext_TableEdit_row`
--

CREATE TABLE IF NOT EXISTS `ext_TableEdit_row` (
  `row_id` int(10) unsigned NOT NULL auto_increment,
  `box_id` int(10) unsigned NOT NULL,
  `owner_uid` int(10) default NULL,
  `row_data` text NOT NULL,
  `row_style` varchar(255) NOT NULL,
  `row_sort_order` int(11) NOT NULL,
  `row_locked` tinyint(1) unsigned not null default 0,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`row_id`),
  KEY `box_id` (`box_id`),
  FULLTEXT KEY `row_data` (`row_data`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;