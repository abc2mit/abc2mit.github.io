<?php
defined('_JEXEC') or die;

class LinkrControllerBookmark extends JController
{
	function LinkrControllerBookmark()
	{
		parent::__construct();
		
		// Register Extra tasks
		$this->registerTask('add', 'edit');
	}
	
	function edit()
	{
		JRequest::setVar('view', 'bookmark');
		JRequest::setVar('layout', 'edit');
		JRequest::setVar('hidemainmenu', 1);
		
		parent::display();
	}
	
	function save()
	{
		JRequest::checkToken() or jexit('invalid token');
		
		$model	= & $this->getModel('Bookmark');
		$msg	= $model->store() ? JText::_('NOTICE_SAVED') : $model->getError();
		
		$this->setRedirect(index .'&view=bookmarks', $msg);
	}
	
	function apply()
	{
		JRequest::checkToken() or jexit('invalid token');
		
		$model	= & $this->getModel('Bookmark');
		if ($id = $model->store()) {
			$msg	= JText::_('NOTICE_SAVED');
			$rdir	= index .'&controller=bookmark&task=edit&bid[]='. $id;
		} else {
			$msg = $model->getError();
			$rdir	= index .'&view=bookmarks';
		}
		
		$this->setRedirect($rdir, $msg);
	}
	
	function remove()
	{
		JRequest::checkToken() or jexit('invalid token');
		
		$model	= & $this->getModel('Bookmark');
		$msg	= $model->delete() ? JText::_('NOTICE_DELETED') : JText::_('NOTICE_DEL_ERROR');
		
		$this->setRedirect(index .'&view=bookmarks', $msg);
	}
	
	function cancel() {
		$msg	= JText::_('NOTICE_CANCELLED');
		$this->setRedirect(index .'&view=bookmarks', $msg);
	}
	
	function makepop()
	{
		JRequest::checkToken() or jexit('invalid token');
		
		$model	= & $this->getModel('Bookmark');
		$msg	= $model->makePopular(1) ? null : $model->getError();
		
		$this->setRedirect(index .'&view=bookmarks', $msg);
	}
	
	function unpop()
	{
		JRequest::checkToken() or jexit('invalid token');
		
		$model	= & $this->getModel('Bookmark');
		$msg	= $model->makePopular(0) ? null : $model->getError();
		
		$this->setRedirect(index .'&view=bookmarks', $msg);
	}
	
	function saveorder()
	{
		JRequest::checkToken() or jexit('invalid token');
		
		$model	= & $this->getModel('Bookmark');
		$bid	= JRequest::getVar('bid', array(), 'post', 'array');
		$orders	= JRequest::getVar('order', array(), 'post', 'array');
		JArrayHelper::toInteger($bid);
		JArrayHelper::toInteger($orders);
		
		// Re-order
		$msg	= $model->reorder($bid, $orders) ? JText::_('NOTICE_SAVED') : $model->getError();
		
		$this->setRedirect(index .'&view=bookmarks', $msg);
	}
	
	function orderup()
	{
		JRequest::checkToken() or jexit('invalid token');
		
		$model	= & $this->getModel('Bookmark');
		$bid	= JRequest::getVar('bid', array(), 'post', 'array');
		JArrayHelper::toInteger($bid);
		
		// Order up
		$msg	= $model->orderItem($bid[0], -1) ? null : $model->getError();
		
		$this->setRedirect(index .'&view=bookmarks', $msg);
	}
	
	function orderdown()
	{
		JRequest::checkToken() or jexit('invalid token');
		
		$model	= & $this->getModel('Bookmark');
		$bid	= JRequest::getVar('bid', array(), 'post', 'array');
		JArrayHelper::toInteger($bid);
		
		// Order down
		$msg	= $model->orderItem($bid[0], 1) ? null : $model->getError();
		
		$this->setRedirect(index .'&view=bookmarks', $msg);
	}
	
