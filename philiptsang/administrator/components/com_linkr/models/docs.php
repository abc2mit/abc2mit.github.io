<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

class LinkrModelDocs extends LinkrModel
{
	function LinkrModelDocs() {
		parent::__construct();
	}
	
	function getTemplate()
	{
		global $mainframe;
		
		$tmpl	= $mainframe->getUserStateFromRequest('linkr.docs', 'about', '', 'word');
		
		switch ( $tmpl )
		{
			case 'bookmarks':
			case 'debug':
			case 'related':
				$template	= $tmpl;
				break;
			
			default:
				$template	= 'faqs';
		}
		
		return $template;
	}
	
	function &getRssFeed()
	{
		// Disabled
		$params = & JComponentHelper::getParams('com_linkr');
		if (!$params->get('rss', 1)) {
			$false	= false;
			return $false;
		}
		
		// Return feeds
		return JFactory::getXMLparser('RSS', array(
			'rssUrl'	=> 'http://feeds.feedburner.com/JoomlaLinkr'
		));
	}
}
