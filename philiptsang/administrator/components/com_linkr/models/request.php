<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

class LinkrModelRequest extends LinkrModel
{
	// Allowed file extensions
	var $_fileExt	= array(
		'aiff', 'asf', 'avi', 'bmp', 'css', 'csv', 'doc', 'docx', 'gif',
		'gz', 'htm', 'html', 'jar', 'jpg', 'jpeg', 'js', 'm3u', 'mid',
		'mov', 'mp3', 'mp4', 'mpg', 'mpeg', 'odg', 'odp', 'ogg', 'ods',
		'odt', 'pdf', 'png', 'ppt', 'qt', 'rar', 'rm', 'rtf', 'swf', 'tar',
		'tar.bz2', 'tar.gz', 'txt', 'wav', 'wks', 'wps', 'wpt', 'xcf',
		'xlr', 'xls', 'xlsx', 'xlt', 'xltx', 'xml', 'zip'
	);
	
	// Allowed bookmark extensions
	var $_bmExt	= array('gif', 'ico', 'jpg', 'jpeg', 'png');
	
	function LinkrModelRequest() {
		parent::__construct();
	}
	
	// Returns requested content
	function getRequest()
	{
		// Performance check
		$request	= JRequest::getWord('request');
		if (!is_string($request) || !strlen($request)) {
			return json_encode($this->badRequest('Empty request'));
		}
		
		// Get content
		switch ($request)
		{
			case 'bm':
			case 'ra':
			case 'dirs':
			case 'files':
			case 'dblist':
			case 'dbobject':
			case 'paths':
			case 'mjs':
				$results	= $this->$request();
				break;
			
			default:
				$results	= $this->badRequest();
		}
		
		// Return results
		return json_encode($results);
	}
	
	// AJAX test
	function mjs() {
		return array('status' => 'ok', 'result' => 1);
	}
	
	// Returns bookmarks data
	function bm()
	{
		//$pc	= & LinkrHelper::getPluginParameters();
		//$p	= $pc ? $pc : new JObject();
		$p	= & $this->getParams();
		
		// Settings
		$s	= JRequest::getVar('s', array(), 'array');
		if (!isset($s['size']) || !strlen($s['size'])) {
			$s['size']	= $p->get('bm_size', 'text');
		}
		if (!isset($s['text']) || !strlen($s['text'])) {
			$s['text']	= 'nn';
		}
		if (!isset($s['separator']) || !JString::strlen($s['separator'])) {
			$s['separator']	= ' ';
		}
		if (!isset($s['badges']) || !strlen($s['badges'])) {
			$s['badges']	= ($p->get('bm_select', '0')) ? '*' : 'p';
		}
		$s	= LinkrHelper::UTF8Encode($s);
		
		// Bookmarks list
		$s['bookmarks']	= $this->getBookmarksList();
		if (is_array($s['bookmarks'])) {
			foreach ($s['bookmarks'] as $i => $b) {
				$this->getIcon( $s['bookmarks'][$i] );
			}
		}
		
		// Return bookmarks data
		return $s;
	}
	
	// Returns bookmarks list
	function getBookmarksList()
	{
		// Query database
		$b	= $this->_getList(
			'SELECT id, name, icon, popular '.
			'FROM #__linkr_bookmarks '.
			'ORDER BY ordering, name'
		);
		
		// Return results
		if ($this->_db->getErrorNum()) {
			return 'Database Error: '. $this->_db->getErrorMsg();
		} elseif (empty($b) || empty($b[0])) {
			return JText::_('NORESULTS');
		} else {
			return $b;
		}
	}
	
	// Formats a bookmark icon
	function getIcon( &$b )
	{
		// Encode name
		$name	= LinkrHelper::UTF8Encode($b->name);
		
		// No icon
		if (empty( $b->icon )) {
			$b->icon	= $name;
			return;
		}
		
		// Check icon URL
		if (strpos($b->icon, 'http') !== 0)
		{
			if (strpos($b->icon, '/') === 0) {
				$b->icon	= substr($b->icon, 1);
			}
			$b->icon	= JURI::root() . $b->icon;
		}
		
		// Check icon extension
		jimport('joomla.filesystem.file');
		$ext	= JFile::getExt($b->icon);
		if (!in_array($ext, $this->_bmExt)) {
			$b->icon	= $name;
		}
	}
	
