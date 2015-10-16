<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

class LinkrModelCss extends LinkrModel
{
	function LinkrModelCss() {
		parent::__construct();
	}
	
	function getCSS()
	{
		// Get params
		//$table	= & JTable::getInstance('component');
		//$table->loadByOption('com_linkr');
		//$params	= new JParameter($table->params);
		$params	= & $this->getParams();
		
		// Return CSS
		return array(
			'bcss'	=> base64_decode($params->get('bcss', '')),
			'rcss'	=> base64_decode($params->get('rcss', ''))
		);
	}
}
