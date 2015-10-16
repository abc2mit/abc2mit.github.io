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
	
	foreach ($markers as $marker) {
		$id = $marker->id;
		$lat = $marker->getLat();
		$lng = $marker->getLng();
		$name = $marker->name;
		$lines = $marker->lines;
		#$query = "INSERT INTO markers (id, lat, lng, name) VALUES ('" . $id . "', '" . $lat . "', '" . $lng . "', '" . $name . "')";
		#$result = mysql_query($query);
		foreach ($lines as $line) {
		  $connections = $line->connections;
          $line_name = $line->name;
		  foreach ($connections as $connection) {
              $cid = $connection->id;
              $type = $connection->type;
              $day = $connection->day;
              $start = $connection->start;
              $end = $connection->end;
              $duration = $connection->duration;
              #$query = "INSERT INTO connections (id, type, marker_id, day, start, end, duration, line) VALUES ('" . $cid . "', '" . $type . "', '" . $id . "', '" . $day . "', '" . $start . "', '" . $end . "', '" . $duration . "', '" . $line_name . "')";
              #$result = mysql_query($query);
		  }
		}
	}
        
    include($oop . "/main.php");
?>
