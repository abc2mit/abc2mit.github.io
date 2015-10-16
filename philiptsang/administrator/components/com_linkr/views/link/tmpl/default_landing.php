<?php defined('_JEXEC') or die; ?>
<div id="linkr">
<?php
/*
 * Tools: Only show if there's more than one link
 */
if ($this->tools['count'] > 1 || ($this->tools['count'] == 1 && $this->links['count']))
{
?>
<div class="llGroup" style="border-bottom:2px solid #ddd;">
	<div class="llTitle">
		<img alt="..." src="<?php echo JURI::root(); ?>components/com_linkr/assets/img/linkr.gif" />
	</div>
	<div class="llContent">
		<?php echo implode(' | ', $this->tools['html']); ?>
		&nbsp;|&nbsp;<?php echo LINKR_VERSION_READ; ?>
	</div>
	<div style="clear:both;"></div>
</div>
<?php
}

/*
 * Links: Only show if there's more than one link
 */
if ($this->links['count'] > 1 || ($this->links['count'] == 1 && $this->tools['count']))
{
?>
<div class="llGroup" style="border-bottom:2px solid silver;">
	<div class="llTitle">
		<?php echo JText::_('LINK'); ?>
	</div>
	<div class="llContent">
		<?php echo implode(' | ', $this->links['html']); ?>
	</div>
	<div style="clear:both;"></div>
</div>
<?php
}
?>

<div id="layout">
	<div style="padding:50px;text-align:center;font-size:14px;letter-spacing:3px;">
		<?php echo JText::_('LL_INSTRUCTIONS'); ?>
	</div>
</div>
</div>
