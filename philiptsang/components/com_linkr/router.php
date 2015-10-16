<?php
defined('_JEXEC') or die;

// SEF url fix
function LinkrBuildRoute( &$q )
{
	$parts	= array();
	
	$token	= JUtility::getToken();
	if (!isset( $q[$token] )) {
		JError::raiseError(403, 'Invalid Token');
	}
	
	$name	= (isset($q['e_name']) && strlen($q['e_name'])) ? $q['e_name'] : 'text';
	$parts[]	= $token;
	$parts[]	= $name;
	foreach ($q as $k => $v) {
		if ($k != 'option') {
			unset( $q[$k] );
		}
	}
	
	return $parts;
}

function LinkrParseRoute( $s )
{
	return array(
			$s[0]		=> '1',
			'e_name'	=> $s[1],
			'tmpl'		=> 'component',
			'view'		=> 'link'
		);
}
