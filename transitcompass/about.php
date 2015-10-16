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
    connectDB("digressi_nyc");
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
    <meta name="Description" content="Directions via Public Transportation Systems (Subway) using Google Maps." />
    <meta name="Keywords" content="geocode, subway, google maps, latitude, longitude, new york, new york city, nyc, directions, mta, map, boston, T" />
    <meta name="save" content="history" />
    <link rel="stylesheet" type="text/css" media="screen" href="/css/map.css" />
    <script type="text/javascript" src="/js/map.js"></script>
    <script type="text/javascript" src="/js/map_functions.js"></script>
    <script type="text/javascript" src="/js/cookie_functions.js"></script>
    <title><?php echo $head_title . $slogan; ?></title>
  </head>
  <body>
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
<?php
    $poweredByDiv = new Div("about", "noselect", null, "uses ");
    $poweredByDiv->addDataElement(new Link("http://maps.google.com", "Google Maps"));
    $affiliateDiv = new Div(null, "noselect", "font-size:7pt;color:#CCCCCC;", "(not affiliated with Google)");
    $poweredByDiv->addDataElement($affiliateDiv);
    echo $poweredByDiv->toString();
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
<?php
    //$poweredByDiv = new Div("poweredby", "noselect", "vertical-align:top;float:right;text-align:right;font-size:10pt;z-index:-1;", "uses ");
    //$poweredByDiv->addDataElement(new Link("http://maps.google.com", "Google Maps"));
    //$affiliateDiv = new Div(null, "noselect", "font-size:7pt;color:#CCCCCC;", "(not affiliated with Google)");
    //$poweredByDiv->addDataElement($affiliateDiv);
    //echo $poweredByDiv->toString();
?>
                </td>
            </tr>
        </table>
    </td></tr>
</table> <!-- end header table -->
<!--<table id="headertitle">
    <tr><td class="title"><?php #echo $title; ?></td><td id="toggle">&nbsp;</td></tr>
</table>-->
<table id="maptable" style="font-size: 12px;"><tr><td>
<div id="questions">
    <dl>
        <dt>Where can I give feedback?</dt>
        <dd>You can email us feedback/comments/questions/requests here: <a href="mailto:subwaymaps@gmail.com?subject=feedback+for+transitcompass.com">subwaymaps@gmail.com</a></dd>
        <dt>Where can I see the latest changes?</dt>
        <dd>Glad you want to keep up-to-date! You can see the changelist here: <a href="/changelist.php">www.transitcompass.com/changelist.php</a></dd>
        <dt>What did you use to make this site?</dt>
        <dd>All the coding was done by hand using SubEthaEdit on a 12" Apple Powerbook G4 and on a Mac Pro. Technologies include PHP, CSS, and Javascript. Mike found it easier to develop on his Mac, mainly because that meant that he could program/make changes anywhere. For example, Mike's currently writing this FAQ while lounging on the grass in Central Park [**EDIT** back in 2005! I now do it from the comfort of the warm California sun].</dd>
        <dt>What browsers are supported?</dt>
        <dd>All browsers should work. So far, we've tested:
        <ul>
            <li>Firefox<br/>
                <ul>
                    <li>Firefox 2.0.0.12</li>
                    <li>Firefox 2.0.0.11</li>
                    <li>Firefox 1.0.6</li>
                </ul>
            </li>
            <li>Internet Explorer<br/>
                <ul>
                    <li>Internet Explorer 7.0.5730.13</li>
                    <li>Internet Explorer 6.0.2900.2180*</li>
                </ul>
            </li>
            <li>Opera<br/>
                <ul>
                    <li>Opera 8.0</li>
                </ul>
            </li>
            <li>Safari<br/>
                <ul>
                    <li>Safari 3.0.4 (523.15) on Windows</li>
                    <li>Safari 2.0 (412.2)</li>
                </ul>
            </li>
        </ul>
        * known issue where PNG files aren't fully transparent as they should be.
        </dd>
        <dt>What gave the inspiration for <b>transitcompass.com</b>?</dt>
        <dd>Two pieces, actually. The initial inspiration came from the frustration of looking at the subway map and still not really knowing the most efficient way to get there. Google Maps and MapQuest are great at plotting out driving directions, but that fails when you don't have a car. There are essentially two modes of transportation in NYC, as most here would know: subway and taxi. Obviously, taxis know where they're going (most of the time). And yes, there is a bus system too, but Mike never used it. Secondly, the idea for using Google Maps came from <a href="http://www.housingmaps.com">housingmaps.com</a>, a site that merges Google Maps with Craigslist. Then Mike realised that there was a great potential in Google Maps' ability to be used as an API. After doing some hunting for several months on a good, clean way (most tutorials and such available on the web are hacks and can be easily broken by Google's updates), Google was gracious enough to offer their own API.<br/><br/>This started the creation of <b>nysubway.info</b> and <b>bostonsubway.info</b>. At some point, it was decided that creating a bunch of disjointed sites wasn't exactly the best idea. Trying to find a domain name isn't easy either since so many are taken. But given the concept and intention of the project, <b>transitcompass</b> seems like a good fit.</dd>
    </dl>
    </div>
    </td>
    </tr>
</table>
<div class="noselect" style="font-size:8pt;margin:20px 0 20px 0;text-align:center;">
&copy; 2005-2008 Michael Ho. All code is proprietary property.<br/>Hacking, discovering or reproducing the algorithm or anything involved thereof will be responded to with a lawsuit. Besides, this service is free. Why would you want to?
</div>
</body>
</html>
