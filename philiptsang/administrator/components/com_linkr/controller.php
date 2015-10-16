<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');

class LinkrController extends JController
{
	function display()
	{
		// Make the documentation the homepage
		if (!JRequest::getVar('view')) {
			JRequest::setVar('view', 'docs');
		}
		
		parent::display();
	}
}
