<html>
<head>
    <title>mike and jess : our pictures</title>
    <link rel="stylesheet" href="gallery/css/jd.gallery.css" type="text/css" media="screen" charset="utf-8" />
    <link rel="stylesheet" href="gallery/css/layout.css" type="text/css" media="screen" charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="css/main.css" />
    <script src="gallery/scripts/mootools.v1.11.js" type="text/javascript"></script>
    <script src="gallery/scripts/jd.gallery.js" type="text/javascript"></script>
    <script src="gallery/scripts/jd.gallery.transitions.js" type="text/javascript"></script>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<div id="main">
<?php
include('verse.php');
?>
<div id="background">
<div id="banner">
    <b class="b1t"></b><b class="b2t"></b><b class="b3t"></b><b class="b4t"></b>
    <div class="contentt">
<?php
include('menu/menu.php');
?>
    </div>
</div>
<div id="text">
<script type="text/javascript">
	function startGallery() {
		var myGallery = new gallery($('myGallery'), {
			timed: true
		});
	}
	window.onDomReady(startGallery);
</script>
<div class="contentf">
<div id="myGallery">
    <div class="imageElement">
        <h3>Proposal</h3>
        <p>Down on my knees!</p>
        <a href="gallery/IMG_6937E.JPG" title="open image" class="open"></a>
        <img src="gallery/proposal.jpg" class="full" />
        <img src="gallery/proposal-1.jpg" class="thumbnail" />
    </div>
    <div class="imageElement">
        <h3>Love</h3>
        <p>It must be love!</p>
        <a href="gallery/DSC_2903E.jpg" title="open image" class="open"></a>
        <img src="gallery/love.jpg" class="full" />
        <img src="gallery/love-1.jpg" class="thumbnail" />
    </div>
    <div class="imageElement">
        <h3>Us</h3>
        <p>We're so happy and loving every moment!</p>
        <a href="gallery/DSC_2912E.jpg" title="open image" class="open"></a>
        <img src="gallery/us.jpg" class="full" />
        <img src="gallery/us-1.jpg" class="thumbnail" />
    </div>
    <div class="imageElement">
        <h3>Ring</h3>
        <p>The ring that she loves and wears proudly.</p>
        <a href="gallery/IMG_9690Artsy.JPG" title="open image" class="open"></a>
        <img src="gallery/ring.jpg" class="full" />
        <img src="gallery/ring-1.jpg" class="thumbnail" />
    </div>
</div> <!-- myGallery -->
<div id="copyright">
&copy; 2008 Michael Ho and Jessica Fung
</div>
</div> <!-- contentf -->
<b class="b4f"></b><b class="b3f"></b><b class="b2f"></b><b class="b1f"></b>
</div> <!-- text -->
</div> <!-- background -->
</div> <!-- main -->
</body>
</html>
