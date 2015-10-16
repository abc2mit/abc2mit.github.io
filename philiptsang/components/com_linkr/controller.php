<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');

class LinkrController extends JController
{
	function LinkrController()
	{
		parent::__construct();
		
		// Include paths
		$this->addViewPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'views');
		$this->addModelPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
	}
	
	function display()
	{
		// Prevent access through "index.php?option=com_linkr"
		if (!JRequest::checkToken('request')) {
			JError::raiseError(403, JText::_('ACCESS FORBIDDEN'));
		}
		
		// Set view
		JRequest::setVar('tmpl', 'component');
		if (JRequest::getWord('view') != 'request') {
			JRequest::setVar('view', 'link');
		}
		
		parent::display(false);
	}
}
