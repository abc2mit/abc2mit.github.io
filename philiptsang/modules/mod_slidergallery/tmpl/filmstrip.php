<?php // no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<?php

$width = $params->get('width','516');
$bgcolor = $params->get('bgcolor','#EFEFEF');
$border_thickness = $params->get('border_thickness','1');
$border_color = $params->get('border_color','#CCCCCC');
$path = $params->get('path','modules/mod_slidergallery/gallery');
$shadowbox = $params->get('shadowbox','0');

$border = $border_thickness."px "."solid ".$border_color;
$show_caption = $params->get('show_caption','1');
$credits = $params->get('credits','1');
$color = $params->get('credits_color','#CCCCCC');
$sort = $params->get('sort','asc');
$cap_path = $path."/captions/";
if($show_caption){
	$height = $params->get('height','390');
}
else{
	$height = $params->get('height','390');
	$height = $height - 34;
}
?>

<style>
	#previewPane, #galleryContainer{
		border:<?php echo $border; ?>;
	}
	#previewPane img{
		height:<?php if($show_caption) {echo $height-40;} else {echo $height-12;} ?>px;
		width:<?php echo $width-$border_thickness-24; ?>px;
		border:none;
	}
	#previewPane a{ background:none;}
</style>
<?php
				$imgdir = $path;  //'modules/mod_slidergallery/gallery';  the directory, where your images are stored
				
				$allowed_types = array('png','jpg','jpeg','gif'); // list of filetypes you want to show
				
				$dimg = opendir($imgdir);
				while($imgfile = readdir($dimg))
				{
				 if(in_array(strtolower(substr($imgfile,-3)),$allowed_types))
				 {
				  $a_img[] = $imgfile;				 
				 } 
				}
				if($sort == "asc"){	sort($a_img); } else { rsort($a_img); }		
				$totimg = count($a_img); // total image number
				
				
				
				//echo $cap_path;
				foreach(glob($cap_path . "*.txt") as $txt)
				{
					//echo "txt = ".$txt."<br/>";
					$a_txt[] = $txt;
					$temp = explode("captions/",$txt);
					$a_txt_name[] = $temp[1];
				}
				
				
				


?>


<?php /*?><script type="text/javascript" src="<?php echo JURI::root(); ?>modules/mod_slidergallery/js/shadowbox.js"></script>
<script type="text/javascript">
	Shadowbox.init();
</script>
<link rel="stylesheet" type="text/css" href="<?php echo JURI::root(); ?>modules/mod_slidergallery/css/shadowbox.css"><?php */?>


<script type="text/javascript" src="<?php echo JURI::root(); ?>modules/mod_slidergallery/js/prototype.js"></script>
<script type="text/javascript" src="<?php echo JURI::root(); ?>modules/mod_slidergallery/js/scriptaculous.js?load=effects,builder"></script>
<script type="text/javascript" src="<?php echo JURI::root(); ?>modules/mod_slidergallery/js/lightbox.js"></script>
<link rel="stylesheet" href="<?php echo JURI::root(); ?>modules/mod_slidergallery/css/lightbox.css" type="text/css" media="screen" />