	function install()
	{
		JRequest::checkToken() or jexit('invalid token');
		
		$db	= & JFactory::getDBO();
		$db->setQuery(
			"INSERT INTO `#__linkr_bookmarks` (`name`, `text`, `size`, `htmltext`, `htmlsmall`, `htmllarge`, `htmlbutton`, `htmlcustom`, `ordering`, `icon`, `popular`) VALUES
			('Digg', 'Digg This!', 'text', '<a href=\"http://digg.com/submit?url=[url]&amp;title=[title]&amp;bodytext=[desc]\" target=\"_blank\">[text]</a>', '<a href=\"http://digg.com/submit?url=[url]&amp;title=[title]&amp;bodytext=[desc]\" target=\"_blank\">\r\n<img src=\"[badgespath]/digg.small.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '<a href=\"http://digg.com/submit?url=[url]&amp;title=[title]&amp;bodytext=[desc]\" target=\"_blank\">\r\n<img src=\"[badgespath]/digg.large.gif\" alt=\"[text]\" border=\"0\" width=\"32\" height=\"32\" />\r\n</a>', '<script type=\"text/javascript\">\r\ndigg_skin=''compact'';\r\ndigg_url=[url-js];\r\n</script>\r\n<script src=\"http://digg.com/tools/diggthis.js\" type=\"text/javascript\"></script>', '<script type=\"text/javascript\">\r\ndigg_url=[url-js];\r\n</script>\r\n<script src=\"http://digg.com/tools/diggthis.js\" type=\"text/javascript\"></script>', 4, 'components/com_linkr/assets/badges/digg.small.gif', 1),
			('Del.icio.us', 'Add to Del.icio.us', 'text', '<a href=\"http://del.icio.us/post?url=[url]&amp;title=[title]\" target=\"_blank\">[text]</a>', '<a href=\"http://del.icio.us/post?url=[url]&amp;title=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/delicious.small.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '<a href=\"http://del.icio.us/post?url=[url]&amp;title=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/delicious.large.gif\" alt=\"[text]\" border=\"0\" width=\"32\" height=\"32\" />\r\n</a>', '<a href=\"http://del.icio.us/post?url=[url]&amp;title=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/delicious.button.gif\" alt=\"[text]\" border=\"0\" />\r\n</a>', '', 7, 'components/com_linkr/assets/badges/delicious.small.gif', 1),
			('Reddit', 'Reddit', 'text', '<a href=\"http://reddit.com/submit?url=[url]&amp;title=[title]\" target=\"_blank\">[text]</a>', '<a href=\"http://reddit.com/submit?url=[url]&amp;title=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/reddit.small.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '<a href=\"http://reddit.com/submit?url=[url]&amp;title=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/reddit.large.gif\" alt=\"[text]\" border=\"0\" width=\"32\" height=\"32\" />\r\n</a>', '<a href=\"http://reddit.com/submit?url=[url]&amp;title=[title]\" target=\"_blank\">\r\n<img src=\"http://www.reddit.com/static/spreddit7.gif\" alt=\"[text]\" border=\"0\" />\r\n</a>', '<script type=\"text/javascript\" src=\"http://www.reddit.com/button.js?t=3\"></script>', 16, 'components/com_linkr/assets/badges/reddit.small.gif', 1),
			('Newsvine', 'Seed Newsvine', 'text', '<a href=\"http://www.newsvine.com/_wine/save?popoff=0&amp;u=[url]&amp;h=[title]\" target=\"_blank\">[text]</a>', '<a href=\"http://www.newsvine.com/_wine/save?popoff=0&amp;u=[url]&amp;h=[title]\" target=\"_blank\">\r\n<img src=\"http://www.newsvine.com/_vine/images/identity/button_seednewsvine.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '<a href=\"http://www.newsvine.com/_wine/save?popoff=0&amp;u=[url]&amp;h=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/newsvine.large.gif\" alt=\"[text]\" border=\"0\" width=\"32\" height=\"32\" />\r\n</a>', '', '', 15, 'components/com_linkr/assets/badges/newsvine.small.gif', 1),
			('Furl', 'Furl', 'text', '<a href=\"http://www.furl.net/storeIt.jsp?t=[title]&amp;u=[url]\" target=\"_blank\">[text]</a>', '<a href=\"http://www.furl.net/storeIt.jsp?t=[title]&amp;u=[url]\" target=\"_blank\">\r\n<img src=\"[badgespath]/furl.small.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '<a href=\"http://www.furl.net/storeIt.jsp?t=[title]&amp;u=[url]\" target=\"_blank\">\r\n<img src=\"[badgespath]/furl.large.gif\" alt=\"[text]\" border=\"0\" width=\"32\" height=\"32\" />\r\n</a>', '', '', 14, 'components/com_linkr/assets/badges/furl.small.gif', 0),
			('BlinkList', 'BlinkList', 'text', '<a href=\"http://www.blinklist.com/index.php?Action=Blink/addblink.php&amp;Url=[url]&amp;Title=[title]\" target=\"_blank\">[text]</a>', '<a href=\"http://www.blinklist.com/index.php?Action=Blink/addblink.php&amp;Url=[url]&amp;Title=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/blinklist.small.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '<a href=\"http://www.blinklist.com/index.php?Action=Blink/addblink.php&amp;Url=[url]&amp;Title=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/blinklist.large.gif\" alt=\"[text]\" border=\"0\" width=\"32\" height=\"32\" />\r\n</a>', '', '', 13, 'components/com_linkr/assets/badges/blinklist.small.gif', 0),
			('Yahoo MyWeb', 'Yahoo MyWeb', 'text', '<a href=\"http://myweb2.search.yahoo.com/myresults/bookmarklet?t=[title]&amp;u=[url]\" target=\"_blank\">[text]</a>', '<a href=\"http://myweb2.search.yahoo.com/myresults/bookmarklet?t=[title]&amp;u=[url]\" target=\"_blank\">\r\n<img src=\"[badgespath]/yahoo.small.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '<a href=\"http://myweb2.search.yahoo.com/myresults/bookmarklet?t=[title]&amp;u=[url]\" target=\"_blank\">\r\n<img src=\"[badgespath]/yahoo.large.gif\" alt=\"[text]\" border=\"0\" width=\"32\" height=\"32\" />\r\n</a>', '<script language=\"javascript\" src=\"http://sm.feeds.yahoo.com/Buttons/V1.0/yactions.js\"></script>\r\n<script language=\"javascript\">yactions.buildButton(''save'', ''My_Web'');</script>', '<script language=\"javascript\" src=\"http://sm.feeds.yahoo.com/Buttons/V1.0/yactions.js\"></script>\r\n<script language=\"javascript\">yactions.buildButton(''save'', ''My_Web'');</script>', 12, 'components/com_linkr/assets/badges/yahoo.small.gif', 0),
			('Google', 'Google', 'text', '<a href=\"http://www.google.com/bookmarks/mark?op=edit&amp;bkmk=[url]&amp;title=[title]\" target=\"_blank\">[text]</a>', '<a href=\"http://www.google.com/bookmarks/mark?op=edit&amp;bkmk=[url]&amp;title=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/google.small.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '<a href=\"http://www.google.com/bookmarks/mark?op=edit&amp;bkmk=[url]&amp;title=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/google.large.gif\" alt=\"[text]\" border=\"0\" width=\"32\" height=\"32\" />\r\n</a>', '', '', 11, 'components/com_linkr/assets/badges/google.small.gif', 0),
			('Diigo', 'Diigo', 'text', '<a href=\"http://www.diigo.com/post?url=[url]&amp;title=[title]\" target=\"_blank\">[text]</a>', '<a href=\"http://www.diigo.com/post?url=[url]&amp;title=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/diigo.small.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '<a href=\"http://www.diigo.com/post?url=[url]&amp;title=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/diigo.large.gif\" alt=\"[text]\" border=\"0\" width=\"32\" height=\"32\" />\r\n</a>', '', '', 10, 'components/com_linkr/assets/badges/diigo.small.gif', 0),
			('StumbleUpon', 'Stumble It!', 'text', '<a href=\"http://www.stumbleupon.com/submit?url=[url]&amp;title=[title]\" target=\"_blank\">[text]</a>', '<a href=\"http://www.stumbleupon.com/submit?url=[url]&amp;title=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/stumbleupon.small.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '<a href=\"http://www.stumbleupon.com/submit?url=[url]&amp;title=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/stumbleupon.large.gif\" alt=\"[text]\" border=\"0\" width=\"32\" height=\"32\" />\r\n</a>', '<a href=\"http://www.stumbleupon.com/submit?url=[url]&amp;title=[title]\" target=\"_blank\">\r\n<img border=0 src=\"http://cdn.stumble-upon.com/images/120x20_su_white.gif\" alt=\"[text]\" border=\"0\" />\r\n</a>', '', 9, 'components/com_linkr/assets/badges/stumbleupon.small.gif', 1),
			('Mr. Wong', 'Mr. Wong', 'text', '<a href=\"http://www.mister-wong.de/index.php?action=addurl&amp;bm_url=[url]&amp;bm_description=[title]\" target=\"_blank\">[text]</a>', '<a href=\"http://www.mister-wong.de/index.php?action=addurl&amp;bm_url=[url]&amp;bm_description=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/misterwong.small.gif\" border=\"0\" width=\"16\" height=\"16\" alt=\"[text]\" />\r\n</a>', '<a href=\"http://www.mister-wong.de/index.php?action=addurl&amp;bm_url=[url]&amp;bm_description=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/misterwong.large.gif\" border=\"0\" width=\"32\" height=\"32\" alt=\"[text]\" />\r\n</a>', '<a href=\"http://www.mister-wong.de/index.php?action=addurl&amp;bm_url=[url]&amp;bm_description=[title]\" target=\"_blank\">\r\n<img src=\"http://www.mister-wong.com/img/en/buttons/btn_1.gif\" alt=\"[text]\" border=\"0\" />\r\n</a>', '', 8, 'components/com_linkr/assets/badges/misterwong.small.gif', 0),
			('Technorati', 'Add to Faves!', 'text', '<a href=\"http://technorati.com/faves?add=[url]\" target=\"_blank\">[text]</a>', '<a href=\"http://technorati.com/faves?add=[url]\" target=\"_blank\">\r\n<img src=\"[badgespath]/technorati.small.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '<a href=\"http://technorati.com/faves?add=[url]\" target=\"_blank\">\r\n<img src=\"[badgespath]/technorati.large.gif\" alt=\"[text]\" border=\"0\" width=\"32\" height=\"32\" />\r\n</a>', '', '', 6, 'components/com_linkr/assets/badges/technorati.small.gif', 1),
			('Facebook', 'Share on Facebook', 'text', '<a href=\"http://www.facebook.com/sharer.php?u=[url]&amp;t=[title]\" target=\"_blank\">[text]</a>', '<a href=\"http://www.facebook.com/sharer.php?u=[url]&amp;t=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/facebook.small.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '<a href=\"http://www.facebook.com/sharer.php?u=[url]&amp;t=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/facebook.large.gif\" alt=\"[text]\" border=\"0\" width=\"32\" height=\"32\" />\r\n</a>', '<script>function fbs_click() {window.open(''http://www.facebook.com/sharer.php?u=[url]&amp;t=[title]'',''sharer'',''toolbar=0,status=0,width=626,height=436'');return false;}</script><style> html .fb_share_button { display: -moz-inline-block; display:inline-block; padding:1px 20px 0 5px; height:15px; border:1px solid #d8dfea; background:url(http://static.ak.fbcdn.net/images/share/facebook_share_icon.gif?7:26981) no-repeat top right; } html .fb_share_button:hover { color:#fff; border-color:#295582; background:#3b5998 url(http://static.ak.fbcdn.net/images/share/facebook_share_icon.gif?7:26981) no-repeat top right; text-decoration:none; } </style> <a href=\"http://www.facebook.com/share.php?u=[url]\" class=\"fb_share_button\" onclick=\"return fbs_click()\" target=\"_blank\" style=\"text-decoration:none;\">[text]</a>', '', 5, 'components/com_linkr/assets/badges/facebook.small.gif', 1),
			('Slashdot', 'Slashdot', 'text', '<a href=\"http://slashdot.org/submit.pl?url=[url]&amp;subj=[title]&amp;story=[desc]\" target=\"_blank\">[text]</a>', '<a href=\"http://slashdot.org/submit.pl?url=[url]&amp;subj=[title]&amp;story=[desc]\" target=\"_blank\">\r\n<img src=\"[badgespath]/slashdot.small.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '<a href=\"http://slashdot.org/submit.pl?url=[url]&amp;subj=[title]&amp;story=[desc]\" target=\"_blank\">\r\n<img src=\"[badgespath]/slashdot.large.gif\" alt=\"[text]\" border=\"0\" width=\"32\" height=\"32\" />\r\n</a>', '', '<script src=\"http://slashdot.org/slashdot-it.js\" type=\"text/javascript\"></script>', 17, 'components/com_linkr/assets/badges/slashdot.small.gif', 0),
			('Twitter', 'Update Twitter status', 'text', '<a href=\"http://twitter.com/home?status=[title]+[url]\" target=\"_blank\">[text]</a>', '<a href=\"http://twitter.com/home?status=[title]+[url]\" target=\"_blank\">\r\n<img src=\"[badgespath]/twitter.small.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '<a href=\"http://twitter.com/home?status=[title]+[url]\" target=\"_blank\">\r\n<img src=\"[badgespath]/twitter.large.gif\" alt=\"[text]\" border=\"0\" width=\"32\" height=\"32\" />\r\n</a>', '', '', 3, 'components/com_linkr/assets/badges/twitter.small.gif', 1),
			('MySpace', 'Post to MySpace', 'text', '<a href=\"http://www.myspace.com/Modules/PostTo/Pages/?u=[url]&amp;t=[title]&amp;c=[desc]\" target=\"_blank\">[text]</a>', '<a href=\"http://www.myspace.com/Modules/PostTo/Pages/?u=[url]&amp;t=[title]&amp;c=[desc]\" target=\"_blank\">\r\n<img src=\"[badgespath]/myspace.small.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '<a href=\"http://www.myspace.com/Modules/PostTo/Pages/?u=[url]&amp;t=[title]&amp;c=[desc]\" target=\"_blank\">\r\n<img src=\"[badgespath]/myspace.gif\" alt=\"[text]\" border=\"0\" width=\"32\" height=\"32\" />\r\n</a>', '', '', 2, '', 1),
			('Weblinkr', 'Save to Weblinkr', 'text', '<a href=\"http://weblinkr.com/add/?popup=1&amp;address=[url]&amp;title=[title]\" target=\"_blank\">[text]</a>', '<a href=\"http://weblinkr.com/add/?popup=1&amp;address=[url]&amp;title=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/weblinkr.small.png\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '<a href=\"http://weblinkr.com/add/?popup=1&amp;address=[url]&amp;title=[title]\" target=\"_blank\">\r\n<img src=\"[badgespath]/weblinkr.large.png\" alt=\"[text]\" border=\"0\" width=\"32\" height=\"32\" />\r\n</a>', '', '', 1, 'components/com_linkr/assets/badges/weblinkr.small.png', 0);"
		);
		$db->Query();
		
		$msg	= $db->getErrorNum() ? $db->getErrorMsg() : JText::_('NOTICE_SAVED');
		
		$this->setRedirect(index .'&view=bookmarks', $msg);
	}
}
