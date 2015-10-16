<?php
defined('_JEXEC') or die;
$seo	= JHTML::link('http://wikipedia.org/wiki/Search_engine_optimization', 'SEO', array('target' => '_blank'));
$download	= JHTML::link('http://extensions.joomla.org/extensions/4010/details', 'Joomla!', array('target' => '_blank'));
?>

<div class="linkrc">
	<h2 style="text-align:center;">
		<a href="<?php echo LINKR_URL_FAQS; ?>" target="_blank">
			<?php echo JText::_('FAQ'); ?>
		</a>
	</h2>
	
	<h3><?php echo JText::_('FAQ_1'); ?></h3>
	<div class="linkrc">
		<?php echo JText::_('FAQ_1_ANSWER'); ?>
	</div>
	
	<h3><?php echo JText::_('FAQ_2'); ?></h3>
	<div class="linkrc">
		<?php echo JText::sprintf('FAQ_2_ANSWER', $seo); ?>
	</div>
	
	<h3><?php echo JText::_('FAQ_9'); ?></h3>
	<div class="linkrc">
		<?php echo JText::_('FAQ_9_ANSWER'); ?>
	</div>
	
	<h3><?php echo JText::_('FAQ_8'); ?></h3>
	<div class="linkrc">
		<?php echo JText::_('FAQ_8_ANSWER'); ?>
	</div>
	
	<h3><?php echo JText::_('FAQ_3'); ?></h3>
	<div class="linkrc">
		<?php echo JText::sprintf('FAQ_3_ANSWER', $download); ?>
	</div>
	
	<h3><?php echo JText::_('FAQ_4'); ?></h3>
	<div class="linkrc">
		<?php echo JText::_('FAQ_4_ANSWER'); ?>
	</div>
	
	<h3><?php echo JText::_('FAQ_5'); ?></h3>
	<div class="linkrc">
		<?php echo JText::_('FAQ_5_ANSWER'); ?>
	</div>
	
	<h3><?php echo JText::_('FAQ_6'); ?></h3>
	<div class="linkrc">
		<?php echo JText::_('FAQ_6_ANSWER'); ?>
	</div>
	
	<h3><?php echo JText::_('FAQ_7'); ?></h3>
	<div class="linkrc">
		<?php echo JText::_('FAQ_7_ANSWER'); ?>
	</div>
</div>