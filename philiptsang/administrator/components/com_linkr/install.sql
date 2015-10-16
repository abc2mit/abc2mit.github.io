CREATE TABLE IF NOT EXISTS `#__linkr_bookmarks` (
	`id` int(5) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(20) NOT NULL DEFAULT '',
	`text` varchar(50) NOT NULL DEFAULT '',
	`size` varchar(20) NOT NULL DEFAULT 'text',
	`htmltext` tinytext NOT NULL,
	`htmlsmall` tinytext NOT NULL,
	`htmllarge` tinytext NOT NULL,
	`htmlbutton` text NOT NULL,
	`htmlcustom` text NOT NULL,
	`ordering` tinyint(2) NOT NULL DEFAULT '0',
	`icon` varchar(100) NOT NULL DEFAULT '',
	`popular` int(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;
