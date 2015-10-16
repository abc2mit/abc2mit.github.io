<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

class LinkrModelConfig extends LinkrModel
{
	function LinkrModelConfig() {
		parent::__construct();
	}
	
	// Get component parameters
	/*function &getParams($rTable = false)
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
	}*/
	
	function store()
	{
		// Table, parameters
		$table	= & $this->getParams(true);
		$params	= & $this->getParams(false);
		
		// New parameters
		$np	= JRequest::getVar('params', array(), 'POST', 'array');
		$params->loadArray($np);
		
		// Save parameters
		$table->params	= $params->toString();
		if (!$table->store()) {
			return $this->setError($table->getError());
		}
		
		return true;
	}
}