	// Returns related articles data
	function ra()
	{
		$c	= array();
		//$pc	= & LinkrHelper::getPluginParameters();
		//$p	= $pc ? $pc : new JObject();
		$p	= & $this->getParams();
		
		// Related articles data
		$c['keywords']	= $this->Base64Decode(JRequest::getString('kw', ''));
		$c['limit']	= JRequest::getInt('limit', $p->get('rel_limit', 5));
		$c['title']	= JRequest::getString('title', '');
		if (!JString::strlen($c['title'])) {
			$c['title']	= $p->get('rel_title', JText::_('RELATED_ARTICLES'));
			$c['title']	= LinkrHelper::UTF8Encode($c['title']);
		} else {
			$c['title']	= $this->Base64Decode($c['title']);
		}
		
		// Excluded articles
		jimport('joomla.utilities.arrayhelper');
		$c['exclude']	= JRequest::getString('exclude', '');
		if (strlen($c['exclude']) > 0) {
			$c['exclude']	= @explode(',', $c['exclude']);
		} else {
			$c['exclude']	= array();
		}
		JArrayHelper::toInteger($c['exclude']);
		
		// Article list
		$c['articles']	= $this->getRelatedPreview($c['keywords']);
		if (is_array($c['articles'])) {
			foreach ($c['articles'] as $i => $a) {
				$short	= $this->snip($a->title, 30);
				$c['articles'][$i]->stitle	= LinkrHelper::UTF8Encode($short);
				$c['articles'][$i]->title	= LinkrHelper::UTF8Encode($a->title);
			}
		}
		
		// Return related articles data
		return $c;
	}
	
	// Returns articles related to keywords
	function getRelatedPreview($txt)
	{
		// Check text
		$txt	= JString::trim($txt);
		if (JString::strlen( $txt ) < 3) {
			return JText::sprintf('TYPEMIN', 3);
		}
		
		// Keywords
		$where 	= array();
		$txt	= JString::str_ireplace(',', ';', $txt);
		$txt	= @explode(';', $txt);
		foreach ($txt as $t)
		{
			$t	= $this->_db->getEscaped($t, true);
			$t	= $this->_db->Quote('%'. $t .'%', false);
			$w	= array(
				'LOWER(title) LIKE '. $t,
				'LOWER(introtext) LIKE '. $t,
				'LOWER('. $this->_nQ('fulltext') .') LIKE '. $t,
				'LOWER(metakey) LIKE '. $t,
				'LOWER(metadesc) LIKE '. $t
			);
			$where[]	= '('. implode(') OR (', $w) .')';
		}
		
		// Publish dates
		$dbo	= & JFactory::getDBO();
		$date	= & JFactory::getDate();
		$now	= $dbo->Quote($date->toMySQL());
		$nulld	= $dbo->Quote($dbo->getNullDate());
		
		// User access
		$user	= & JFactory::getUser();
		$aid	= (int) $user->get('aid', 0);
		
		// Order
		//switch (LinkrHelper::getPluginParam('rel_sort', 'random'))
		switch (LinkrHelper::getParam('rel_sort', 'random'))
		{
			case 'title':
				$order	= 'title ASC, alias ASC';
				break;
			
			case 'date_asc':
				$order	= 'publish_up ASC, created ASC, modified ASC';
				break;
			
			case 'date_desc':
				$order	= 'publish_up DESC, created DESC, modified DESC';
				break;
			
			case 'modified':
				$order	= 'modified ASC, publish_up ASC, created ASC';
				break;
			
			case 'popular':
				$order	= 'hits DESC, publish_up ASC, created ASC';
				break;
			
			default:
				$order	= 'rand()';
		}
		
		// SQL Query
		$q	=
		' SELECT id, title FROM #__content '.
		' WHERE state = 1 AND access <= '. $aid .
		' AND ( publish_up = '. $nulld .' OR publish_up <= '. $now .' ) '.
		' AND ( publish_down = '. $nulld .' OR publish_down >= '. $now .' ) '.
		' AND ('. implode(') OR (', $where) .') '.
		' ORDER BY '. $order;
		
		// Query database
		$list	= $this->_getList($q);
		if ($this->_db->getErrorNum()) {
			return 'Database Error: '. $this->_db->getErrorMsg();
		} elseif (empty($list) || empty($list[0])) {
			return JText::_('NORESULTS');
		}
		
		// Return article list
		return $list;
	}
	
