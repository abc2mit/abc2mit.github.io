   	/***********************************************************************************************
	
	Copyright (c) 2005 - Alf Magne Kalleland post@dhtmlgoodies.com
	
	UPDATE LOG:
	
	March, 10th, 2006 : Added support for a message while large image is loading
	
	Get this and other scripts at www.dhtmlgoodies.com
	
	You can use this script freely as long as this copyright message is kept intact.
	
	***********************************************************************************************/ 
   	
	var displayWaitMessage=true;	// Display a please wait message while images are loading?
  	
   		
	var activeImage = false;
	var imageGalleryLeftPos = false;
	var imageGalleryWidth = false;
	var imageGalleryObj = false;
	var maxGalleryXPos = false;
	var slideSpeed = 0;
	var imageGalleryCaptions = new Array();
			
	function gallerySlide()
	{
		if(slideSpeed!=0){
			var leftPos = imageGalleryObj.offsetLeft;
			leftPos = leftPos/1 + slideSpeed;
			if(leftPos>maxGalleryXPos){
				leftPos = maxGalleryXPos;
				slideSpeed = 0;				
			}
			if(leftPos<minGalleryXPos){
				leftPos = minGalleryXPos;
				slideSpeed=0;
			}
			
			imageGalleryObj.style.left = leftPos + 'px';
		}
		setTimeout('gallerySlide()',20);
		
	}
	
	function showImage()
	{
		if(activeImage){
			activeImage.style.filter = 'alpha(opacity=50)';	
			activeImage.style.opacity = 0.5;
		}	
		this.style.filter = 'alpha(opacity=100)';
		this.style.opacity = 1;	
		activeImage = this;	
	}
	
	function initSlideShow()
	{
		document.getElementById('arrow_left').onmousemove = startSlide;
		document.getElementById('arrow_left').onmouseout = releaseSlide;
		document.getElementById('arrow_right').onmousemove = startSlide;
		document.getElementById('arrow_right').onmouseout = releaseSlide;
		
		imageGalleryObj = document.getElementById('theImages');
		imageGalleryLeftPos = imageGalleryObj.offsetLeft;
		imageGalleryWidth = document.getElementById('galleryContainer').offsetWidth - 80;
		maxGalleryXPos = imageGalleryObj.offsetLeft; 
		minGalleryXPos = imageGalleryWidth - document.getElementById('slideEnd').offsetLeft;
		var slideshowImages = imageGalleryObj.getElementsByTagName('IMG');
		for(var no=0;no<slideshowImages.length;no++){
			slideshowImages[no].onmouseover = showImage;			
		}
		
		var divs = imageGalleryObj.getElementsByTagName('DIV');
		for(var no=0;no<divs.length;no++){
			if(divs[no].className=='imageCaption')imageGalleryCaptions[imageGalleryCaptions.length] = divs[no].innerHTML;
		}
		
		gallerySlide();
	}
	
	
	function initImage() {
	  imageId = 'previewImage';
	  image = document.getElementById(imageId);
	  setOpacity(image, 0);
	  image.style.visibility = 'visible';
	  fadeIn(imageId,0);
	}
	
	function setOpacity(obj, opacity) {
		  opacity = (opacity == 100)?99.999:opacity;
		  
		  // IE/Win
		  obj.style.filter = "alpha(opacity:"+opacity+")";
		  
		  // Safari<1.2, Konqueror
		  obj.style.KHTMLOpacity = opacity/100;
		  
		  // Older Mozilla and Firefox
		  obj.style.MozOpacity = opacity/100;
		  
		  // Safari 1.2, newer Firefox and Mozilla, CSS3
		  obj.style.opacity = opacity/100;
		}
	
	function fadeIn(objId,opacity) {
	  if (document.getElementById) {
		obj = document.getElementById(objId);
		if (opacity <= 100) {
		  setOpacity(obj, opacity);
		  opacity += 8;
		  window.setTimeout("fadeIn('"+objId+"',"+opacity+")", 60);
		}
	  }
	} 