<?php
defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');

/**
 * Linkr Content Plugin
 *
 * @package	Linkr - Content rendering
 * @author		Frank <francisamankrah@gmail.com>
 * @license		GNU/GPL, see LICENSE.php
 */
class plgContentLinkr_content extends JPlugin
{
	var $settings	= array();
	var $loaded		= array();
	
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject	The object to observe
	 * @param array $config	An array that holds the plugin configuration
	 * @since				1.5
	 */
	function plgContentLinkr_content(&$subject, $config)
	{
		parent::__construct($subject, $config);
		
		// Load administrator language file
		//$this->loadLanguage('plg_content_linkr_content', JPATH_ADMINISTRATOR);
		$this->loadLanguage('com_linkr', JPATH_ADMINISTRATOR);
		
		// Obsolete since 2.3.7
		// Fix params (MyBlog disables these... why?)
		/*if (!$this->cParams || empty($this->cParams))
		{
			$db	= & JFactory::getDBO();
			$db->setQuery(
				' SELECT '. $db->nameQuote('params') .
				' FROM #__plugins WHERE '. $db->nameQuote('element') .
				' = '. $db->Quote('linkr_content'));
			$params	= (string) $db->loadResult();
			$this->cParams	= new JParameter($params);
		}*/
		
		// Get component params
		$this->cParams	= & JComponentHelper::getParams('com_linkr');
	}
	
	// Parameter shortcut
	function cp($var, $def = null) {
		return $this->cParams->get($var, $def);
	}
	
	// Linkr tools
	function onLinkrGetTools($version)
	{
		$tools	= array();
		
		// Bookmarks
		$tools[]	= array(
			'name'	=> 'bookmarks',
			'text'	=> JText::_('BOOKMARKS'),
			'click'	=> 'LinkrBookmarks.landing()'
		);
		
		// Related links
		$tools[]	= array(
			'name'	=> 'related',
			'text'	=> JText::_('RELATED_ARTICLES'),
			'click'	=> 'LinkrRelated.landing()'
		);
		
		// {linkr:none}
		$tools['{linkr:none}']	= 'Linkr.insertAtEnd(\'{linkr:none}\')';
		
		// Debug (give access to managers and up)
		$user	= & JFactory::getUser();
		if ($this->cp('debug', 0) && JRequest::getBool('_ld', false) && $user->gid >= 23)
		{
			$tools[]	= array(
				'name'	=> 'debug',
				'text'	=> JText::_('PARAM_DEBUG'),
				'click'	=> 'LinkrDebug.load()'
			);
		}
		
		return $tools;
	}
	
	// Linkr links
	function onLinkrGetLinks($version)
	{
		$links	= array();
		
		// Files
		if ($this->cp('link_file', 1))
		{
			$links[]	= array(
				'name'	=> 'file',
				'text'	=> JText::_('FILE'),
				'click'	=> 'LinkrFile.landing()'
			);
		}
		
		// Articles
		if ($this->cp('link_article', 1))
		{
			$links[]	= array(
				'name'	=> 'article',
				'text'	=> JText::_('ARTICLE'),
				'click'	=> 'LinkrArticle.landing()'
			);
		}
		
		// Menu links
		if ($this->cp('link_menu', 1))
		{
			$links[]	= array(
				'name'	=> 'menu',
				'text'	=> JText::_('MENU'),
				'click'	=> 'LinkrMenu.landing()'
			);
		}
		
		// Contacts
		if ($this->cp('link_contact', 1))
		{
			$links[]	= array(
				'name'	=> 'contact',
				'text'	=> JText::_('CONTACT'),
				'click'	=> 'LinkrContact.landing()'
			);
		}
		
		// Automatic links
		/*if (false)
		{
			$links[]	= array(
				'name'	=> 'auto',
				'text'	=> JText::_('EXTENSION'),
				'click'	=> 'LinkrX.landing()'
			);
		}*/
		
		return $links;
	}
	