	// Returns directories data
	function dirs()
	{
		$folders	= array();
		$i	= $this->getFileInfo();
		$dirs	= LinkrHelper::listDirectories($i['base'], $i['base.regex']);
		
		// Base path
		$folders[]	= array(
			'value'	=> $i['base.64'],
			'text'	=> rawurlencode($i['base.folder'])
		);
		
		// Other paths
		foreach ($dirs as $d)
		{
			$folders[]	= array(
				'value'	=> $d['path.64'],
				'text'	=> rawurlencode($d['folder'])
			);
			
			// Add subdirectories
			$subs	= LinkrHelper::listDirectories($d['path'], '.', true);
			if (!empty( $subs )) {
				foreach ($subs as $s) {
					$folders[]	= array(
						'value'	=> $s['path.64'],
						'text'	=> rawurlencode($s['folder'])
					);
				}
			}
		}
		
		// Return directories data
		return array(
			'info'	=> $i,
			'folders'	=> $folders
		);
	}
	
	// Returns files data
	function files()
	{
		// Get folder
		$folder	= JRequest::getVar('f', '', 'REQUEST', 'base64');
		$folder	= @base64_decode($folder);
		if (!$folder || !strlen($folder)) {
			return $this->badRequest();
		}
		
		// Check folder
		$i	= $this->getFileInfo($folder);
		if ($folder != $i['base'])
		{
			foreach ($i['path.list'] as $p)
			{
				if (strpos($folder, $p) === 0) {
					$c	= true;
					break;
				}
			}
			
			// Invalid folder
			if (!$c) {
				return $this->badRequest('Invalid directory');
			}
		}
		
		// Save folder
		$this->setState('current', $i['path.64']);
		
		// Get folders
		if ($folder != $i['base']) {
			$folders	= LinkrHelper::listDirectories($folder);
		} else {
			$folders	= LinkrHelper::listDirectories($i['base'], $i['base.regex']);
		}
		
		// Get files
		$exts	= LinkrHelper::getMediaParam('upload_extensions', '');
		$exts	= strlen($exts) ? $exts : 'bmp,csv,doc,gif,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,txt,xcf,xls';
		$exts	= preg_replace('/[^A-Z0-9,]/i', '', $exts);
		$exts	= '\.('. str_replace(',', '|', $exts) .')';
		$simple	= (bool) LinkrHelper::getParam('simple_list', false);
		$simple	= (bool) $this->getState('simplelist', $simple, 'INT');
		$files	= LinkrHelper::listFiles($folder, $exts, $simple);
		
		// Return files data
		return array(
			'files'		=> $files,
			'folders'	=> $folders,
			'current'	=> $i['path.64'],
			'parent'	=> $i['parent'] ? $i['parent.64'] : false,
			'mode'		=> $simple ? 'simple' : 'normal'
		);
	}
	
