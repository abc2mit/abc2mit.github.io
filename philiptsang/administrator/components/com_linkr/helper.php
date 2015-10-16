<?php
defined('_JEXEC') or die;
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'defines.php');

class LinkrHelper extends JObject
{
	function getLinkrUrl($editor = 'text')
	{
		// Linkr URL
		$link	= 'index.php?option=com_linkr&amp;view=link&amp;tmpl=component&amp;e_name='. $editor;
		
		// Use "popup" mode for IE
		jimport('joomla.environment.browser');
		$browser	= & JBrowser::getInstance();
		if ($browser->getBrowser() == 'msie') {
			$link	.= '&amp;mode=popup';
		}
		
		// Return link
		return JRoute::_($link .'&amp;'. JUtility::getToken() .'=1');
	}
	
	function listDirectories($path, $regex = '.', $recurse = false)
	{
		$dirs	= array();
		
		// Make sure path is valid
		jimport('joomla.filesystem.path');
		$path	= JPath::clean( $path );
		$root	= strlen(JPATH_ROOT) ? JPATH_ROOT : DS;
		if (empty( $path ) || JString::strpos($path, $root) !== 0) {
			return $dirs;
		}
		
		// Find folders
		jimport('joomla.filesystem.folder');
		$list	= JFolder::folders($path, $regex, $recurse, true);
		if (empty( $list )) {
			return $dirs;
		}
		
		// Create list of directories and the names
		foreach ($list as $path)
		{
			//$folder	= JString::str_ireplace(JPATH_ROOT, '', $path);
			$folder	= JString::substr($path, JString::strlen($root));
			$folder	= substr(str_replace(DS, '/', $folder), 1);
			$name	= @explode('/', $folder);
			$name	= array_pop( $name );
			$dirs[]	= array(
						'folder'	=> $folder,
						'name'		=> $name,
						'path'		=> $path,
						'path.64'	=> base64_encode( $path )
					);
		}
		return $dirs;
	}
	
	function listFiles($path, $regex = '.', $simple = false)
	{
		$files	= array();
		
		// Make sure path is valid
		jimport('joomla.filesystem.path');
		$path	= JPath::clean( $path );
		$root	= strlen(JPATH_ROOT) ? JPATH_ROOT : DS;
		if (empty( $path ) || JString::strpos($path, $root) === false) {
			return $files;
		}
		
		// Get files
		jimport('joomla.filesystem.folder');
		$list	= JFolder::files($path, $regex, false, true);
		if (empty( $list )) {
			return $files;
		}
		
		// List files
		jimport('joomla.filesystem.file');
		
		// Simple list
		if ($simple)
		{
			foreach ($list as $f)
			{
				// Get source
				$s	= strlen(JPATH_ROOT) ? JString::str_ireplace(JPATH_ROOT.DS, JURI::root(), $f) : JURI::root() . $f;
				$files[]	= array('src' => str_replace(DS, '/', $s), 'name' => JFile::getName($f));
			}
		}
		
		// Normal list
		else
		{
			foreach ($list as $filename)
			{
				$f	= new JObject();
				$f->name	= JFile::getName( $filename );
				$f->path	= $filename;
				if (strlen(JPATH_ROOT)) {
					$f->src		= JString::str_ireplace(JPATH_ROOT.DS, JURI::root(), $f->path);
				} else {
					$f->src	= JURI::root() . $f->path;
				}
				$f->src		= str_replace(DS, '/', $f->src);
				$f->size	= LinkrHelper::parseSize( $f->path );
				$f->ext		= strtolower(JFile::getExt($f->name));
				
				switch ( $f->ext )
				{
					// Image
					case 'bmp':
					case 'gif':
					case 'jpg':
					case 'jpeg':
					case 'odg':
					case 'png':
					case 'xcf':
						list($w, $h)	= @getimagesize( $f->path );
						$size	= LinkrHelper::imageResize($w, $h, 32);
						$f->width	= $size['width'];
						$f->height	= $size['height'];
						if (strlen(JPATH_ROOT)) {
							$f->icon	= JString::str_ireplace(JPATH_ROOT.DS, JURI::root(), $f->path);
						} else {
							$f->icon	= JURI::root() . $f->path;
						}
						$f->icon	= str_replace(DS, '/', $f->icon);
						//$f->type	= JText::_( 'Image' );
						$f->type	= 'Image';
						break;
					
					// Other files
					default:
						$f->type	= strtoupper( $f->ext );
						$f->width	= 24;
						$f->height	= 24;
						$icon	= JPATH_ADMINISTRATOR.DS.'components'.DS.'com_media'.DS.'images'.DS.'mime-icon-32'.DS. $f->ext .'.png';
						if (file_exists( $icon )) {
							$f->icon	= JURI::root() .'administrator/components/com_media/images/mime-icon-32/'. $f->ext .'.png';
						} else {
							$f->icon	= LINKR_ASSETS .'img/files.unkown-type.png';
						}
						break;
				}
				$files[]	= $f;
			}
		}
		
		return $files;
	}
	