	// Linkr JavaScript
	function onLinkrLoadJS($version, $inc = array())
	{
		// File append
		$append	= $this->cp('compress', 1) ? '.js' : '-UCP.js';
		$append	.= '?'. LINKR_VERSION_INC;
		
		// Initialize variables
		$js	= '';
		$d	= & JFactory::getDocument();
		$r	= JURI::root() .'components/com_linkr/assets/js/';
		
		// Base files
		$d->addScript($r .'tree'. $append);
		$d->addScript($r .'object'. $append);
		$js	.= 'LinkrObject.options.defaultItemid='.
					LinkrHelper::getParam('itemid', 0) .';';
		
		// Plugin check
		// ToDo: use some other check
		$p	= LinkrHelper::getPluginParam() ? 'true' : 'false';
		
		// Bookmarks
		if ($inc['bookmarks'])
		{
			$d->addScript($r .'bookmarks'. $append);
			$bm	= $this->cp('def_bm', 0) ? 'true' : 'false';
			$js	.= 'LinkrBookmarks.loadedByDefault='. $bm .';'.
					'LinkrBookmarks.isPluginInstalled='. $p .';';
		}
		
		// Related articles
		if ($inc['related'])
		{
			$d->addScript($r .'related'. $append);
			$rl	= $this->cp('def_rel', 0) ? 'true' : 'false';
			$js	.= 'LinkrRelated.loadedByDefault='. $rl .';'.
					'LinkrRelated.isPluginInstalled='. $p .';';
		}
		
		// Files
		if ($this->cp('link_file', 1) && $inc['file'])
		{
			$d->addScript($r .'file'. $append);
			$u	= (bool) $this->cp('frontend_upload', 0);
			$u	= $u ? true : !LinkrHelper::isSite();
			$u	= $u ? JURI::base() .'index.php?option=com_linkr&controller=file&task=upload&e_name='. JRequest::getString('e_name', 'text') .'&'. JUtility::getToken() .'=1' : false;
			
			// Upload
			$js	.= 'LinkrFile.uploadURL='. ($u ? '"'. $u .'"' : 'false') .';';
			
			// Message
			if ($msg = JRequest::getString('msg', null)) {
				$js	.= 'LinkrFile.msg="'. LinkrHelper::UTF8Encode($msg) .'";';
			}
			
			// Paths
			global $mainframe;
			$path	= JRequest::getString('path', '', 'REQUEST');
			if (strlen($path)) {
				$mainframe->setUserState('linkr.path', $path);
			} else {
				$mainframe->setUserState('linkr.path', null);
			}
			
			// Simple list
			$simple	= JRequest::getInt('simplelist', -1, 'REQUEST');
			if ($simple != -1) {
				$mainframe->setUserState('linkr.simplelist', $simple);
			} else {
				$mainframe->setUserState('linkr.simplelist', null);
			}
		}
		
		// Articles
		if ($this->cp('link_article', 1) && $inc['article'])
		{
			$d->addScript($r .'article'. $append);
			$s	= (bool) $this->cp('use_slug', 1);
			
			$js	.= 'ArtOpts.slug='. ($s ? 'true' : 'false') .';'.
					'var LinkrArticle=LinkrObject.getInstance(ArtOpts, ArtSrch, ArtLay, ArtTmpl);';
		}
		
		// Menu links
		if ($this->cp('link_menu', 1) && $inc['menu'])
		{
			$d->addScript($r .'menu'. $append);
			
			// Get menus
			$db	= & JFactory::getDBO();
			$db->setQuery('SELECT * FROM #__menu_types');
			$ml	= (array) $db->loadObjectList();
			foreach ($ml as $m)
			{
				$ti	= LinkrHelper::UTF8Encode($m->title);
				$de	= LinkrHelper::UTF8Encode($m->description);
				$js	.=
				'MITree.include({'.
					'id:"'. $m->menutype .'",'.
					'name:"'. urlencode($ti) .'",'.
					'description:"'. urlencode($de) .'"'.
				'});';
			}
			
			$js	.= 'var LinkrMenu=LinkrTree.getInstance(MIOpts,MITree,MISrch,MITmpl);';
		}
		
		// Contacts
		if ($this->cp('link_contact', 1) && $inc['contact'])
		{
			$d->addScript($r .'contact'. $append);
			$js	.= 'var LinkrContact = LinkrObject.getInstance(ConOpts,ConSrch,ConLay,ConTmpl);';
		}
		
		// Automatic links
		/*if ($inc['auto'])
		{
			$d->addScript($r .'auto'. $append);
			
			// Get extensions
			$db	= & JFactory::getDBO();
			$db->setQuery(
				'SELECT c.id, c.name, c.option '.
				'FROM #__components AS c '.
				'WHERE c.link <> "" AND c.parent = 0 '.
				'AND c.enabled = 1');
			$ex	= (array) $db->loadObjectList();
			foreach ($ex as $e)
			{
				$js	.=
				'_xr.include({'.
					'id:'. $e->id .','.
					'option:"'. $e->option .'",'.
					'name:"'. urlencode(LinkrHelper::UTF8Encode($e->name)) .'"'.
				'});';
			}
			
			$js	.= 'var LinkrX=LinkrTree.getInstance(_xo,_xr,_xs,_xt);';
		}*/
		
		// Debug
		$user	= & JFactory::getUser();
		if ($this->cp('debug', 0) && $user->gid >= 23) {
			$d->addScript($r .'debug.js?'. LINKR_VERSION_INC);
		}
		
		return $js;
	}
	
