<?php
defined('_JEXEC') or die;

/*
 * Linkr Super Installer
 *
 */
$GLOBALS['LINKR_INSTALLER']	= & $this;
$GLOBALS['LINKR_INSTALLER_MSG']	= array();
function com_install() {
	return LinkrInstaller::install();
}


/*
 * Installer helper
 */
class LinkrInstaller extends JObject
{
	/*
	 * Install plugins
	 */
	function install()
	{
		// Component installed successfully
		LinkrInstaller::msg('Installing Linkr component... Done!', 'green');
		
		// Get installer object
		$i	= & $GLOBALS['LINKR_INSTALLER'];
		if (!is_object($i)) {
			LinkrInstaller::msg('Could not find Installer object...', 'blue');
			LinkrInstaller::dlMsg('the button and plugin');
			return LinkrInstaller::printInMsg(true);
		}
		
		// Get manifest file details
		$c	= new JObject();
		$c->set('path.source', $i->parent->getPath('source'));
		$c->set('path.manifest', $i->parent->getPath('manifest'));
		$c->set('path.root', $i->parent->getPath('extension_root'));
		$c->set('path.site', $i->parent->getPath('extension_site'));
		$c->set('path.admin', $i->parent->getPath('extension_administrator'));
		
		// Install button
		$base	= $i->parent->getPath('source');
		$source	= $base . DS . 'plg.linkr_button';
		LinkrInstaller::iPlugin($i->parent, $source, 'linkr_button', 'editors-xtd', 'Linkr button');
		
		// Install plugin
		$source	= $base . DS . 'plg.linkr_content';
		LinkrInstaller::iPlugin($i->parent, $source, 'linkr_content', 'content', 'Linkr plugin');
		
		// Default CSS
		LinkrInstaller::setDefaultCSS($i);
		
		// Reset manifest file
		$i->parent->set('message', '');
		$i->parent->setPath('source', $c->get('path.source'));
		$i->parent->setPath('manifest', $c->get('path.manifest'));
		$i->parent->setPath('extension_root', $c->get('path.root'));
		$i->parent->setPath('extension_site', $c->get('path.site'));
		$i->parent->setPath('extension_administrator', $c->get('path.admin'));
		
		// Installation complete
		return LinkrInstaller::printInMsg(true);
	}
	
	/*
	 * Uninstall plugins
	 */
	function uninstall()
	{
		// Component uninstalling...
		LinkrInstaller::msg('Uninstalling Linkr component...', 'blue');
		
		// Get installer object
		$i	= new JInstaller();
		
		// Uninstall the button
		LinkrInstaller::uPlugin($i, 'linkr_button', 'editors-xtd', 'Linkr button');
		
		// Uninstall the plugin
		LinkrInstaller::uPlugin($i, 'linkr_content', 'content', 'Linkr plugin');
		
		// Uninstallation complete
		return LinkrInstaller::printUnMsg(true);
	}
	
	/*
	 * Add a message
	 */
	function msg($msg, $colour = 'blue')
	{
		// Add message
		$GLOBALS['LINKR_INSTALLER_MSG'][]	= array(
			'colour'	=> $colour,
			'text'		=> $msg
		);
	}
	
	/*
	 * Message shorcut
	 */
	function dlMsg($x = '')
	{
		$m	= '<a href="'. LINKR_URL_DOWNLOAD .'" target="_blank" style="color:#111;">download and install</a>';
		$m	= 'You\'ll need to '. $m .' &quot;'. $x .'&quot; from JoomlaCode.';
		
		LinkrInstaller::msg($m, 'blue');
	}
	
