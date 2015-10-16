<?php defined('_JEXEC') or die; ?>

<script language="javascript" type="text/javascript">
<!--
	function checkFile()
	{
		var f = document.importForm, i = f.file;
		if (i.value.length < 5) {
			alert('<?php echo JText::_('INVALID_CSV', true); ?>');
			return i.focus();
		}
		var e	= i.value.toLowerCase().split('.');
		if (e[e.length-1] != 'csv') {
			alert('<?php echo JText::_('INVALID_CSV', true); ?>');
			return i.focus();
		}
		f.task.value	= 'import';
		f.source.value	= 'file';
		return f.submit();
	}
	function checkText()
	{
		var f = document.importForm, i = f.text;
		if (i.value.length < 5) {
			alert('<?php echo JText::_('INVALID_CSV', true); ?>');
			return i.focus();
		}
		f.task.value	= 'import';
		f.source.value	= 'text';
		return f.submit();
	}
	function exportToFile()
	{
		var f	= document.importForm;
		f.task.value	= 'export';
		f.submit();
	}
//-->
</script>

<form enctype="multipart/form-data" action="index.php" method="post" name="importForm">

	<div style="padding:10px 100px;text-align:center;font-size:12px;line-height:20px;letter-spacing:2px;">
		<?php echo JText::_('IN_EX_INSTRUCTIONS'); ?>
	</div>
	<table class="adminform">
	<tr>
		<th colspan="2"><?php echo JText::_('UPLOAD'); ?></th>
	</tr>
	<tr>
		<td width="120">
			&nbsp;
		</td>
		<td>
			<input id="file" name="file" type="file" class="input_box" size="57"/>
			<input class="button" type="button" value="<?php echo JText::_('IMPORT'); ?>" onclick="checkFile()"/>
		</td>
	</tr>
	</table>

	<table class="adminform">
	<tr>
		<th colspan="2"><?php echo JText::_('COPY_PASTE'); ?></th>
	</tr>
	<tr>
		<td width="120">
			&nbsp;
		</td>
		<td>
			<textarea id="text" name="text" class="input_box" cols="50" rows="4"></textarea>
			<br/><input type="button" class="button" value="<?php echo JText::_('IMPORT'); ?>" onclick="checkText()"/>
		</td>
	</tr>
	</table>

	<div style="padding:5px 0 20px 0;text-align:center;">
		<a href="index.php?option=com_linkr&view=export&format=csv"
			style="padding:10px 20px;border:1px solid;font-size:18px;"
			target="_blank"><?php echo JText::_('EXPORT'); ?></a>
	</div>

	<input type="hidden" name="option" value="com_linkr"/>
	<input type="hidden" name="controller" value="export"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="source" value=""/>
	<?php echo JHTML::_('form.token'); ?>
</form>
