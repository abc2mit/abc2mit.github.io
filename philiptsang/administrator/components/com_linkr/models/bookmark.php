<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

class LinkrModelBookmark extends LinkrModel
{
	function LinkrModelBookmark()
	{
		parent::__construct();
		
		$ids	= JRequest::getVar('bid', array(0), '', 'array');
		$this->_setID((int) $ids[0]);
	}
	
	function _setID( $id ) {
		$this->_id		= $id;
		$this->_data	= null;
	}
	
	function getBookmark()
	{
		if (empty( $this->_bookmark ))
		{
			$q	=	' SELECT * FROM #__linkr_bookmarks '.
					' WHERE id = '. $this->_id;
			$this->_db->setQuery( $q );
			$this->_bookmark	= $this->_db->loadObject();
		}
		
		if (!$this->_bookmark)
		{
			$this->_bookmark	= new stdClass;
			$this->_bookmark->id	= 0;
			$this->_bookmark->name	= '';
			$this->_bookmark->text	= '';
			$this->_bookmark->size	= 'text';
			$this->_bookmark->htmltext	= '';
			$this->_bookmark->htmlsmall	= '';
			$this->_bookmark->htmllarge	= '';
			$this->_bookmark->htmlbutton	= '';
			$this->_bookmark->htmlcustom	= '';
			$this->_bookmark->ordering	= 0;
			$this->_bookmark->icon	= '';
			$this->_bookmark->popular	= 0;
		}
		
		return $this->_bookmark;
	}
	
	function getLists()
	{
		$b		= $this->getBookmark();
		
		// Lists
		$lists	= array();
		$size	= array();
		
		// Size
		$size[]	= JHTML::_('select.option', 'text', JText::_('SIZE_TEXT'));
		$size[]	= JHTML::_('select.option', 'small', JText::_('SIZE_SMALL_M'));
		$size[]	= JHTML::_('select.option', 'large', JText::_('SIZE_LARGE_M'));
		$size[]	= JHTML::_('select.option', 'button', JText::_('SIZE_BTN_M'));
		$size[]	= JHTML::_('select.option', 'custom', JText::_('SIZE_CSTM'));
		$size	= JHTML::_('select.genericlist', $size, 'size', '', 'value', 'text', $b->size);
		
		// Return lists
		$lists['size']	= $size;
		return $lists;
	}
	
	function store()
	{
		$table	= & $this->getTable();
		$post	= JRequest::get('post');
		
		// Fix HTML
		$post['htmltext']	= JRequest::getString('htmltext', '', 'post', JREQUEST_ALLOWRAW);
		$post['htmltext']	= JString::str_ireplace('&amp;amp;', '&amp;', $post['htmltext']);
		$post['htmlsmall']	= JRequest::getString('htmlsmall', '', 'post', JREQUEST_ALLOWRAW);
		$post['htmlsmall']	= JString::str_ireplace('&amp;amp;', '&amp;', $post['htmlsmall']);
		$post['htmllarge']	= JRequest::getString('htmllarge', '', 'post', JREQUEST_ALLOWRAW);
		$post['htmllarge']	= JString::str_ireplace('&amp;amp;', '&amp;', $post['htmllarge']);
		$post['htmlbutton']	= JRequest::getString('htmlbutton', '', 'post', JREQUEST_ALLOWRAW);
		$post['htmlbutton']	= JString::str_ireplace('&amp;amp;', '&amp;', $post['htmlbutton']);
		$post['htmlcustom']	= JRequest::getString('htmlcustom', '', 'post', JREQUEST_ALLOWRAW);
		$post['htmlcustom']	= JString::str_ireplace('&amp;amp;', '&amp;', $post['htmlcustom']);
		
		// Bind form data to table fields
		if (!$table->bind($post)) {
	        $this->setError( $table->getError() );
	        return false;
		}
		
		// Make sure the record is a valid one
	    if (!$table->check()) {
	        $this->setError( $table->getError() );
	        return false;
	    }
		
		// Save bookmark
	    if (!$table->store()) {
	        $this->setError( $table->getError() );
	        return false;
	    }
		
		return $table->get('id');
	}
	
	function delete()
	{
		$ids	= JRequest::getVar( 'bid', array(0), 'request', 'array' );
		$table	= & $this->getTable();
		
		if (count( $ids )) {
			foreach($ids as $id) {
				if (!$table->delete( $id )) {
					$this->setError( $table->getError() );
					return false;
				}
			}						
		}
		
		return true;
	}
	
	function makePopular( $pop = 1 )
	{
		JRequest::checkToken() or die( 'Invalid Token' );
		
		$ids	= JRequest::getVar( 'bid', array(), 'post', 'array' );
		JArrayHelper::toInteger($ids);
		
		if (count( $ids ) < 1) {
			$this->setError( 'Bad Request' );
			return false;
		}
		
		// Update bookmark
		$ids	= implode( ',', $ids );
		$query	= 'UPDATE #__linkr_bookmarks' .
				' SET popular = '. (int) $pop .
				' WHERE id IN ( '. $ids .' )';
		$this->_db->setQuery( $query );
		if (!$this->_db->query()) {
			$this->setError( $this->_db->getErrorMsg() );
			return false;
		} else {
			return true;
		}
	}
	
	function orderItem( $id, $to )
	{
		$id	= (int) $id;
		if (!$id) {
			$this->setError( JText::_( 'INVALID_ID' ) );
			return false;
		}
		
		$table	= & $this->getTable();
		$table->load( $id );
		if (!$table->move( $to )) {
			$this->setError( $table->getError() );
			return false;
		}
		
		return true;
	}
	
	function reorder( $ids, $orders )
	{
		$total	= count( $ids );
		$table	= & $this->getTable();
		
		// update ordering values
		for($i = 0; $i < $total; $i++)
		{
			$table->load( $ids[$i] );
			
			if ($table->ordering != $orders[$i]) {
				$table->ordering	= $orders[$i];
				if (!$table->store()) {
					$this->setError( $table->getError() );
					return false;
				}
			}
		}
		
		// Sort
		$table->reorder();
		
		return true;
	}
}