	// Linkr translations
	function onLinkrLoadL18N($version, $inc = array())
	{
		// General
		$L18N	= array(
			'ALL', 'BACK', 'BTN_OPTIONS', 'BTN_SEARCH',
			'CANCEL', 'CATEGORIES', 'CATEGORY',
			'CONFIGURE_LINK', 'CLEAR', 'CREATED', 'DESCRIPTION',
			'EXPAND', 'GET_LINK', 'LC_ANCHOR', 'LC_ATTRIBUTES',
			'LC_CLASS', 'LC_FORMAT', 'LC_PAGEBREAK', 'LC_RELATION',
			'LC_TARGET', 'LC_TARGET_SELF', 'LC_TARGET_BLANK',
			'LC_TEXT', 'LC_TITLE', 'LEFT', 'MODIFIED',
			'NAME', 'NO', 'NONE', 'NORESULTS', 'NOTICE_INSTALL',
			'OPTIONS', 'OR', 'ORDERING', 'PICK', 'PREVIEW', 'RIGHT',
			'SEARCH', 'SEARCH_IN', 'SEARCH_TYPE_RESULTS', 'TEXT',
			'TITLE', 'UNCATEGORIZED'
		);
		
		// Debug
		if (@$inc['debug']) {
			$L18N	= array_merge($L18N, array(
				'DEBUG_ABOUT', 'PLEASE SELECT A CATEGORY.'
			));
		}
		
		// Bookmarks
		if ($inc['bookmarks']) {
			$L18N	= array_merge($L18N, array(
				'NOTICE_BOOKMARKS', 'BOOKMARKS', 'BM_CONFIG_TITLE', 'GET_BMS',
				'BM_POST_TXT', 'BM_PRE_TXT', 'BM_CONFIG_SIZE', 'SIZE_TEXT',
				'SIZE_SMALL', 'SIZE_LARGE', 'SIZE_BTN', 'SIZE_CSTM',
				'BM_CONFIG_TXT', 'TEXT_LEFT', 'TEXT_RIGHT', 'BM_CONFIG_SEP',
				'BM_CONFIG_SELALL', 'BM_CONFIG_SELPOP'
			));
		}
		
		// Related links
		if ($inc['related']) {
			$L18N	= array_merge($L18N, array(
				'NOTICE_RELATED', 'RELATED_ARTICLES', 'TYPE_IN_KEYWORDS',
				'RL_CONFIG_LIMIT', 'RL_CONFIG_SHOW', 'RL_CONFIG_TITLE', 'RL_CONFIG_UPDATE',
				'RL_CONFIG_SHOW_ALL', 'NOTICE_RELATED_RANDOM', 'NO_PREVIEW', 'GET_REL'
			));
		}
		
		// Files
		if ($this->cp('link_file', 1) && $inc['file']) {
			$L18N	= array_merge($L18N, array(
				'ADD_TYPE_ICON', 'ALIGN', 'DIRECTORY', 'IMAGE',
				'GET_IMAGE_LINK', 'LC_IMG_SIZE', 'LINK_FILES',
				'TOP', 'BOTTOM', 'MIDDLE', 'UP', 'UPLOAD'
			));
		}
		
		// Articles
		if ($this->cp('link_article', 1) && $inc['article']) {
			$L18N	= array_merge($L18N, array(
				'ARTICLES', 'ARTICLE', 'SECTIONS',
				'SECTION', 'SORT_BY', 'TITLE'
			));
		}
		
		// Menu links
		if ($this->cp('link_menu', 1) && $inc['menu']) {
			$L18N	= array_merge($L18N, array(
				'MENU ITEM NAME', 'MENU', 'MENUS', 'MENU_ITEMS'
			));
		}
		
		// Contacts
		if ($this->cp('link_contact', 1) && $inc['contact']) {
			$L18N	= array_merge($L18N, array(
				'CONTACT', 'CONTACTS'
			));
		}
		
		// Automatic links
		/*if ($inc['auto']) {
			$L18N	= array_merge($L18N, array(
				'EXTENSION', 'EXTENSIONS'
			));
		}*/
		
		return $L18N;
	}
	
	// Related articles/Bookmarks
	function onPrepareContent(&$article, &$params, $limitstart = 0)
	{
		// Don't bother if item doesn't even have an ID
		if (!isset($article->id) || $article->id < 1) {
			return true;
		}
		
		// Debugging
		Linkr::log('---Linkr Plugin--- Article: '. @$article->title .' ('. @$article->id .')');
		
		// Set some stuff
		$this->findRouter();
		$this->article	= $this->getArticle($article);
		$this->articleRoute	= $this->route($this->article);
		$op	= strtolower(JRequest::getCmd('option'));
		$vi	= strtolower(JRequest::getCmd('view'));
		$fm	= strtolower(JRequest::getCmd('format', 'html'));
		$tm	= strtolower(JRequest::getCmd('tmpl', 'index'));
		if ($fm != 'html' || $tm != 'index' || JRequest::getBool('print', false)) {
			$this->showBookmarks	= false;
			$this->showRelated		= false;
		} elseif ($op == 'com_content' && $vi == 'article') {
			$this->showBookmarks	= true;
			$this->showRelated		= true;
		} else {
			$this->showBookmarks	= ($this->cp('bm_fp', 0));
			$this->showRelated		= ($this->cp('rel_fp', 0));
		}
		
		$sets	= $this->getSettings();
		if (!$sets || empty( $sets )) {
			return true;
		}
		
		// FIX: some plugins (e.g. CrossContent) reset "text" to "introtext + fulltext"
		//if (JString::strlen($article->fulltext)) {
		//	$article->text	= $article->introtext .'__LINKR_RM_SPLIT__'. $article->fulltext;
		//}
		
		// Linkr magic
		foreach ($sets as $s)
		{
			switch ($s->get('linkr', 'none'))
			{
				case 'bookmarks':
					$this->getBookmarks( $s );
					break;
				
				case 'related':
					$this->getRelatedLinks( $s );
					break;
			}
		}
		
		// FIX: some plugins (e.g. CrossContent) reset "text" to "introtext + fulltext"
		/*if (JString::strpos($article->text, '__LINKR_RM_SPLIT__') !== false)
		{
			list($intro, $full)	= explode('__LINKR_RM_SPLIT__', $article->text);
			$article->fulltext	= $full;
			$article->introtext	= $intro;
			$article->text	= $intro . $full;
		} else {
			$article->fulltext	= '';
			$article->introtext	= $this->article->text;
		}*/
		
		// PHP4 compatibility
		$article	= $this->article;
		
		// Done
		return true;
	}
	
