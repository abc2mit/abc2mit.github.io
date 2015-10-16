<?php
defined('_JEXEC') or die;

class TableBookmark extends JTable
{
	var $id;
	var $name;
	var $text;
	var $size;
	var $htmltext;
	var $htmlsmall;
	var $htmllarge;
	var $htmlbutton;
	var $htmlcustom;
	var $ordering;
	var $icon;
	var $popular;
	
	function TableBookmark( &$db ) {
		parent::__construct('#__linkr_bookmarks', 'id', $db);
	}
	
	function check()
	{
		// Check name
		$this->name	= JString::trim( $this->name );
		if (empty( $this->name )) {
			$this->setError( JText::_( 'NOTICE_MISSING_NAME' ) );
			return false;
		}
		
		// Check html
		$this->htmltext		= trim( $this->htmltext );
		$this->htmlsmall	= trim( $this->htmlsmall );
		$this->htmllarge	= trim( $this->htmllarge );
		$this->htmlbutton	= trim( $this->htmlbutton );
		$this->htmlcustom	= trim( $this->htmlcustom );
		$defSize	= 'html'. $this->size;
		if (empty( $this->$defSize )) {
			$this->setError( JText::_( 'NOTICE_MISSING_HTML' ) );
			return false;
		}
		
		$this->popular	= ($this->popular) ? 1 : 0;
		$this->text		= JString::trim( $this->text );
		if (empty( $this->text )) {
			$this->text	= $this->name;
		}
		
		return true;
	}
}
