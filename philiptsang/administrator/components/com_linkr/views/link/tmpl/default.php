<?php defined('_JEXEC') or die;

// Check parameters
$p	= & LinkrHelper::getPluginParameters();
if (!$p) {
	echo $this->loadTemplate('install');
}

// Display landing page
else {
	echo $this->loadTemplate('landing');
}
