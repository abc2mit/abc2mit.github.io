<?php
    #
    #   Code copyright 2005-2008 Michael Ho
    #   Algorithm copyright 2008 Jason Gu and Michael Ho
    #   Unauthorized reproduction of this code and algorithm are prohibited by law.
    #

    #require_once($oop . '/algorithm.php');
    require_once($oop . '/algorithm2.php');
    require_once($oop . '/functions.php');
    require_once($oop . '/location_functions.php');
    require_once($oop . '/map_functions.php');
    require_once($oop . '/stations.php');
    require_once($oop . '/display_classes.php');
    require_once($oop . '/display_functions.php');
    // global variables
    $default_zoom = 14;
    $start_zoom = 12;
    $location_marker = "mm_20_yellow.png";
    $station_marker_img = "mm_20_blue.png";
    $transfer_marker_img = "mm_20_orange.png";
    $start_marker_img = "mm_20_green.png";
    $end_marker_img = "mm_20_red.png";
    $WALKING_SPEED = 2 / 3600;
    
    $debug = false;
    $logging = false;
    
    $current_url = $_SERVER['PHP_SELF'];
    
    $slogan = getSlogan();
    
    $linksTable = new Table("linkstable");
    $homeCell = new Cell("link", new Link("/", "home"));
    $aboutCell = new Cell("link", new Link("/about.php", "about"));
    $linksRow = new Row();
    $linksRow->addCell($homeCell);
    $linksRow->addCell($aboutCell);
    $linksTable->addRow($linksRow);
    $linksDiv = new Div("about", "noselect", null, $linksTable);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
  <head>
    <meta name="Description" content="<?php echo $description; ?>" />
    <meta name="Keywords" content="<?php echo $keywords; ?>" />
    <meta name="save" content="history" />
    <link rel="stylesheet" type="text/css" media="screen" href="/css/map.css" />
    <!--<script src="http://maps.google.com/maps?file=api&v=2&key=ABQIAAAAXJk3_ifz0EKtxl6PfNqOdRR2623heX1dGsZVYrnM2m6YdB3UOBSa0OfY2TkvTRfMAD_61x1skcuSag" type="text/javascript"></script>-->
    <script src="http://maps.google.com/maps?file=api&v=2&key=ABQIAAAAXJk3_ifz0EKtxl6PfNqOdRT4PIyuWKk6FxGrDqCErMTujTxAwRSe-8nmtDZA5hi3dMUJ8Xlk_fA9eg" type="text/javascript"></script>
    <script type="text/javascript" src="/js/map.js"></script>
    <script type="text/javascript" src="/js/map_functions.js"></script>
    <script type="text/javascript" src="/js/cookie_functions.js"></script>
    <title><?php echo $head_title . $slogan; ?></title>
  </head>
  <body onresize="mapResize()">
<?php
    // set the type
    $type = $_GET['type'];
    if ($debug) {
        echo "type = " . $type . "<br/>";
    }
    
    // this file does the search functions
    include($oop . '/search.php');
    if (count($locations) == 1) {
        if ($locations[0]->name == null && $_GET['n'] != null) {
            $locations[0]->name = fixSearchString($_GET['n']);
        }
    }
    //$affiliateDiv = new Div(null, "noselect", "font-size:7pt;color:#CCCCCC;", "(not affiliated with Google)");
    //$poweredByDiv->addDataElement($affiliateDiv);
    echo $linksDiv->toString();
?>
<table id="header">
<tr>
<?php
    $logoDiv = new Div(null, "logo", new Link($link, new Image("logo", "images/$logo", "Go To $link", "55", "150")));
    $headerLogoTD = new Cell("header_logo", null, null, $logoDiv);
    $sloganDiv = new Div("slogan", null, $slogan);
    $headerLogoTD->addData($sloganDiv);
    echo $headerLogoTD->toString();
?>
    <td width="100%">
        <div id="printheader"></div>
        <table class="form" width="100%">
            <tr>
                <td class="menu">
                <?php
                    if ($type == 's2s') {
                        $s2s = "selected";
                    }
                    else if ($type == 'location') {
                        $station = "selected";
                    }
                    else /*if ($type == 'dir')*/ {
                        $dir = "selected";
                    }
                    echo "<a class=\"$dir\" href=\"javascript:void(0);\" id=\"directions\" onclick=\"return _form('directions', true)\" style=\"text-decoration: none\">Directions</a>&nbsp;&nbsp;&nbsp;\n";
                    echo "<a class=\"$s2s\" href=\"javascript:void(0);\" id=\"local\" onclick=\"return _form('local', true)\" style=\"text-decoration: none\">Station To Station</a>&nbsp;&nbsp;&nbsp;\n";
                    echo "<a class=\"$station\" href=\"javascript:void(0);\" id=\"maps\" onclick=\"return _form('maps', true)\" style=\"text-decoration: none\">Address</a>\n";
                if ($showmaplink) {
                    echo "&nbsp;&nbsp;&nbsp;<a href=\"subwaymap.pdf\">Map</a>";
                }
                ?>
                </td>
                <!--<td align="right" rowspan="2" style="padding-right:5px;width:250px;vertical-align:top;">
<?php
    //$poweredByDiv = new Div("poweredby", "noselect", "vertical-align:top;float:right;text-align:right;font-size:10pt;z-index:-1;", "uses ");
    //$poweredByDiv->addDataElement(new Link("http://maps.google.com", "Google Maps"));
    //$affiliateDiv = new Div(null, "noselect", "font-size:7pt;color:#CCCCCC;", "(not affiliated with Google)");
    //$poweredByDiv->addDataElement($affiliateDiv);
    //echo $poweredByDiv->toString();
?>
                </td>-->
            </tr>
            <tr><td style="vertical-align: top;">
                    <?php
                        include($oop . '/forms.php');
                    ?>
            </td></tr>
        </table>
    </td></tr>
</table> <!-- end header table -->
<!--<table id="headertitle">
    <tr><td class="title"><?php #echo $title; ?></td><td id="toggle">&nbsp;</td></tr>
</table>-->
<table id="maptable" style="font-size: 12px;">
    <tr>
    <td style="vertical-align: top;width: 250px">
    <div id="info">
        <?php
            include($oop . '/info.php');
        ?>
    </div>
    </td>
    <td id="mapcell" style="height: 400px"><div id="map"></div></td>
    </tr>
</table>
<!--
    this section manipulates the map to show the path and markers
-->
<script type="text/javascript">
//<![CDATA[	

var mapType = readCookie('mapType');
//document.write("mapType = " + typeof (mapType) + "<br/>");
//document.write("mapType 2 = " + mapType + "<br/>");
var map = new GMap2(document.getElementById("map"));
map.addControl(new GLargeMapControl());
map.addControl(new GMapTypeControl());
<?php
    echo "map.setCenter(new GLatLng($center_lat,$center_lng), $start_zoom);\n";
?>
//document.write("map = " + map.getCurrentMapType().getName());
if (mapType != null) {
    var done = setCurrentMapType(map, mapType);
}
//document.write("map = " + map.getCurrentMapType().getName());
setMapSize(map);
<?php
    include($oop . '/map.php');
?>
    //]]>
</script>
<script type="text/javascript">
// Google Analytics
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("UA-3853080-1");
pageTracker._initData();
pageTracker._trackPageview();
</script>
</body>
</html>