	/*
	 * Echo messages
	 */
	function printInMsg($done = true)
	{
		// Title
		LinkrInstaller::loadScripts();
		echo '<div class="inst-head">Linkr '. LINKR_VERSION_READ .'</div>';
		
		// Messages
		if (count($GLOBALS['LINKR_INSTALLER_MSG']))
		{
			foreach ($GLOBALS['LINKR_INSTALLER_MSG'] as $msg)
			{
				echo
				'<div class="inst-msg inst-msg-'. $msg['colour'] .'">'.
					$msg['text'] .
				'</div>';
			}
		}
		if (!$done) {
			return $done;
		}
		
		// Installation message
		$jed	= 'http://extensions.joomla.org/extensions/search/linkr/';
		echo
		'<div class="inst-text">
			<h2 class="inst-title">Congratulations!</h2>
			<div class="inst-div">
				You\'ve just installed the Linkr extension. You\'ll now be able to link 
				to internal articles while editing, without having to browse to your site. 
				You can also link files, menu items, and contacts. In fact, you might be 
				able to link to other content as well, because Linkr is extensible. Search 
				the <a href="'. $jed .'" target="_blank">Joomla! Extensions Directory (JED)</a> 
				to find more plugins. For more information about the Linkr API, browse to 
				<a href="'. LINKR_URL_API .'" target="_blank">'. LINKR_URL_API .'</a>.
			</div>
			<h2 class="inst-title">What now?</h2>
			<div class="inst-div">
				By the time you install this extension, there might be patches 
				available which fix issues and bugs that have been reported already. 
				You should drop by <a href="'. LINKR_URL_DOWNLOAD .'" target="_blank">
				the download page</a> to do a quick check. Patches are easily recognizable 
				because they have the word &quot;patch&quot; in their file names.
			</div>
			<h2 class="inst-title">Translations</h2>
			<div class="inst-div">
				Linkr has been translated into a few languages. The available languages 
				should have been installed automatically. You may also write your own 
				language files. For more information, 
				<a href="'. LINKR_URL_TRANSLATION .'" target="_blank">follow this link</a>.
			</div>
		</div>';
		
		return $done;
	}
	
	function printUnMsg($return = true)
	{
		// Title
		LinkrInstaller::loadScripts();
		echo '<div class="inst-head">Linkr '. LINKR_VERSION_READ .'</div>';
		
		// Messages
		if (count($GLOBALS['LINKR_INSTALLER_MSG']))
		{
			foreach ($GLOBALS['LINKR_INSTALLER_MSG'] as $msg)
			{
				echo
				'<div class="inst-msg inst-msg-'. $msg['colour'] .'">'.
					$msg['text'] .
				'</div>';
			}
		}
		
		// Uninstall message
		echo
		'<div class="inst-text">
			<h2 class="inst-title">What now?</h2>
			<div class="inst-div">
				If you\'re planning on upgrading to a newer version of Linkr, simply head on 
				to <a href="'. LINKR_URL_DOWNLOAD .'" target="_blank">the download page</a> 
				to get the latest package, and install it through this page. If instead Linkr 
				was giving you more headaches than anything else, please spare a minute of your 
				time to <a href="'. LINKR_URL_SUPPORT .'" target="_blank">leave your comments</a>.
			</div>
		</div>';
		
		return $return;
	}
	
	function loadScripts()
	{
		// Defines
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_linkr'.DS.'defines.php');
		
		// Styles
		$doc	= & JFactory::getDocument();
		$doc->addStyleDeclaration(
			'div.inst-head{'.
				'margin:5px;'.
				'padding:15px;'.
				'color:#556480;'.
				'font-size:30px;'.
				'text-align:center;'.
				'letter-spacing:2px;'.
				'background-color:#dfeccc;'.
			'}'.
			'div.inst-msg{'.
				'margin:5px;'.
				'padding:5px;'.
				'color:#f9f9f9;'.
				'font-size:14px;'.
			'}'.
			'div.inst-msg-green{'.
				'background-color:#558a01;'.
			'}'.
			'div.inst-msg-blue{'.
				'background-color:#556480;'.
			'}'.
			'div.inst-msg-red{'.
				'background-color:#880000;'.
			'}'.
			'h2.inst-title{'.
				'margin-left:50px;'.
				'color:#293751;'.
			'}'.
			'div.inst-div{'.
				'margin:5px;'.
				'color:#1a212e;'.
				'font-size:14px;'.
			'}'.
			'div.inst-text{'.
				'margin:5px;'.
				'padding:5px;'.
				'background-color:#d2d7e0;'.
			'}'
		);
	}
	
	/*
	 * Installs a Linkr plugin
	 */
	function iPlugin(&$installer, $source, $plugin, $group, $name)
	{
		$msg	= 'Installing '. $name .'... ';
		
		// Check plugin folder
		if (!JFolder::exists($source)) {
			LinkrInstaller::msg($msg .'Fail: could not find plugin folder.', 'blue');
			LinkrInstaller::dlMsg($name);
			return false;
		}
		
		// Install plugin
		if (!$installer->install($source)) {
			LinkrInstaller::msg($msg .'Fail: could not install plugin.', 'blue');
			LinkrInstaller::dlMsg($name);
			return false;
		}
		LinkrInstaller::msg($msg .'Done!', 'green');
		
		// Enable plugin
		$db	= & $installer->getDBO();
		$db->setQuery(
			' UPDATE #__plugins SET published = 1 '.
			' WHERE folder = '. $db->Quote($group) .
			' AND element = '. $db->Quote($plugin)
		);
		
		if (!$db->query()) {
			LinkrInstaller::msg('Could not enable &quot;'. $name .'&quot;. You\'re going to have to do it manually.', 'blue');
		}
		
		return true;
	}
	
