<?php
defined('_JEXEC') or die;

// Linkr icons path
$icons	= JURI::root() .'components/com_linkr/assets/img/';
?>

<script type="text/javascript">
function popLinkr()
{
	// Window position
	var s	= window.getSize().size;
	var t	= ((s.y - 350) / 2).round();
	var l	= ((s.x - 620) / 2).round();
	
	// Open Linkr
	var u	= "index.php?option=com_linkr&view=link&tmpl=component&mode=popup&links=0&tool=debug&<?php echo JUtility::getToken(); ?>=1&_ld=1";
	window.open(u, "LinkrWindow", "dependent=1,scrollbars=1,width=620,height=350,top="+ t +",left="+ l);
	
	return false;
}
</script>

<div class="linkrc">
	<h2 style="text-align:center;">
		<?php echo JText::_('PARAM_DEBUG'); ?>
	</h2>
	
	<div class="linkrc">
<?php
		echo JText::_('DEBUG_ABOUT') .' '. JText::_('DEBUG_INTRO');
		echo ' '. JText::sprintf('DEBUG_PENDING', '<img src="'. $icons .'loading.gif" align="absmiddle" />');
		echo ' '. JText::sprintf('DEBUG_SUCCESS', '<img src="'. $icons .'tick.png" align="absmiddle" />');
		echo ' '. JText::sprintf('DEBUG_FAIL', '<img src="'. $icons .'disabled.png" align="absmiddle" />');
?>
		<br /><br />
<?php
		echo JText::sprintf('DEBUG_PURPOSE', '<a href="'. LINKR_URL_SUPPORT .'" target="_blank">', '</a>');
?>
		<br /><br />
<?php
		echo JText::_('DEBUG_INST');
		echo ' '. JText::_('DEBUG_INST_DBT');
?>
	</div>
	
	<br /><br />
	<div style="text-align:center;">
		<a href="#" onclick="return popLinkr()" class="linkr-btn"><?php echo JText::_('DEBUG_CLICK'); ?></a>
	</div>
</div>