<script src="<?php echo JURI::root(); ?>modules/mod_slidergallery/js/image-slideshow.js" language="JavaScript1.2"></script>
<script>
	function startSlide(e)
	{
		if(document.all)e = event;
		var id = this.id;
		this.getElementsByTagName('IMG')[0].src = '<?php echo JURI::root(); ?>modules/mod_slidergallery/images/' + this.id + '_over.gif';	
		if(this.id=='arrow_right'){
			slideSpeedMultiply = Math.floor((e.clientX - this.offsetLeft) / 5);
			slideSpeed = -1*slideSpeedMultiply;
			slideSpeed = Math.max(-10,slideSpeed);
		}else{			
			slideSpeedMultiply = 10 - Math.floor((e.clientX - this.offsetLeft) / 5);
			slideSpeed = 1*slideSpeedMultiply;
			slideSpeed = Math.min(10,slideSpeed);
			if(slideSpeed<0)slideSpeed=10;
		}
	}
	
	function releaseSlide()
	{
		var id = this.id;
		this.getElementsByTagName('IMG')[0].src = '<?php echo JURI::root(); ?>modules/mod_slidergallery/images/' + this.id + '.gif';
		slideSpeed=0;
	}
	
	function showPreview(imagePath,imageIndex){
		var subImages = document.getElementById('previewPane').getElementsByTagName('IMG');
		if(subImages.length==0){
			var img = document.createElement('IMG');
			document.getElementById('previewPane').appendChild(img);
		}else img = subImages[0];
		
		if(displayWaitMessage){
			document.getElementById('waitMessage').style.display='inline';
		}
		<?php if($show_caption) {?>
		document.getElementById('largeImageCaption').style.display='none';
		<?php } ?>
		img.onload = function() { hideWaitMessageAndShowCaption(imageIndex-1); };		
		img.src = imagePath;
		
	}
	function hideWaitMessageAndShowCaption(imageIndex)
	{
		document.getElementById('waitMessage').style.display='none';	
		<?php if($show_caption) {?>
		document.getElementById('largeImageCaption').innerHTML = imageGalleryCaptions[imageIndex];
		document.getElementById('largeImageCaption').style.display='block';
		<?php } ?>
	}
	window.onload = initSlideShow;
</script>
<link rel="stylesheet" href="<?php echo JURI::root(); ?>modules/mod_slidergallery/css/image-slideshow.css" type="text/css">

<div id="slidergallery" style="width:<?php echo $width; ?>px; ">
	
	<div id="galleryContainer" style="background:<?php echo $bgcolor; ?>">
		<div id="arrow_left"><img src="<?php echo JURI::root(); ?>modules/mod_slidergallery/images/arrow_left.gif"></div>
		<div id="arrow_right"><img src="<?php echo JURI::root(); ?>modules/mod_slidergallery/images/arrow_right.gif"></div>
		<div id="theImages">
				<!-- Thumbnails -->
                <?php 
				//$directory = ;
 
				//get all image files with a .jpg extension.
				//$file = glob($cap_path . "*.txt");			 
				//print each file name
				
					for($x=0; $x < $totimg; $x++)
					{   
						 $size = getimagesize($imgdir.'/'.$a_img[$x]);										
						 $halfwidth = ceil($size[0]/2);
						 $halfheight = ceil($size[1]/2);
						 
						 $txt_temp = explode(".",$a_img[$x]);
						 $captionfile = $cap_path.$txt_temp[0].".txt";
						 if(in_array($txt_temp[0].".txt",$a_txt_name)){
						 	
						 	$fh = fopen($captionfile, 'r');
							$theData = fread($fh, 5000);
							fclose($fh);
							//echo "captions of = ".$a_txt[$x]."is = ".$theData."<br/>";
							//echo "text name iis = ".$a_txt_name[$x]."<br/>";
						 }
							
				?>
                			
				 <a href="<?php echo $imgdir."/".$a_img[$x]; ?>" rel="lightbox[gallery]" title="<?php echo $theData;?>"><img src="<?php echo $imgdir."/".$a_img[$x]; ?>" height="100" width="160" /></a>
				<?php } ?>                
				<!-- End thumbnails -->				
				<!-- Image captions -->				
                <?php
				if($totimg !== 0)
				{
                	for($x=0; $x < $totimg; $x++)
					{
						 $size = getimagesize($imgdir.'/'.$a_img[$x]);						
						 
						 $temp = explode(".",$a_img[$x]);
						 
                ?>
                	<div class="imageCaption"><?php echo $temp[0]; ?></div>
                <?php } 
				}
				else
				{
					echo "No images found!";
				}
				?>
				<!-- End image captions -->				
				<div id="slideEnd"></div>
		</div>
	</div>    
</div>
<?php if($credits) { ?>
<div id="link" style="color:<?php echo $color; ?>; width:<?php echo $width; ?>px"; class="<?php echo $color;?>"><a href="http://yashvyas.in" target="_blank">Yash Vyas</a></div>
<?php } ?>