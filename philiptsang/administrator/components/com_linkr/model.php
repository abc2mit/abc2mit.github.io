<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

class LinkrModel extends JModel
{
	// Get component parameters
	function &getParams($rTable = false)
	{
		static $params, $table;
		
		// Parameters
		if (is_null($params))
		{
			$table	= & JTable::getInstance('component');
			$table->loadByOption('com_linkr');
			$params	= new JParameter($table->params);
			$params->loadSetupFile(JPATH_COMPONENT.DS.'config.xml');
		}
		
		// Return table
		if ($rTable) {
			return $table;
		}
		
		// Return params
		return $params;
	}
}
