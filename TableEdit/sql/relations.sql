--
-- Table structure for table `ext_TableEdit_relations`
--

CREATE TABLE IF NOT EXISTS `ext_TableEdit_relations` (
  `rel_id` int(8) NOT NULL auto_increment,
  `from_row` int(10) NOT NULL,
  `from_field` int(4) NOT NULL,
  `to_row` int(10) NOT NULL,
  `to_field` int(4) NOT NULL,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`rel_id`),
  KEY `from_row` (`from_row`),
  KEY `to_row` (`to_row`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;