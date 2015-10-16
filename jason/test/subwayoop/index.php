<?php
    #
    #   Copyright 2005-2008 Michael Ho
    #   Algorithm Copyright 2008 Jason Gu and Michael Ho
    #

    $oop = "../subwayoop";
    #require_once($oop . '/algorithm.php');
    require_once($oop . '/algorithm2.php');
    require_once($oop . '/classes.php');
    require_once($oop . '/functions.php');
    require_once($oop . '/location_functions.php');
    require_once($oop . '/map.php');
    require_once($oop . '/stations.php');
    // global variables
    $website = "http://mta.info/nyct/service/";
    $center_lng = -73.980584;
    $center_lat = 40.751532;
    $default_zoom = 14;
    $start_zoom = 12;
    $city = "New York";
    $state = "NY";
    $location_marker = "mm_20_yellow.png";
    $station_marker_img = "mm_20_blue.png";
    $transfer_marker_img = "mm_20_orange.png";
    $start_marker_img = "mm_20_green.png";
    $end_marker_img = "mm_20_red.png";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
  <head>
    <meta name="Description" content="Directions via New York City (NYC) Subway (MTA) using Google Maps." />
    <meta name="Keywords" content="geocode, subway, google maps, latitude, longitude, new york, new york city, nyc, directions, mta, map" />
    <meta name="save" content="history" />
    <link rel="stylesheet" type="text/css" media="screen" href="css/map.css" />
    <script src="http://maps.google.com/maps?file=api&v=2&key=ABQIAAAAXJk3_ifz0EKtxl6PfNqOdRR2623heX1dGsZVYrnM2m6YdB3UOBSa0OfY2TkvTRfMAD_61x1skcuSag" type="text/javascript"></script>
    <script type="text/javascript" src="js/map.js"> </script>
    <title>nysubway.info // from a to b. new york style.</title>
  </head>
  <body>
  <?php
    //database is an array of files
    include($oop . '/xmldb.php');
    // markers must be initialised here.
    $markers = array();
    $file = "nyc_time.xml";
    init_db();
    $current_url = $_SERVER['PHP_SELF'];
    #echo $current_url . "<br/>";
    $locations = array();
    $current_element="";
    
    if ($_GET['type'] == 'location') {
        $loc = $_GET['l'];
        // check for new york and ny
        $loc = checkString($loc, $city);
        $loc = checkString($loc, $state);
        $locations = getLocations($loc);
        if (count($locations) == 1) {
            // grab stations
            $stations = findStations($locations[0]->point);
        }
    }
    else if ($_GET['type'] == 's2s') {
        // get starting point
        $start_marker = $markers[$_GET['start']];
        // get ending point
        $end_marker = $markers[$_GET['end']];
        // find the path
        $path = findS2SPath($start_marker, $end_marker);
    }
    else if ($_GET['type'] == 'dir') {
        // get locations for end address
        $saddr = $_GET['saddr'];
        // check for new york and ny
        $saddr = checkString($saddr, $city);
        $saddr = checkString($saddr, $state);
        $starts = getLocations($saddr);
        
        // get locations for start address
        $daddr = $_GET['daddr'];
        // check for new york and ny
        $daddr = checkString($daddr, $city);
        $daddr = checkString($daddr, $state);
        $ends = getLocations($daddr);
        
        // only one of each, so we can calculate the path
        if ((count($starts) == 1) && (count($ends) == 1)) {
            $start_location = $starts[0];
            $end_location = $ends[0];
            
            #echo "start_location = " . $start_location . "<br/>";
            #echo "end_location = " . $end_location . "<br/>";
            if ($start_location != false && $end_location != false) {
                // find three closest starting stations
                $start_stations = findNearestStations2($start_location);
                // find three closest ending stations
                $end_stations = findNearestStations2($end_location);
                $path = findDirPath($start_location,$end_location, $start_stations, $end_stations);
            }
        }
    }
?>
<table id="header">
<tbody>
<tr>
	<td class="logo">
		<a href="http://www.nysubway.info">
		<img id="logo" src="images/nysubway.png" alt="Go To nysubway.info" height="55" width="150">
		</a>
	</td>
    <td width="100%">
    <div id="printheader"></div>
    <table class="form" width="100%">
    <tbody>
        <tr>
            <td class="menu">
