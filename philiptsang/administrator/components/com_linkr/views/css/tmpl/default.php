<?php
defined('_JEXEC') or die;

// Default bookmarks
$a	= array('onclick' => 'return confirm(\''. JText::_('NOTICE_OVERWRITE', true) .'\')');
$b	= index .'&task=css.defb&'. JUtility::getToken() .'=1';
$b	= JHTML::link($b, JText::_('RESTORE_DEFAULT'), $a);

// Default related
$r	= index .'&task=css.defr&'. JUtility::getToken() .'=1';
$r	= JHTML::link($r, JText::_('RESTORE_DEFAULT'), $a);
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">

<fieldset class="adminform">
	<legend>CSS</legend>
	<table class="adminlist">
	<thead>
		<tr>
			<th colspan="2">
				<?php echo JText::_('CSS_INSTRUCTIONS'); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr><td colspan="2">&nbsp;</td></tr>
	</tfoot>
	<tbody>
		<tr>
			<td width="100" align="right" class="item">
				<label for="bcss">
					<?php echo JText::_('BOOKMARKS'); ?><br/>
					<?php echo $b; ?>
				</label>
			</td>
			<td>
				<textarea class="text_area" name="bcss" id="bcss"><?php echo $this->css['bcss']; ?></textarea>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="item">
				<label for="rcss">
					<?php echo JText::_('RELATED_ARTICLES'); ?><br/>
					<?php echo $r; ?>
				</label>
			</td>
			<td>
				<textarea class="text_area" name="rcss" id="rcss"><?php echo $this->css['rcss']; ?></textarea>
			</td>
		</tr>
	</tbody>
	</table>
</fieldset>
</div>
<div class="clr"></div>

	<input type="hidden" name="option" value="com_linkr"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="controller" value="css"/>
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
