<?php defined('_JEXEC') or die; ?>

<div class="linkrc">
	<h2 style="text-align:center;">
		<?php echo JText::_('RELATED_ARTICLES'); ?>
	</h2>
	
	<h3><?php echo JText::_('CONFIGURATION'); ?></h3>
	<div class="linkrc">
		<?php echo JText::_('RELATED_CONFIG_TEXT'); ?>
	</div>
	
	<h3><?php echo JText::_('CUSTOMIZING'); ?></h3>
	<div class="linkrc">
		<?php echo JText::_('MORE_OPTIONS_IN_PLUGIN'); ?>
	</div>
	
	<h3><?php echo JText::_('HTML_EXAMPLE'); ?></h3>
	<code class="linkrc"><br/>
<?php
echo	sprintf(lDiv, 'linkr-rl') .
		lTab . sprintf(lDiv, 'linkr-rl-t') .
		lTab . lTab . 'title<br/>'.
		lTab . '&lt;/div&gt;<br/>'.
		lTab . '&lt;ul&gt;<br/>'.
		lTab . lTab . '&lt;li&gt;<br/>'.
		lTab . lTab . lTab . JText::_( 'EXAMPLE' ) .'<br/>'.
		lTab . lTab . '&lt;/li&gt;<br/>'.
		lTab . lTab . '&lt;li&gt;<br/>'.
		lTab . lTab . lTab . JText::_( 'EXAMPLE' ) .'<br/>'.
		lTab . lTab . '&lt;/li&gt;<br/>'.
		lTab . lTab . '&lt;li&gt;<br/>'.
		lTab . lTab . lTab . JText::_( 'EXAMPLE' ) .'<br/>'.
		lTab . lTab . '&lt;/li&gt;<br/>'.
		lTab . '&lt;/ul&gt;<br/>'.
		'&lt;/div&gt;';
?>
	</code>
</div>