	/*
	 * Uninstalls a Linkr plugin
	 */
	function uPlugin(&$installer, $plugin, $group, $name)
	{
		$msg	= 'Uninstalling '. $name .'... ';
		$fail	= 'You\'ll have to uninstall &quot;'. $name .'&quot; manually.';
		
		// Get plugin
		$db	= & JFactory::getDBO();
		$db->setQuery(
			' SELECT id FROM #__plugins '.
			' WHERE folder = '. $db->Quote($group) .
			' AND element = '. $db->Quote($plugin));
		$id	= (int) $db->loadResult();
		if ($id < 1) {
			LinkrInstaller::msg($msg .'Fail: could not find plugin.', 'blue');
			LinkrInstaller::msg($fail, 'blue');
			return false;
		}
		
		// Uninstall plugin
		if (!$installer->uninstall('plugin', $id, 0)) {
			LinkrInstaller::msg($msg .'Fail: could not uninstall plugin.', 'blue');
			LinkrInstaller::msg($fail, 'blue');
			return false;
		}
		LinkrInstaller::msg($msg .'Done!', 'green');
		
		return true;
	}
	
	/*
	 * Saves default CSS styles
	 */
	function setDefaultCSS(&$i)
	{
		// Check
		$t	= & JTable::getInstance('component');
		$t->loadByOption('com_linkr');
		$p	= new JParameter($t->params);
		if (strlen($p->get('bcss', '')) || strlen($p->get('rcss', ''))) {
			return true;
		}
		
		// Default CSS
		$p->set('bcss', 'ZGl2LmxpbmtyLWJtIHsKIG1hcmdpbjoyMHB4IDMwcHggNXB4IDMwcHg7Cn0KZGl2LmxpbmtyLWJtIGRpdi5saW5rci1ibS1wcmUsCmRpdi5saW5rci1ibSBkaXYubGlua3ItYm0tcG9zdCB7CiBmbG9hdDpyaWdodDsKIGZvbnQtc2l6ZToxNHB4OwogbGV0dGVyLXNwYWNpbmc6MnB4Owp9CmRpdi5saW5rci1ibSBkaXYubGlua3ItYm0tc2VwIHtmbG9hdDpyaWdodDt9CmRpdi5saW5rci1ibSBkaXYubGlua3ItYm0tYiB7CiBmbG9hdDpyaWdodDsKIHBhZGRpbmc6NHB4OwogYm9yZGVyOjFweCBzb2xpZCB0cmFuc3BhcmVudDsKfQpkaXYubGlua3ItYm0gZGl2LmxpbmtyLWJtLWIgaW1nIHsKIG1hcmdpbjowOwp9CmRpdi5saW5rci1ibSBkaXYubGlua3ItYm0tYjpob3ZlciB7CiBib3JkZXItY29sb3I6I2FhYTsKIGJhY2tncm91bmQtY29sb3I6I2RkZDsKfQpkaXYubGlua3ItYm0tYWZ0ZXIge2NsZWFyOmJvdGg7fQ==');
		$p->set('rcss', 'ZGl2LmxpbmtyLXJsIHsKIG1hcmdpbi10b3A6MjBweDsKIHBhZGRpbmc6MTBweCA1cHggMCA1cHg7CiBib3JkZXItdG9wOjFweCBkb3R0ZWQgI2NjYzsKfQpkaXYubGlua3ItcmwgZGl2LmxpbmtyLXJsLXQgewogZm9udC1zaXplOjEuMWVtOwogbGV0dGVyLXNwYWNpbmc6MnB4OwogdGV4dC10cmFuc2Zvcm06dXBwZXJjYXNlOwp9CmRpdi5saW5rci1ybCB1bCB7CiBsaXN0LXN0eWxlLXR5cGU6c3F1YXJlOwogbGluZS1oZWlnaHQ6MS41ZW07CiB0ZXh0LWluZGVudDo1cHg7Cn0KZGl2LmxpbmtyLXJsIHVsIGxpIHsKIHBhZGRpbmc6MCA1cHg7CiBiYWNrZ3JvdW5kOm5vbmU7Cn0=');
		$t->params	= $p->toString();
		return $t->store();
	}
}