	function buildRegex($folders = null)
	{
		// Make sure expression limits viewed folders... in case of
		// error, default to expression that will reject all directories
		$regex	= '_-_-__-_-_';
		$array	= array();
		
		// Checks
		if (is_array( $folders ) && !empty( $folders )) {
			$folders	= implode(',', $folders);
		} elseif (!is_string( $folders ) || empty( $folders )) {
			return $regex;
		}
		
		// Try and correct mistakes if any
		jimport('joomla.filesystem.folder');
		$folders	= str_replace(array(';', '|'), ',', $folders);
		$folders	= @explode(',', $folders);
		foreach ($folders as $f)
		{
			$f	= trim(JFolder::makeSafe($f));
			if (!empty( $f )) {
				$array[]	= $f;
			}
		}
		
		// Build regex for use with JFolders::folders
		if (!empty( $array )) {
			$regex	= '('. implode('|', $array) .')';
		}
		return $regex;
	}
	
	/*
	 * Method to get a Linkr parameter
	 */
	function getParam($var, $def = null)
	{
		static $params;
		if (is_null( $params )) {
			jimport('joomla.application.component.helper');
			$params	= & JComponentHelper::getParams('com_linkr');
		}
		return $params->get($var, $def);
	}
	
	/*
	 * Method to get the plugin parameters
	 */
	function &getPluginParameters()
	{
		static $params;
		if (!isset( $params )) {
			jimport('joomla.plugin.plugin');
			$linkr	= & JPluginHelper::getPlugin('content', 'linkr_content');
			$params	= empty($linkr) ? false : new JParameter($linkr->params);
		}
		return $params;
	}
	
	/*
	 * Method to get a plugin parameter
	 */
	function getPluginParam($var = 'isInstalled', $def = null)
	{
		if ($var == 'isInstalled') {
			$params	= & LinkrHelper::getPluginParameters();
			return ($params) ? true : false;
		}
		return LinkrHelper::getParam($var, $def);
	}
	
	/*
	 * Method to get a com_media parameter
	 */
	function getMediaParam($var, $def = null)
	{
		static $params;
		if (is_null( $params )) {
			jimport('joomla.application.component.helper');
			$params	= & JComponentHelper::getParams('com_media');
		}
		return $params->get($var, $def);
	}
	
	// See administrator >> components >> com_media >> helpers >> media.php
	function parseSize( $size )
	{
		if (!is_numeric( $size )) {
			if (!is_string( $size ) || !is_file( $size )) {
				return '?';
			} else {
				$size	= filesize( $size );
			}
		}
		if ($size < 1024) {
			return $size . ' bytes';
		}
		else
		{
			if ($size >= 1024 && $size < 1024 * 1024) {
				return sprintf('%01.2f', $size / 1024.0) . ' KB';
			} else {
				return sprintf('%01.2f', $size / (1024.0 * 1024)) . ' MB';
			}
		}
	}
	
	// See administrator >> components >> com_media >> helpers >> media.php
	function imageResize($width, $height, $target)
	{
		// takes the larger size of the width and height and applies the
		// formula accordingly...this is so this script will work
		// dynamically with any size image
		if ($width > $height) {
			$percentage	= ($target / $width);
		} else {
			$percentage	= ($target / $height);
		}
		
		// gets the new value and applies the percentage, then rounds the value
		$width	= ($width > $target) ? round($width * $percentage) : $width;
		$height	= ($height > $target) ? round($height * $percentage) : $height;
		
		return array('width' => $width, 'height' => $height);
	}
	
	/*
	 * UTF-8 encodes a string if it isn't already encoded
	 */
	function UTF8Encode($str)
	{
		if (is_array($str) || is_object($str)) {
			settype($str, 'array');
			foreach ($str as $k => $v) {
				$str[$k]	= LinkrHelper::UTF8Encode($v);
			}
			return $str;
		} elseif (is_string($str)) {
			return LinkrHelper::isUTF8Encoded($str) ? $str : utf8_encode($str);
		} else {
			return $str;
		}
	}
	
	/*
	 * UTF-8 decodes a string if it isn't already decoded
	 */
	function UTF8Decode($str)
	{
		if (is_array($str)) {
			foreach ($str as $k => $v) {
				$str[$k]	= LinkrHelper::UTF8Decode($v);
			}
			return $str;
		} elseif (is_string($str)) {
			if (LinkrHelper::isUTF8Encoded($str)) {
				return utf8_decode($str);
			} else {
				return $str;
			}
		} else {
			return $str;
		}
	}
	
	/*
	 * Checks if a string is UTF-8 encoded
	 */
	function isUTF8Encoded($str)
	{
		if (is_array($str)) {
			foreach ($str as $s) {
				if (!LinkrHelper::isUTF8Encoded($s)) {
					return false;
				}
			}
			return true;
		} elseif (is_string($str)) {
			if (function_exists('mb_detect_encoding')) {
				return mb_detect_encoding($str) == 'UTF-8';
			} else {
				jimport('phputf8.utils.ascii');
				return utf8_is_ascii($str);
			}
		} else {
			return false;
		}
	}
	
	function isSite() {
		global $mainframe;
		return $mainframe->isSite();
	}
	
	// Debuging
	
	function debug()
	{
		static $debug;
		if (is_null( $debug )) {
			$debug	= (JDEBUG || LinkrHelper::getParam('debug', '0'));
		}
		return $debug;
	}
	
	function dump( $var ) {
		ob_clean();jexit(var_dump($var));
	}
	
	function log( $msg )
	{
		if (empty( $msg ) || !LinkrHelper::debug()) return;
		static $log;
		if (is_null( $log )) {
			$o	= array('format' => '{DATE} {TIME} ({C-IP}), {COMMENT}');
			jimport( 'joomla.error.log' );
			$log	= & JLog::getInstance('linkr.php', $o);
		}
		$log->addEntry(array('comment' => $msg));
	}
}
