<?php
defined('_JEXEC') or die;

// Linkr: JSON wrapper
function json_encode($arg)
{
	global $services_json;
	if (is_null($services_json)) {
		$services_json = new Services_JSON();
	}
	return $services_json->encode($arg);
}
function json_decode($arg)
{
	global $services_json;
	if (is_null($services_json)) {
		$services_json = new Services_JSON();
	}
	return $services_json->decode($arg);
}
