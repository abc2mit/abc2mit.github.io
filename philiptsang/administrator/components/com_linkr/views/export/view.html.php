<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

class LinkrViewExport extends LinkrView
{
	function display($tpl = null)
	{
		// Toolbar
		JToolBarHelper::title(JText::_('BOOKMARKS') .' - '. JText::_('IN_EX'), 'export');
		
		// Sub menu
		JSubMenuHelper::addEntry(JText::_('DOCUMENTATION'), index .'&view=docs');
		JSubMenuHelper::addEntry(JText::_('BOOKMARKS'), index .'&view=bookmarks');
		JSubMenuHelper::addEntry('CSS', index .'&view=css');
		JSubMenuHelper::addEntry(JText::_('IN_EX'), index .'&view=export', true);
		JSubMenuHelper::addEntry(JText::_('CONFIGURATION'), index .'&controller=config&task=edit');
		
		// Document
		$this->setTitle(JText::_('BOOKMARKS') .' - '. JText::_('IN_EX'));
		$this->addStyle(
			'.icon-48-export{'.
				'background-image:url(images/backup.png);'.
			'}'
		);
		
		parent::display($tpl);
	}
}
