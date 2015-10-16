<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

class LinkrViewCss extends LinkrView
{
	function display($tpl = null)
	{
		// Toolbar
		JToolBarHelper::title('CSS', 'css');
		JToolBarHelper::save();
		
		// Sub menu
		JSubMenuHelper::addEntry(JText::_('DOCUMENTATION'), index .'&view=docs');
		JSubMenuHelper::addEntry(JText::_('BOOKMARKS'), index .'&view=bookmarks');
		JSubMenuHelper::addEntry('CSS', index .'&view=css', true);
		JSubMenuHelper::addEntry(JText::_('IN_EX'), index .'&view=export');
		JSubMenuHelper::addEntry(JText::_('CONFIGURATION'), index .'&controller=config&task=edit');
		
		// Document
		$this->setTitle('CSS');
		$this->addStyle(
			'.icon-48-css{'.
				'background-image:url(components/com_linkr/assets/icon.pad.png);'.
			'}'.
			'textarea.text_area{'.
				'width:100%;'.
				'height:200px;'.
			'}'
		);
		
		// References
		$this->assignRef('css', $this->get('CSS'));
		
		parent::display($tpl);
	}
}
