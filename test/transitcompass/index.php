<?php
    #
    #   Code copyright 2005-2008 Michael Ho
    #   Algorithm copyright 2008 Jason Gu and Michael Ho
    #   Unauthorized reproduction of this code and algorithm are prohibited by law.
    #

    $oop = "../subwayoop";
    $xml = "../subwayxml";
    #require_once($oop . '/algorithm.php');
    require_once($oop . '/algorithm2.php');
    require_once($oop . '/classes.php');
    require_once($oop . '/functions.php');
    require_once($oop . '/location_functions.php');
    require_once($oop . '/map_functions.php');
    require_once($oop . '/stations.php');
    require_once($oop . '/display_classes.php');
    require_once($oop . '/display_functions.php');
    // global variables
    $website = "http://mta.info/nyct/service/";
    $center_lng = -95.677068000000006;
    $center_lat = 37.0625;
    $default_zoom = 14;
    $start_zoom = 4;
    $city = "New York";
    $state = "NY";
    $location_marker = "mm_20_yellow.png";
    $coming_soon_marker_img = "mm_20_blue.png";
    $transfer_marker_img = "mm_20_orange.png";
    $start_marker_img = "mm_20_green.png";
    $end_marker_img = "mm_20_red.png";
    $WALKING_SPEED = 2 / 3600;
    
    $location_example = "213 W. 32nd St.";
    $start_station_example = "14th St-Union Square";
    $end_station_example = "Bowling Green";
    $title = "Directions through Public Transporation Systems";
    $head_title = "transitcompass.com // ";
    $link = "http://transitcompass.com";
    $logo = "nysubway.png";
    
    $debug = false;
    $logging = false;
    
    include('../subwaydb/db.php');
    // connect to database
    connectDB("michaeq6_nyc");
    //database is an array of files
    //include($xml . '/xmldb.php');
    // markers must be initialised here.
    //$markers = array();
    //$file = "nyc_time.xml";
    //init_db();
    $current_url = $_SERVER['PHP_SELF'];
    
    $slogan = getSlogan();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
  <head>
    <meta name="Description" content="Directions via New York City (NYC) Subway (MTA) using Google Maps." />
    <meta name="Keywords" content="geocode, subway, google maps, latitude, longitude, new york, new york city, nyc, directions, mta, map" />
    <meta name="save" content="history" />
    <link rel="stylesheet" type="text/css" media="screen" href="../subwayoop/map.css" />
    <script src="http://maps.google.com/maps?file=api&v=2&key=ABQIAAAAXJk3_ifz0EKtxl6PfNqOdRR2623heX1dGsZVYrnM2m6YdB3UOBSa0OfY2TkvTRfMAD_61x1skcuSag" type="text/javascript"></script>
    <script type="text/javascript" src="js/map.js"></script>
    <script type="text/javascript" src="<?php echo $oop; ?>/map_functions.js"></script>
    <script type="text/javascript" src="<?php echo $oop; ?>/cookie_functions.js"></script>
    <title><?php echo $head_title . $slogan; ?></title>
  </head>
  <body onLoad="setMapSize()" onresize="mapResize()">
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
?>
<table id="header"><tr>
<?php
    $logo_img = new Image("logo", null, "images/logo.png", "transitcompass", null, null); 
    $logo_link = new Link($link, $logo_img);
    $logo_data = new Cell("logo", null, "color:#008000", $logo_link);
    echo $logo_data->toString();
?>
<!--<td>transit<b>compass</b> <i style="font-size:10px;position:relative;left:-110px;top:-20px;width:100px">NYC Edition</i></td>-->
    <td width="100%">
        <table class="form" width="100%">
            <tr>
                <td align="right" rowspan="2" valign="top" style="padding-right:5px;width:250px">
                    <!-- credits -->
                    <div class="noselect" style="vertical-align:top;float:right;text-align:right;font-size:10pt;" id="poweredby">
                    uses <!--<a href="http://www.geocoder.us" style="color:#0000cc">geocoder</a> and --><a href="http://maps.google.com" style="color:#0000cc">Google Maps</a>
                        <div class="noselect" style="font-size:7pt;color:#cccccc;">
                            (not affiliated with <!--geocoder or -->Google)
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </td></tr>
</table> <!-- end header table -->
<!--<table id="headertitle">
    <tr><td class="title"><?php #echo $title; ?></td><td id="toggle">&nbsp;</td></tr>
</table>-->
<table id="maptable" style="font-size: 12px;">
    <tr>
    <td style="vertical-align: top;">
