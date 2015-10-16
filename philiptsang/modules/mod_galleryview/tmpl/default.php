<?php // no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<?php

$width = $params->get('width','600');
$height = $params->get('height','150');
$thumb_width = $params->get('thumb_width','50');
$path = $params->get('path','modules/mod_galleryview/gallery');
$tras_speed = $params->get('trans_speed','400');
$bgcolor = $params->get('bgcolor','#000');
$border_color = $params->get('border_color','#000');
$theme = $params->get('theme','light');
$auto_slide = $params->get('auto_slide','1');
$thumb_gen = $params->get('thumb_gen','0');
$transition_interval = $params->get('transition_interval','6000');
$loadjq = $params->get('loadjq','1');
$load_scripts = $params->get('load_scripts','1');
$uniuqe_id = $params->get('uniuqe_id','');
$show_film = $params->get('show_film','');
?>


<?php
				$imgdir = $path;  //'modules/mod_galleryview/gallery';  the directory, where your images are stored
				
				$allowed_types = array('png','jpg','jpeg','gif'); // list of filetypes you want to show
				
				$dimg = opendir($imgdir);
				while($imgfile = readdir($dimg))
				{
				 if(in_array(strtolower(substr($imgfile,-3)),$allowed_types))
				 {
				  $a_img[] = $imgfile;
				  sort($a_img);
				  reset ($a_img);
				 } 
				}
				
				$totimg = count($a_img); // total image number
				if($totimg){		
				}
?>


	<?php if($loadjq) {?>
		<script type="text/javascript" src="<?php echo JURI::root(); ?>modules/mod_galleryview/js/jquery-1.3.2.min.js"></script>
    <?php }?>
    <script type="text/javascript">
  		
	  // Code that uses other library's $ can follow here.
	</script>
	
    <?php if($load_scripts) {?> 
	<script type="text/javascript" src="<?php echo JURI::root(); ?>modules/mod_galleryview/js/jquery.galleryview-1.1-pack.js"></script>
    <script type="text/javascript" src="<?php echo JURI::root(); ?>modules/mod_galleryview/js/jquery.timers-1.1.2.js"></script>
    <script type="text/javascript" src="<?php echo JURI::root(); ?>modules/mod_galleryview/js/jquery.easing.1.3.js"></script>
    <?php } ?>

	<script type="text/javascript">
		jQuery.noConflict();
		jQuery(document).ready(function($) {
            $('#photos<?php echo "-".$uniuqe_id; ?>').galleryView({
            panel_width:<?php echo $width; ?>,
            panel_height:<?php echo $height; ?>,
            frame_width:<?php echo $thumb_width; ?>,
            frame_height:<?php echo $thumb_width; ?>,
            transition_speed:<?php echo $tras_speed; ?>,
			background_color: '<?php echo $bgcolor; ?>',
			overlay_position: 'bottom',
			border: '1px solid <?php echo $border_color; ?>',
			nav_theme: '<?php echo $theme; ?>',
			easing: 'easeInQuad',
			transition_interval:<?php echo $transition_interval;  ?>
        });
    });
    </script>


<div id="photos<?php echo "-".$uniuqe_id; ?>" class="galleryview">
	
    <?php for($x=0; $x < $totimg; $x++)	{ ?>
    <div class="panel">    	
		<img src="<?php echo $imgdir.'/'.$a_img[$x]; ?>" />        
	</div>
    <?php } ?>
    
    <?php if ($show_film) { ?>
		<?php if ($thumb_gen) { ?>
        <ul class="filmstrip">
            <?php for($x=0; $x < $totimg; $x++)	{ ?>
                <?php $temp = explode(".",$a_img[$x]); ?>
                <li><img src="<?php echo $imgdir.'/thumb/'.$temp[0].'_thumb.'.$temp[1]; ?>" alt="<?php echo $a_img[$x]; ?>" title="<?php echo $a_img[$x]; ?>" height="<?php echo $thumb_width; ?>" width="<?php echo $thumb_width; ?>" /></li>
            <?php } ?>     
        </ul>
        <?php } else { ?>
        <ul class="filmstrip">
            <?php for($x=0; $x < $totimg; $x++)	{ ?>
                <?php $temp = explode(".",$a_img[$x]); ?>
                <li><img src="<?php echo $imgdir.'/'.$a_img[$x]; ?>" alt="<?php echo $a_img[$x]; ?>" title="<?php echo $a_img[$x]; ?>" height="<?php echo $thumb_width; ?>" width="<?php echo $thumb_width; ?>" /></li>
            <?php } ?>     
        </ul>
        <?php } ?>
    <?php } ?>
</div>