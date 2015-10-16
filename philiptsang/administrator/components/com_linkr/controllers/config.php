<?php
defined('_JEXEC') or die;

class LinkrControllerConfig extends JController
{
	function LinkrControllerConfig() {
		parent::__construct();
	}
	
	function edit()
	{
		JRequest::setVar('view', 'config');
		JRequest::setVar('hidemainmenu', 1);
		
		parent::display();
	}
	
	function save()
	{
		JRequest::checkToken() or jexit('invalid token');
		
		$model	= & $this->getModel('Config');
		$msg	= $model->store() ? JText::_('NOTICE_SAVED') : $model->getError();
		
		$this->setRedirect(index .'&view=docs&about=faqs', $msg);
	}
	
	function apply()
	{
		JRequest::checkToken() or jexit('invalid token');
		
		$model	= & $this->getModel('Config');
		$msg	= $model->store() ? JText::_('NOTICE_SAVED') : $model->getError();
		
		$this->setRedirect(index .'&controller=config&task=edit', $msg);
	}
	
	function cancel() {
		$msg	= JText::_('NOTICE_CANCELLED');
		$this->setRedirect(index .'&view=docs&about=faqs', $msg);
	}
}