	// Returns filepaths
	function paths()
	{
		// Get folder
		$f	= JRequest::getVar('f', '', 'REQUEST', 'BASE64');
		if (!$f = base64_decode($f)) {
			return $this->badRequest('Invalid directory');
		}
		
		// Get extensions
		$e	= trim(JRequest::getCmd('e', ''));
		if (!strlen($e)) {
			return $this->badRequest('No extensions specified');
		} elseif ($e == 'all') {
			$e	= $this->_fileExt;
		} else {
			$e	= @explode('-', $e);
		}
		
		// Check folder
		$inSite	= false;
		$root	= LinkrHelper::listDirectories(JPATH_ROOT);
		foreach ($root as $check) {
			if (stripos($f, $check['name']) === 0) {
				$inSite	= true;
				break;
			}
		}
		if (!$inSite) {
			return $this->badRequest('Directory not within Joomla! installation');
		}
		
		// Check extensions
		$exts	= array();
		foreach ($e as $x)
		{
			if (!in_array(strtolower(trim($x)), $this->_fileExt)) {
				return $this->badRequest('Disallowed extension "'. $x .'"');
			}
			
			$exts[]	= '.'. $x;
		}
		
		// List files
		$regex	= '('. implode('|', $exts) .')$';
		$path	= JPATH_ROOT . DS . str_replace('/', DS, $f);
		$files	= LinkrHelper::listFiles($path, $regex, true);
		
		// Return files data
		return array(
			'status'	=> 'ok',
			'result'	=> $files
		);
	}
	
	// Returns filesystem information
	function getFileInfo($path = null)
	{
		$i	= $this->_fileInfo();
		
		// Set current path
		if ($path && is_dir($path) && strpos($path, $i['base']) === 0)
		{
			$i['path']	= $path;
			$i['path.64']	= base64_encode($path);
			if ($i['path'] != $i['base']) {
				$parent	= substr($i['path'], 0, strrpos($i['path'], DS));
				$i['parent']	= $parent;
				$i['parent.64']	= base64_encode($parent);
			} else {
				$i['parent']	= false;
				$i['parent.64']	= false;
			}
		}
		
		return $i;
	}
	function _fileInfo()
	{
		if (isset($this->_fi)) {
			return $this->_fi;
		}
		
		// Paths list
		$i	= array('path.list' => array());
		$paths	= LinkrHelper::getParam('paths', 'images');
		$paths	= $this->getState('path', $paths, 'STRING');
		$list	= @explode(',', $paths);
		foreach ($list as $p)
		{
			$p	= trim($p);
			if (!strlen($p)) {
				continue;
			}
			
			$p	= str_replace('/', DS, $p);
			$p	= substr($p, 0, 1) == DS ? $p : DS . $p;
			$p	= substr($p, -1) == DS ? substr($p, 0, -1) : $p;
			if (is_dir(JPATH_ROOT . $p)) {
				$i['path.list'][]	= JPATH_ROOT . $p;
			}
		}
		if (!count($i['path.list'])) {
			$i['path.list'][]	= JPATH_ROOT.DS.'images';
		}
		
		// Base folder
		if (count($i['path.list']) == 1)
		{
			$i['base']	= $i['path.list'][0];
			$i['base.regex']	= '.';
			$i['base.folder']	= str_replace(JPATH_ROOT.DS, '', $i['base']);
			$i['base.folder']	= str_replace(DS, '/', $i['base.folder']);
		}
		else
		{
			$i['base']	= strlen(JPATH_ROOT) ? JPATH_ROOT : DS;
			$i['base.regex']	= LinkrHelper::buildRegex($paths);
			$i['base.folder']	= '/';
		}
		
		// Current path
		$i['path']	= $this->getState('current', null);
		$i['path']	= $i['path'] ? base64_decode($i['path']) : $i['base'];
		if (strpos($i['path'], $i['base']) !== 0) {
			$i['path']	= $i['base'];
		}
		$i['path.64']	= base64_encode($i['path']);
		
		// Collect information
		$i['base.64']	= base64_encode($i['base']);
		if ($i['path'] != $i['base']) {
			$parent	= substr($i['path'], 0, strrpos($i['path'], DS));
			$i['parent']	= $parent;
			$i['parent.64']	= base64_encode($parent);
		} else {
			$i['parent']	= false;
			$i['parent.64']	= false;
		}
		
		// Return information
		$this->_fi	= $i;
		return $this->_fi;
	}
	
