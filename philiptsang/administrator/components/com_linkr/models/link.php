<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

class LinkrModelLink extends LinkrModel
{
	// Links array
	var $links	= array();
	
	function LinkrModelLink() {
		parent::__construct();
	}
	
	// Returns links array
	function getLinks() {
		return $this->getFormattedLinks('link', 'xlink', 'links');
	}
	
	// Returns tools array
	function getToolLinks() {
		return $this->getFormattedLinks('tool', 'xtool', 'tools');
	}
	
	// Returns array of included links
	function getIncludedLinks() {
		return $this->links;
	}
	
	// Shortcut to format links array
	function getFormattedLinks($type, $xtype, $atype)
	{
		// Trigger event
		JPluginHelper::importPlugin('content');
		$dispatcher	= & JDispatcher::getInstance();
		$event	= 'onLinkrGet'. ucfirst($type) .'s';
		$results	= $dispatcher->trigger($event, array(LINKR_VERSION));
		if (empty($results) || !is_array($results)) {
			return false;
		}
		
		// Format links
		$filter	= & $this->getInputFilter();
		$lks	= array('name' => array(), 'html' => array());
		list($i, $x)	= $this->getFilters($type, $xtype, $atype);
		foreach ($results as $link)
		{
			// Performance check
			if (!is_array($link) || empty($link)) {
				continue;
			}
			
			foreach ($link as $text => $js)
			{
				// Link details
				if (is_array($js)) {
					$name	= @$js['name'];
					$text	= @$js['text'];
					$js		= @$js['click'];
				} else {
					$name	= strtolower(trim($text));
					$name	= $filter->clean($name, 'CMD');
					$text	= JText::_($text);
				}
				
				// Performance check
				if (empty($name) || empty($text) || empty($js)) {
					continue;
				}
				
				// Exclude link
				if ($x && ((is_string($x) && $x == 'all') || isset($x[$name]))) {
					$this->links[$name]	= false;
					continue;
				}
				
				// Don't include
				if ($i && !isset($i[$name])) {
					$this->links[$name]	= false;
					continue;
				}
				
				$this->links[$name]	= $js;
				$lks['name'][]	= $name;
				$lks['html'][]	= '<a href="javascript:void(0);" class="linkr-lks" rel="'. $name .'" onclick="'. $js .'">'. $text .'</a>';
			}
		}
		
		// Return formated links
		$lks['count']	= count($lks['name']);
		return $lks;
	}
	
	// Shortcut to filter links
	function getFilters($type, $xtype, $atype)
	{
		// Exclude all
		$all	= JRequest::getInt($atype, -1);
		if ($all == 0) {
			return array(false, 'all');
		}
		
		$filter	= & $this->getInputFilter();
		
		// Add links
		$include	= array();
		$i	= JRequest::getVar($type, array(), 'REQUEST', 'ARRAY');
		if (count($i))
		{
			foreach ($i as $l)
			{
				$l	= $filter->clean($l, 'CMD');
				
				if (strlen($l)) {
					$include[$l]	= true;
				}
			}
		}
		$include	= count($include) ? $include : false;
		
		// Exclude links
		$exclude	= array();
		$x	= JRequest::getVar($xtype, array(), 'REQUEST', 'ARRAY');
		if (count($x))
		{
			foreach ($x as $l)
			{
				$l	= $filter->clean($l, 'CMD');
				
				if (strlen($l)) {
					$exclude[$l]	= false;
				}
			}
		}
		$exclude	= count($exclude) ? $exclude : false;
		
		return array($include, $exclude);
	}
	
	// Shortcut to get FilterInput
	function &getInputFilter()
	{
		static $filter;
		if (is_null($filter)) {
			jimport('joomla.filter.filterinput');
			$filter	= & JFilterInput::getInstance();
		}
		
		return $filter;
	}
	
	// Returns localized text
	function getL18N()
	{
		// Trigger "onLinkrLoadL18N"
		JPluginHelper::importPlugin('content');
		$dispatcher	= & JDispatcher::getInstance();
		$results	= $dispatcher->trigger('onLinkrLoadL18N', array(LINKR_VERSION, $this->links));
		if (empty($results) || !is_array($results)) {
			return false;
		}
		
		// Get localized text
		$l18n	= array();
		foreach ($results as $strings)
		{
			// Performance check
			if (empty($strings)) {
				continue;
			}
			
			// Include localized text (once)
			foreach ($strings as $s)
			{
				if (!isset($l18n[$s])) {
					$l18n[$s]	= LinkrHelper::UTF8Encode(JText::_($s, true));
				}
			}
		}
		
		// Return localized text
		return empty($l18n) ? false : $l18n;
	}
	
	// Returns javascript statements
	function getScripts()
	{
		// Trigger "onLinkrLoadJS"
		JPluginHelper::importPlugin('content');
		$dispatcher	= & JDispatcher::getInstance();
		$results	= $dispatcher->trigger('onLinkrLoadJS', array(LINKR_VERSION, $this->links));
		if (empty($results) || !is_array($results)) {
			return false;
		}
		
		// Collect javascript statements
		$js	= '';
		foreach ($results as $script)
		{
			if (!empty($script))
			{
				$js	.= $script;
				if (strrpos($script, ';') !== 0) {
					$js	.= ';';
				}
			}
		}
		
		// Return script declarations
		return empty($js) ? false : $js;
	}
}
