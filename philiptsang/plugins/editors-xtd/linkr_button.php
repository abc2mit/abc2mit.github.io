<?php
defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');

class plgButtonLinkr_button extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject	The object to observe
	 * @param array $config	An array that holds the plugin configuration
	 * @since				1.5
	 */
	function plgButtonLinkr_button(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	function onDisplay($name = 'text')
	{
		// Button image
		$doc 	= & JFactory::getDocument();
		$bUrl	= JURI::root() .'components/com_linkr/assets/img/button.png';
		$doc->addStyleDeclaration('.button2-left .linkr{background:url('. $bUrl .') 100% 0 no-repeat;}');
		
		// Linkr URL
		list($modal, $link, $click)	= $this->getLink($name);
		
		// Linkr button
		$btn	= new JObject();
		$btn->set('link', $link);
		$btn->set('name', 'linkr');
		$btn->set('text', 'Linkr');
		$btn->set('modal', $modal);
		$btn->set('onclick', $click);
		if ($modal) {
			$btn->set('options', "{handler:'iframe',size:{x:620,y:330}}");
		}
		
		return $btn;
	}

	/**
	 * Returns Linkr URL
	 *
	 * NOTE: This URL needs to be the same in LinkrHelper::getLinkrUrl
	 */
	function getLink($editor)
	{
		// Browser
		jimport('joomla.environment.browser');
		$browser	= & JBrowser::getInstance();
		
		// Linkr URL
		$link	= 'index.php?option=com_linkr&amp;view=link&amp;tmpl=component&amp;e_name='. $editor;
		
		// Use "popup" mode for IE
		if ($browser->getBrowser() == 'msie')
		{
			$modal	= false;
			$link	.= '&amp;mode=popup';
			$click	= 'return popLinkr(this);';
			
			// Insert script
			JHTML::_('behavior.mootools');
			$doc	= & JFactory::getDocument();
			$doc->addScriptDeclaration(
				'function popLinkr(a)
				{
					// Window position
					var s	= window.getSize().size;
					var t	= ((s.y - 350) / 2).round();
					var l	= ((s.x - 620) / 2).round();
					
					// Open Linkr
					window.open(a.href, "linkr", "dependent=1,scrollbars=1,width=620,height=350,top="+ t +",left="+ l);
					
					return false;
				}
				
				function LinkrInsert(y, o, e) {
					jInsertEditorText((y == "object" ? o.html : o), e);
				}'
			);
		}
		
		// Regular squeezebox
		else {
			$modal	= true;
			$click	= false;
			JHTML::_('behavior.modal');
		}
		
		// Return link
		$link	.= '&amp;'. JUtility::getToken() .'=1';
		return array($modal, $link, $click);
	}
}

/*
 * Compatibility issues?
 */
if (!class_exists('plgButtonlinkr_button')) {
	class plgButtonlinkr_button extends plgButtonLinkr_button {}
}

