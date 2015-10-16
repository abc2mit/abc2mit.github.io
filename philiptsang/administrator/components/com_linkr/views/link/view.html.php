<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

// Linkr landing page
class LinkrViewLink extends JView
{
	function display($tpl = null)
	{
		// Frontpage hack
		$this->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'link'.DS.'tmpl');
		
		// Only allow "component" template
		JRequest::setVar('tmpl', 'component');
		
		// JFactory
		$user	= & JFactory::getUser();
		$config	= & JFactory::getConfig();
		$doc 	= & JFactory::getDocument();
		$lingo	= & JFactory::getLanguage();
		
		// Include styles
		$doc->addStyleSheet(LINKR_ASSETS .'css/modal.css?'. LINKR_VERSION_INC);
		
		// Template override
		global $mainframe;
		$tmpl	= $mainframe->getTemplate();
		if (file_exists(JPATH_BASE.DS.'templates'.DS.$tmpl.DS.'linkr.css')) {
			$doc->addStyleSheet(JURI::base().'templates/'.$tmpl.'/linkr.css');
		} elseif (file_exists(JPATH_BASE.DS.'templates'.DS.$tmpl.DS.'css'.DS.'linkr.css')) {
			$doc->addStyleSheet(JURI::base().'templates/'.$tmpl.'/css/linkr.css');
		}
		
		// Editor name, request endpoint, document base
		$editor	= JRequest::getString('e_name', 'text');
		$rUrl	= JURI::base() .'index.php?option=com_linkr&view=request&'. JUtility::getToken() .'=1';
		
		// Include scripts
		JHTML::_('behavior.mootools');
		$unc	= LinkrHelper::getParam('compress', 1) ? '.js' : '-UCP.js';
		$doc->addScript(LINKR_ASSETS .'js/helper'. $unc .'?'. LINKR_VERSION_INC);
		$doc->addScriptDeclaration(
			'var Linkr=new LinkrAPI('.
				'['. implode(',', explode('.', LINKR_VERSION_READ)) .'],'.
				'"'. $rUrl .'",'.
				'"'. $editor .'",'.
				'"'. JURI::root() .'",'.
				'['.
					'"'. JRequest::getWord('mode', 'squeezebox') .'",'.
					'"'. $lingo->getTag() .'",'.
					'"'. LinkrHelper::UTF8Encode(JText::_('MISSING_TEXT', true)) .'",'.
					'"'. LinkrHelper::UTF8Encode(JText::_('IMG_ANCHOR', true)) .'",'.
					'"'. LINKR_ASSETS .'img/",'.
					$user->get('aid') .
				']'.
			');'.
			'var LinkrHelper=Linkr;'
		);
		
		// Frontend fix
		if (LinkrHelper::isSite()) {
			//$doc->setBase(LinkrHelper::getLinkrUrl($editor));
		}
		
		// References
		$this->assign('links', $this->get('Links'));
		$this->assign('tools', $this->get('ToolLinks'));
		
		// Localize text
		if ($text = $this->get('L18N'))
		{
			$l18n	= array();
			foreach ($text as $k => $v) {
				$l18n[]	= '["'. $k .'","'. $v .'"]';
			}
			
			$doc->addScriptDeclaration(
				'Linkr.setL18N(['. implode(',', $l18n) .']);'
			);
		}
		
		// 3rd party javascript
		if ($js = $this->get('Scripts')) {
			$doc->addScriptDeclaration($js);
		}
		
		// Load single link
		$load	= '';
		$inc	= $this->get('IncludedLinks');
		if (!$this->tools['count'] && $this->links['count'] == 1) {
			$load	= 'Linkr.__fr=function(){'. $inc[$this->links['name'][0]] .'};';
		} elseif (!$this->links['count'] && $this->tools['count'] == 1) {
			$load	= 'Linkr.__fr=function(){'. $inc[$this->tools['name'][0]] .'};';
		}
		
		// Fire "onLoad" event
		$doc->addScriptDeclaration(
			'window.addEvent("domready",function(){'.
				 $load .'Linkr.fireEvent("onLoad");'.
			'});'
		);
		
		parent::display($tpl);
	}
}
