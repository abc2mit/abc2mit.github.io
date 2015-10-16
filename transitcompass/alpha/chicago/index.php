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
    $website = "http://www.transitchicago.com/";
    $center_lng = -87.633780;
    $center_lat = 41.882695;
    $city = "Chicago";
    $state = "IL";
    $showmaplink = false;
    
    $location_example = "220 South Michigan Avenue, Chicago, IL 60604";
    $start_station_example = "LaSalle";
    $end_station_example = "Washington";
    $title = "The L | Chicago Transit Authority";
    $head_title = "transitcompass:chicago // ";
    $link = "/chicago";
    $logo = "logo.png";
    
    $description = "Directions via the Chicago Subway (L) using Google Maps.";
    $keywords = "geocode, subway, google maps, latitude, longitude, chicago, L, directions";
    
    $db_name = "digressi_chicago";
    connectDB($db_name);
    
    // markers must be initialised here.
    $markers = retrieveMarkers();
        
    include($oop . "/main.php");
?>