	// Creates bookmark list
	function getBookmarks( $sets )
	{
		if (!$this->showBookmarks) {
			return $this->remove( $sets->match );
		}
		
		$badges	= trim($sets->get('badges', ''));
		if (empty( $badges )) {
			return $this->remove( $sets->match );
		}
		
		// Get badges
		$db	= & JFactory::getDBO();
		$q	= 'SELECT * FROM #__linkr_bookmarks ';
		if ($badges == 'p') {
			$q	.= 'WHERE popular = 1 ';
		} elseif ($badges != '*') {
			$q	.= 'WHERE id IN ('. $badges .') ';
		}
		$db->setQuery($q .' ORDER BY ordering');
		if (!$list = $db->loadObjectList()) {
			Linkr::log('Could not retrieve bookmarks: '. $db->getErrorMsg());
			return $this->remove( $sets->match );
		}
		
		$tips	= (bool) $this->cp('bm_tips', 1);
		$tips ? JHTML::_('behavior.tooltip') : null;
		$_size	= $sets->get('size', $this->cp('bm_size', 'text'));
		$html	= 'html'. $_size;
		$content	= array();
		foreach ($list as $bm)
		{
			// Get HTML
			$code	= '';
			if (!empty( $bm->$html )) {
				$size	= $_size;
				$code	= $bm->$html;
			} else {
				$size	= ($bm->size) ? $bm->size : 'text';
				$def	= 'html'. $size;
				$code	= $bm->$def;
			}
			
			if (!empty( $code ))
			{
				// Set things
				$bmText	= $this->HTMLEncode($bm->text);
				$bmName	= $this->HTMLEncode($bm->name);
				
				$this->repBookmarkAnchors($sets, $code, $bmText);
				$title	= '';
				if ($tips) {
					$title	= JText::sprintf('ADD_BM', $bmText, JString::ucfirst($bmName));
				}
				
				// Add text
				if ($size != 'text')
				{
					switch ($sets->get('text', 'nn'))
					{
						case 'yl':
							$code	= $bmText .' '. $code;
							break;
						
						case 'yr':
							$code	.= ' '. $bmText;
							break;
					}
				}
				
				$content[]	=
				'<div class="hasTip linkr-bm-b linkr-size-'. $size .'" title="'. $title .'">'.
					$code .
				'</div>';
			}
		}
		
		// Performance check
		if (empty( $content )) {
			return $this->remove( $sets->match );
		}
		
		// Text before bookmarks
		$txtPre	= JString::trim($sets->get('pre', ''));
		if (JString::strlen($txtPre)) {
			$txtPre	= $this->HTMLEncode($this->UTF8Decode(urldecode($txtPre)));
			$txtPre	= '<div class="linkr-bm-pre">'. $txtPre .'</div>';
		}
		
		// Text after bookmarks
		$txtPost	= JString::trim($sets->get('post', ''));
		if (JString::strlen($txtPost)) {
			$txtPost	= $this->HTMLEncode($this->UTF8Decode(urldecode($txtPost)));
			$txtPost	= '<div class="linkr-bm-post">'. $txtPost .'</div>';
		}
		
		// Separator
		$sep	= $sets->get('separator', '');
		$sep	= JString::strlen($sep) ? $sep : $this->cp('bm_sep', ' ');
		$sep	= $this->UTF8Decode(urldecode($sep));
		if ($sep != ' ' && JString::strlen($sep)) {
			$sep	= '<div class="linkr-bm-sep">'. $this->HTMLEncode($sep) .'</div>';
		}
		
		// Build bookmarks HTML
		$content	=
		"\n\n<!-- Linkr: Bookmark Links -->\n".
		'<div class="linkr-bm-before"></div>'.
		'<div class="linkr-bm">'.
			$txtPre . implode($sep, $content) . $txtPost .
		'</div>'.
		'<div class="linkr-bm-after"></div>';
		$this->remove($sets->match, $content);
		
		// Default styles
		if ($this->cp('bm_def_css', 1))
		{
			if (!isset($this->bmStyles))
			{
				$this->bmStyles	= true;
				$d	= & JFactory::getDocument();
				$s	= $this->cp('bcss', '');
				$d->addStyleDeclaration(base64_decode($s));
			}
		}
	}
	
