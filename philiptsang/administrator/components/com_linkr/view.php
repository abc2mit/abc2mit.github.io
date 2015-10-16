<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

class LinkrView extends JView
{
	function LinkrView() {
		parent::__construct();
	}
	
	function display($tpl = null) {
		parent::display($tpl);
	}
	
	function setTitle($title = '')
	{
		$doc	= & JFactory::getDocument();
		$title	= JString::strlen($title) ? 'Linkr: '. $title : 'Linkr';
		$doc->setTitle($doc->getTitle() .' - '. $title);
	}
	
	function addStyle($style = '')
	{
		$doc	= & JFactory::getDocument();
		$doc->addStyleDeclaration($style);
	}
}