<?php
    if ($_GET['type'] == 's2s') {
        $s2s = "selected";
    }
    else if ($_GET['type'] == 'dir') {
        $dir = "selected";
    }
    else {
        $station = "selected";
    }
	echo "<a class=\"$station\" href=\"javascript:void(0);\" id=\"maps\" onclick=\"return _form('maps', true)\" style=\"text-decoration: none\">Address</a>&nbsp;&nbsp;&nbsp;\n";
	echo "<a class=\"$s2s\" href=\"javascript:void(0);\" id=\"local\" onclick=\"return _form('local', true)\" style=\"text-decoration: none\">Station To Station</a>&nbsp;&nbsp;&nbsp;\n";
	echo "<a class=\"$dir\" href=\"javascript:void(0);\" id=\"directions\" onclick=\"return _form('directions', true)\" style=\"text-decoration: none\">Directions</a>\n";
?>
            &nbsp;&nbsp;&nbsp;<a href="subwaymap.pdf">Map</a>
            </td>
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
        <tr>
            <td style="vertical-align: top;">
                <table>
                    <tbody>
                    <tr>
                        <td>
                        <?php
                            echo "<form id=\"formq\" action=\"$current_url\" method=\"get\">\n";
                            if ($_GET['type'] == 'dir' || $_GET['type'] == 's2s') {
                                echo "<table id=\"maps_form\" style=\"display: none;\">\n";
                            }
                            else {
                                echo "<table id=\"maps_form\">\n";
                            }
                        ?>
                        <tbody>
                        <tr>
                            <td>
                            <?php
                              if (isset($_GET['l'])) {
                                  $input = str_replace("%20"," ", $_GET['l']);
                                  echo "<input tabindex=\"0\" name=\"l\" id=\"l\" size=\"50\" value=\"$input\" type=\"text\" />\n";
                              }
                              else {
                                  echo "<input tabindex=\"0\" name=\"l\" id=\"l\" size=\"50\" value=\"\" type=\"text\" />\n";
                              }
                            ?>
                            </td>
                            <td rowspan="2" class="submit">
                              &nbsp;<!-- <input tabindex="5" id="submitq" value="Search" type="submit" /> -->
                              <input tabindex="5" id="submitq" type="image" value="Search" alt="Search" src="images/searchbutton.png" />
                            </td>
                        </tr>
                        <tr>
                            <td class="boxlabel">
                                Location Address&nbsp;&nbsp;<span class="example">e.g., 213 W. 32nd St.</span>
                            </td>
                        </tr>
                        </tbody>
                        </table>
                        <input type="hidden" name="type" value="location" />
                        </form>
                        <!-- this is the station to station form -->
                        <?php
                            echo "<form id=\"forml\" action=\"$current_url\" method=\"get\">\n";
                            if ($_GET['type'] == 's2s') {
                                echo "<table id=\"local_form\">\n";
                            }
                            else {
                                echo "<table id=\"local_form\" style=\"display: none;\">\n";
                            }
                        ?>
                        <tbody>
                        <tr>
                            <td style="white-space: nowrap;">
                                <SELECT NAME="start">;
                                <OPTION VALUE="">Pick a Starting Point...</OPTION>;
                                <?php
                                    listStations($_GET['start'], $markers);
                                ?>
                                </SELECT>
                            </td>
                            <td>
                                <SELECT NAME="end">
                                <OPTION VALUE="">Pick a Ending Point...</OPTION>
                                <?php
                                    listStations($_GET['end'], $markers);
                                ?>
                                </SELECT>
                            </td>
                            <td rowspan="2" class="submit">
                                &nbsp;<!-- <input tabindex="5" id="submitl" value="Search" type="submit"> -->
                              <input tabindex="5" id="submitl" type="image" value="Search" alt="Search" src="images/searchbutton.png" />
                            </td>
                        </tr>
                        <tr>
                            <td class="boxlabel">
                                Start&nbsp;&nbsp;<span class="example">e.g., 14th St-Union Square</span>
                            </td>
                            <td class="boxlabel">
                                End&nbsp;&nbsp;<span class="example">e.g., Bowling Green</span>
                            </td>
                        </tr>
                        </tbody>
                        </table>
                        <input type="hidden" name="type" value="s2s" />
                    </form>
                    <!-- this is the directions form -->
                    <?php
                        echo "<form id=\"formd\" action=\"$current_url\" method=\"get\">\n";
                        if ($_GET['type'] == 'dir') {
                        echo "<table id=\"directions_form\">\n";
                        }
                        else {
                            echo "<table id=\"directions_form\" style=\"display: none;\">\n";
                        }
                    ?>
                        <tbody>
                        <tr>
                            <td>
                            <?php
                            if (isset($_GET['saddr'])) {
                                echo "<input tabindex=\"3\" name=\"saddr\" id=\"saddr\" size=\"36\" value=\"$saddr\" type=\"text\" />\n";
                            }
                            else {
                                echo "<input tabindex=\"3\" name=\"saddr\" id=\"saddr\" size=\"36\" value=\"\" type=\"text\" />\n";
                            }
                            ?>
                            </td>
                            <td class="reverse">
                            <a href="" onclick="return _fd()">
                            	<img src="images/ddirflip.gif" alt="Switch start and end address" title="Switch start and end address" height="14" width="10">
                            </a>
                            </td>
                            <td>
                            <?php
                            if (isset($_GET['daddr'])) {
                                echo "<input tabindex=\"3\" name=\"daddr\" id=\"daddr\" size=\"36\" value=\"$daddr\" type=\"text\" />\n";
                            }
                            else {
                                echo "<input tabindex=\"3\" name=\"daddr\" id=\"daddr\" size=\"36\" value=\"\" type=\"text\" />\n";
                            }
                            ?>
                            </td>
                            <td rowspan="3" class="submit">
                                &nbsp;<!-- <input tabindex="5" id="submitd" value= "Search" type="submit"> -->      
                              <input tabindex="5" id="submitd" type="image" value="Search" alt="Search" src="images/searchbutton.png" />
                            </td>
                        </tr>
                        <tr>
                        <td class="boxlabel">Start address</td>
                        <td></td>
                        <td class="boxlabel">End address</td>
                        </tr>
                        </tbody>
                        </table>
                        <input type="hidden" name="type" value="dir" />
                    </form>
                </td>
                <!-- <td class="help"><div><a href="faq_maps.html">Help</a></div></td> -->
            </tr>
        </tbody>
        </table>
    </td>
    </tr>
    </tbody>
    </table>
    </td>
