<?php
defined('_JEXEC') or die;

/*
 * Linkr Super Installer
 *
 * @version		2.3.4
 *
 */
function com_uninstall()
{
	// Include install helper
	$helper	= dirname(__FILE__) . DS . 'install.linkr.php';
	if (file_exists($helper)) {
		require_once($helper);
		if (!class_exists('LinkrInstaller')) {
			return true;
		}
	} else {
		return true;
	}
	
	// Uninstall plugins
	return LinkrInstaller::uninstall();
}

