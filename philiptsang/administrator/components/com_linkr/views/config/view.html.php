<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

class LinkrViewConfig extends LinkrView
{
	function display($tpl = null)
	{
		// Toolbar
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::title(JText::_('CONFIGURATION'), 'config');
		
		// Document
		JHTML::_('behavior.tooltip');
		JHTML::_('behavior.keepalive');
		$this->setTitle(JText::_('CONFIGURATION'));
		
		// References
		$this->assignRef('params', $this->get('Params'));
		
		// ToDo: finish moving plugin parameters to backend
		$this->assignRef('plgParams', LinkrHelper::getPluginParameters());
		
		// Slider pane
		jimport('joomla.html.pane');
		$pane	= & JPane::getInstance('sliders');
		$this->assignRef('pane', $pane);
		
		parent::display($tpl);
	}
}
