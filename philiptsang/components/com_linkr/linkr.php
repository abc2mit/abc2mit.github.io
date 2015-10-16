<?php
defined('_JEXEC') or die;
define('index', 'index.php?option=com_linkr');

// Load admin languages
$lingo	= & JFactory::getLanguage();
$lingo->load('joomla', JPATH_ADMINISTRATOR);
$lingo->load('com_linkr', JPATH_ADMINISTRATOR);

// Require some stuff
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'model.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'view.php');
require_once(JPATH_COMPONENT.DS.'controller.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helper.php');

// Require specific controller if requested
if ($controller = JRequest::getWord('controller')) {
	require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'controllers'.DS.$controller.'.php');
}

// Perform task
$classname	= 'LinkrController'.$controller;
$controller = new $classname();
$controller->execute(JRequest::getCmd('task'));
