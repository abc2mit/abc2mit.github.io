<?php defined('_JEXEC') or die; ?>

<!-- File List -->
<div>
	<div id="messages" style="display:none;"></div>
	<div id="fileList">
		<div style="clear:both;"></div>
<?php
	if (count($this->form->files))
	{
		foreach ($this->form->files as $f)
		{
			$icon	= array('width' => $f->width, 'height' => $f->height);
			$icon	= JHTML::image($f->icon, $f->name, $icon);
			$title	= $f->type .' - '. $f->name .' ('. $f->size .')';
			$name	= str_replace('.'. $f->ext, '', $f->name);
			$name	= strlen($name) > 10 ? substr($name, 0, 10) .'...' : $name;
			$src	= JString::str_ireplace(JURI::root(), '', $f->src);
			$file	= base64_encode($f->name);
			$click	= 'return selectIcon(\''. $src .'\', \''. $file .'\');';
			echo
			'<div class="item">
				<div class="icon" title="'. $title .'" onclick="'. $click .'">
					<div align="center" class="border">
						<a>'. $icon .'</a>
					</div>
				</div>
				<div class="name">'. $name .'</div>
			</div>';
		}
	}
?>
		<div style="clear:both;"></div>
	</div>
</div>

<!-- Upload Form -->
<?php
$del	= JRequest::getBool('delete', false);
$del	= $del ? 'checked="checked"' : '';
?>
<form action="<?php echo $this->form->uploadURL; ?>" id="uploadForm" name="uploadForm" method="post" enctype="multipart/form-data">
	<fieldset>
		<legend><?php echo JText::_('UPLOAD'); ?></legend>
		<input type="file" id="file-upload" name="Filedata" />
		<input type="submit" id="file-upload-submit" value="<?php echo JText::_('UPLOAD'); ?>"/>
		<span id="upload-clear"></span>
		<span style="margin:0 100px;"> </span>
		<input type="checkbox" name="deli" <?php echo $del; ?>/> <?php echo JText::_('DELETE_ONCLICK'); ?>
		<ul class="upload-queue" id="upload-queue">
			<li style="display:none" />
		</ul>
	</fieldset>
</form>
