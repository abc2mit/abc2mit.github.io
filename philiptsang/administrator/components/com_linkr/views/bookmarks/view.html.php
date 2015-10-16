<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

class LinkrViewBookmarks extends LinkrView
{
	function display($tpl = null)
	{
		// Toolbar
		JToolBarHelper::customX('install', 'export', 'export', 'DEFAULT', false);
		JToolBarHelper::title(JText::_('BOOKMARKS'), 'bookmarks');
		JToolBarHelper::deleteListX(JText::_('VALIDDELETEITEMS', true));
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		
		// Sub menu
		JSubMenuHelper::addEntry(JText::_('DOCUMENTATION'), index .'&view=docs');
		JSubMenuHelper::addEntry(JText::_('BOOKMARKS'), index .'&view=bookmarks', true);
		JSubMenuHelper::addEntry('CSS', index .'&view=css');
		JSubMenuHelper::addEntry(JText::_('IN_EX'), index .'&view=export');
		JSubMenuHelper::addEntry(JText::_('CONFIGURATION'), index .'&controller=config&task=edit');
		
		// Document
		$this->setTitle(JText::_('BOOKMARKS'));
		$this->addStyle(
			'.icon-48-bookmarks{'.
				'background-image:url(components/com_linkr/assets/icon.bms.png);'.
			'}'.
			'.icon-32-export{'.
				'background-image:url(templates/khepri/images/toolbar/icon-32-export.png);'.
			'}'
		);
		
		$this->assignRef('bookmarks', $this->get('Bookmarks'));
		$this->assignRef('page', $this->get('Pagination'));
		$this->assignRef('order', $this->get('Order'));
		
		parent::display($tpl);
	}
}
