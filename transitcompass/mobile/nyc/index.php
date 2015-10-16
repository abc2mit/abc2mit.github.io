<?php
    #
    #   Code copyright 2005-2008 Michael Ho
    #   Algorithm copyright 2008 Jason Gu and Michael Ho
    #   Unauthorized reproduction of this code and algorithm are prohibited by law.
    #
    
    $oop = "../subwayoop";
    $xml = "../subwayxml";
    $db = "../subwaydb";
    require_once($oop . '/classes.php');
    require_once($db . '/db.php');
    // global variables
    $website = "http://mta.info/nyct/service/";
    $center_lng = -73.980584;
    $center_lat = 40.751532;
    $city = "New York";
    $state = "NY";
    $showmaplink = true;
    
    $location_example = "213 W. 32nd St.";
    $start_station_example = "14th St-Union Square";
    $end_station_example = "Bowling Green";
    $title = "New York City Subway | Metropolitan Transportation Authority";
    $head_title = "transitcompass:nyc // ";
    $link = "http://www.transitcompass.com/nyc";
    $logo = "logo.png";
    
    $description = "Directions via New York City (NYC) Subway (MTA) using Google Maps.";
    $keywords = "geocode, subway, google maps, latitude, longitude, new york, new york city, nyc, directions, mta, map";
    
    $db_name = "digressi_nyc";
    connectDB($db_name);
    $file = "nyc_time.xml";
    
    //database is an array of files
    include($xml . '/xmldb.php');
    // markers must be initialised here.
    $markers = array();
    init_db();
        
    include($oop . "/main.php");
?>
