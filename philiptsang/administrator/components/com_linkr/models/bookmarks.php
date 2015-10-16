<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

class LinkrModelBookmarks extends LinkrModel
{
	function LinkrModelBookmarks() {
		parent::__construct();
	}
	
	function getBookmarks()
	{
		if (isset( $this->_bms )) {
			return $this->_bms;
		}
		
		// Retrieve bookmarks from database
		$o	=	$this->getOrder();
		$q	=	'SELECT id,name,text,ordering,popular '.
				'FROM #__linkr_bookmarks '.
				'ORDER BY '. $o['order'] .
				' '. $o['order_Dir'];
		
		if ($list = $this->_getList( $q )) {
			$this->_bms	= $list;
		} else {
			$this->_bms	= array();
		}
		
		return $this->_bms;
	}
	
	function getOrder()
	{
		if (isset( $this->_order )) {
			return $this->_order;
		}
		
		$o	= array();
		global $mainframe;
		$o['order']			= $mainframe->getUserStateFromRequest( 'linkrbm.order', 'filter_order', 'ordering', 'word' );
		$o['order_Dir']		= $mainframe->getUserStateFromRequest( 'linkrbm.dir', 'filter_order_Dir', 'ASC', 'word' );
		
		$this->_order	= $o;
		return $this->_order;
	}
	
	function getPagination()
	{
		if (!isset( $this->_page ))
		{
			$bms	= $this->getBookmarks();
			jimport( 'joomla.html.pagination' );
			$this->_page	= new JPagination( count( $bms ), 0, 0 );
		}
		
		return $this->_page;
	}
}
