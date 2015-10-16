<?php
defined('_JEXEC') or die;

// Feed
if ($this->feed)
{
?>
<div style="margin-bottom:20px;padding-bottom:10px;border-bottom:1px solid #dddddd;text-align:center;">
<?php
	echo $this->loadTemplate('feed');
?>
</div>
<?php
}
else {
	echo '<br />';
}
?>

<a name="goto"></a>
<div>
	<a href="<?php echo LINKR_URL_HOME; ?>" target="_blank" class="linkr-btn">Linkr <?php echo LINKR_VERSION_READ; ?></a>
	&nbsp;&nbsp;
	<?php echo JText::_('Go to:'); ?>&nbsp;
	<a href="index.php?option=com_linkr&view=docs&about=bookmarks#goto"><?php echo JText::_('BOOKMARKING'); ?></a>
	&nbsp;|&nbsp;
	<a href="index.php?option=com_linkr&view=docs&about=related#goto"><?php echo JText::_('RELATED_ARTICLES'); ?></a>
	&nbsp;|&nbsp;
	<a href="index.php?option=com_linkr&view=docs&about=faqs#goto"><?php echo JText::_('FAQs'); ?></a>
	&nbsp;|&nbsp;
<?php
// Debug
if (LinkrHelper::getParam('debug', 0))
{
?>
	<a style="color:#990000;" href="index.php?option=com_linkr&view=docs&about=debug#goto"><?php echo JText::_('PARAM_DEBUG'); ?></a>
	&nbsp;|&nbsp;
<?php
}
?>
	<a href="http://extensions.joomla.org/extensions/4010/details" target="_blank">JED &#187;</a>
	<!-- http://extensions.joomla.org/index.php?option=com_mtree&task=viewlink&link_id=4010 -->
</div>

<?php echo $this->loadTemplate($this->about); ?>

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