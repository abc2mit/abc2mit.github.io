<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

class LinkrViewDocs extends LinkrView
{
	function display($tpl = null)
	{
		// Toolbar
		JToolBarHelper::title(JText::_('DOCUMENTATION'), 'info');
		//JToolBarHelper::preferences('com_linkr', 240, 600);
		
		// Sub menu
		JSubMenuHelper::addEntry(JText::_('DOCUMENTATION'), index .'&view=docs', true);
		JSubMenuHelper::addEntry(JText::_('BOOKMARKS'), index .'&view=bookmarks');
		JSubMenuHelper::addEntry('CSS', index .'&view=css');
		JSubMenuHelper::addEntry(JText::_('IN_EX'), index .'&view=export');
		JSubMenuHelper::addEntry(JText::_('CONFIGURATION'), index .'&controller=config&task=edit');
		
		// Document
		$this->setTitle(JText::_('DOCUMENTATION'));
		$this->addStyle(
			'.icon-48-info{'.
				'background-image:url(components/com_linkr/assets/icon.docs.png);'.
			'}'.
			'.linkrc{'.
				'padding:0 20px;'.
			'}'.
			'.linkr-btn{'.
				'margin:0 20px;'.
				'padding:5px;'.
				'border:1px solid;'.
			'}'.
			'.linkr-btn:hover{}'
		);
		
		// HTML examples
		define('lTab', '&nbsp;&nbsp;&nbsp;');
		define('lDivL', '&lt;div class=&quot;%s&quot;&gt;');
		define('lDiv', lDivL .'<br/>');
		
		// Template
		$this->assign('about', $this->get('Template'));
		
		// RSS feed
		$this->assignRef('feed', $this->get('RssFeed'));
		
		parent::display($tpl);
	}
}
