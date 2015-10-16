<?php
defined('_JEXEC') or die;
define('index', 'index.php?option=com_linkr');

// Require some stuff
require_once(JPATH_COMPONENT.DS.'model.php');
require_once(JPATH_COMPONENT.DS.'view.php');
require_once(JPATH_COMPONENT.DS.'controller.php');
require_once(JPATH_COMPONENT.DS.'helper.php');

// Get task and controller
$task	= JRequest::getCmd('task');
if (strpos($task, '.') && count(explode('.', $task)) == 2)
{
	list($cName, $task)	= explode('.', $task);

	// Define the controller name and path
	$cName	= strtolower( $cName );
	$file	= JPATH_COMPONENT.DS.'controllers'.DS.$cName.'.php';

	if (file_exists( $file )) {
		require_once( $file );
	} else {
		JError::raiseError(500, 'Invalid Controller');
	}
	$cName	= 'LinkrController'. ucfirst( $cName );
}

elseif ($cName = JRequest::getWord('controller'))
{
	$cName	= strtolower( $cName );
	$file	= JPATH_COMPONENT.DS.'controllers'.DS.$cName.'.php';

	if (file_exists( $file )) {
		require_once( $file );
	} else {
		JError::raiseError(500, 'Invalid Controller');
	}
	$cName	= 'LinkrController'. ucfirst( $cName );
}

else {
	$cName	= 'LinkrController';
}

// Perform task
$controller = new $cName();
$controller->execute( $task );
$controller->redirect();
