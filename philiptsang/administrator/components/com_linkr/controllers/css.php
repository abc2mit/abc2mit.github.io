<?php
defined('_JEXEC') or die;

class LinkrControllerCss extends JController
{
	function LinkrControllerCss() {
		parent::__construct();
	}
	
	function save()
	{
		if (!JRequest::checkToken('request')) {
			return $this->setRedirect(index, 'invalid token');
		}
		
		// Get styles
		$b	= JRequest::getString('bcss', '', 'POST');
		$r	= JRequest::getString('rcss', '', 'POST');
		
		// Save parameters
		$table	= & JTable::getInstance('component');
		$table->loadByOption('com_linkr');
		$params	= new JParameter($table->params);
		$params->set('bcss', base64_encode($b));
		$params->set('rcss', base64_encode($r));
		$table->params	= $params->toString();
		
		$m	= $table->store() ? JText::_('NOTICE_SAVED') : $table->getError();
		$this->setRedirect(index .'&view=css', $m);
	}
	
	// Default bookmarks styles
	function defb()
	{
		if (!JRequest::checkToken('request')) {
			return $this->setRedirect(index, 'invalid token');
		}
		
		$b	=
		'div.linkr-bm {'."\n".
		' margin:20px 30px 5px 30px;'."\n".
		'}'."\n".
		'div.linkr-bm div.linkr-bm-pre,'."\n".
		'div.linkr-bm div.linkr-bm-post {'."\n".
		' float:right;'."\n".
		' font-size:14px;'."\n".
		' letter-spacing:2px;'."\n".
		'}'."\n".
		'div.linkr-bm div.linkr-bm-sep {float:right;}'."\n".
		'div.linkr-bm div.linkr-bm-b {'."\n".
		' float:right;'."\n".
		' padding:4px;'."\n".
		' border:1px solid transparent;'."\n".
		'}'."\n".
		'div.linkr-bm div.linkr-bm-b img {'."\n".
		' margin:0;'."\n".
		'}'."\n".
		'div.linkr-bm div.linkr-bm-b:hover {'."\n".
		' border-color:#aaa;'."\n".
		' background-color:#ddd;'."\n".
		'}'."\n".
		'div.linkr-bm-after {clear:both;}';
		
		// Restore default
		$table	= & JTable::getInstance('component');
		$table->loadByOption('com_linkr');
		$params	= new JParameter($table->params);
		$params->set('bcss', base64_encode($b));
		$table->params	= $params->toString();
		
		$m	= $table->store() ? JText::_('NOTICE_SAVED') : $table->getError();
		$this->setRedirect(index .'&view=css', $m);
	}
	
	// Default related styles
	function defr()
	{
		if (!JRequest::checkToken('request')) {
			return $this->setRedirect(index, 'invalid token');
		}
		
		$r	=
		'div.linkr-rl {'."\n".
		' margin-top:20px;'."\n".
		' padding:10px 5px 0 5px;'."\n".
		' border-top:1px dotted #ccc;'."\n".
		'}'."\n".
		'div.linkr-rl div.linkr-rl-t {'."\n".
		' font-size:1.1em;'."\n".
		' letter-spacing:2px;'."\n".
		' text-transform:uppercase;'."\n".
		'}'."\n".
		'div.linkr-rl ul {'."\n".
		' list-style-type:square;'."\n".
		' line-height:1.5em;'."\n".
		' text-indent:5px;'."\n".
		'}'."\n".
		'div.linkr-rl ul li {'."\n".
		' padding:0 5px;'."\n".
		' background:none;'."\n".
		'}';
		
		// Restore default
		$table	= & JTable::getInstance('component');
		$table->loadByOption('com_linkr');
		$params	= new JParameter($table->params);
		$params->set('rcss', base64_encode($r));
		$table->params	= $params->toString();
		
		$m	= $table->store() ? JText::_('NOTICE_SAVED') : $table->getError();
		$this->setRedirect(index .'&view=css', $m);
	}
}