	// Replaces anchors in bookmark's HTML codes
	function repBookmarkAnchors($s, &$html, $text)
	{
		// [badgespath]: Path to badges folder
		if (JString::strpos($html, '[badgespath]') !== false) {
			$path	= JURI::root() .'components/com_linkr/assets/badges';
			$html	= JString::str_ireplace('[badgespath]', $path, $html);
		}
		
		// [url]: Article URL
		if (JString::strpos($html, '[url]') !== false) {
			$html	= JString::str_ireplace('[url]', rawurlencode($this->articleRoute), $html);
		}
		
		// [url-js]
		if (JString::strpos($html, '[url-js]') !== false) {
			$html	= JString::str_ireplace('[url-js]', 'unescape("'. str_replace(array('&amp;', '&'), '%26', $this->articleRoute) .'")', $html);
		}
		
		// [title]: Article title
		if (JString::strpos($html, '[title]') !== false) {
			$title	= $s->get('articleTitle', '');
			$html	= JString::str_ireplace('[title]', $title, $html);
		}
		
		// [desc]: Article summary
		if (JString::strpos($html, '[desc]') !== false) {
			$desc	= $s->get('articleDesc', '');
			$html	= JString::str_ireplace('[desc]', $desc, $html);
		}
		
		// [text]: Bookmark text
		if (JString::strpos($html, '[text]') !== false) {
			$html	= JString::str_ireplace('[text]', $text, $html);
		}
	}
	
