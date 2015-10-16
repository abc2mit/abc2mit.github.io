<?php
    #
    #   Code copyright 2005-2008 Michael Ho
    #   Algorithm copyright 2008 Jason Gu and Michael Ho
    #   Unauthorized reproduction of this code and algorithm are prohibited by law.
    #

    $oop = "subwayoop";
    $xml = "subwayxml";
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
    $center_lng = -95.677068000000006;
    $center_lat = 37.0625;
    $default_zoom = 6;
    $start_zoom = 4;
    $location_marker = "mm_20_yellow.png";
    $coming_soon_marker_img = "mm_20_blue.png";
    $transfer_marker_img = "mm_20_orange.png";
    $start_marker_img = "mm_20_green.png";
    $end_marker_img = "mm_20_red.png";
    
    $title = "Directions through Public Transporation Systems";
    $head_title = "transitcompass.com // ";
    $link = "http://transitcompass.com";
    $logo = "nysubway.png";
    
    $debug = false;
    $logging = false;
    
    include('../subwaydb/db.php');
    // connect to database
    connectDB("digressi_nyc");
    //database is an array of files
    //include($xml . '/xmldb.php');
    // markers must be initialised here.
    //$markers = array();
    //$file = "nyc_time.xml";
    //init_db();
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
    <meta name="Description" content="Directions via Public Transportation Systems using Google Maps." />
    <meta name="Keywords" content="geocode, subway, google maps, latitude, longitude, new york, new york city, nyc, boston, T, MTA, MBTA, directions, map" />
    <meta name="save" content="history" />
    <link rel="stylesheet" type="text/css" media="screen" href="/css/map.css" />
    <script type="text/javascript" src="/js/map.js"></script>
    <title><?php echo $head_title . $slogan; ?></title>
  </head>
  <body onresize="mapResize()">
<?php
    echo $linksDiv->toString();
?>
<table id="header"><tr>
<?php
    $logo_img = new Image("logo", null, "images/logo.png", "transitcompass", null, null); 
    $logo_link = new Link($link, $logo_img);
    $logo_data = new Cell("logo", null, "color:#008000", $logo_link);
    echo $logo_data->toString();
?>
</tr>
</table> <!-- end header table -->
<!--<table id="headertitle">
    <tr><td class="title"><?php #echo $title; ?></td><td id="toggle">&nbsp;</td></tr>
</table>-->
<table id="maptable" style="font-size: 12px;">
    <tr>
    <td style="vertical-align: top;width: 300px">
<?php
    $cities_link = array();
    $cities_link[] = "transitcompass.com/nyc";
    $cities_link[] = "transitcompass.com/boston";

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
    </tr>
</table>
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
