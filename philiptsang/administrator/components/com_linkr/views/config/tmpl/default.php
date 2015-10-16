<?php defined('_JEXEC') or die; ?>
<form name="adminForm" action="" method="post">

<!-- Global config -->
<div class="col width-60">
	<fieldset class="adminform">
	<legend><?php echo JText::_('CONFIGURATION'); ?></legend>
	<table class="admintable" cellspacing="1">
	<tbody>
<?php
		// Back-end news feed
		$this->_pd	= array(
			'id'	=> 'admin_feed',
			'name'	=> 'params[rss]',
			'desc'	=> JText::_('NEWSFEED_DESC'),
			'text'	=> JText::_('NEWSFEED'),
			'selected'	=> $this->params->get('rss', 1)
		);
		
		echo $this->loadTemplate('yesno');
		
		// Debug
		$this->_pd	= array(
			'id'	=> 'admin_debug',
			'name'	=> 'params[debug]',
			'desc'	=> JText::_('PARAM_DEBUG_DESC'),
			'text'	=> JText::_('PARAM_DEBUG'),
			'selected'	=> $this->params->get('debug', 1)
		);
		
		echo $this->loadTemplate('yesno');
?>
	</tbody>
	</table>
	</fieldset>
</div>

<!-- Parameters -->
<div class="col width-40">
	<fieldset class="adminform">
	<legend><?php echo JText::_('PARAMETERS'); ?></legend>
<?php
// Open pane
echo $this->pane->startPane('params');

// Related articles
echo $this->pane->startPanel(JText::_('RELATED_ARTICLES'), 'rel-params');
echo $this->params->render('params', 'related');
echo $this->pane->endPanel();

// Bookmarks
echo $this->pane->startPanel(JText::_('BOOKMARKS'), 'bm-params');
echo $this->params->render('params', 'bookmarks');
echo $this->pane->endPanel();

// Advanced parameters
echo $this->pane->startPanel(JText::_('ADVANCED PARAMETERS'), 'adv-params');
echo $this->params->render('params', 'advanced');
echo $this->pane->endPanel();

// Close pane
echo $this->pane->endPane();
?>
	</fieldset>
</div>

	<input type="hidden" name="option" value="com_linkr" />
	<input type="hidden" name="controller" value="config" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_('form.token'); ?>
</form>

<div class="clr"></div>
<div style="margin-top:15px;padding-top:15px;border-top:1px solid #dddddd;text-align:center;">
	<?php
	$linkr	= '<a href="'. LINKR_URL .'" target="_blank">';
	$frank	= '<a href="mailto:'. LINKR_MAIL .'?subject=Linkr">';
	$fback	= '<a href="'. LINKR_URL_SUPPORT .'" target="_blank">';
	$api	= '<a href="'. LINKR_URL_API .'" target="_blank">';
	$egs	= '<a href="'. LINKR_URL_API_EG .'" target="_blank">';
	echo JText::sprintf('LINKR_CREATED_BY', '</a>', $linkr, $frank, $fback, $api, $egs);
	?>
</div>