</tr>
</tbody>
</table>
<table id="headertitle">
    <tbody>
        <tr>
        <td class="title">New York City Subway | Metropolitan Transportation Authority</td>
        <td id="toggle">&nbsp;</td>
        </tr>
    </tbody>
</table>
<table style="font-size: 12px;">
<tbody>
<tr>
<td style="vertical-align: top;">
    <div id="map" style="width: 640px; height: 480px"></div>
</td>
<td style="vertical-align: top;">
<div>
<?php
    if($_GET['type'] == 's2s') {
        echo "Start Marker:<br/>\n";
        printMarker($start_marker, $start_marker_img);
        echo "<br/>\n";
        echo "End Marker:<br/>\n";
        printMarker($end_marker, $end_marker_img);
        echo "<br/><br/>\n";
        $cpath = $path;
        if ($path != "walk" && $path != "same") {
            $cpath = $path->path;
        }
        echo $cpath . "<br/>";
        if ($cpath == "walk") {
            echo "You don't need to take the subway. Why don't you walk instead?<br/>";
        }
        else if ($cpath == "same") {
            echo "You're just testing the system, aren't you? Go do something more interesting, like telling your friends about this great site.<br/>";
        }
        else {
            if (count($path->path) > 1) {
                #echo "s2s consolidating<br/>";
                $cpath = consolidatePath($path, $_GET['type']);
                // remove the last path because we're not walking
                //array_pop($cpath);
            }
            printPath($cpath);
        }
    }
    else if ($_GET['type'] == 'location') {
        if (count($locations) > 1) {
            echo "Multiple Locations Found:<br/><br/>\n";
            for ($i = 0; $i < count($locations); $i++) {
                $loc = $locations[$i];
                $num = $i + 1;
                echo "<form id=\"form_pick\" action=\"$current_url\" method=\"get\">\n";
                echo $loc . "\n";
                echo "<input type=\"hidden\" name=\"l\" value=\"$loc\" />\n";
                //correctOutput($_GET['l'], $loc, $num);
                echo "<input type=\"hidden\" name=\"type\" value=\"location\" />\n";
                echo "<input type=\"image\" name=\"submit\" src=\"images/usebutton.png\">\n";
                echo "</form><br/>\n";
            }
        }
        else if (count($locations) == 1) {
            echo "<table>\n<tbody>\n<tr>\n<td>\n";
            echo "<table>\n<tbody>\n<tr>\n<td><b>The Three Closest Stations</b></td>\n</tr>\n</tbody>\n</table>\n";
            echo "</td>\n</tr>\n<tr>\n<td>\n";
            echo "<table border=\"1\">\n<thead>\n<tr>\n<td>&nbsp;<b>Show</b>&nbsp;</td>\n<td>&nbsp;<b>Station Name</b></td>\n<td>&nbsp;<b>Subway Lines</b></td>\n</tr>\n</thead>\n";
            echo "<tbody>\n";
            foreach($stations as $loc) {
                $station = $loc['m'];
                echo "<tr>\n";
                printStation($station);
                echo "</tr>\n";
            }
            echo "</tbody>\n</table>\n";
            echo "</td>\n</tr>\n</tbody>\n</table>\n";
            echo "<br/>\n<br/>\n";
            $l = $_GET['l'];
            echo "Directions: [<a href=\"javascript:void(0)\" onclick=\"return _gd('to', '$l')\">To Here</a>] [<a href=\"javascript:void(0)\" onclick=\"return _gd('from', '$l')\">From Here</a>]\n";
        }
        else {
            echo "Sorry. Address could not be found. Make sure that it is a valid postal address, not a vanity address.<br/>\n"; 
        }
    }
    // directions
    else if ($_GET['type'] == 'dir') {
        if ((count($starts) == 1) && (count($ends) == 1)) {
            echo "Start Address:<br/>\n";
            $loc = $starts[0];
            $start_loc = $loc->desc;
            printAddress($loc, $saddr, $start_marker_img);
            echo "<br/>\n";
            echo "End Address:<br/>\n";
            $loc = $ends[0];
            $end_loc = $loc->desc;
            printAddress($loc, $daddr, $end_marker_img);
            echo "<br/>\n";
            if ($path) {
                if ($path == "walk" || $path == "same") {
                    $cpath = $path;
                }
                else {
                    $cpath = $path->path;
                }
                if ($cpath == "walk") {
                    echo "You don't need to take the subway. Why don't you walk instead?<br/>";
                }
                else if ($cpath == "same") {
                    echo "You're just testing the system, aren't you? Go do something more interesting, like telling your firends about this great site.<br/>";
                }
                else {
                    if (count($path->path) > 1) {
                        $cpath = consolidatePath($path, $_GET['type']);
                    }
                    printPath($cpath);
                }
                
                echo "<br/>\n";
                echo "<form id=\"form_pick\" action=\"$current_url\" method=\"get\">\n";
                // list alternate stations
                echo "<table border=\"1\">\n<tbody>\n<tr>\n<td colspan=\"4\"><b>Start Station Choices</b></td>\n</tr>\n<tr>\n<td>&nbsp;<b>Show</b>&nbsp;</td>\n<td><b>Station Name</b></td>\n<td><b>Subway Lines</b></td>\n</tr>\n";
                $num = 1;
                // grab the stations
                $start_stations = findNearestStations2($starts[0]);
                $start_station_marker = $path_array[0]->m2;
                foreach($start_stations as $station) {
                    $station_marker = $station->marker;
                    echo "<tr>\n";
                    printStation($station_marker);
                    echo "<td>\n";
                    if ($start_station_marker->id == $station_marker->id) {
                        echo "<input type=\"radio\" name=\"ssta\" value=\"$station_marker->id\" checked/>\n";
                    }
                    else {
                        echo "<input type=\"radio\" name=\"ssta\" value=\"$station_marker->id\" />\n";
                    }
                    echo "</td>\n";
                    echo "</tr>\n";
                    $num++;
                }
                // list alternate end stations
                $end_stations = findNearestStations2($ends[0]);
                $end_station_marker = $path_array[count($path_array) - 1]->m1;
                echo "<tr>\n<td colspan=\"4\"><b>End Station Choices</b></td>\n</tr>\n";
                echo "<tr>\n<td>&nbsp;<b>Show</b>&nbsp;</td>\n";
                echo "<td><b>Station Name</b></td>\n";
                echo "<td><b>Subway Lines</b></td>\n</tr>\n";
                $num = 1;
                foreach($end_stations as $station) {
                    $station_marker = $station->marker;
                    echo "<tr>\n";
                    printStation($station_marker);
                    echo "<td>\n";
                    if ($end_station_marker->id == $station_marker->id) {
                        echo "<input type=\"radio\" name=\"esta\" value=\"$station_marker->id\" checked/>\n";
                    }
                    else {
                        echo "<input type=\"radio\" name=\"esta\" value=\"$station_marker->id\" />\n";
                    }
                    echo "</td>\n</tr>\n";
                    $num++;
                }
                echo "</tbody>\n</table>\n";
                
                // geocoder leaves out intersections! we have to fill in with the query here
                correctHiddenValue($start_loc, $saddr, "saddr");
                // geocoder leaves out intersections! we have to fill in with the query here
                correctHiddenValue($end_loc, $daddr, "daddr");
                echo "<input type=\"hidden\" name=\"type\" value=\"dir\" />\n";
                echo "<br/>\n";
                echo "<input type=\"image\" name=\"submit\" src=\"images/usebutton.png\">\n";
                echo "</form><br/>\n";
            }
            else {
                echo "No Path Found.<br/>\n";
            }
        }
        // more than one location found
        else {
            echo "<form id=\"form_pick\" action=\"$current_url\" method=\"get\">\n";
            if (count($starts) > 1) {
                echo "Multiple Locations Found for Start Address:<br/><br/>\n";
                for ($i = 0; $i < count($starts); $i++) {
                    //$loc = $starts[$i];
                    $num = $i + 1;
                    correctRadioValue($starts[$i], $saddr, "saddr", $num);
                    echo "<br/>\n";
                }
            }
            else if (count($starts) == 1) {
                echo "Start Address:<br/>\n";
                $num = 1;
                correctRadioValue($starts[0], $saddr, "saddr", $num);
                echo "<br/>\n";
            }
            else {
                echo "No starting address found. Make sure that it is a valid postal address, not a vanity address.<br/>\n";
                echo "<input type=\"hidden\" name=\"saddr\" value=\"$saddr\" />\n";
            }
            echo "<br/>\n";
            if (count($ends) > 1) {
                echo "Multiple Locations Found for End Address:<br/><br/>\n";
                for ($i = 0; $i < count($ends); $i++) {
                    $num = $i + 1;
                    correctRadioValue($ends[$i], $daddr, "daddr", $num);
                    echo "<br/>\n";
                }
            }
            else if (count($ends) == 1) {
                echo "End Address:<br/>\n";
                $num = $num + 1;
                correctRadioValue($ends[0], $daddr, "daddr", $num);
                echo "<br/>\n";
            }
            else {
                echo "No destination address found. Make sure that it is a valid postal address, not a vanity address.<br/>\n";
                echo "<input type=\"hidden\" name=\"daddr\" value=\"$daddr\" />\n";
            }
            echo "<br/>\n";
            echo "<input type=\"image\" name=\"submit\" src=\"images/usebutton.png\" />\n";
            echo "<input type=\"hidden\" name=\"type\" value=\"dir\" />\n";
            echo "</form><br/>\n";
        }
    }
    // default page
    else {
        echo "<table>\n<thead>\n<tr>\n<td colspan=\"2\">\n";
        echo "<b>How To Use This Site</b>";
        echo "</td>\n</tr>\n</thead>\n";
        echo "<tbody>\n<tr style=\"background-color:#DDDDDD\">\n<td style=\"vertical-align:top\">\n";
        echo "<b>Station</b></td><td>Find the nearest subway station to your location. Simply enter the address and click <b>search</b>.\n";
        echo "</td>\n</tr>\n";
        echo "<tr>\n<td style=\"vertical-align:top\">\n";
        echo "<b>Station To Station</b></td><td>Find the shortest distance directions between two stations.\n";
        echo "</td>\n</tr>\n";
        echo "<tr style=\"background-color:#DDDDDD\">\n<td style=\"vertical-align:top\">\n";
        echo "<b>Directions</b></td><td>Need to get from A to B? Put the addresses into the fields and see the fastest way by subway!\n";
        echo "</td>\n</tr>\n</tbody>\n</table>\n";
?>
<div style="background-color:#ffeac0;padding:2px 5px 2px 5px;margin-top:10px">
<b>If you like this site, please tell your friends!</b> The site is written entirely by hand and funded by personal wallets, just to make travel in the Big Apple greater and better for you!<br/><br/><i>NOTE: Anything Google can find, we can find.</i>
</div>
<p>Great news!  After sacrificing a good proportion of our winter vacations, we are able to bring you a massive upgrade to our previous search algorithms.  The number of criteria being considered when searching for the optimal path has been drastically increased.  The search algorithm now considers distance traveled, time traveled, transfer time, walk distance, distance from stop in question to end point (only previous criteria), etc.</p>
<p>After completing the algorithm, copious tests were performed, and compared to our personal experience with those paths.  The tests performed were long paths (Columbia to Jamaica) and paths as short as one stop.  Our new algorithm has exceeded all expectations, and have even taught us faster routes than we ourselves knew.  We tried our best to optimize the speed at which these calculations are performed.</p>
<p>Additional, the drastic changes in our algorithm has caused the output to be significantly different.  Painstaking effort has been made such that the clean, crisp user interface has been preserved, and has been made more reliable.</p>
<p>This vacation has given ample time to plan the future of the sight.  Very exciting things are in store for us as the new year continues.</p>

<div style="background-color:#ffeac0;padding:2px 5px 2px 5px">
We need your help! If you run into an error, can you please email us the address(es) or stations you were using to search? This would help us immensely in debugging any problems the code might have. Thanks so much! Email: <a href="mailto:subwaymaps@gmail.com">subwaymaps@gmail.com</a>
</div>
<?php
    }
