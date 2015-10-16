<?php defined('_JEXEC') or die; ?>

<div class="linkrc">
	<h2 style="text-align:center;">
		<?php echo JText::_('BOOKMARKING'); ?>
	</h2>
	
	<h3><?php echo JText::_('CONFIGURATION'); ?></h3>
	<div class="linkrc">
<?php
echo	JText::_('DOCS_BOOKMARKING_CONFIG_TEXT_1') .' '.
		JText::_('DOCS_BOOKMARKING_CONFIG_TEXT_2');
?>
	</div>
	
	<h3><?php echo JText::_('HTML_ANCHORS'); ?></h3>
	<div class="linkrc">
<?php
echo
	JText::_('LIST_ANCHORS') .
	'<br/><br/>'.
	lTab . JText::sprintf('ANCHOR_DESC_TEXT', '<b>[text]</b>') .'<br/>'.
	lTab . JText::sprintf('ANCHOR_DESC_TITLE', '<b>[title]</b>') .'<br/>'.
	lTab . JText::sprintf('ANCHOR_DESC_DESC', '<b>[desc]</b>') .'<br/>'.
	lTab . JText::sprintf('ANCHOR_DESC_URL', '<b>[url]</b>');
?>
	</div>
	
	<h3><?php echo JText::_('CUSTOMIZING'); ?></h3>
	<div class="linkrc">
		<?php echo JText::_('MORE_OPTIONS_IN_PLUGIN'); ?>
	</div>
	
	<h3><?php echo JText::_('SIZE_EXAMPLES'); ?></h3>
	<div class="linkrc">
<?php
		$align	= array('align' => 'absmiddle');
		$small	= JHTML::image(LINKR_ASSETS .'img/egsb.gif', JText::_('SIZE_SMALL'), $align);
		$large	= JHTML::image(LINKR_ASSETS .'img/eglb.gif', JText::_('SIZE_LARGE'), $align);
		$btn	= JHTML::image(LINKR_ASSETS .'img/egbutton.gif', JText::_('SIZE_BTN'), $align);
		$cust	= JHTML::image(LINKR_ASSETS .'img/egcustom.gif', JText::_('SIZE_CSTM'), $align);
		
echo	JText::_('SIZE_TEXT') .'&nbsp;<b>Digg This!</b>&nbsp;|&nbsp;'.
		JText::_('SIZE_SMALL') .'&nbsp;'. $small .'&nbsp;|&nbsp;'.
		JText::_('SIZE_LARGE') .'&nbsp;'. $large .'&nbsp;|&nbsp;'.
		JText::_('SIZE_BTN') .'&nbsp;'. $btn .'&nbsp;|&nbsp;'.
		JText::_('SIZE_CSTM') .'&nbsp;'. $cust;
?>
	</div>
	
	<h3><?php echo JText::_('HTML_EXAMPLE'); ?></h3>
	<code class="linkrc"><br/>
<?php
echo	sprintf(lDivL, 'linkr-bm-before') .'&lt;/div&gt;<br/>'.
		sprintf(lDiv, 'linkr-bm') .
		lTab . sprintf(lDivL, 'linkr-bm-pre') .'...&lt;/div&gt;<br/>'.
		lTab . sprintf(lDiv, 'linkr-bm-b linkr-size-small') .
		lTab . lTab . JText::_('EXAMPLE') .'<br/>'.
		lTab . '&lt;/div&gt;<br/>'.
		lTab . sprintf(lDivL, 'linkr-bm-sep') .
			JText::_('BM_CONFIG_SEP') .
		'&lt;/div&gt;<br/>'.
		lTab . sprintf(lDiv, 'linkr-bm-b linkr-size-large') .
		lTab . lTab . JText::_('EXAMPLE') .'<br/>'.
		lTab . '&lt;/div&gt;<br/>'.
		lTab . sprintf(lDivL, 'linkr-bm-sep') .
			JText::_('BM_CONFIG_SEP') .
		'&lt;/div&gt;<br/>'.
		lTab . sprintf(lDiv, 'linkr-bm-b linkr-size-button') .
		lTab . lTab . JText::_('EXAMPLE') .'<br/>'.
		lTab . '&lt;/div&gt;<br/>'.
		lTab . sprintf(lDivL, 'linkr-bm-post') .'...&lt;/div&gt;<br/>'.
		'&lt;/div&gt;<br/>'.
		sprintf(lDivL, 'linkr-bm-after') .'&lt;/div&gt;';
?>
	</code>
</div>