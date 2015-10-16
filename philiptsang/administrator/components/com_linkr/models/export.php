<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

class LinkrModelExport extends LinkrModel
{
	function LinkrModelExport() {
		parent::__construct();
	}
	
	function import($source = 'file')
	{
		// Get import data
		switch ($source)
		{
			case 'text':
				$data	= JRequest::getString('text', '', 'POST', JREQUEST_ALLOWRAW);
				break;
			
			default:
				$data	= $this->_fileData();
				break;
		}
		
		// Check for errors and import
		if ($error = $this->getError()) {
			return false;
		} elseif (!$amount = $this->_import($data)) {
			return false;
		}
		
		return $amount;
	}
	
	function _fileData()
	{
		$file	= JRequest::getVar('file', '', 'files', 'array');
		
		// Checks
		if (strlen($file['name']) < 5) {
			$this->setError(JText::_('INVALID_CSV'));
			return false;
		}
		jimport('joomla.filesystem.file');
		$format	= strtolower(JFile::getExt($file['name']));
		if ($format != 'csv') {
			$this->setError(JText::_('INVALID_CSV'));
			return false;
		}
		
		// See administrator >> components >> com_media >> helpers >> media.php
		$xss	= JFile::read($file['tmp_name'], false, 256);
		$tags	= array('abbr','acronym','address','applet','area','audioscope','base','basefont','bdo','bgsound','big','blackface','blink','blockquote','body','bq','br','button','caption','center','cite','code','col','colgroup','comment','custom','dd','del','dfn','dir','div','dl','dt','em','embed','fieldset','fn','font','form','frame','frameset','h1','h2','h3','h4','h5','h6','head','hr','html','iframe','ilayer','img','input','ins','isindex','keygen','kbd','label','layer','legend','li','limittext','link','listing','map','marquee','menu','meta','multicol','nobr','noembed','noframes','noscript','nosmartquotes','object','ol','optgroup','option','param','plaintext','pre','rt','ruby','s','samp','script','select','server','shadow','sidebar','small','spacer','span','strike','strong','style','sub','sup','table','tbody','td','textarea','tfoot','th','thead','title','tr','tt','ul','var','wbr','xml','xmp','!DOCTYPE', '!--');
		foreach ($tags as $t) {
			if(stristr($xss, '<'. $t .' ') || stristr($xss, '<'. $t .'>')) {
				$this->setError(JText::_('INVALID_CSV'));
				return false;
			}
		}
		
		return JFile::read($file['tmp_name'], false);
	}
	
	function _import($data = '')
	{
		if (!is_string($data) || JString::strlen($data) < 10 || JString::strpos($data, 'Bookmarks//Linkr') !== 0) {
			$this->setError(JText::_('INVALID_CSV'));
			return false;
		}
		$data	= explode("\r\n", $data);
		if (JString::strpos($data[1], 'name') !== 0) {
			$this->setError(JText::_('INVALID_CSV'));
			return false;
		} elseif (!isset($data[2]) || strlen($data[2]) < 5) {
			$this->setError(JText::_('INVALID_CSV'));
			return false;
		}
		
		// Get current bookmarks
		$this->_db->setQuery('SELECT name FROM #__linkr_bookmarks');
		$names	= (array) $this->_db->loadResultArray();
		$names	= empty($names) ? array(JText::_('NONE')) : $names;
		
		// Get new bookmarks
		$new	= array();
		$amount	= 0;
		for ($i = 2, $n = count($data); $i < $n; $i++)
		{
			$as	= $this->_getAttributes($data[$i]);
			$bm	= $this->_removeQuotes($as);
			if (!in_array($bm[0], $names)) {
				$new[]	= $bm;
				$amount++;
			}
		}
		if (empty($new)) {
			$this->setError(JText::_('NO_BMS'));
			return false;
		}
		
		// Add new bookmarks
		$query	= 'INSERT INTO #__linkr_bookmarks %1$s VALUES %2$s';
		$cols	= explode(',', $data[1]);
		foreach ($cols as $i => $c) {
			$cols[$i]	= $this->_db->nameQuote($c);
		}
		$bms	= array();
		foreach ($new as $b) {
			$bm	= array();
			foreach ($b as $z) {
				$bm[]	= is_numeric($z) ? (int) $z : $this->_db->Quote(htmlspecialchars_decode($z, ENT_COMPAT));
			}
			$bms[]	= implode(',', $bm);
		}
		$this->_db->setQuery(
			sprintf($query,
				'('. implode(',', $cols) .')',
				'('. implode('),(', $bms) .')'
		));
		$this->_db->query();
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->stderr());
			return false;
		}
		
		return $amount;
	}
	
	function _getAttributes($data)
	{
		$sep	= '-__LINKR__TEMP__SEP__-';
		$data	= JString::str_ireplace('","', '"'. $sep .'"', $data);
		return explode($sep, $data);
	}
	
	function _removeQuotes($line)
	{
		if (is_string($line) && JString::strpos($line, '"') === 0) {
			return JString::substr($line, 1, -1);
		} elseif (is_array($line)) {
			foreach ($line as $i => $l) {
				$line[$i]	= $this->_removeQuotes($l);
			}
			return $line;
		} else {
			return $line;
		}
	}
	
	function getCSV()
	{
		// CSV file
		$csv	= array();
		
		// Header
		$csv[]	= 'Bookmarks//Linkr '. LINKR_VERSION;
		
		// Bookmarks
		$bms	= (array) $this->_getList('SELECT * FROM #__linkr_bookmarks ORDER BY ordering');
		if (empty($bms) || empty($bms[0])) {
			return implode("\r\n", $csv);
		}
		
		// Columns
		$model	= $bms[0];
		$cols	= array();
		foreach (get_object_vars($bms[0]) as $name => $value) {
			if ($name != 'id') {
				$cols[]	= $name;
			}
		}
		$csv[]	= implode(',', $cols);
		
		
		foreach ($bms as $b)
		{
			$bm	= array();
			foreach (get_object_vars($b) as $k => $v) {
				if ($k != 'id') {
					$bm[]	= $this->_line($v);
				}
			}
			$csv[]	= implode(',', $bm);
		}
		
		return implode("\r\n", $csv);
	}
	
	function _line($line)
	{
		if (is_array($line)) {
			foreach ($line as $k => $v) {
				$line[$k]	= $this->_line($v);
			}
			return $line;
		} elseif (is_numeric($line)) {
			return '"'. $line .'"';
		} elseif (!strlen($line)) {
			return '""';
		} elseif (JString::strpos($line, '<') === false) {
			return '"'. JString::str_ireplace('"', '&quot;', $line) .'"';
		} else {
			$line	= JString::str_ireplace(array("\r", "\n", "\r\n"), ' ', $line);
			return '"'. htmlspecialchars($line, ENT_COMPAT, 'UTF-8') .'"';
		}
	}
}