<?php
    $cities_link = array();
    $cities_link[] = "www.nysubway.info";
    $cities_link[] = "www.bostonsubway.info";

    $cities = array();
    $nyc = new Location2("NYC", 40.714509999999997, -74.007140000000007, "New York City", null, null, "New York City", "NY", null, "USA", null);
    $cities[] = $nyc;
    $boston = new Location2("Boston", 42.358640000000001, -71.056659999999994, "Boston", null, null, "Boston", "MA", null, "USA", null);
    $cities[] = $boston;

    $cities_div = new Div("cities", "<b>Available Cities</b>");
    // build the table
    $cities_table = new Table(null, "info_table");
    $cities_div->addDataElement($cities_table);
    // create the header
    $header_row = new Row(null, "header_row");
    $cities_table->addRow($header_row);
    $header_row->addCell(new Cell("header_cell", "<b>Show</b>"));
    $header_row->addCell(new Cell("header_cell", "<b>City</b>"));
    $header_row->addCell(new Cell("header_cell", "<b>Site Link</b>"));
    $highlight = false;
    for ($i = 0; $i < count($cities); $i++) {
        if ($highlight) {
            $class_name = "stationRowHighlight";
            $highlight = false;
        }
        else {
            $class_name = "stationRow";
            $highlight = true;
        }
        $cities_table->addRow(createCityTableRow($cities[$i], $location_marker, $class_name, $cities_link[$i]));
    }
    echo $cities_div->toString();
    
?>
    </td>
    <td id="mapcell" style="width:80%;height: 400px">
    <!--<td style="vertical-align: top;float: right;"><div id="map" style="width: 640px; height: 480px">--><div id="map"></div></td>
    </tr>
</table>
<!--<div id="other_sites" style="margin:20px 0 0 0">
If you are in Boston, MA, you can use the same great features for the MBTA! Visit the sister site: <a href="http://www.bostonsubway.info">bostonsubway.info</a>
</div>-->

<div class="noselect" style="font-size:8pt;margin:20px 0 20px 0;text-align:center;">
    <a style="color:#0000cc;margin-right:5px;" href="faq_maps.html">About / Feedback</a> | <a style="color:#0000cc;margin-left:5px;" href="known_issues.html">Known Issues</a>
</div>
<div class="noselect" style="font-size:8pt;margin:20px 0 20px 0;text-align:center;">
&copy; 2005-2008 Michael Ho. All code is proprietary property.<br/>Hacking, discovering or reproducing the algorithm or anything involved thereof will be responded to with a lawsuit. Besides, this service is free. Why would you want to?
</div>
<!--
    this section manipulates the map to show the path and markers
-->
<script type="text/javascript">
//<![CDATA[

function f_clientWidth() {
	return f_filterResults (
		window.innerWidth ? window.innerWidth : 0,
		document.documentElement ? document.documentElement.clientWidth : 0,
		document.body ? document.body.clientWidth : 0
	);
}
function f_clientHeight() {
	return f_filterResults (
		window.innerHeight ? window.innerHeight : 0,
		document.documentElement ? document.documentElement.clientHeight : 0,
		document.body ? document.body.clientHeight : 0
	);
}
function f_scrollLeft() {
	return f_filterResults (
		window.pageXOffset ? window.pageXOffset : 0,
		document.documentElement ? document.documentElement.scrollLeft : 0,
		document.body ? document.body.scrollLeft : 0
	);
}
function f_scrollTop() {
	return f_filterResults (
		window.pageYOffset ? window.pageYOffset : 0,
		document.documentElement ? document.documentElement.scrollTop : 0,
		document.body ? document.body.scrollTop : 0
	);
}
function f_filterResults(n_win, n_docel, n_body) {
	var n_result = n_win ? n_win : 0;
	if (n_docel && (!n_result || (n_result > n_docel)))
		n_result = n_docel;
	return n_body && (!n_result || (n_result > n_body)) ? n_body : n_result;
}

function getWindowHeight() {
    if (window.self && self.innerHeight) {
        return self.innerHeight;
    }
    if (document.documentElement && document.documentElement.clientHeight) {
        return document.documentElement.clientHeight;
    }
    return 0;
}

function setMapSize() {
    mapResize();
    var mapElement = document.getElementById("map");
    mapElement.checkResize();
    //document.write("//" + f_clientWidth());
    //var width = f_clientWidth() - 300;
    //if (width > 0) {
    //    mapElement.style.width= width + 'px';
    //}
}

function mapResize() {
    var mapCell = document.getElementById("map");
    var height = getWindowHeight();
    mapCell.style.height = height*0.7 + 'px';
}

var mapType = readCookie('mapType');
var map = new GMap2(document.getElementById("map"));
map.addControl(new GLargeMapControl());
map.addControl(new GMapTypeControl());
<?php
    echo "map.setCenter(new GLatLng($center_lat,$center_lng), $start_zoom);\n";
?>
if (mapType != null) {
    var done = setCurrentMapType(map, mapType);
}
	
<?php
    for ($i = 0; $i < count($cities); $i++) {
        markCity($cities[$i], $location_marker, $cities[$i]->id, $cities_link[$i]);
    }
?>
    //]]>
    </script>
</body>
</html>