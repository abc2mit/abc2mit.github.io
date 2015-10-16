<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

// Media manager model
class LinkrModelMm extends LinkrModel
{
	function LinkrModelMm() {
		parent::__construct();
	}
	
	// Form info
	function &getFormData()
	{
		if (!isset($this->_fd))
		{
			$i	= new JObject();
			$s	= & JFactory::getSession();
			$i->set('basepath', $this->getBasePath());
			$i->set('files', $this->getFiles());
			$i->set('uploadURL', JURI::base() . index .'&task=file.upload&mm=1&tool=badges&'. JUtility::getToken() .'=1&'. $s->getName() .'='. $s->getId());
			$i->set('deleteURL', JURI::base() . index .'&task=file.delete&mm=1&tool=badges&'. JUtility::getToken() .'=1&'. $s->getName() .'='. $s->getId() .'&file=');
			$this->_fd	= $i;
		}
		
		$ref	= & $this->_fd;
		return $ref;
	}
	
	function getFiles() {
		$b	= $this->getBasePath();
		$e	= '\.(bmp|gif|jpg|jpeg|png)';
		return LinkrHelper::listFiles($b, $e);
	}
	
	function getBasePath() {
		return JPATH_COMPONENT_SITE . DS .'assets'. DS .'badges';
	}
	
	/*
	 * Session variable handling
	 */
	function getState($request, $def = null, $type = 'none') {
		global $mainframe;
		return $mainframe->getUserStateFromRequest('linkr.mm.'. $request, $request, $def, $type);
	}
	function setState($var, $value) {
		global $mainframe;
		return $mainframe->setUserState('linkr.mm.'. $var, $value);
	}
}