?>
</div>
</td>
</tr>
</tbody>
</table>
<br/><br/>
<div id="other_sites">
If you are in Boston, MA, you can use the same great features for the MBTA! Visit the sister site: <a href="http://www.bostonsubway.info">bostonsubway.info</a>
</div>

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
    
    //if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
        map.addControl(new GLargeMapControl());
        map.addControl(new GMapTypeControl());
        <?php
            echo "map.setCenter(new GLatLng($center_lat,$center_lng), $start_zoom);\n";
        ?>
    //}
	
	
// Creates a marker whose info window displays the given number
function createStation2(point, name, lines, icon) {
  var marker = new GMarker(point, icon);

  // Show this marker's index in the info window when it is clicked
  var html = "<b>" + name + "</b><br/>";
  for (var j = 0; j < lines.length; j++) {
  	// list each line by adding its gif to the html of the popup
    html = html + "<img width=\"20\" src=\"images/" + lines[j].toLowerCase() + ".gif\" /> ";
  }
  GEvent.addListener(marker, "click", function() {
    marker.openInfoWindowHtml(html);
  });

  return marker;
}


function createMarker2(point, name, icon) {
  var marker = new GMarker(point, icon);

  // Show this marker's index in the info window when it is clicked
  var html = "<b>" + name + "</b>";
  GEvent.addListener(marker, "click", function() {
    marker.openInfoWindowHtml(html);
  });

  return marker;
}
	
<?php
    // location search
    if ($_GET['type'] == 'location') {
    	if (count($locations) == 1) {
        	mapLocation($locations, $stations);
        }
    }
    // searching from station to station
    else if ($_GET['type'] == 's2s') {
        //mapPath($path, $cpath);
        if ($path != "walk" || $path != "same") {
            mapPath($path->path, $cpath);
        }
        // mark marker
        markMarker($start_marker, $start_marker_img, "start");
        markMarker($end_marker, $end_marker_img, "end");
        // center on start
        $lng = $start_marker->getLng();
        $lat = $start_marker->getLat();
	    echo "map.setCenter(new GLatLng($lat,$lng), $default_zoom);\n";
    }
    // searching for directions
    else if ($_GET['type'] == 'dir') {
        // plot starting point
        markLocation($starts[0], $start_marker_img, "start");
        if ($cpath != "same") {
            //plot ending point
            markLocation($ends[0], $end_marker_img, "end");
            if ($cpath != "walk") {
                if ($path != null) {
                    mapStations($start_stations, "start");
                    mapStations($end_stations, "end");
                    mapPath($path->path, $cpath);
                }
            }
        }
    }
?>
    //]]>
    </script>
</body>
</html>