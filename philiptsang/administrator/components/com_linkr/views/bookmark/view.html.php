<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

class LinkrViewBookmark extends LinkrView
{
	function display($tpl = null)
	{
		// Toolbar
		$bookmark	= & $this->get('Bookmark');
		$isNew		= ($bookmark->id < 1);
		$text 		= $isNew ? JText::_('NEW') : JText::_('EDIT');
		JToolBarHelper::title(JText::_('BOOKMARK').': <small><small>[ '. $text .' ]</small></small>', 'bookmark');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			JToolBarHelper::cancel('cancel', JText::_('CLOSE'));
		}
		
		// Document
		JHTML::_('behavior.modal');
		$this->setTitle(JText::_('BOOKMARK'));
		$this->addStyle(
			'.icon-48-bookmark{'.
				'background-image:url(components/com_linkr/assets/icon.bm.png);'.
			'}'
		);
		
		$this->assignRef('bookmark', $bookmark);
		$this->assignRef('lists', $this->get('Lists'));
		
		parent::display($tpl);
	}
}