	// Creates related articles list
	function getRelatedLinks( $sets )
	{
		if (!$this->showRelated) {
			return $this->remove( $sets->match );
		}
		
		$keywords	= $sets->get('keywords', $this->getKeywords());
		if (empty( $keywords )) {
			return $this->remove( $sets->match );
		}
		
		$exclude	= trim($sets->get('exclude', ''));
		$exclude	= strlen($exclude) ? $exclude .','. $this->article->id : $this->article->id;
		$limit		= $sets->get('limit', $this->cp('rel_limit', 5));
		
		$db	= & JFactory::getDBO();
		if (is_string($keywords)) {
			$keywords	= JString::str_ireplace(',', ';', $keywords);
			$keywords	= @explode(';', $keywords);
		}
		
		// Build 'WHERE' clause
		$where 	= array();
		foreach ($keywords as $k)
		{
			$k	= $db->getEscaped(JString::trim($this->UTF8Decode(urldecode($k))), true);
			$k	= $db->Quote('%'. $k .'%', false);
			$w	= array();
			$w[] 	= 'LOWER(a.title) LIKE '. $k;
			$w[] 	= 'LOWER(a.introtext) LIKE '. $k;
			$w[] 	= 'LOWER(a.`fulltext`) LIKE '. $k;
			$w[] 	= 'LOWER(a.metakey) LIKE '. $k;
			$w[] 	= 'LOWER(a.metadesc) LIKE '. $k;
			$where[]	= '('. implode( ') OR (', $w ) .')';
		}
		$where 	= ' ('. implode(') OR (', $where) .') ';
		$where	= ' ('. $where .') AND a.id NOT IN ('. $exclude .') ';
		
		// Filter article sections
		if ($this->cp('rel_lsec', 1)) {
			$where	.= ' AND a.sectionid = '. $this->article->sectionid;
		} else {
			$relx1	= (int) $this->cp('rel_x1', 0);
			$relx2	= (int) $this->cp('rel_x2', 0);
			$relx3	= (int) $this->cp('rel_x3', 0);
			if ($relx1 || $relx2 || $relx3) {
				$relx	= array();
				if ($relx1) $relx[]	= $relx1;
				if ($relx2) $relx[]	= $relx2;
				if ($relx3) $relx[]	= $relx3;
				$where	.= ' AND a.sectionid NOT IN ('. implode(',', $relx) .') ';
			}
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
		switch ($this->cp('rel_sort', 'random'))
		{
			case 'title':
				$order	= 'a.title ASC, a.alias ASC';
				break;
			
			case 'date_asc':
				$order	= 'a.publish_up ASC, a.created ASC, a.modified ASC';
				break;
			
			case 'date_desc':
				$order	= 'a.publish_up DESC, a.created DESC, a.modified DESC';
				break;
			
			case 'modified':
				$order	= 'a.modified ASC, a.publish_up ASC, a.created ASC';
				break;
			
			case 'popular':
				$order	= 'a.hits DESC, a.publish_up ASC, a.created ASC';
				break;
			
			default:
				$order	= 'rand()';
		}
		
		// SQL Query
		$q	= 	' SELECT a.id, a.title, a.alias, a.introtext, a.catid, '.
				' a.sectionid, a.publish_up AS date, a.created_by_alias, '.
				' c.title AS category, u.id AS uid, u.name AS author, '.
				' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) '.
				' ELSE a.id END as slug, '.
				' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) '.
				' ELSE c.id END as catslug '.
				' FROM #__content AS a '.
				' LEFT JOIN #__categories AS c ON c.id = a.catid '.
				' LEFT JOIN #__sections AS s ON s.id = a.sectionid '.
				' LEFT JOIN #__users AS u ON u.id = a.created_by' .
				' WHERE a.state = 1 AND a.access <= '. $aid .
				' AND ( a.publish_up = '. $nulld .' OR a.publish_up <= '. $now .' ) '.
				' AND ( a.publish_down = '. $nulld .' OR a.publish_down >= '. $now .' ) '.
				' AND '. $where .' ORDER BY '. $order;
		$db->setQuery($q, 0, $limit);
		if (!$list = $db->loadObjectList()) {
			Linkr::log('Could not create related article links: '. $db->getErrorMsg());
			return $this->remove( $sets->match );
		}
		
		// External module
		if ($this->cp('rel_extm', 0))
		{
			// Save articles
			foreach ($list as $article) {
				$article->link	= $this->route($article);
			}
			JRequest::setVar('linkr_related_articles', $list);
			
			// Optional: title
			$title	= $sets->get('title', $this->cp('rel_title', JText::_('REL_TITLE')));
			$title	= JString::trim($this->UTF8Decode(urldecode($title)));
			JRequest::setVar('linkr_related_articles_title', $title);
			
			// Optional: css styles
			$css	= $this->cp('rcss', '');
			JRequest::setVar('linkr_related_articles_css', base64_decode($css));
			
			// Let module handle links
			return $this->remove( $sets->match );
		}
		
		// Related links
		$text		= $sets->get('title', false);
		
		// Title from settings
		if ($text) {
			$text	= JString::trim($this->UTF8Decode(urldecode($text)));
		}
		
		// Default title from database
		else {
			$text	= JString::trim($this->cp('rel_title', JText::_('REL_TITLE')));
		}
		
		$content	=
		"\n\n<!-- Linkr: Related Articles -->\n".
		'<div class="linkr-rl">'.
			'<div class="linkr-rl-t"> '.
				$text .
			' </div>'.
			'<ul>';
		$n	= count( $list );
		for ($i = 0; $i < $n; $i++)
		{
			// NOTE: article comes from database... no need for UTF8Decode
			$a	= & $list[$i];
			$articleTitle	= $this->HTMLEncode($a->title);
			$content	.=
				'<li>'.
					JHTML::link($this->route($a), $articleTitle) .
				'</li>';
		}
		$content	.= '</ul></div>';
		$this->remove($sets->match, $content);
		
		// Default styles
		if ($this->cp('rel_def_css', 1))
		{
			if (!isset($this->relStyles))
			{
				$this->relStyles	= true;
				$d	= & JFactory::getDocument();
				$s	= $this->cp('rcss', '');
				$d->addStyleDeclaration(base64_decode($s));
			}
		}
	}
	
	// Get keywords
	function getKeywords($words = null)
	{
		if (!is_null($words)) {
			if (is_string($words)) {
				$words	= JString::str_ireplace(',', ';', $words);
				$words	= @explode(';', $words);
			}
			return $words;
		}
		
		// Get keywords
		$source	= $this->cp('key_source', 'article');
		switch ($source)
		{
			case 'article':
				$words	= $this->_akeys();
				break;
			
			default:
				if (!empty($this->article->metakey)) {
					$words	= JString::str_ireplace(',', ';', $this->article->metakey);
					$words	= @explode(';', $words);
				}
		}
		return $words;
	}
	function _akeys()
	{
		jimport('joomla.filter.filterinput');
		$kw	= JFilterInput::clean($this->article->text);
		$kw	= JString::str_ireplace(array('(', ')', '?', '!', '.', ',', ';', ':'), ' ', $kw);
		$kw	= @explode(' ', $kw);
		if (empty( $kw )) {
			return array();
		}
		
		// Valid keywords
		$i	= 0;
		$keys	= array();
		$kw	= array_count_values($kw);
		foreach ($kw as $word => $count)
		{
			$i++;
			$length	= JString::strlen($word);
			if ($length > 4) {
				$keys[$count .'.'. $length .'.'. $i]	= $word;
			}
		}
		if (empty( $keys )) {
			return array();
		}
		
		// Get 15 keywords
		krsort($keys);
		$count	= 0;
		$words	= array();
		foreach ($keys as $k => $word) {
			$words[]	= $word;
			$count++;
			if ($count == 15) {
				break;
			}
		}
		return $words;
	}
	
	// Retrieves settings from Linkr anchor in text
	function getSettings($off = 0)
	{
		if (($a = JString::strpos($this->article->text, '{linkr', $off)) === false) {
			return $this->getDefaultSettings();
		}
		
		if (($b = JString::strpos($this->article->text, '}', $a)) === false) {
			return false;
		}
		
		if (($c = JString::substr($this->article->text, $a, $b - $a + 1)) === false) {
			return false;
		}
		
		// Format settings
		$d	= str_replace(array('{', '}'), '', $c);
		if ($d = @explode(';', $d))
		{
			$s	= new JObject();
			foreach ($d as $e)
			{
				if (strpos($e, ':') !== false) {
					list($k, $v) = explode(':', $e);
					//$s->set($k, $this->UTF8Decode($v));
					$s->set($k, $v);
				} else {
					$s->set($e, true);
				}
			}
			$s->set('match', $c);
			$s->set('articleTitle', urlencode($this->article->title));
			$s->set('articleDesc', urlencode($this->article->introtext));
			
			// Mark as loaded
			$this->loaded[$s->linkr]	= true;
			
			$this->settings[]	= $s;
			return $this->getSettings($b + 1);
		}
		
		return false;
	}
	
	// Handles default bookmarks and related links
	function getDefaultSettings()
	{
		if (JString::strpos($this->article->text, '{linkr:none}') !== false) {
			$this->remove('{linkr:none}');
			return empty( $this->settings ) ? false : $this->settings;
		}
		
		$settings	= array();
		
		// Show bookmarking by default
		if ($this->showBookmarks && ! @$this->loaded['bookmarks'] && $this->cp('def_bm', 0))
		{
			$q	= 'SELECT id FROM #__linkr_bookmarks';
			if (!$this->cp('bm_select', 0)) {
				$q	.= ' WHERE popular = 1';
			}
			
			$db	= & JFactory::getDBO();
			$db->setQuery( $q );
			
			if ($badges = $db->loadResultArray())
			{
				if ($this->cp('bm_top', 0)) {
					$this->article->text	= '{linkr:bookmarks}'. $this->article->text;
				} else {
					$this->article->text	.= '{linkr:bookmarks}';
				}
				$s	= new JObject();
				$s->set('linkr', 'bookmarks');
				$s->set('size', $this->cp('bm_size', 'text'));
				$s->set('separator', $this->cp('bm_sep', ' '));
				$s->set('badges', implode(',', $badges));
				$s->set('match', '{linkr:bookmarks}');
				$s->set('articleTitle', urlencode($this->article->title));
				$s->set('articleDesc', urlencode($this->article->introtext));
				$settings[]	= $s;
			}
		}
		
		// Show related links by default
		if ($this->showRelated && ! @$this->loaded['related'] && $this->cp('def_rel', 0))
		{
			$words	= $this->getKeywords();
			if (!empty( $words ))
			{
				$this->article->text	.= '{linkr:related}';
				$s	= new JObject();
				$s->set('linkr', 'related');
				$s->set('keywords', $this->UTF8Decode($words));
				$s->set('limit', $this->cp('rel_limit', 5));
				$s->set('match', '{linkr:related}');
				$settings[]	= $s;
			}
		}
		
		if (empty( $settings ) && empty( $this->settings )) {
			return false;
		} else {
			$this->settings	= array_merge($settings, $this->settings);
			return $this->settings;
		}
	}
	
	// Finds article route
	function route($a, $full = true)
	{
		// Make sure we have an article object
		if (is_numeric( $a )) {
			$a	= $this->_getArticle( $a );
		} elseif (is_string( $a )) {
			preg_match('/id=(\d+)/i', $a, $id);
			$a	= $this->_getArticle( $id[1] );
		}
		if (!is_object( $a )) {
			$route	= JRoute::_('index.php');
			return ($full) ? $this->_furl . $route : $route;
		}
		
		// Handle uncategorized articles and avoid PHP notices
		$a->slug	= (isset( $a->slug ) && !empty( $a->slug )) ? $a->slug : $a->id;
		$a->catslug	= (isset( $a->catslug ) && !empty( $a->catslug )) ? $a->catslug : 0;
		$a->sectionid	= (isset( $a->sectionid ) && !empty( $a->sectionid )) ? $a->sectionid : 0;
		
		// Create article route
		switch ( $this->router )
		{
			case 'JoomlaSEF':
				$route	= $this->_routeJoomlaSEF( $a );
				break;
			
			case 'Sh404SEF':
			case 'SmartSEF':
			default:
				$route	= $this->_routeOther( $a );
		}
		
		return ($full) ? $this->_furl . $route : $route;
	}
	
	// Retrieves an article object
	function _getArticle( $id )
	{
		// Performance check
		$id	= (int) $id;
		if (!$id) {
			return null;
		}
		
		// SQL query
		static $q;
		if (is_null($q))
		{
			// Date info
			$dbo	= & JFactory::getDBO();
			$date	= & JFactory::getDate();
			$now	= $dbo->Quote($date->toMySQL());
			$nulld	= $dbo->Quote($dbo->getNullDate());
			
			// User access
			$user	= & JFactory::getUser();
			$aid	= (int) $user->get('aid', 0);
			
			// Query
			$q	=
			' SELECT a.id, a.catid, a.sectionid, '.
			' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) '.
			' ELSE a.id END as slug, '.
			' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) '.
			' ELSE c.id END as catslug '.
			' FROM #__content AS a '.
			' LEFT JOIN #__categories AS c ON c.id = a.catid '.
			' WHERE a.state = 1 AND a.access <= '. $aid .
			' AND ( a.publish_up = '. $nulld .' OR a.publish_up <= '. $now .' ) '.
			' AND ( a.publish_down = '. $nulld .' OR a.publish_down >= '. $now .' ) '.
			' AND a.id = %d';
		}
		
		// Retrieve article from database
		$db	= & JFactory::getDBO();
		$db->setQuery(sprintf($q, $id));
		if ($a = $db->loadObject()) {
			return $a;
		} else {
			Linkr::log('Could not retrieve article (id = "'. $id .'"): '. $db->getErrorMsg());
			return null;
		}
	}
	
	// Returns an article object
	function getArticle($a = null)
	{
		// Get article from ID
		if (is_numeric($a)) {
			$a	= $this->_getArticle($a);
		}
		
		// Get object
		if (is_array($a)) {
			settype($a, 'object');
		} elseif (!is_object($a)) {
			$a	= new JObject();
		}
		
		// Set default values
		$a->id			= isset($a->id) ? $a->id : 0;
		$a->title		= isset($a->title) ? $a->title : '';
		$a->slug		= isset($a->slug) ? $a->slug : '';
		$a->catslug		= isset($a->catslug) ? $a->catslug : '';
		$a->sectionid	= isset($a->sectionid) ? $a->sectionid : '';
		$a->text		= isset($a->text) ? $a->text : '';
		$a->introtext	= isset($a->introtext) ? $a->introtext : '';
		
		return $a;
	}
	
	// Removes string from current article
	function remove($text, $replace = '') {
		$this->article->text	= JString::str_ireplace($text, $replace, $this->article->text);
	}
	
	// Encodes HTML characters
	function HTMLEncode($str) {
		return @htmlspecialchars($this->UTF8Encode($str), ENT_COMPAT, 'UTF-8');
	}
	
	// UTF-8 encoding
	function UTF8Encode($str)
	{
		if (is_array($str) || is_object($str)) {
			settype($str, 'array');
			foreach ($str as $k => $v) {
				$str[$k]	= $this->UTF8Encode($v);
			}
			return $str;
		} elseif (is_string($str)) {
			return $this->isUTF8($str) ? $str : utf8_encode($str);
		} else {
			return $str;
		}
	}
	function UTF8Decode($str)
	{
		if (is_array($str)) {
			foreach ($str as $k => $v) {
				$str[$k]	= $this->UTF8Decode($v);
			}
			return $str;
		} elseif (is_string($str)) {
			return $this->isUTF8($str) ? utf8_decode($str) : $str;
		} else {
			return $str;
		}
	}
	function isUTF8($str)
	{
		if (is_array($str)) {
			foreach ($str as $s) {
				if (!$this->isUTF8($s)) {
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
	
	function _routeJoomlaSEF( $a )
	{
		static $app, $sef, $sefrw, $suffix, $items;
		
		// Set static variables
		if (!isset( $app ))
		{
			$app	= & JFactory::getApplication('site');
			$sef	= $app->getCfg('sef');
			$sefrw	= $app->getCfg('sef_rewrite');
			$suffix	= $app->getCfg('sef_suffix');
		}
		
		// Not using SEF
		if (!$sef) {
			require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
			return JRoute::_(ContentHelperRoute::getArticleRoute($a->slug, $a->catslug, $a->sectionid));
		}
		
		if (!isset( $items )) {
			$menu	= & $app->getMenu('site');
			$items	= $menu->getItems('componentid', 20);
		}
		
		// We can use JRoute, but if the article is directly linked
		// to a menu item, then we don't need the article slug that
		// is automatically added by JRoute.
		
		// Find a menu item
		$route	= '';
		foreach($items as $i)
		{
			if ((@$i->query['view'] == 'article') && (@$i->query['id'] == $a->id)) {
				$route	= $i->route;
				break;
			}
		}
		
		// Build URL
		$url	= JURI::base(true) .'/';
		if (!empty( $route ))
		{
			if ($sefrw) {
				$url	.= $route;
			} else {
				$url	.= 'index.php/'. $route;
			}
			
			if ($suffix) {
				$url	.= '.html';
			}
		} else {
			require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
			$url	= JRoute::_(ContentHelperRoute::getArticleRoute($a->slug, $a->catslug, $a->sectionid));
		}
		
		return $url;
	}
	
	function _routeOther( $a )
	{
		// These routers use their own application router. So we can use JRoute.
		// We just need to make sure we have the form id:slug and not id-slug
		require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
		$route	= ContentHelperRoute::getArticleRoute($a->slug, $a->catslug, $a->sectionid);
		$route	= preg_replace('/([id|catid]=\d+)(-)/', '$1:', $route);
		return JRoute::_( $route );
	}
	
	function findRouter()
	{
		if (isset($this->router)) {
			return;
		}
		
		global $mainframe;
		
		// This can be done in different ways.. lets look at the class name
		// since this is not likely to change with future versions
		$router	= & $mainframe->getRouter();
		$name	= strtolower( get_class( $router ) );
		
		switch ( $name )
		{
			case 'jrouter':
			case 'jroutersite':
			case 'jrouteradministrator':
				$this->router	= 'JoomlaSEF';
				break;
			
			case 'shrouter':
				$this->router	= 'Sh404SEF';
				break;
			
			case 'smartsefrouter':
				$this->router	= 'SmartSEF';
				break;
			
			default:
				$this->router	= '?';
		}
		
		Linkr::log('Found routing application: '. $this->router);
		
		// Find the first piece of the URL that can make URLs full URLs
		// e.g. "http://www.site.com"
		//$this->_furl	= str_replace(JURI::base(true).'/','',JURI::base());
		$uri	= & JURI::getInstance();
		$this->_furl	= $uri->toString(array('scheme', 'host', 'port'));
	}
}

/*
 * Compatibility issues?
 */
if (!class_exists('plgContentlinkr_content')) {
	class plgContentlinkr_content extends plgContentLinkr_content {}
}

// Testing and Debuging
class Linkr
{
	function dump($var) {
		if (ob_get_level()) ob_end_clean();
		jexit(var_dump($var));
	}
	
	function log($msg)
	{
		static $log, $debug;
		
		if (is_null($debug)) {
			jimport('joomla.application.component.helper');
			$params	= & JComponentHelper::getParams('com_linkr');
			$debug	= (JDEBUG || $params->get('debug', 0));
		}
		if (!JString::strlen($msg) || !$debug) return;
		
		if (is_null($log)) {
			jimport('joomla.error.log');
			$log	= & JLog::getInstance('linkr.php', array('format' => '{DATE} {TIME} ({C-IP}), {COMMENT}'));
		}
		
		$log->addEntry(array('comment' => $msg));
	}
}
