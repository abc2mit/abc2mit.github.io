<?php
defined('_JEXEC') or die;

$o		= array();
$o[]	= JHTML::_('select.option', 0, JText::_('NO'));
$o[]	= JHTML::_('select.option', 1, JText::_('YES'));
$d		= $this->_pd;
?>
<tr>
	<td width="100" class="key">
		<label for="<?php echo $d['id']; ?>">
			<span class="editlinktip hasTip" title="::<?php echo $d['desc']; ?>">
				<?php echo $d['text']; ?>
			</span>
		</label>
	</td>
	<td class="paramlist_value">
		<?php echo JHTML::_('select.genericlist', $o, $d['name'], 'class="inputbox"', 'value', 'text', $d['selected'], $d['id']); ?>
	</td>
</tr>