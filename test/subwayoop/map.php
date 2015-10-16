<?php
    #
    #   Code copyright 2005-2008 Michael Ho
    #   Algorithm copyright 2008 Jason Gu and Michael Ho
    #   Unauthorized reproduction of this code and algorithm are prohibited by law.
    #
    #   map.php
    #   This file contains the logic to determine what code to display. Called from index.php
    #
    
    // location search
    if ($type == 'location') {
    	//if (count($locations) == 1) {
        	mapLocation($locations, $stations);
        //}
    }
    // searching from station to station
    else if ($type == 's2s') {
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
    else if ($type == 'dir' || $type == 'dir_alt') {
        // plot starting point
        if (count($starts) == 1 && count($ends) == 1) {
            if ($debug) {
                echo "//start location:\n//";
                $start_location->printInfo();
                echo "\n//end location:\n//";
                $end_location->printInfo();
                echo "\n";
            }
            markLocation($start_location, $start_marker_img, "start");
            if (is_array($cpath) && !is_null($path)) {
                //plot ending point
                markLocation($end_location, $end_marker_img, "end");
                mapStations($start_stations, "start");
                mapStations($end_stations, "end");
                mapPath($path->path, $cpath);
            }
            else if (is_string($cpath) && $cpath == "walk") {
                //plot ending point
                markLocation($end_location, $end_marker_img, "end");
            }
        }
        else {
            for ($i = 0; $i < count($starts); $i++) {
                markLocation($starts[$i], $start_marker_img, "start" . $i);
            }
            
            for ($i = 0; $i < count($ends); $i++) {
                markLocation($ends[$i], $end_marker_img, "end" . $i);
            }
        }
    }
?>