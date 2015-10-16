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
    $website = "http://www.mbta.com/schedules_and_maps/subway/lines";
    $center_lng = -71.058651;
    $center_lat = 42.357341;
    $city = "Boston";
    $state = "MA";
    $showmaplink = false;
    
    $location_example = "400 Memorial Drive, Cambridge, MA 02139";
    $start_station_example = "Harvard Square";
    $end_station_example = "Roxbury Crossing";
    $title = "The T | Massachusetts Bay Transporation Authority";
    $head_title = "transitcompass:boston // ";
    $link = "/boston";
    $logo = "logo.png";
    
    $description = "Directions via Boston Subway (T) using Google Maps.";
    $keywords = "geocode, subway, google maps, latitude, longitude, boston, cambridge, T, directions";
    
    $db_name = "digressi_boston";
    connectDB($db_name);
    
    // markers must be initialised here.
    $markers = retrieveMarkers();
        
    include($oop . "/main.php");
?>