	// Queries the database
	function dblist()
	{
		// Get query
		$q	= $this->getQuery('q');
		if (is_array($q)) {
			return $q;
		}
		
		// Query database
		$s	= JRequest::getInt('s', 0);
		$l	= JRequest::getInt('l', 0);
		$r	= $this->_getAssocList($q, $s, $l);
		if ($this->_db->getErrorNum()) {
			return $this->badRequest($this->_db->getErrorMsg());
		} elseif (is_string($r)) {
			return $this->badRequest($r);
		}
		
		// UTF8 encode values
		$r	= LinkrHelper::UTF8Encode($r);
		
		// Return results
		return array(
			'status'	=> 'ok',
			'result'	=> $r
		);
	}
	
	// Queries the database
	function dbobject()
	{
		// Get query
		$q	= $this->getQuery('q');
		if (is_array($q)) {
			return $q;
		}
		
		// Query database
		$r	= $this->_getObj($q);
		if ($this->_db->getErrorNum()) {
			return $this->badRequest($this->_db->getErrorMsg());
		} elseif (is_string($r)) {
			return $this->badRequest($r);
		}
		
		// UTF8 encode values
		$r	= LinkrHelper::UTF8Encode($r);
		
		// Return results
		return array(
			'status'	=> 'ok',
			'result'	=> $r
		);
	}
	
	// Returns SQL query
	function getQuery($name = 'q')
	{
		$q	= JRequest::getVar($name, array(), 'REQUEST', 'ARRAY');
		
		// Decode
		if (!$q = $this->Base64Decode(implode('', $q))) {
			return $this->badRequest('Malformed SQL query');
		}
		
		// Replace Linkr markers
		$q	= JString::str_ireplace('_Q_', '\'', $q);
		$q	= JString::str_ireplace('_WC_', '%', $q);
		$q	= JString::str_ireplace('_NQ_', $this->_db->_nameQuote, $q);
		
		// Return query
		return $q;
	}
	
	// Special decode function for Linkr.Base64.Encode (helper.js)
	function Base64Decode($str) {
		$str	= (string) preg_replace('/[^A-Z0-9\/+,=]/i', '', $str);
		return base64_decode(str_replace(',', '+', $str));
	}
	
	// Database functions
	function _Q($w, $l = false, $x = false) {
		$w	= $this->_db->getEscaped($w, $x);
		$w	= $l ? '%'. $w .'%' : $w;
		return $this->_db->Quote($w, false);
	}
	function _nQ($n) {
		return $this->_db->nameQuote($n);
	}
	function _getAssocList($q, $s = 0, $l = 0) {
		$this->_db->setQuery($q, $s, $l);
		return $this->_db->loadAssocList();
	}
	function _getObj($q, $type = 'object') {
		$this->_db->setQuery($q);
		return $type == 'array' ? $this->_db->loadResultArray() : $this->_db->loadObject();
	}
	
	// Method to shorten text
	function snip($string, $length = 30)
	{
		$string	= JString::trim( $string );
		$orglen	= JString::strlen( $string );
		$words	= @explode(' ', $string);
		$strlen	= 0;
		$final	= array();
		for ($i = 0, $n = count( $words ); $i < $n; $i++) {
			if ($strlen < $length) {
				$final[]	= $words[$i];
				$strlen		+= JString::strlen( $words[$i] ) + 1;
			} else {
				break;
			}
		}
		$final	= implode(' ', $final);
		$final	.= ($orglen == $strlen - 1) ? '' : '...';
		return $final;
	}
	
	// Invalid requests
	function badRequest($msg = '') {
		$msg	= JString::strlen($msg) ? $msg : JText::_('bad request');
		return array(
			'status'=> 'error',
			'msg'	=> $msg
		);
	}
	
	// Variable handling
	function getState($request, $def = null, $type = 'none') {
		global $mainframe;
		return $mainframe->getUserStateFromRequest('linkr.'. $request, $request, $def, $type);
	}
	function setState($var, $value) {
		global $mainframe;
		return $mainframe->setUserState('linkr.'. $var, $value);
	}
}
