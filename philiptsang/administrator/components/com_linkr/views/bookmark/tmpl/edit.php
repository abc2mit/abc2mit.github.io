<?php
defined('_JEXEC') or die;
$b	= & $this->bookmark;

// Popular checkbox
$popular	= $b->popular == 1 ? 'checked="checked"' : '';

// Sample icons & browse link
$egsb	= JHTML::image(LINKR_ASSETS .'img/egsb.gif', JText::_('SIZE_SMALL'));
$eglb	= JHTML::image(LINKR_ASSETS .'img/eglb.gif', JText::_('SIZE_LARGE'));
$egb	= JHTML::image(LINKR_ASSETS .'img/egbutton.gif', JText::_('SIZE_BTN'));
$egc	= JHTML::image(LINKR_ASSETS .'img/egcustom.gif', JText::_('SIZE_CSTM'));
$browse	= JURI::base() .'components/com_linkr/assets/badge.browse.png';
$browse	= JHTML::link(
	index .'&view=mm',
	JHTML::image($browse, 'browse'),
	array('class' => 'modal', 'rel' => '{handler:\'iframe\',size:{x:700,y:330}}')
);

// Keep "&amp;"
$b->htmltext	= JString::str_ireplace('&', '&amp;', $b->htmltext);
$b->htmlsmall	= JString::str_ireplace('&', '&amp;', $b->htmlsmall);
$b->htmllarge	= JString::str_ireplace('&', '&amp;', $b->htmllarge);
$b->htmlbutton	= JString::str_ireplace('&', '&amp;', $b->htmlbutton);
$b->htmlcustom	= JString::str_ireplace('&', '&amp;', $b->htmlcustom);

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">

<fieldset class="adminform">
	<legend>
		<?php echo JText::_('BOOKMARK_DETAILS'); ?>
	</legend>
	<table class="admintable">
	<tr>
		<td width="100" align="right" class="key">
			<label for="name">
				<?php echo JText::_('NAME'); ?>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="name" id="name" size="94" maxlength="20" value="<?php echo $this->bookmark->name;?>"/>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key">
			<label for="text">
				<?php echo JText::_('TEXT'); ?>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="text" id="text" size="94" maxlength="50" value="<?php echo $this->bookmark->text;?>"/>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key">
			<label for="popular">
				<?php echo JText::_('MAKE_POPULAR'); ?>
			</label>
		</td>
		<td>
			<input class="inputbox" type="checkbox" name="popular" id="popular" <?php echo $popular;?>/>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key">
			<label for="size">
				<?php echo JText::_('DEFAULT_SIZE'); ?>
			</label>
		</td>
		<td>
			<?php echo $this->lists['size']; ?>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key">
			<label for="icon">
				<?php echo JText::_('ICON_O'); ?>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="icon" id="icon" size="94" maxlength="100" value="<?php echo $this->bookmark->icon;?>"/>
			<?php echo $browse; ?>
		</td>
	</tr>
	</table>
</fieldset>

<fieldset class="adminform">
	<legend>
		<?php echo JText::_('HTML_CODE'); ?>
	</legend>
	<table class="adminlist">
	<thead>
		<tr>
			<th colspan="2">
				<?php echo JText::_('ANCHOR_NOTICE'); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr><td colspan="2">&nbsp;</td></tr>
	</tfoot>
	<tbody>
		<tr>
			<td width="100" align="right" class="item">
				<label for="htmltext">
					<?php echo JText::_('SIZE_TEXT'); ?><br/>
					<?php echo '<b>Digg This!</b>'; ?>
				</label>
			</td>
			<td>
				<textarea class="text_area" cols="99" rows="4" name="htmltext" id="htmltext"><?php echo $b->htmltext; ?></textarea>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="item">
				<label for="htmlsmall">
					<?php echo JText::_('SIZE_SMALL'); ?><br/>
					<?php echo $egsb; ?>
				</label>
			</td>
			<td>
				<textarea class="text_area" cols="99" rows="4" name="htmlsmall" id="htmlsmall"><?php echo $b->htmlsmall; ?></textarea>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="item">
				<label for="htmllarge">
					<?php echo JText::_('SIZE_LARGE'); ?><br/>
					<?php echo $eglb; ?>
				</label>
			</td>
			<td>
				<textarea class="text_area" cols="99" rows="4" name="htmllarge" id="htmllarge"><?php echo $b->htmllarge; ?></textarea>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="item">
				<label for="htmlbutton">
					<?php echo JText::_('SIZE_BTN'); ?><br/>
					<?php echo $egb; ?>
				</label>
			</td>
			<td>
				<textarea class="text_area" cols="99" rows="4" name="htmlbutton" id="htmlbutton"><?php echo $b->htmlbutton; ?></textarea>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="item">
				<label for="htmlcustom">
					<?php echo JText::_('SIZE_CSTM'); ?><br/>
					<?php echo $egc; ?>
				</label>
			</td>
			<td>
				<textarea class="text_area" cols="99" rows="4" name="htmlcustom" id="htmlcustom"><?php echo $b->htmlcustom; ?></textarea>
			</td>
		</tr>
	</tbody>
	</table>
</fieldset>
</div>
<div class="clr"></div>

	<input type="hidden" name="option" value="com_linkr"/>
	<input type="hidden" name="id" value="<?php echo $b->id; ?>"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="controller" value="bookmark"/>
	<?php echo JHTML::_('form.token'); ?>
</form>